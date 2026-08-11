<?php

use App\Models\Industry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\ResponseCache\Facades\ResponseCache;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('industry index lists seeded industries', function () {
    $this->get('/industries')->assertOk()->assertSee('Fashion');
});

// Industry detail pages were retired: routes/web.php 301s /industries/{slug} to
// the index, and a tile now opens the quote wizard pre-filled with its industry.
// These four tests asserted the detail page still rendered, which is why they
// failed on a route that had deliberately become a redirect.

it('redirects a retired industry detail page to the index', function () {
    $industry = Industry::orderBy('order')->firstOrFail();

    $this->get('/industries/'.$industry->slug)
        ->assertStatus(301)
        ->assertRedirect('/industries');
});

it('redirects an unknown industry slug rather than 404ing', function () {
    // The redirect is a wildcard, so it catches slugs that never existed too.
    // Deliberate: every old /industries/* URL lands somewhere useful.
    $this->get('/industries/not-a-real-industry')
        ->assertStatus(301)
        ->assertRedirect('/industries');
});

it('shows every industry title on the index, and no summaries', function () {
    $industry = Industry::orderBy('order')->firstOrFail();

    // Title-only cards. The summary had a home on the detail page; with that
    // gone it is admin-managed copy that no public page renders.
    $this->get('/industries')
        ->assertOk()
        ->assertSee($industry->title)
        ->assertDontSee($industry->summary);
});

it('opens the quote wizard from an industry tile instead of a detail page', function () {
    $industry = Industry::orderBy('order')->firstOrFail();

    $this->get('/industries')
        ->assertOk()
        // href stays a real URL so the tile still works without JS.
        ->assertSee('data-quote-prefill=\''.e($industry->title).'\'', false);
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

it('renders each industry tile as a real link, not a lightbox tile', function () {
    $response = $this->get('/industries')->assertOk();

    // Still anchors rather than lightbox tiles — that part of the contract did
    // not change when detail pages went; only the destination did, from
    // /industries/{slug} to the contact page carrying the prefill.
    $response->assertSee('href="'.url('/contact').'"', false);
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
