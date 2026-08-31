<?php

use App\Models\Industry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\ResponseCache\Facades\ResponseCache;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

// Industry detail pages are live again — see IndustryShowTest for the page
// itself. What is left here is the deck: what it lists, how its tiles link, and
// the cache invalidation behind it.
//
// Assertions read the seeded rows rather than naming a vertical. The taxonomy
// has been rewritten twice; a test that pins "Fashion" only records which
// version it was written against.

it('industry index lists seeded industries', function () {
    $first = Industry::orderBy('order')->firstOrFail();

    $this->get('/industries')->assertOk()->assertSee($first->title);
});

it('shows every industry title on the index, and no summaries', function () {
    $industry = Industry::orderBy('order')->firstOrFail();

    // Title-only cards. The summary belongs to the detail page, which now leads
    // with it — repeating it on the deck would just be duplicate copy.
    $this->get('/industries')
        ->assertOk()
        ->assertSee($industry->title)
        ->assertDontSee($industry->summary);
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

it('links each industry tile to its own page, not to the quote wizard', function () {
    $industry = Industry::orderBy('order')->firstOrFail();

    $response = $this->get('/industries')->assertOk();

    // The tiles were quote-modal triggers while detail pages were retired. Now
    // that each vertical argues its own case, the deck leads there instead; the
    // wizard is one click away from the CTA at the foot of that page.
    $response->assertSee('href="'.url('/industries/'.$industry->slug).'"', false);
    expect($response->getContent())->not->toContain('data-quote-prefill=\''.e($industry->title).'\'');

    // Still anchors rather than lightbox tiles — that half of the contract did
    // not change when the destination did.
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
