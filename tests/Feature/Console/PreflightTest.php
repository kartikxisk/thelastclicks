<?php

use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // A reachable media disk WITH a public base URL, so each test isolates the
    // setting it cares about. Faking the disk alone is not a healthy baseline:
    // reachable and displayable are different things, and the url is what turns
    // a stored key into something a browser can load.
    config([
        'media-library.disk_name' => 's3',
        'filesystems.disks.s3.url' => 'https://cdn.thelastclicks.com',
    ]);
    Storage::fake('s3');
});

/** Put the app in production with an otherwise healthy config. */
function asProduction(array $overrides = []): void
{
    app()['env'] = 'production';
    config(array_merge([
        'app.url' => 'https://thelastclicks.com',
        'app.debug' => false,
        'queue.default' => 'redis',
    ], $overrides));
}

it('passes on a healthy production config', function () {
    asProduction();

    $this->artisan('app:preflight')->assertExitCode(0);
});

it('fails when APP_URL is still local in production', function (string $url) {
    asProduction(['app.url' => $url]);

    $this->artisan('app:preflight')
        ->expectsOutputToContain('do not serve this build publicly')
        ->assertExitCode(1);
})->with([
    'http://localhost',
    'http://127.0.0.1:8000',
    'https://thelastclicks.test',
]);

it('fails when APP_URL is not https in production', function () {
    asProduction(['app.url' => 'http://thelastclicks.com']);

    $this->artisan('app:preflight')->assertExitCode(1);
});

it('fails when debug is left on in production', function () {
    asProduction(['app.debug' => true]);

    $this->artisan('app:preflight')->assertExitCode(1);
});

it('fails when the media disk cannot be reached', function () {
    asProduction(['media-library.disk_name' => 'does-not-exist']);

    $this->artisan('app:preflight')->assertExitCode(1);
});

it('fails when the media disk has no public url', function () {
    // The disk is reachable, so checkMediaDisk() is happy — but with no url
    // MediaUrl falls through to a direct bucket URL that 403s on a private
    // bucket. This is the "images work locally, not on prod" shape: AWS_URL is
    // set on the machine that works and missing on the one that does not.
    asProduction(['filesystems.disks.s3.url' => null]);

    $this->artisan('app:preflight')
        ->expectsOutputToContain('do not serve this build publicly')
        ->assertExitCode(1);
});

it('fails when the media disk url is not absolute', function () {
    asProduction(['filesystems.disks.s3.url' => '/uploads']);

    $this->artisan('app:preflight')->assertExitCode(1);
});

it('accepts the public disk without a configured url', function () {
    // Served from the app origin, so there is nothing to configure. Asserted on
    // this check's own output rather than the exit code: two other production
    // checks fail in the test environment for unrelated reasons, so an exit-code
    // assertion here would be testing those instead of this.
    asProduction(['media-library.disk_name' => 'public']);

    $this->artisan('app:preflight')
        ->expectsOutputToContain('served from the app origin');
});

it('only warns about a local APP_URL outside production', function () {
    config(['app.url' => 'http://localhost', 'app.debug' => true]);

    // Local dev is allowed to look like local dev.
    $this->artisan('app:preflight')->assertExitCode(0);
});

it('warns but does not fail on a sync queue, unless strict', function () {
    asProduction(['queue.default' => 'sync']);

    $this->artisan('app:preflight')->assertExitCode(0);
    $this->artisan('app:preflight', ['--strict' => true])->assertExitCode(1);
});
