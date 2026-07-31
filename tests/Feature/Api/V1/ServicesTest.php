<?php

use App\Models\Service;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    foreach (range(1, 8) as $i) {
        Work::create([
            'title' => "Work {$i}",
            'slug' => "work-{$i}",
            'order' => $i,
            'is_published' => true,
        ]);
    }
});

it('lists services in admin order', function () {
    $slugs = collect($this->getJson('/api/v1/services')->assertOk()->json('data'))->pluck('slug');

    expect($slugs->all())->toBe(Service::orderBy('order')->pluck('slug')->all());
});

it('returns a single service with its full content', function () {
    $service = Service::firstOrFail();

    $this->getJson("/api/v1/services/{$service->slug}")
        ->assertOk()
        ->assertJsonPath('data.slug', $service->slug)
        ->assertJsonStructure([
            'data' => [
                'slug', 'title', 'hero_headline', 'pillars', 'phases',
                'kit', 'faqs', 'cta', 'gallery', 'related_works',
            ],
            'seo' => ['title', 'canonical', 'json_ld'],
        ]);
});

it('caps related works at six', function () {
    $service = Service::firstOrFail();

    expect($this->getJson("/api/v1/services/{$service->slug}")->json('data.related_works'))
        ->toHaveCount(6);
});

it('never shows unpublished work under a service', function () {
    Work::query()->update(['is_published' => false]);
    $service = Service::firstOrFail();

    expect($this->getJson("/api/v1/services/{$service->slug}")->json('data.related_works'))
        ->toBeEmpty();
});

it('emits Service json-ld on the detail endpoint', function () {
    $service = Service::firstOrFail();

    $types = collect($this->getJson("/api/v1/services/{$service->slug}")->json('seo.json_ld'))
        ->pluck('@type');

    expect($types)->toContain('Service');
});

it('sets the canonical to the public service path', function () {
    $service = Service::firstOrFail();

    expect($this->getJson("/api/v1/services/{$service->slug}")->json('seo.canonical'))
        ->toBe(url("/services/{$service->slug}"));
});

it('404s on an unknown service slug', function () {
    $this->getJson('/api/v1/services/not-a-service')->assertNotFound();
});

it('serves a service detail within the query budget', function () {
    $slug = Service::firstOrFail()->slug;

    assertQueryCount(8, fn () => $this->getJson("/api/v1/services/{$slug}")->assertOk());
});
