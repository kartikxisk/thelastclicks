<?php

use App\Models\HeroSlide;
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
