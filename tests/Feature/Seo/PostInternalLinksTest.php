<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * Every seeded post links into the page its topic sells.
 *
 * Search Console showed the failure this prevents: "video post production"
 * impressions were landing on the blog post at position 57 while the service
 * page — the page that converts — sat unranked for the same cluster. A post
 * that ranks and links nowhere hoards its relevance.
 */
it('links each seeded post to at least one service or industry page', function () {
    Post::all()->each(function (Post $post): void {
        expect((string) $post->body)
            ->toMatch('#href="[^"]*/(services|industries)/#', "post {$post->slug} links to no service or industry page");
    });
});

it('links the post-production explainer to the post-production service', function () {
    // The specific Tier 1 fix: this is the page that actually registers for
    // "video post production", and the service page is where that intent buys.
    $body = (string) Post::where('slug', 'what-post-production-actually-includes')->firstOrFail()->body;

    expect($body)->toContain('/services/post-production');
});

it('links the corporate posts to the corporate industry page', function () {
    foreach (['photo-vs-video-corporate-event-coverage', 'preparing-your-team-for-a-corporate-shoot'] as $slug) {
        expect((string) Post::where('slug', $slug)->firstOrFail()->body)
            ->toContain('/industries/corporate-shoots');
    }
});

it('links no post at a retired url', function () {
    // /services/editing 301s now; a seeded link straight into a redirect wastes
    // the hop and looks stale in the copy.
    Post::all()->each(function (Post $post): void {
        expect(str_contains((string) $post->body, '/services/editing'))->toBeFalse();
    });
});
