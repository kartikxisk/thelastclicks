<?php

use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

/** The homepage renders two media grids (industries + work); isolate the work one. */
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

it('shows featured works with a link to the full page', function () {
    Work::create(['title' => 'Featured Reel', 'is_featured' => true]);
    Work::create(['title' => 'Plain Reel']);

    $this->get('/')
        ->assertOk()
        ->assertSee('data-work-grid', false)
        ->assertSee('Featured Reel')
        ->assertSee('/our-works', false);
});

it('falls back to recent works when none are featured', function () {
    Work::create(['title' => 'Recent Reel']);

    $this->get('/')->assertOk()->assertSee('Recent Reel');
});

it('caps the homepage grid at six works', function () {
    foreach (range(1, 8) as $i) {
        Work::create(['title' => "Reel {$i}"]);
    }

    $response = $this->get('/')->assertOk();

    expect(substr_count(workSection($response->getContent()), 'work-tile reveal'))->toBe(6);
});
