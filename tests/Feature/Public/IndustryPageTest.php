<?php

use App\Models\Industry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\ResponseCache\Facades\ResponseCache;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('industry index lists seeded industries', function () {
    $this->get('/industries')->assertOk()->assertSee('Fashion');
});

it('renders an industry detail page for a valid slug', function () {
    $industry = Industry::orderBy('order')->firstOrFail();

    $this->get('/industries/'.$industry->slug)
        ->assertOk()
        ->assertSee($industry->title);
});

it('returns 404 for an unknown industry slug', function () {
    $this->get('/industries/not-a-real-industry')->assertNotFound();
});

it('labels a gallery tile with its caption and hides the decorative poster', function () {
    $industry = Industry::orderBy('order')->firstOrFail();
    $industry->mediaItems()->create([
        'type' => 'youtube',
        'order' => 1,
        'caption' => 'Diwali brand film',
        'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
    ]);

    $html = $this->get('/industries/'.$industry->slug)->assertOk()->getContent();

    // Accessible name mirrors the visible caption (WCAG 2.5.3 label-in-name).
    expect($html)->toContain('aria-label="Play — Diwali brand film"');
});

it('shows each industry title on the index, and the summary on its detail page', function () {
    $industry = Industry::orderBy('order')->firstOrFail();

    // The "What we cover" cards are title-only now — the summary lives on the
    // detail page, not the grid.
    $this->get('/industries')
        ->assertOk()
        ->assertSee($industry->title)
        ->assertDontSee($industry->summary);

    $this->get('/industries/'.$industry->slug)
        ->assertOk()
        ->assertSee($industry->title)
        ->assertSee($industry->summary);
});

it('gives every seeded industry a cover so the grid is never blank', function () {
    // Regression: industries shipped with image_url = null once, so every card
    // on /industries rendered as an empty dark tile on a fresh deploy.
    Industry::all()->each(function (Industry $industry): void {
        expect($industry->coverUrl())->not->toBeNull()
            ->and($industry->image_url)->toStartWith('industries/');
    });

    $html = $this->get('/industries')->assertOk()->getContent();

    // Every tile must render its cover <img>, not just an empty dark box.
    Industry::all()->each(
        fn (Industry $industry) => expect($html)->toContain('src="'.$industry->coverUrl().'"')
    );
});

it('renders each industry tile as a link to its detail page', function () {
    $industry = Industry::orderBy('order')->firstOrFail();

    $response = $this->get('/industries')->assertOk();

    // Industry tiles navigate to a detail page — they are anchors, not lightbox tiles.
    $response->assertSee('href="'.url('/industries/'.$industry->slug).'"', false);
    expect(substr_count($response->getContent(), 'data-work-tile'))->toBe(0);
});

it('clears the response cache when an industry media row is saved', function () {
    $industry = Industry::orderBy('order')->firstOrFail();

    ResponseCache::shouldReceive('clear')->once();

    $industry->mediaItems()->create(['type' => 'image', 'order' => 1]);
});

it('clears the response cache when an industry media row is deleted', function () {
    $industry = Industry::orderBy('order')->firstOrFail();
    $item = $industry->mediaItems()->create(['type' => 'image', 'order' => 1]);

    ResponseCache::shouldReceive('clear')->once();

    $item->delete();
});
