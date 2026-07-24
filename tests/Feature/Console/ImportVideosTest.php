<?php

use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
});

it('uploads bundled videos to the media disk, preserving their paths', function () {
    $this->artisan('videos:import', ['--only' => 'hero-reel'])->assertExitCode(0);

    Storage::disk('s3')->assertExists('videos/hero-reel.mp4');
    Storage::disk('s3')->assertExists('videos/posters/hero-reel.jpg');
})->skip(
    fn () => ! is_file(public_path('videos/hero-reel.mp4')),
    'No bundled hero reel in public/videos/',
);

it('skips anything already on the disk so it is safe to re-run', function () {
    $this->artisan('videos:import', ['--only' => 'hero-reel'])->assertExitCode(0);

    $this->artisan('videos:import', ['--only' => 'hero-reel'])
        ->expectsOutputToContain('skip')
        ->assertExitCode(0);
})->skip(
    fn () => ! is_file(public_path('videos/hero-reel.mp4')),
    'No bundled hero reel in public/videos/',
);

it('honours the --only filter', function () {
    $this->artisan('videos:import', ['--only' => 'no-such-file'])->assertExitCode(0);

    expect(Storage::disk('s3')->allFiles())->toBeEmpty();
});
