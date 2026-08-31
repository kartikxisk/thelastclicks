<?php

use App\Models\Industry;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * Filtering the portfolio by industry.
 *
 * Work::CATEGORIES files a project by what it is (brand-film, wedding, product)
 * and CRAFTS by what was done in house. Neither answers "who was it for", which
 * is the question a visitor arrives with — and industries already exist as their
 * own records. This links the two and adds a third chip group to /portfolio.
 *
 * Industries have no detail page (`/industries/{slug}` is a 301 to the deck), so
 * the portfolio filter is the only place the link is read publicly.
 */
it('links a work to industries in both directions', function () {
    $industry = Industry::first();
    $work = Work::factory()->create();

    $work->industries()->attach($industry);

    expect($work->fresh()->industries->pluck('id'))->toContain($industry->id);
    // The same row from the other side — the admin edits it from either record.
    expect($industry->fresh()->works->pluck('id'))->toContain($work->id);
});

it('tags a portfolio tile with the industries it belongs to', function () {
    $industry = Industry::first();
    $work = Work::factory()->create(['title' => 'Atlas Refinery']);
    $work->industries()->attach($industry);

    $html = $this->get('/portfolio')->assertOk()->getContent();

    expect($html)->toContain('data-industries="'.$industry->slug.'"');
});

it('offers a chip for an industry that has published work', function () {
    $industry = Industry::first();
    Work::factory()->create()->industries()->attach($industry);

    $response = $this->get('/portfolio')->assertOk();

    // assertSee escapes, which matters: the seeded titles carry an ampersand.
    $response->assertSee($industry->title);
    expect($response->getContent())->toContain('data-filter="industry:'.$industry->slug.'"');
});

it('offers no chip for an industry nothing is filed under', function () {
    // Deliberately attach nothing. An empty filter that always yields nothing is
    // worse than no filter — the same rule the category and craft chips follow.
    $empty = Industry::first();

    $html = $this->get('/portfolio')->assertOk()->getContent();

    expect($html)->not->toContain('data-filter="industry:'.$empty->slug.'"');
});

it('does not let an unpublished project put a chip on the page', function () {
    $industry = Industry::first();
    Work::factory()->draft()->create(['title' => 'Unannounced Campaign'])
        ->industries()->attach($industry);

    $html = $this->get('/portfolio')->assertOk()->getContent();

    expect($html)->not->toContain('data-filter="industry:'.$industry->slug.'"')
        ->and($html)->not->toContain('Unannounced Campaign');
});

it('loads the industries for the whole grid without a query per tile', function () {
    $industry = Industry::first();

    $countQueries = function (): int {
        $connection = DB::connection();
        $connection->flushQueryLog();
        $connection->enableQueryLog();

        try {
            $this->get('/portfolio')->assertOk();
        } finally {
            $log = $connection->getQueryLog();
            $connection->disableQueryLog();
        }

        return count($log);
    };

    foreach (Work::factory()->count(2)->create() as $work) {
        $work->industries()->attach($industry);
    }
    $small = $countQueries();

    foreach (Work::factory()->count(10)->create() as $work) {
        $work->industries()->attach($industry);
    }
    $large = $countQueries();

    // Asserted as a shape, not a ceiling: every tile reads $work->industries, so
    // without eager loading this grows by one query per project. A fixed maximum
    // would instead drift out of date every time the shared chrome gains a
    // lookup, and would not actually be testing the N+1.
    expect($large)->toBe($small);
});
