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

it('renders each seeded industry with its title and summary', function () {
    $industry = Industry::orderBy('order')->firstOrFail();

    $this->get('/industries')
        ->assertOk()
        ->assertSee($industry->title)
        ->assertSee($industry->summary);
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
