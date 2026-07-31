<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('returns a paginated list of published posts', function () {
    $this->getJson('/api/v1/posts')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'slug', 'title', 'excerpt', 'published_at', 'reading_minutes', 'cover', 'category', 'tags']],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            'filters' => ['categories', 'tags'],
            'seo' => ['title', 'canonical'],
        ])
        ->assertJsonPath('meta.per_page', 9);
});

it('orders posts newest first', function () {
    $dates = collect($this->getJson('/api/v1/posts')->json('data'))->pluck('published_at');

    expect($dates->all())->toBe($dates->sortDesc()->values()->all());
});

it('excludes posts scheduled for the future', function () {
    $post = Post::published()->firstOrFail();
    $post->update(['published_at' => now()->addYear()]);

    expect(collect($this->getJson('/api/v1/posts')->json('data'))->pluck('slug'))
        ->not->toContain($post->slug);
});

it('excludes drafts', function () {
    $post = Post::published()->firstOrFail();
    $post->update(['status' => 'draft']);

    expect(collect($this->getJson('/api/v1/posts')->json('data'))->pluck('slug'))
        ->not->toContain($post->slug);
});

it('filters by category', function () {
    $post = Post::published()->with('categories')->get()
        ->first(fn (Post $p) => $p->categories->isNotEmpty());

    expect($post)->not->toBeNull('no seeded post carries a category');

    $slug = $post->categories->first()->slug;
    $returned = collect($this->getJson("/api/v1/posts?category={$slug}")->json('data'))->pluck('slug');

    expect($returned)->toContain($post->slug);
});

it('returns a single post with related posts', function () {
    $post = Post::published()->firstOrFail();

    $this->getJson("/api/v1/posts/{$post->slug}")
        ->assertOk()
        ->assertJsonPath('data.slug', $post->slug)
        ->assertJsonStructure(['data' => ['body', 'related'], 'seo' => ['json_ld']]);
});

it('emits BlogPosting json-ld on the detail endpoint', function () {
    $post = Post::published()->firstOrFail();

    $types = collect($this->getJson("/api/v1/posts/{$post->slug}")->json('seo.json_ld'))
        ->pluck('@type');

    expect($types)->toContain('BlogPosting');
});

it('prefers the admin-set seo title over the display title', function () {
    $post = Post::published()->firstOrFail();
    $post->update(['seo_title' => 'Admin SEO title']);

    expect($this->getJson("/api/v1/posts/{$post->slug}")->json('seo.title'))->toBe('Admin SEO title');
});

it('falls back to the post title when no seo title is set', function () {
    $post = Post::published()->firstOrFail();
    $post->update(['seo_title' => null]);

    // Never the SeoPage row for /blog — that would give every article the
    // index's title.
    expect($this->getJson("/api/v1/posts/{$post->slug}")->json('seo.title'))->toBe($post->title);
});

it('404s on an unpublished post slug', function () {
    $post = Post::published()->firstOrFail();
    $post->update(['published_at' => now()->addYear()]);

    $this->getJson("/api/v1/posts/{$post->slug}")->assertNotFound();
});

it('never includes the post itself in its own related list', function () {
    $post = Post::published()->firstOrFail();

    expect(collect($this->getJson("/api/v1/posts/{$post->slug}")->json('data.related'))->pluck('slug'))
        ->not->toContain($post->slug);
});

it('serves the post list within the query budget', function () {
    assertQueryCount(8, fn () => $this->getJson('/api/v1/posts')->assertOk());
});

it('serves a post detail within the query budget', function () {
    $slug = Post::published()->firstOrFail()->slug;

    assertQueryCount(10, fn () => $this->getJson("/api/v1/posts/{$slug}")->assertOk());
});
