<?php

use App\Http\Requests\StoreQuoteRequest;
use App\Models\SeoPage;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('returns the about page bundle', function () {
    $this->getJson('/api/v1/pages/about')
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['testimonials', 'clients', 'stats' => ['works', 'clients']],
            'seo' => ['title', 'canonical'],
        ]);
});

it('returns the contact page bundle with form options', function () {
    $response = $this->getJson('/api/v1/pages/contact')
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['services', 'project_types', 'budget_ranges'],
            'seo' => ['title', 'canonical'],
        ]);

    expect($response->json('data.project_types'))->not->toBeEmpty();
    expect($response->json('data.budget_ranges'))->not->toBeEmpty();
});

it('offers the same budget ranges the blade form does', function () {
    $ranges = collect($this->getJson('/api/v1/pages/contact')->json('data.budget_ranges'))
        ->pluck('value');

    expect($ranges->all())->toBe(StoreQuoteRequest::BUDGET_RANGES);
});

it('serves each legal page with rendered body copy', function (string $slug) {
    $response = $this->getJson("/api/v1/pages/{$slug}")
        ->assertOk()
        ->assertJsonStructure(['data' => ['body'], 'seo' => ['title', 'canonical']]);

    // Body is the copy only — not a whole HTML document. A page wrapper
    // leaking through would drag the nav, footer and script tags into the API.
    $body = $response->json('data.body');
    expect($body)->not->toBeEmpty();
    expect($body)->not->toContain('<html');
    expect($body)->not->toContain('<nav');
    expect($body)->toContain('<h2>');
})->with(['privacy-policy', 'terms-of-service', 'cookie-policy', 'disclaimer']);

it('serves thank-you with no body, since the frontend designs that page', function () {
    $this->getJson('/api/v1/pages/thank-you')
        ->assertOk()
        ->assertJsonPath('data.body', null)
        ->assertJsonStructure(['seo' => ['title', 'canonical']]);
});

it('resolves the admin contact email inside legal copy', function () {
    SiteSetting::set('contact_email', 'legal@thelastclicks.com');

    expect($this->getJson('/api/v1/pages/cookie-policy')->json('data.body'))
        ->toContain('legal@thelastclicks.com');
});

it('sets the canonical to the public path', function () {
    expect($this->getJson('/api/v1/pages/privacy-policy')->json('seo.canonical'))
        ->toBe(url('/privacy-policy'));
});

it('rejects a slug outside the static enum', function () {
    $this->getJson('/api/v1/pages/not-a-real-page')->assertNotFound();
});

it('does not let an arbitrary SeoPage row become a page endpoint', function () {
    SeoPage::create([
        'page_url' => '/secret',
        'label' => 'Secret',
        'title' => 'Secret',
        'is_active' => true,
    ]);

    $this->getJson('/api/v1/pages/secret')->assertNotFound();
});

it('serves a legal page within the query budget', function () {
    assertQueryCount(3, fn () => $this->getJson('/api/v1/pages/privacy-policy')->assertOk());
});
