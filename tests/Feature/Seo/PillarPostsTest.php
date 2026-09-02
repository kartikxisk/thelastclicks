<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * The two pillar posts backing the keyword map's Tier 1 clusters.
 *
 * The blog carried five craft posts and nothing aimed at a commercial query,
 * while the measured cluster ("video post production", positions 57–64) had no
 * supporting content at all. These two are the support: a cost guide — the
 * content type AI answers cite most readily and no local competitor publishes —
 * and a definitive planning guide for the vertical with the strongest proof.
 */
it('publishes the video editing cost guide, linked into the money page', function () {
    $post = Post::where('slug', 'video-editing-cost-india')->firstOrFail();

    expect($post->status)->toBe('published')
        ->and((string) $post->body)->toContain('/services/post-production')
        // The studio does not publish rates; the post explains the drivers and
        // converts to a quote instead of stating figures.
        ->and(str_contains((string) $post->body, '₹'))->toBeFalse()
        ->and((string) $post->body)->toContain('/contact');

    $this->get('/blog/video-editing-cost-india')->assertOk();
});

it('publishes the corporate event coverage guide, linked into its vertical', function () {
    $post = Post::where('slug', 'corporate-event-video-coverage-guide')->firstOrFail();

    expect($post->status)->toBe('published')
        ->and((string) $post->body)->toContain('/industries/corporate-shoots');

    $this->get('/blog/corporate-event-video-coverage-guide')->assertOk();
});

it('gives both pillars an intent-bearing seo title', function () {
    foreach ([
        'video-editing-cost-india' => '/cost|price|rate/i',
        'corporate-event-video-coverage-guide' => '/corporate|event/i',
    ] as $slug => $pattern) {
        expect((string) Post::where('slug', $slug)->firstOrFail()->seo_title)->toMatch($pattern);
    }
});

it('lands both pillars in the sitemap', function () {
    $this->artisan('sitemap:generate', ['--force' => true]);

    expect(file_get_contents(public_path('sitemap.xml')))
        ->toContain('/blog/video-editing-cost-india')
        ->toContain('/blog/corporate-event-video-coverage-guide');
});
