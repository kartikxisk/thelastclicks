<?php

use App\Models\Client;
use App\Models\HeroSlide;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponseCache\Facades\ResponseCache;

uses(RefreshDatabase::class);

/**
 * N+1 guards for the public pages that render collections.
 *
 * These assert a *delta*, not an absolute number: render the page, add more
 * rows, render again, and require the query count not to have moved. An
 * absolute cap would have to be rewritten every time a page gains a legitimate
 * query, and the number would drift upward unchallenged — which is how the
 * client-logo strip ended up costing one query per logo unnoticed.
 *
 * ResponseCache::clear() before every render: the public routes sit behind
 * cacheResponse, and a cache hit issues no queries at all, so without it these
 * tests would pass by measuring nothing.
 */
beforeEach(function () {
    config(['media-library.disk_name' => 'public']);
    Storage::fake('public');
    $this->seed();
});

/** Query count for one uncached GET. */
function queriesForUrl(string $url): int
{
    ResponseCache::clear();

    $connection = DB::connection();
    $connection->flushQueryLog();
    $connection->enableQueryLog();

    try {
        test()->get($url)->assertOk();
    } finally {
        $count = count($connection->getQueryLog());
        $connection->disableQueryLog();
    }

    return $count;
}

/** A work the marquee and the portfolio grid will both actually render. */
function workForQueryCount(string $title, bool $featured = false): Work
{
    $work = Work::create(['title' => $title, 'is_featured' => $featured]);
    $item = $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $item->addMedia(UploadedFile::fake()->image('cover.jpg'))->toMediaCollection('file');

    return $work;
}

it('does not query per client logo on the homepage', function () {
    // The regression this exists for: Client::active()->get() without
    // with('media'), where logoUrl() then lazy-loaded the relation per row.
    foreach (range(1, 3) as $i) {
        Client::create(['name' => "Brand {$i}", 'logo_path' => "logos/brand-{$i}.png", 'order' => $i, 'is_active' => true]);
    }

    $baseline = queriesForUrl('/');

    foreach (range(4, 15) as $i) {
        Client::create(['name' => "Brand {$i}", 'logo_path' => "logos/brand-{$i}.png", 'order' => $i, 'is_active' => true]);
    }

    assertQueryCount($baseline, fn () => queriesForUrl('/'));
});

it('does not query per hero slide', function () {
    // Same shape as the client strip: assetUrl()/posterUrl() both go through
    // getFirstMediaUrl(), so an unloaded media relation costs a query a slide.
    HeroSlide::create(['label' => 'One', 'order' => 0, 'is_active' => true])
        ->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection('asset');

    $baseline = queriesForUrl('/');

    foreach (range(2, 6) as $i) {
        HeroSlide::create(['label' => "Slide {$i}", 'order' => $i, 'is_active' => true])
            ->addMedia(UploadedFile::fake()->image("slide-{$i}.jpg"))->toMediaCollection('asset');
    }

    assertQueryCount($baseline, fn () => queriesForUrl('/'));
});

it('does not query per work in the homepage marquee', function () {
    workForQueryCount('First Reel', featured: true);

    $baseline = queriesForUrl('/');

    // Past the twelve-tile cap on purpose: the strip must not pay for the works
    // it declines to render either.
    foreach (range(2, 15) as $i) {
        workForQueryCount("Reel {$i}", featured: true);
    }

    assertQueryCount($baseline, fn () => queriesForUrl('/'));
});

it('does not query per work on the portfolio page', function () {
    workForQueryCount('First Reel');

    $baseline = queriesForUrl('/portfolio');

    foreach (range(2, 15) as $i) {
        workForQueryCount("Reel {$i}");
    }

    assertQueryCount($baseline, fn () => queriesForUrl('/portfolio'));
});
