<?php

use App\Models\HeroSlide;
use App\Models\SiteSetting;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

it('renders no hero background when the admin has no active slide', function () {
    // HeroSlidesSeeder is deliberately outside db:seed, so the seeded state is
    // the empty-admin state. There is no bundled fallback reel: an empty admin
    // must produce an empty hero, not footage the editor cannot replace.
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->not->toContain('hero__bg')
        ->and($html)->not->toContain('hero-reel.mp4');
});

it('still renders the hero copy with no background slide', function () {
    // The background going away must not take the headline or the CTAs with it.
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('class="hero__title"');
});

it('renders the background once a slide is active', function () {
    $slide = HeroSlide::create(['label' => 'Reel', 'order' => 0, 'is_active' => true]);
    $slide->addMedia(UploadedFile::fake()->create('reel.mp4', 64, 'video/mp4'))
        ->toMediaCollection('asset');

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('hero__bg')
        ->and($html)->toContain('hero__slide');
});

it('ignores a slide that is switched off in the admin', function () {
    $slide = HeroSlide::create(['label' => 'Off', 'order' => 0, 'is_active' => false]);
    $slide->addMedia(UploadedFile::fake()->create('off.mp4', 64, 'video/mp4'))
        ->toMediaCollection('asset');

    expect($this->get('/')->assertOk()->getContent())->not->toContain('hero__bg');
});

it('ignores an active slide whose asset never uploaded', function () {
    // A row created in the admin but abandoned before the upload finished would
    // otherwise render an empty layer and the scrim over nothing.
    HeroSlide::create(['label' => 'Empty', 'order' => 0, 'is_active' => true]);

    expect($this->get('/')->assertOk()->getContent())->not->toContain('hero__bg');
});

/**
 * Leading the hero with the studio's own featured work.
 *
 * The empty-admin rule above still holds: nothing appears on its own. This is
 * a source an editor picks in Site Settings, and the material it reaches for
 * is the studio's own published work rather than a bundled asset — which is
 * what the no-fallback rule was written to keep out.
 */
it('leads the hero with featured work once the admin picks that source', function () {
    SiteSetting::set('hero_source', 'work');

    $work = Work::create([
        'title' => 'Jaisalmer Craft Gin',
        'slug' => 'jaisalmer-craft-gin',
        'is_published' => true,
        'is_featured' => true,
        'preview_video_url' => 'https://cdn.example.com/previews/jaisalmer.mp4',
    ]);
    $work->addMedia(UploadedFile::fake()->image('cover.jpg'))->toMediaCollection('cover');

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('hero__bg')
        ->and($html)->toContain('hero__slide')
        ->and($html)->toContain('https://cdn.example.com/previews/jaisalmer.mp4');
});

it('leaves the hero empty when the work source has no featured work to show', function () {
    // Picking the source is not a promise that something exists behind it.
    SiteSetting::set('hero_source', 'work');

    Work::create(['title' => 'Not featured', 'slug' => 'not-featured', 'is_published' => true]);

    expect($this->get('/')->assertOk()->getContent())->not->toContain('hero__bg');
});

it('keeps unrecognised hero sources on the uploaded slides', function () {
    // Same allowlist reasoning as WORK_TILE_RATIOS: the value drives what the
    // page renders, so anything unknown falls back rather than guessing.
    SiteSetting::set('hero_source', 'whatever-someone-typed');

    expect(SiteSetting::heroSource())->toBe(SiteSetting::DEFAULT_HERO_SOURCE);
});
