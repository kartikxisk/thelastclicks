<?php

use App\Models\Portfolio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('portfolio index shows only published items', function () {
    Portfolio::factory()->create(['status' => 'draft', 'title' => 'HiddenDraft']);
    $r = $this->get('/portfolio')->assertOk();
    $r->assertDontSee('HiddenDraft');
});

it('portfolio detail renders by slug', function () {
    $p = Portfolio::published()->first();
    $this->get('/portfolio/'.$p->slug)->assertOk()->assertSee($p->title);
});

it('portfolio detail 404 on unknown slug', function () {
    $this->get('/portfolio/nope')->assertNotFound();
});

it('portfolio detail 404 on draft', function () {
    $p = Portfolio::factory()->create(['status' => 'draft']);
    $this->get('/portfolio/'.$p->slug)->assertNotFound();
});

it('portfolio index lists seeded real cases', function () {
    $this->get('/portfolio')->assertOk()->assertSee('Indian Navy');
});

it('renders gallery videos from media with the cover as poster', function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');

    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    // UploadedFile::fake()->create() writes no real bytes, so MediaLibrary's
    // content-sniffed mime_type would detect as application/x-empty; force it
    // to reflect what a real mp4 upload would resolve to.
    $portfolio->addMedia(UploadedFile::fake()->create('film.mp4', 100, 'video/mp4'))
        ->withAttributes(['mime_type' => 'video/mp4'])
        ->toMediaCollection('gallery');
    $portfolio->addMedia(UploadedFile::fake()->image('poster.jpg'))
        ->toMediaCollection('cover');

    $this->get('/portfolio/'.$portfolio->slug)
        ->assertOk()
        ->assertSee($portfolio->getFirstMediaUrl('gallery'), false)
        ->assertSee('poster="'.$portfolio->getFirstMediaUrl('cover').'"', false)
        ->assertDontSee('/videos/posters/', false);
});
