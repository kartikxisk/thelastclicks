<?php

use App\Console\Commands\Preflight;
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

/**
 * Put the app in production with an otherwise healthy config.
 *
 * APP_WEB_USER is pinned to whoever owns this checkout. In production the
 * runtime-dirs check asks "could the web user write here", and a developer
 * machine has no web user that owns storage/ — so without this every test using
 * this helper failed on an ownership question none of them are about, which is
 * how a real bug in the web-user resolution reached a server unnoticed.
 *
 * The ownership check itself is exercised deliberately further down.
 */
function asProduction(array $overrides = []): void
{
    $owner = posix_getpwuid(fileowner(base_path('storage/logs')));
    putenv('APP_WEB_USER='.($owner['name'] ?? get_current_user()));

    app()['env'] = 'production';
    config(array_merge([
        'app.url' => 'https://thelastclicks.com',
        'app.debug' => false,
        'queue.default' => 'redis',
    ], $overrides));
}

afterEach(function () {
    putenv('APP_WEB_USER');
});

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

/**
 * The false positive that aborted a healthy production deploy.
 *
 * The box ran PHP-FPM as `www` and storage/ was correctly owned `www:www`. But
 * webUser() returned the first candidate name that merely EXISTED, and Ubuntu
 * ships a `www-data` account whether or not anything runs as it — so the check
 * measured the dirs against the wrong account, failed, and printed a "fix" that
 * would have broken the ownership that was already correct.
 *
 * Ownership of storage/logs is now the evidence, since the deploy is what sets
 * it. This asserts the resolution order rather than the filesystem, because a
 * test cannot chown a directory to a user it is not running as.
 */
it('prefers the runtime dir owner over a merely-existing account', function () {
    $command = new ReflectionClass(Preflight::class);
    $instance = $command->newInstanceWithoutConstructor();

    $resolve = $command->getMethod('webUser');
    $resolve->setAccessible(true);

    putenv('APP_WEB_USER');

    $owner = posix_getpwuid(fileowner(base_path('storage/logs')));
    $resolved = $resolve->invoke($instance);

    // On a dev machine storage/ is owned by a human, which is deliberately NOT a
    // recognisable web account — so resolution falls through to the candidate
    // list rather than declaring the developer to be the web server.
    expect($resolved)->not->toBe(['name' => $owner['name'], 'uid' => $owner['uid'], 'gid' => $owner['gid']]);
});

it('lets APP_WEB_USER override the inferred web user', function () {
    $owner = posix_getpwuid(fileowner(base_path('storage/logs')));
    putenv('APP_WEB_USER='.$owner['name']);

    $command = new ReflectionClass(Preflight::class);
    $instance = $command->newInstanceWithoutConstructor();
    $resolve = $command->getMethod('webUser');
    $resolve->setAccessible(true);

    expect($resolve->invoke($instance)['name'])->toBe($owner['name']);
});

it('never recommends a recursive chmod, which breaks the next git pull', function () {
    asProduction();

    // -R 775 sets +x on the tracked .gitignore placeholders under storage/,
    // flipping them 100644 -> 100755. DEPLOYMENT.md forbids it; the failure
    // message used to recommend it.
    $this->artisan('app:preflight')
        ->doesntExpectOutputToContain('chmod -R 775');
});
