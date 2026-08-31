<?php

use App\Models\Industry;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * The industry detail page.
 *
 * `/industries/{slug}` was a 301 to the deck, so an industry had a name and a
 * tile and nowhere to make its case. It now renders like a service page: the
 * industry's own copy above the work actually filed under it.
 *
 * Tests assert structure, not marketing copy — the prose gets rewritten and
 * pinning it just breaks the suite.
 */
it('renders the page for an industry', function () {
    $industry = Industry::first();

    $this->get('/industries/'.$industry->slug)
        ->assertOk()
        ->assertSee($industry->title);
});

it('404s an industry that does not exist', function () {
    $this->get('/industries/not-a-real-industry')->assertNotFound();
});

it('shows the published work filed under the industry', function () {
    $industry = Industry::first();
    $work = Work::factory()->create(['title' => 'Atlas Refinery']);
    $industry->works()->attach($work);

    $this->get('/industries/'.$industry->slug)
        ->assertOk()
        ->assertSee('Atlas Refinery');
});

it('never leaks an unpublished project onto the page', function () {
    $industry = Industry::first();
    $draft = Work::factory()->draft()->create(['title' => 'Unannounced Campaign']);
    $industry->works()->attach($draft);

    $this->get('/industries/'.$industry->slug)
        ->assertOk()
        ->assertDontSee('Unannounced Campaign');

    // Still attached, though: the admin multi-select must keep showing it or
    // saving the form would silently detach a project that was only unpublished.
    expect($industry->fresh()->works->pluck('id'))->toContain($draft->id);
});

it('orders the work by the work order, not by when it was attached', function () {
    $industry = Industry::first();
    $second = Work::factory()->create(['title' => 'Runs Second', 'order' => 2]);
    $first = Work::factory()->create(['title' => 'Runs First', 'order' => 1]);

    $industry->works()->attach([$second->id, $first->id]);

    $html = $this->get('/industries/'.$industry->slug)->assertOk()->getContent();

    expect(strpos($html, 'Runs First'))->toBeLessThan(strpos($html, 'Runs Second'));
});

it('links each deck tile to its industry page', function () {
    $industry = Industry::first();

    $this->get('/industries')
        ->assertOk()
        ->assertSee(url('/industries/'.$industry->slug));
});

it('renders the work grid without a query per tile', function () {
    $industry = Industry::first();

    $count = function () use ($industry): int {
        $c = DB::connection();
        $c->flushQueryLog();
        $c->enableQueryLog();
        try {
            $this->get('/industries/'.$industry->slug)->assertOk();
        } finally {
            $log = $c->getQueryLog();
            $c->disableQueryLog();
        }

        return count($log);
    };

    $industry->works()->attach(Work::factory()->count(2)->create()->pluck('id'));
    $small = $count();

    $industry->works()->attach(Work::factory()->count(10)->create()->pluck('id'));
    $large = $count();

    // A shape, not a ceiling: the grid reads cover media per tile, so without
    // eager loading this grows one query per project. A fixed maximum would
    // drift every time the shared chrome gains a lookup.
    expect($large)->toBe($small);
});

it('301s a slug retired with the old eight-vertical taxonomy', function () {
    // Retired means gone from the table. Deleted here rather than assumed, so
    // the test does not depend on which taxonomy the seeder happens to ship.
    Industry::where('slug', 'nightlife-entertainment')->get()->each->delete();

    // These slugs are indexed. Without the redirect they would start 404ing the
    // moment the taxonomy was replaced.
    $this->get('/industries/nightlife-entertainment')
        ->assertRedirect('/industries')
        ->assertStatus(301);
});

it('prefers a live industry over a retired redirect of the same slug', function () {
    // The reason the retirement list lives in the controller and not in a
    // Route::redirect: registered as a route it would shadow this row, and the
    // shadowing would be invisible.
    Industry::where('slug', 'nightlife-entertainment')->get()->each->delete();

    $revived = Industry::create(['title' => 'Nightlife Revived', 'slug' => 'nightlife-entertainment']);

    $this->get('/industries/nightlife-entertainment')
        ->assertOk()
        ->assertSee($revived->title);
});
