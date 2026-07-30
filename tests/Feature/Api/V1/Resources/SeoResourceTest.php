<?php

use App\Http\Resources\Api\V1\SeoResource;
use App\Models\SeoPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds seo from the matching SeoPage row', function () {
    SeoPage::create([
        'page_url' => '/portfolio',
        'label' => 'Portfolio',
        'title' => 'Our Work',
        'meta_description' => 'Films and photography.',
        'og_title' => 'Our Work — TheLastClicks',
        'og_image_url' => 'https://cdn.example.com/og.jpg',
        'canonical_url' => 'https://thelastclicks.com/portfolio',
        'noindex' => false,
        'nofollow' => false,
        'is_active' => true,
    ]);

    $seo = SeoResource::forPath('/portfolio');

    expect($seo['title'])->toBe('Our Work');
    expect($seo['description'])->toBe('Films and photography.');
    expect($seo['canonical'])->toBe('https://thelastclicks.com/portfolio');
    expect($seo['noindex'])->toBeFalse();
    expect($seo['og']['title'])->toBe('Our Work — TheLastClicks');
    expect($seo['og']['image'])->toBe('https://cdn.example.com/og.jpg');
});

it('falls back to an absolute canonical when the SeoPage row has none', function () {
    SeoPage::create([
        'page_url' => '/blog',
        'label' => 'Blog',
        'title' => 'Journal',
        'is_active' => true,
    ]);

    expect(SeoResource::forPath('/blog')['canonical'])->toBe(url('/blog'));
});

it('returns a usable shape when no SeoPage row exists', function () {
    $seo = SeoResource::forPath('/nothing-here');

    expect($seo)->toHaveKeys(['title', 'description', 'canonical', 'noindex', 'nofollow', 'og', 'json_ld']);
    expect($seo['canonical'])->toBe(url('/nothing-here'));
    expect($seo['noindex'])->toBeFalse();
});

it('ignores SeoPage rows that are not active', function () {
    SeoPage::create([
        'page_url' => '/about',
        'label' => 'About',
        'title' => 'Draft title',
        'is_active' => false,
    ]);

    expect(SeoResource::forPath('/about')['title'])->not->toBe('Draft title');
});

it('applies overrides on top of the SeoPage row', function () {
    SeoPage::create([
        'page_url' => '/blog/a-post',
        'label' => 'Post',
        'title' => 'Row title',
        'is_active' => true,
    ]);

    $seo = SeoResource::forPath('/blog/a-post', ['title' => 'Model title']);

    expect($seo['title'])->toBe('Model title');
});

it('merges an og override without wiping the rest of the og block', function () {
    SeoPage::create([
        'page_url' => '/blog/b-post',
        'label' => 'Post',
        'title' => 'Row title',
        'og_title' => 'Row og title',
        'is_active' => true,
    ]);

    $seo = SeoResource::forPath('/blog/b-post', ['og' => ['image' => 'https://cdn.example.com/x.jpg']]);

    expect($seo['og']['image'])->toBe('https://cdn.example.com/x.jpg');
    expect($seo['og']['title'])->toBe('Row og title');
});

it('carries json_ld through overrides', function () {
    $ld = [['@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => 'X']];

    expect(SeoResource::forPath('/blog/x', ['json_ld' => $ld])['json_ld'])->toBe($ld);
});
