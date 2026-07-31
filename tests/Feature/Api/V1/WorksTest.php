<?php

use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    // DatabaseSeeder does not call DevWorksSeeder, so the grid needs fixtures.
    // Two categories and enough rows to force a second page.
    foreach (range(1, 20) as $i) {
        Work::create([
            'title' => "Work {$i}",
            'slug' => "work-{$i}",
            'category' => $i % 2 === 0 ? 'commercial' : 'brand-film',
            'order' => $i,
            'is_published' => true,
        ]);
    }
});

it('returns a paginated list of published works', function () {
    $this->getJson('/api/v1/works')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'slug', 'title', 'cover', 'category']],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            'filters' => ['categories'],
            'seo' => ['title', 'canonical'],
        ])
        ->assertJsonPath('meta.per_page', 12)
        ->assertJsonPath('meta.total', 20)
        ->assertJsonCount(12, 'data');
});

it('excludes unpublished works', function () {
    $hidden = Work::published()->firstOrFail();
    $hidden->update(['is_published' => false]);

    $slugs = collect($this->getJson('/api/v1/works')->json('data'))->pluck('slug');

    expect($slugs)->not->toContain($hidden->slug);
});

it('filters by category', function () {
    $categories = collect($this->getJson('/api/v1/works?category=commercial')->json('data'))
        ->pluck('category')
        ->unique();

    expect($categories->all())->toBe(['commercial']);
});

it('returns an empty page rather than an error for an unknown category', function () {
    $this->getJson('/api/v1/works?category=does-not-exist')
        ->assertOk()
        ->assertJsonPath('data', [])
        ->assertJsonPath('meta.total', 0);
});

it('offers only categories that have published works as filters', function () {
    $offered = collect($this->getJson('/api/v1/works')->json('filters.categories'))
        ->pluck('value')
        ->sort()
        ->values();

    expect($offered->all())->toBe(['brand-film', 'commercial']);
});

it('labels filter categories from the CATEGORIES map', function () {
    $filters = collect($this->getJson('/api/v1/works')->json('filters.categories'))
        ->keyBy('value');

    expect($filters['commercial']['label'])->toBe(Work::CATEGORIES['commercial']);
});

it('sets a page-aware canonical', function () {
    expect($this->getJson('/api/v1/works?page=2')->json('seo.canonical'))->toContain('page=2');
    expect($this->getJson('/api/v1/works')->json('seo.canonical'))->not->toContain('page=');
});

it('rejects a page number that is not a positive integer', function () {
    $this->getJson('/api/v1/works?page=nope')->assertStatus(422);
});

it('serves the works list within the query budget regardless of page size', function () {
    assertQueryCount(8, fn () => $this->getJson('/api/v1/works')->assertOk());
});
