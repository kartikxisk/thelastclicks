<?php

use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // A reachable media disk, so each test isolates the setting it cares about.
    config(['media-library.disk_name' => 's3']);
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
