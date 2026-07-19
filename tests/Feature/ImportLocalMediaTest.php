<?php

use App\Models\Portfolio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');

    $this->source = storage_path('framework/testing/legacy-videos');
    File::ensureDirectoryExists($this->source.'/posters');
    File::put($this->source.'/ins-navy-blackdog.mp4', 'fake-video');
    File::put($this->source.'/posters/ins-navy-blackdog.jpg', 'fake-poster');

    $this->seed();
});

afterEach(fn () => File::deleteDirectory($this->source));

it('attaches legacy files to the mapped portfolio and uploads to the media disk', function () {
    $this->artisan('media:import-local', ['--source' => $this->source])->assertSuccessful();

    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();

    expect($portfolio->getFirstMedia('cover'))->not->toBeNull()
        ->and($portfolio->getFirstMedia('gallery'))->not->toBeNull()
        ->and($portfolio->getFirstMedia('gallery')->disk)->toBe('s3');

    Storage::disk('s3')->assertExists(
        $portfolio->getFirstMedia('gallery')->getPathRelativeToRoot()
    );
});

it('is idempotent — second run attaches nothing new', function () {
    $this->artisan('media:import-local', ['--source' => $this->source])->assertSuccessful();
    $this->artisan('media:import-local', ['--source' => $this->source])->assertSuccessful();

    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    expect($portfolio->getMedia('gallery'))->toHaveCount(1)
        ->and($portfolio->getMedia('cover'))->toHaveCount(1);
});

it('skips portfolios whose source files are missing without failing', function () {
    // source dir only contains ins-navy files; the other 8 mapped portfolios must be skipped
    $this->artisan('media:import-local', ['--source' => $this->source])->assertSuccessful();

    expect(Portfolio::where('slug', 'range-rover')->firstOrFail()->getMedia('gallery'))->toHaveCount(0);
});

it('imports files larger than the old 10 MB default limit', function () {
    File::put($this->source.'/range-rover.mp4', str_repeat('x', 11 * 1024 * 1024));
    File::put($this->source.'/posters/range-rover.jpg', 'fake-poster');

    $this->artisan('media:import-local', ['--source' => $this->source])->assertSuccessful();

    expect(Portfolio::where('slug', 'range-rover')->firstOrFail()->getMedia('gallery'))->toHaveCount(1);
});

it('migrates existing public-disk media rows to the media disk', function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $portfolio = Portfolio::where('slug', 'diwali-motion')->firstOrFail();
    $portfolio->addMedia(UploadedFile::fake()->image('old.jpg'))->toMediaCollection('cover');

    config(['media-library.disk_name' => 's3']);
    $this->artisan('media:import-local', ['--source' => $this->source])->assertSuccessful();

    $media = $portfolio->fresh()->getFirstMedia('cover');
    expect($media->disk)->toBe('s3')
        ->and($media->conversions_disk)->toBe('s3');
    Storage::disk('s3')->assertExists($media->getPathRelativeToRoot());
});
