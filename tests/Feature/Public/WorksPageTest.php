<?php

use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponseCache\Facades\ResponseCache;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

it('renders published works and hides drafts', function () {
    Work::create(['title' => 'Public Reel', 'is_published' => true]);
    Work::create(['title' => 'Secret Reel', 'is_published' => false]);

    $this->get('/our-works')
        ->assertOk()
        ->assertSee('Public Reel')
        ->assertDontSee('Secret Reel');
});

it('renders a masonry grid and a clickable tile carrying its media payload', function () {
    $work = Work::create(['title' => 'Has Media']);
    $item = $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $item->addMedia(UploadedFile::fake()->image('a.jpg'))->toMediaCollection('file');

    $response = $this->get('/our-works')->assertOk();

    $response->assertSee('data-work-grid', false)
        ->assertSee('data-work-tile', false)
        ->assertSee($item->fresh()->getFirstMediaUrl('file'), false);
});

it('renders a work without media as a non-interactive tile', function () {
    Work::create(['title' => 'No Media Yet']);

    $this->get('/our-works')
        ->assertOk()
        ->assertSee('No Media Yet')
        ->assertDontSee('data-work-tile', false);
});

it('skips the work section entirely when there are no published works', function () {
    $this->get('/our-works')
        ->assertOk()
        ->assertDontSee('data-work-grid', false);
});

it('reflects newly added work media on the next request', function () {
    $work = Work::create(['title' => 'Live Update', 'is_published' => true]);

    $this->get('/our-works')->assertOk();

    $item = $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $item->addMedia(UploadedFile::fake()->image('new.jpg'))->toMediaCollection('file');

    $this->get('/our-works')
        ->assertOk()
        ->assertSee($item->fresh()->getFirstMediaUrl('file'), false);
});

it('clears the response cache when a work media row is saved', function () {
    $work = Work::create(['title' => 'Cache Save Test', 'is_published' => true]);

    ResponseCache::shouldReceive('clear')->once();

    $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
});

it('clears the response cache when a work media row is deleted', function () {
    $work = Work::create(['title' => 'Cache Delete Test', 'is_published' => true]);
    $item = $work->mediaItems()->create(['type' => 'image', 'order' => 1]);

    ResponseCache::shouldReceive('clear')->once();

    $item->delete();
});
