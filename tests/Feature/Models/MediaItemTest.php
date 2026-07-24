<?php

use App\Models\MediaItem;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
});

it('stores media items polymorphically', function () {
    expect(Schema::hasTable('media_items'))->toBeTrue()
        ->and(Schema::hasColumns('media_items', ['mediable_type', 'mediable_id', 'type', 'youtube_url', 'caption', 'order']))->toBeTrue()
        ->and(Schema::hasTable('work_media'))->toBeFalse();
});

it('attaches media items to a work through the shared relation', function () {
    $work = Work::create(['title' => 'Poly Work']);
    $work->mediaItems()->create(['type' => 'youtube', 'order' => 1, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);

    $item = MediaItem::firstOrFail();

    expect($item->mediable_type)->toBe(Work::class)
        ->and($item->mediable_id)->toBe($work->id)
        ->and($item->mediable->is($work))->toBeTrue()
        ->and($work->fresh()->mediaPayload())->toHaveCount(1);
});

it('keeps medialibrary files attached across the rename', function () {
    $work = Work::create(['title' => 'With File']);
    $item = $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $item->addMedia(UploadedFile::fake()->image('a.jpg'))->toMediaCollection('file');

    expect($item->fresh()->getFirstMediaUrl('file'))->not->toBe('')
        ->and($work->fresh()->coverUrl())->toBe($item->fresh()->getFirstMediaUrl('file'));
});
