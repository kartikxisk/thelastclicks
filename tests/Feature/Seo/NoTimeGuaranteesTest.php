<?php

use App\Models\Industry;
use App\Models\Post;
use App\Models\SeoPage;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * The site promises no turnaround.
 *
 * Delivery time depends on the project and on the client — footage volume,
 * deliverable count, revision rounds, how fast feedback comes back — and so
 * does price. A published "same-day" or "within 48 hours" is a commitment the
 * studio controls only half of, so the copy describes what drives the time
 * and never states a window. The same standing rule as no public pricing
 * figures, pinned the same way: over everything the seeders publish.
 */
const TIME_GUARANTEE = '/\bsame[- ](day|night)\b'
    .'|\bwithin \d+ ?(working )?(hour|day|week|h)s?\b'
    .'|\b\d+ working (hour|day)s?\b'
    .'|\b\d+[- ](hour|day|week)s? (turnaround|delivery)\b'
    .'|\bturnaround (in|of|within) \d+'
    .'|\bdelivered (in|within) \d+'
    .'|\bovernight\b'
    .'|\bnext[- ]day\b/i';

/** Everything a row publishes, as one string — so a new text column is covered without editing this test. */
function publishedText(object $model): string
{
    $attributes = collect($model->getAttributes())
        ->except(['id', 'created_at', 'updated_at', 'published_at', 'slug', 'order'])
        ->all();

    return (string) json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

it('publishes no turnaround promise on any service', function () {
    foreach (Service::all() as $service) {
        expect(publishedText($service))->not->toMatch(TIME_GUARANTEE, "service {$service->slug}");
    }
});

it('publishes no turnaround promise on any industry', function () {
    foreach (Industry::all() as $industry) {
        expect(publishedText($industry))->not->toMatch(TIME_GUARANTEE, "industry {$industry->slug}");
    }
});

it('publishes no turnaround promise in any post', function () {
    foreach (Post::all() as $post) {
        expect(publishedText($post))->not->toMatch(TIME_GUARANTEE, "post {$post->slug}");
    }
});

it('publishes no turnaround promise in any seo title or description', function () {
    foreach (SeoPage::all() as $page) {
        expect(publishedText($page))->not->toMatch(TIME_GUARANTEE, "seo page {$page->path}");
    }
});
