<?php

use App\Models\Post;
use App\Models\SeoPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('overrides the page title and description for a matching url', function () {
    SeoPage::create([
        'page_url' => '/about',
        'title' => 'Custom About Title ZZZ',
        'meta_description' => 'Custom about description ZZZ',
    ]);

    $this->get('/about')
        ->assertOk()
        ->assertSee('<title>Custom About Title ZZZ</title>', false)
        ->assertSee('name="description" content="Custom about description ZZZ"', false);
});

it('matches the homepage on the root path', function () {
    SeoPage::create(['page_url' => '/', 'title' => 'Home SEO ZZZ']);

    $this->get('/')->assertOk()->assertSee('<title>Home SEO ZZZ</title>', false);
});

it('falls back to the page own seo when no row exists', function () {
    $this->get('/about')->assertOk()->assertDontSee('Custom About Title ZZZ');
});

it('keeps the page description when the row only sets a title', function () {
    SeoPage::create(['page_url' => '/about', 'title' => 'Only Title ZZZ']);

    $response = $this->get('/about')->assertOk();

    expect($response->getContent())
        ->toContain('<title>Only Title ZZZ</title>')
        ->not->toContain('name="description" content=""');
});

it('ignores an inactive row', function () {
    SeoPage::create([
        'page_url' => '/about',
        'title' => 'Inactive Title ZZZ',
        'is_active' => false,
    ]);

    $this->get('/about')->assertOk()->assertDontSee('Inactive Title ZZZ');
});

it('normalizes the stored path so a trailing slash still matches', function () {
    $row = SeoPage::create(['page_url' => '/about/', 'title' => 'Normalized ZZZ']);

    expect($row->fresh()->page_url)->toBe('/about');

    $this->get('/about')->assertOk()->assertSee('Normalized ZZZ', false);
});

it('renders robots meta and drops the page from the sitemap when noindex', function () {
    SeoPage::create(['page_url' => '/about', 'noindex' => true, 'nofollow' => true]);

    $this->get('/about')
        ->assertOk()
        ->assertSee('name="robots" content="noindex,nofollow"', false);

    Artisan::call('sitemap:generate', ['--force' => true]);
    $xml = file_get_contents(public_path('sitemap.xml'));

    expect($xml)
        ->not->toContain('<loc>'.url('/about').'</loc>')
        ->and($xml)->toContain('<loc>'.url('/contact').'</loc>');

    @unlink(public_path('sitemap.xml'));
});

it('emits no robots tag when nothing is restricted', function () {
    SeoPage::create(['page_url' => '/about', 'title' => 'No Robots ZZZ']);

    $this->get('/about')->assertOk()->assertDontSee('name="robots"', false);
});

it('uses og fields, with the pasted url beating the upload', function () {
    SeoPage::create([
        'page_url' => '/about',
        'title' => 'Base Title ZZZ',
        'og_title' => 'OG Title ZZZ',
        'og_image_url' => 'https://cdn.example.test/og.jpg',
        'og_image_path' => 'seo/ignored.jpg',
    ]);

    $this->get('/about')
        ->assertOk()
        ->assertSee('property="og:title" content="OG Title ZZZ"', false)
        ->assertSee('property="og:image" content="https://cdn.example.test/og.jpg"', false)
        ->assertSee('name="twitter:image" content="https://cdn.example.test/og.jpg"', false);
});

it('falls back to the seo title for og when og_title is blank', function () {
    SeoPage::create(['page_url' => '/about', 'title' => 'Shared Title ZZZ']);

    $this->get('/about')
        ->assertOk()
        ->assertSee('property="og:title" content="Shared Title ZZZ"', false);
});

it('applies a canonical override', function () {
    SeoPage::create([
        'page_url' => '/about',
        'canonical_url' => 'https://canonical.example.test/about',
    ]);

    $this->get('/about')
        ->assertOk()
        ->assertSee('rel="canonical" href="https://canonical.example.test/about"', false);
});

it('renders meta keywords only when set', function () {
    $this->get('/about')->assertOk()->assertDontSee('name="keywords"', false);

    SeoPage::create(['page_url' => '/about', 'meta_keywords' => 'film, video, ZZZ']);

    $this->get('/about')
        ->assertOk()
        ->assertSee('name="keywords" content="film, video, ZZZ"', false);
});

it('works for a dynamic blog post path', function () {
    $post = Post::published()->firstOrFail();

    SeoPage::create([
        'page_url' => '/blog/'.$post->slug,
        'title' => 'Post SEO Override ZZZ',
    ]);

    $this->get('/blog/'.$post->slug)
        ->assertOk()
        ->assertSee('<title>Post SEO Override ZZZ</title>', false);
});
