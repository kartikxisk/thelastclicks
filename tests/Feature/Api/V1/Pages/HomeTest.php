<?php

use App\Models\HeroSlide;
use App\Models\SeoPage;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    // DatabaseSeeder does not call DevWorksSeeder and there is no hero slide
    // seeder, so the homepage's two lead sections need fixtures here.
    HeroSlide::create(['label' => 'Reel 2026', 'order' => 0, 'is_active' => true]);

    foreach (range(1, 20) as $i) {
        Work::create([
            'title' => "Work {$i}",
            'slug' => "work-{$i}",
            'category' => 'brand-film',
            'order' => $i,
            'is_published' => true,
            'is_featured' => $i <= 2,
        ]);
    }
});

it('returns every homepage section', function () {
    $this->getJson('/api/v1/pages/home')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'hero_slides' => [['id', 'label', 'asset', 'poster', 'mime', 'is_video']],
                'services' => [['id', 'slug', 'title']],
                'featured_works' => [['id', 'slug', 'title', 'cover', 'is_featured']],
                'industries' => [['id', 'slug', 'title']],
                'testimonials',
                'clients',
            ],
            'seo' => ['title', 'description', 'canonical', 'noindex', 'nofollow', 'og', 'json_ld'],
        ]);
});

it('serves seo from the SeoPage row for the home path', function () {
    SeoPage::updateOrCreate(
        ['page_url' => '/'],
        ['label' => 'Home', 'title' => 'TheLastClicks — Film & Photography', 'is_active' => true]
    );

    $this->getJson('/api/v1/pages/home')
        ->assertOk()
        ->assertJsonPath('seo.title', 'TheLastClicks — Film & Photography');
});

it('emits Organization and WebSite json-ld', function () {
    $types = collect($this->getJson('/api/v1/pages/home')->json('seo.json_ld'))
        ->pluck('@type');

    expect($types)->toContain('Organization');
    expect($types)->toContain('WebSite');
});

it('prefers featured works and tops up with recent ones', function () {
    $works = $this->getJson('/api/v1/pages/home')->json('data.featured_works');

    // Only two are flagged, but the collage needs enough tiles to cluster —
    // the rest are topped up from recent published work.
    expect(count($works))->toBe(15);
    expect(collect($works)->take(2)->pluck('is_featured')->all())->toBe([true, true]);
});

it('never returns unpublished works', function () {
    Work::query()->update(['is_published' => false]);

    expect($this->getJson('/api/v1/pages/home')->json('data.featured_works'))->toBeEmpty();
});

it('does not duplicate a featured work in the top-up', function () {
    $slugs = collect($this->getJson('/api/v1/pages/home')->json('data.featured_works'))
        ->pluck('slug');

    expect($slugs->unique()->count())->toBe($slugs->count());
});

it('serves the whole homepage within the query budget', function () {
    assertQueryCount(16, fn () => $this->getJson('/api/v1/pages/home')->assertOk());
});
