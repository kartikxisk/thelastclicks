<?php

use App\Http\Resources\Api\V1\ClientResource;
use App\Http\Resources\Api\V1\HeroSlideResource;
use App\Http\Resources\Api\V1\IndustryResource;
use App\Http\Resources\Api\V1\PostResource;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Http\Resources\Api\V1\TestimonialResource;
use App\Http\Resources\Api\V1\WorkResource;
use App\Models\Client;
use App\Models\HeroSlide;
use App\Models\Industry;
use App\Models\Post;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    // DatabaseSeeder does not call DevWorksSeeder, and there is no hero slide
    // seeder at all, so these two fixtures are built here rather than by
    // widening a seeder the admin panel tests also depend on.
    $work = Work::create([
        'title' => 'Spring Launch Film',
        'slug' => 'spring-launch-film',
        'summary' => 'A brand film for a spring product launch.',
        'client' => 'Acme',
        'category' => 'brand-film',
        'crafts' => ['direction'],
        'credits' => [['role' => 'Director', 'name' => 'Ada Lovelace']],
        'location' => 'London',
        'year' => '2026',
        'is_published' => true,
        'is_featured' => true,
    ]);
    $work->mediaItems()->create([
        'type' => 'youtube',
        'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'order' => 0,
    ]);

    HeroSlide::create(['label' => 'Reel 2026', 'order' => 0, 'is_active' => true]);
});

function shape(string $resource, $model): array
{
    return $resource::make($model)->resolve(Request::create('/'));
}

it('shapes a work with every documented key', function () {
    $out = shape(WorkResource::class, Work::published()->firstOrFail());

    expect($out)->toHaveKeys([
        'id', 'slug', 'title', 'summary', 'client', 'category', 'category_label',
        'crafts', 'credits', 'location', 'agency', 'year', 'cover',
        'preview_video_url', 'media', 'is_featured',
    ]);
    expect($out['crafts'])->toBeArray();
    expect($out['credits'])->toBeArray();
    expect($out['media'])->toBeArray();
    expect($out['is_featured'])->toBeBool();
});

it('shapes a service with every documented key', function () {
    $out = shape(ServiceResource::class, Service::firstOrFail());

    expect($out)->toHaveKeys([
        'id', 'slug', 'title', 'hero_headline', 'hero_copy', 'hero_meta', 'hero',
        'proof', 'pillars', 'phases', 'kit', 'faqs', 'cta', 'tags', 'gallery', 'body', 'share',
    ]);
    expect($out['pillars'])->toBeArray();
    expect($out['faqs'])->toBeArray();
    expect($out['gallery'])->toBeArray();
});

it('never returns null for an array-cast column', function () {
    $service = Service::create(['title' => 'Bare Service', 'slug' => 'bare-service']);

    $out = shape(ServiceResource::class, $service);

    // The frontend iterates these without guarding, so an unset column has to
    // arrive as [] rather than null.
    foreach (['hero_meta', 'proof', 'pillars', 'phases', 'kit', 'faqs', 'tags', 'gallery'] as $key) {
        expect($out[$key])->toBeArray("{$key} should be an array");
    }
});

it('shapes an industry with nested testimonials', function () {
    $out = shape(IndustryResource::class, Industry::firstOrFail());

    expect($out)->toHaveKeys(['id', 'slug', 'title', 'summary', 'body', 'cover', 'media', 'testimonials']);
    expect($out['testimonials'])->toBeArray();
});

it('shapes a post with every documented key', function () {
    $out = shape(PostResource::class, Post::published()->firstOrFail());

    expect($out)->toHaveKeys([
        'id', 'slug', 'title', 'excerpt', 'body', 'published_at',
        'reading_minutes', 'cover', 'category', 'tags',
    ]);
    expect($out['published_at'])->toMatch('/^\d{4}-\d{2}-\d{2}T/');
    expect($out['reading_minutes'])->toBeInt();
    expect($out['reading_minutes'])->toBeGreaterThanOrEqual(1);
});

it('shapes a testimonial with the fields the model actually has', function () {
    $out = shape(TestimonialResource::class, Testimonial::published()->firstOrFail());

    // No avatar: Testimonial does not use InteractsWithMedia.
    expect($out)->toHaveKeys(['id', 'quote', 'client_name', 'role_company']);
    expect($out)->not->toHaveKey('avatar');
});

it('shapes a client with a resolved logo url', function () {
    $out = shape(ClientResource::class, Client::firstOrFail());

    expect($out)->toHaveKeys(['id', 'name', 'logo', 'url']);
});

it('shapes a hero slide with asset, poster and a video flag', function () {
    $out = shape(HeroSlideResource::class, HeroSlide::active()->firstOrFail());

    expect($out)->toHaveKeys(['id', 'label', 'asset', 'poster', 'mime', 'is_video']);
    expect($out['is_video'])->toBeBool();
});

it('declares its eager loads', function () {
    expect(WorkResource::eagerLoads())->toContain('media');
    expect(WorkResource::eagerLoads())->toContain('mediaItems.media');
    expect(IndustryResource::eagerLoads())->toContain('testimonials');
    expect(PostResource::eagerLoads())->toContain('tags');
    expect(PostResource::eagerLoads())->toContain('categories');
});

it('resolves a collection without an n+1', function () {
    $works = Work::published()->with(WorkResource::eagerLoads())->get();

    assertQueryCount(0, fn () => WorkResource::collection($works)->resolve(Request::create('/')));
});
