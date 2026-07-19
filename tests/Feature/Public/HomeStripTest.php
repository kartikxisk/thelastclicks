<?php

use App\Models\Portfolio;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

function attachFilm(Portfolio $portfolio): void
{
    $portfolio->addMedia(UploadedFile::fake()->create('film.mp4', 100, 'video/mp4'))
        ->toMediaCollection('gallery');
    $portfolio->addMedia(UploadedFile::fake()->image('poster.jpg'))
        ->toMediaCollection('cover');
}

it('renders strip cards from settings using media urls', function () {
    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    attachFilm($portfolio);

    $this->get('/')
        ->assertOk()
        ->assertSee($portfolio->getFirstMediaUrl('gallery'), false)
        ->assertSee($portfolio->getFirstMediaUrl('cover'), false)
        ->assertSee('001 · Defence · 2026');
});

it('skips strip entries whose portfolio has no media', function () {
    // no media attached at all -> no strip cards, page still renders
    $this->get('/')
        ->assertOk()
        ->assertDontSee('data-strip-video');
});

it('skips strip entries whose slug does not resolve', function () {
    SiteSetting::set('home_strip', [
        ['portfolio_slug' => 'nope', 'tag' => 'X', 'title' => 'X', 'meta' => 'X'],
    ]);

    $this->get('/')->assertOk()->assertDontSee('data-strip-video');
});

it('renders hero tiles from settings using media urls', function () {
    $portfolio = Portfolio::where('slug', 'salesforce-blr')->firstOrFail();
    attachFilm($portfolio);

    $response = $this->get('/')->assertOk();

    $response->assertSee($portfolio->getFirstMediaUrl('gallery'), false);
    expect(substr_count($response->getContent(), '<div class="tile">'))->toBe(2); // 1 video tile + the static img tile
});

it('hero renders without video tiles when no media exists', function () {
    $this->get('/')->assertOk()->assertDontSee('videos/ins-navy-blackdog.mp4');
});
