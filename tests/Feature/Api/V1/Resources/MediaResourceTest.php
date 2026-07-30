<?php

use App\Http\Resources\Api\V1\MediaItemResource;
use App\Http\Resources\Api\V1\MediaResource;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

it('returns null for a missing media record', function () {
    // Not make(): JsonResource::resolve() casts null to [], which would reach
    // the frontend as an empty object on a field typed `Media | null`.
    expect(MediaResource::nullable(null))->toBeNull();
});

it('shapes a media record with url, dimensions and mime', function () {
    $work = Work::create(['title' => 'Media Fixture', 'slug' => 'media-fixture']);
    // A real image, not addMediaFromString — medialibrary sniffs the bytes, so
    // a text payload named .jpg registers as text/plain and proves nothing.
    $work->addMedia(UploadedFile::fake()->image('cover.jpg', 800, 600))
        ->toMediaCollection('cover');

    $shaped = MediaResource::make($work->getFirstMedia('cover'))->resolve(Request::create('/'));

    expect($shaped)->toHaveKeys(['url', 'srcset', 'width', 'height', 'mime', 'alt']);
    expect($shaped['url'])->toStartWith('http');
    expect($shaped['mime'])->toBe('image/jpeg');
});

it('shapes youtube media items with an embed url and poster', function () {
    $work = Work::create(['title' => 'Media Fixture', 'slug' => 'media-fixture']);
    $work->mediaItems()->create([
        'type' => 'youtube',
        'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'caption' => 'Behind the scenes',
        'order' => 0,
    ]);

    $shaped = MediaItemResource::collection($work->fresh()->mediaItems)
        ->resolve(Request::create('/'));

    expect($shaped)->toHaveCount(1);
    expect($shaped[0]['type'])->toBe('youtube');
    expect($shaped[0]['url'])->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
    expect($shaped[0]['poster'])->toContain('dQw4w9WgXcQ');
    expect($shaped[0]['caption'])->toBe('Behind the scenes');
});

it('drops media items that resolve to no url', function () {
    $work = Work::create(['title' => 'Media Fixture', 'slug' => 'media-fixture']);
    $work->mediaItems()->create([
        'type' => 'youtube',
        'youtube_url' => 'not-a-youtube-url',
        'order' => 0,
    ]);

    $shaped = MediaItemResource::collection($work->fresh()->mediaItems)
        ->resolve(Request::create('/'));

    expect($shaped)->toBeEmpty();
});
