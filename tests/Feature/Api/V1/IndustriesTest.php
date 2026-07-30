<?php

use App\Models\Industry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('lists industries in admin order with nested testimonials', function () {
    $response = $this->getJson('/api/v1/industries')->assertOk();

    expect(collect($response->json('data'))->pluck('slug')->all())
        ->toBe(Industry::orderBy('order')->orderBy('id')->pluck('slug')->all());

    $response->assertJsonStructure([
        'data' => [['id', 'slug', 'title', 'summary', 'cover', 'media', 'testimonials']],
        'seo' => ['title', 'canonical'],
    ]);
});

it('sets the canonical to the industries index', function () {
    expect($this->getJson('/api/v1/industries')->json('seo.canonical'))
        ->toBe(url('/industries'));
});

it('exposes no detail endpoint, since those pages are retired', function () {
    $slug = Industry::firstOrFail()->slug;

    $this->getJson("/api/v1/industries/{$slug}")->assertNotFound();
});

it('serves industries within the query budget', function () {
    assertQueryCount(6, fn () => $this->getJson('/api/v1/industries')->assertOk());
});
