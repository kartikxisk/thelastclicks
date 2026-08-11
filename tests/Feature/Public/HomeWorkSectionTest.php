<?php

use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * A work the marquee will actually render.
 *
 * The strip filters to works with a cover — a tile with nothing to show is not
 * worth a slot — so a bare Work::create() is invisible to it. These tests used
 * to create bare works and assert on grid markup that rendered them anyway,
 * which is why they kept failing after the grid became a marquee.
 */
function workWithCover(string $title, bool $featured = false): Work
{
    $work = Work::create(['title' => $title, 'is_featured' => $featured]);
    $item = $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $item->addMedia(UploadedFile::fake()->image('cover.jpg'))->toMediaCollection('file');

    return $work;
}

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

/** The homepage renders two media blocks (industries + work); isolate the work one. */
function workSection(string $html): string
{
    $start = strpos($html, 'data-screen-label="07 Work"');

    if ($start === false) {
        return '';
    }

    $end = strpos($html, '</section>', $start);

    return substr($html, $start, $end === false ? null : $end - $start);
}

it('hides the work section when nothing is published', function () {
    // The industries grid still renders, so assert on the work section itself.
    $html = $this->get('/')->assertOk()->getContent();

    expect(workSection($html))->toBe('');
});

// The homepage work block is a marquee, not a grid. These assertions used to
// look for `data-work-grid`, a `/our-works` link and a `work-tile reveal` class,
// none of which survived that change — the URL now 301s to /portfolio and the
// tiles are .wmq__tile. The tests were asserting a section that no longer exists
// while the real one rendered fine underneath them.
it('shows featured works in the marquee, with a link to the full portfolio', function () {
    workWithCover('Featured Reel', featured: true);
    workWithCover('Plain Reel');

    $this->get('/')
        ->assertOk()
        ->assertSee('data-work-marquee', false)
        // Titles are not printed on the tiles by design — they survive only as the
        // play button's accessible name and the lightbox caption.
        ->assertSee('Play Featured Reel', false)
        ->assertSee('/portfolio', false);
});

it('falls back to recent works when none are featured', function () {
    workWithCover('Recent Reel');

    $this->get('/')->assertOk()->assertSee('Play Recent Reel', false);
});

it('caps the homepage marquee at twelve works', function () {
    foreach (range(1, 15) as $i) {
        workWithCover("Reel {$i}");
    }

    $response = $this->get('/')->assertOk();

    // Twelve works, each rendered twice: the strip is duplicated so it can loop
    // seamlessly under translateX(-50%). Counting tiles therefore counts 2n.
    expect(substr_count(workSection($response->getContent()), 'wmq__tile'))->toBe(24);
});
