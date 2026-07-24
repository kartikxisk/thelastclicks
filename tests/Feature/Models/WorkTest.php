<?php

use App\Models\MediaItem;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
});

it('generates a slug and scopes published works', function () {
    $published = Work::create(['title' => 'Navy Film', 'is_published' => true]);
    Work::create(['title' => 'Hidden One', 'is_published' => false]);

    expect($published->slug)->toBe('navy-film')
        ->and(Work::published()->pluck('title')->all())->toBe(['Navy Film']);
});

it('extracts youtube ids from every common url form', function (string $url) {
    $m = new MediaItem(['type' => 'youtube', 'youtube_url' => $url]);

    expect($m->youtubeId())->toBe('dQw4w9WgXcQ')
        ->and($m->embedUrl())->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->and($m->thumbnailUrl())->toBe('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg');
})->with([
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'https://www.youtube.com/watch?list=xyz&v=dQw4w9WgXcQ',
    'https://youtu.be/dQw4w9WgXcQ',
    'https://www.youtube.com/embed/dQw4w9WgXcQ',
    'https://www.youtube.com/shorts/dQw4w9WgXcQ',
]);

it('returns null for an unparseable youtube url', function () {
    $m = new MediaItem(['type' => 'youtube', 'youtube_url' => 'https://example.com/nope']);

    expect($m->youtubeId())->toBeNull()
        ->and($m->embedUrl())->toBeNull();
});

it('builds an ordered media payload and skips unresolvable rows', function () {
    $work = Work::create(['title' => 'Mixed Reel']);

    $img = $work->mediaItems()->create(['type' => 'image', 'order' => 1, 'caption' => 'Still']);
    $img->addMedia(UploadedFile::fake()->image('shot.jpg'))->toMediaCollection('file');

    $work->mediaItems()->create(['type' => 'youtube', 'order' => 2, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    // no file attached -> skipped
    $work->mediaItems()->create(['type' => 'video', 'order' => 3]);
    // unparseable -> skipped
    $work->mediaItems()->create(['type' => 'youtube', 'order' => 4, 'youtube_url' => 'nope']);

    $payload = $work->fresh()->mediaPayload();

    expect($payload)->toHaveCount(2)
        ->and($payload[0]['type'])->toBe('image')
        ->and($payload[0]['caption'])->toBe('Still')
        ->and($payload[1]['type'])->toBe('youtube')
        ->and($payload[1]['url'])->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
});

it('falls back through cover, first image, then youtube thumbnail', function () {
    $work = Work::create(['title' => 'Fallbacks']);
    expect($work->coverUrl())->toBeNull();

    $yt = $work->mediaItems()->create(['type' => 'youtube', 'order' => 2, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    expect($work->fresh()->coverUrl())->toBe('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg');

    $img = $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $img->addMedia(UploadedFile::fake()->image('first.jpg'))->toMediaCollection('file');
    expect($work->fresh()->coverUrl())->toBe($img->fresh()->getFirstMediaUrl('file'));

    $work->addMedia(UploadedFile::fake()->image('cover.jpg'))->toMediaCollection('cover');
    expect($work->fresh()->coverUrl())->toBe($work->fresh()->getFirstMediaUrl('cover'));
});

it('deletes child media rows and their media records with the work', function () {
    $work = Work::create(['title' => 'Cascade']);
    $item = $work->mediaItems()->create(['type' => 'image']);
    $item->addMedia(UploadedFile::fake()->image('gone.jpg'))->toMediaCollection('file');

    expect(Media::count())->toBe(1);

    $work->delete();

    expect(MediaItem::count())->toBe(0)
        ->and(Media::count())->toBe(0);
});
