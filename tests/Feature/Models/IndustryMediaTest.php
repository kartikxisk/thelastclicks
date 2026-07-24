<?php

use App\Models\Industry;
use App\Models\MediaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

it('builds an ordered media payload for an industry', function () {
    $industry = Industry::firstOrFail();

    $img = $industry->mediaItems()->create(['type' => 'image', 'order' => 1, 'caption' => 'On set']);
    $img->addMedia(UploadedFile::fake()->image('set.jpg'))->toMediaCollection('file');
    $industry->mediaItems()->create(['type' => 'youtube', 'order' => 2, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    $industry->mediaItems()->create(['type' => 'video', 'order' => 3]); // no file -> skipped

    $payload = $industry->fresh()->mediaPayload();

    expect($payload)->toHaveCount(2)
        ->and($payload[0]['caption'])->toBe('On set')
        ->and($payload[1]['url'])->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
});

it('leads with the curated still: hero upload, then image_url, over gallery media', function () {
    $industry = Industry::firstOrFail();

    // A seeded industry carries a curated image_url and no media.
    expect($industry->coverUrl())->toBe($industry->image_url);

    // Gallery media must NOT hijack the curated still.
    $industry->mediaItems()->create(['type' => 'youtube', 'order' => 2, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    expect($industry->fresh()->coverUrl())->toBe($industry->image_url);

    // An uploaded hero outranks even the curated image_url.
    $industry->addMedia(UploadedFile::fake()->image('hero.jpg'))->toMediaCollection('hero');
    expect($industry->fresh()->coverUrl())->toBe($industry->fresh()->getFirstMediaUrl('hero'));
});

it('derives an industry cover from the media array when no still is set', function () {
    $industry = Industry::factory()->create(['image_url' => null]);

    // First a YouTube thumbnail…
    $industry->mediaItems()->create(['type' => 'youtube', 'order' => 2, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    expect($industry->fresh()->coverUrl())->toBe('https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg');

    // …then an image row wins over the YouTube thumbnail.
    $img = $industry->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $img->addMedia(UploadedFile::fake()->image('first.jpg'))->toMediaCollection('file');
    expect($industry->fresh()->coverUrl())->toBe($img->fresh()->getFirstMediaUrl('file'));
});

it('removes an industry media rows and their files when the industry is deleted', function () {
    $industry = Industry::firstOrFail();
    $item = $industry->mediaItems()->create(['type' => 'image']);
    $item->addMedia(UploadedFile::fake()->image('gone.jpg'))->toMediaCollection('file');

    $industry->delete();

    expect(MediaItem::count())->toBe(0)
        ->and(Media::count())->toBe(0);
});
