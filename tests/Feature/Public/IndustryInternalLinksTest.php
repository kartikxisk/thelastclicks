<?php

use App\Models\Industry;
use App\Models\Service;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * Internal links into the industry detail pages.
 *
 * The pages shipped reachable from exactly one place — the /industries deck —
 * which is thin for crawling and gives a visitor no route in from the pages
 * where they actually land. These are the four routes in.
 */
it('links the homepage coverflow cards at the industry pages', function () {
    $industry = Industry::orderBy('order')->firstOrFail();

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('href="'.url('/industries/'.$industry->slug).'"')
        // The card used to open the quote wizard directly. It now leads to the
        // page that argues the case; the wizard is on the CTA there.
        ->and($html)->not->toContain('data-quote-prefill="'.e($industry->title).'"');
});

it('lists every industry in the sitewide footer', function () {
    // The footer is a Blade component (app.blade.php renders <x-footer /> on
    // every page), so the links are server-rendered. chrome.js has its own
    // footer, but it only injects when Blade has not already produced one.
    $industries = Industry::orderBy('order')->get();

    $html = $this->get('/')->assertOk()->getContent();

    foreach ($industries as $industry) {
        expect($html)->toContain('href="'.url('/industries/'.$industry->slug).'"');
    }
});

it('renders no footer industries column when there are none', function () {
    // An empty heading over nothing reads as broken markup.
    Industry::query()->get()->each->delete();

    expect($this->get('/')->assertOk()->getContent())
        ->not->toContain('> Industries</h3>');
});

it('cross-links the other industries from a detail page, excluding itself', function () {
    $all = Industry::orderBy('order')->get();
    $current = $all->first();
    $other = $all->get(1);

    $html = $this->get('/industries/'.$current->slug)->assertOk()->getContent();

    expect($html)->toContain('href="'.url('/industries/'.$other->slug).'"');

    // Its own slug appears in the canonical and breadcrumb, so count the
    // cross-link block specifically rather than the whole document.
    preg_match('/<nav class="ind-cross".*?<\/nav>/s', $html, $block);
    expect($block)->not->toBeEmpty()
        ->and($block[0])->not->toContain(url('/industries/'.$current->slug));
});

it('lists the industries a service covers on the service page', function () {
    $service = Service::firstOrFail();
    $industry = Industry::orderBy('order')->firstOrFail();

    $service->industries()->sync([$industry->id]);

    $this->get('/services/'.$service->slug)
        ->assertOk()
        ->assertSee(url('/industries/'.$industry->slug), false);
});

it('renders no industry block on a service with none attached', function () {
    $service = Service::firstOrFail();
    $service->industries()->detach();

    $html = $this->get('/services/'.$service->slug)->assertOk()->getContent();

    // An empty band reads as a broken section rather than a quiet one.
    expect($html)->not->toContain('data-service-industries');
});

it('links a service and an industry in both directions', function () {
    $service = Service::firstOrFail();
    $industry = Industry::orderBy('order')->firstOrFail();

    $service->industries()->sync([$industry->id]);

    expect($service->fresh()->industries->pluck('id'))->toContain($industry->id)
        ->and($industry->fresh()->services->pluck('id'))->toContain($service->id);
});

it('keeps the footer nav on one row of columns rather than orphaning one', function () {
    // The Industries column made the footer four columns wide. .foot__nav was
    // pinned to repeat(3, 1fr), so Contact dropped onto a second row on its own.
    // The count is not fixed any more, because the Industries column only exists
    // when there are industries — the footer has to read at three and at four.
    $css = file_get_contents(resource_path('css/core.css'));

    preg_match('#\.foot__nav\s*\{[^}]*\}#s', $css, $rule);

    expect($rule)->not->toBeEmpty()
        ->and($rule[0])->toContain('auto-fit')
        ->and($rule[0])->not->toMatch('/repeat\(\s*\d+\s*,/');
});

it('renders one footer column per section, industries included', function () {
    $html = $this->get('/')->assertOk()->getContent();

    preg_match('#<nav class="foot__nav".*?</nav>#s', $html, $nav);

    expect($nav)->not->toBeEmpty()
        ->and(substr_count($nav[0], 'foot__col'))->toBe(4);
});

it('drops to three footer columns when there are no industries', function () {
    Industry::query()->get()->each->delete();

    $html = $this->get('/')->assertOk()->getContent();
    preg_match('#<nav class="foot__nav".*?</nav>#s', $html, $nav);

    expect(substr_count($nav[0], 'foot__col'))->toBe(3);
});

it('spotlights the artist work on the homepage, linked to its industry', function () {
    // The full-bleed reel exists to route the homepage's live-music proof into
    // /industries/cover-artist. It shows frames, not names — the copy is thin
    // by design — but it only earns its place while there is published work
    // behind it, so a published cover-artist work is what turns it on.
    // WorksSeeder is skipped under testing, hence the row created here.
    $ca = Industry::where('slug', 'cover-artist')->firstOrFail();
    $ca->works()->attach(Work::create([
        'title' => 'Sonu Nigam',
        'slug' => 'sonu-nigam',
        'is_published' => true,
    ]));

    $html = $this->get('/')->assertOk()->getContent();

    preg_match('#<section[^>]*data-artist-band.*?</section>#s', $html, $band);

    expect($band)->not->toBeEmpty()
        ->and($band[0])->toContain(url('/industries/cover-artist'))
        ->and($band[0])->toContain('images/artist/wm/');
});

it('renders no artist band while the cover-artist work is unpublished', function () {
    $ca = Industry::where('slug', 'cover-artist')->firstOrFail();
    $ca->works()->attach(Work::create([
        'title' => 'Unreleased Artist',
        'slug' => 'unreleased-artist',
        'is_published' => false,
    ]));

    expect($this->get('/')->assertOk()->getContent())->not->toContain('data-artist-band');
});

it('renders no artist band while the cover-artist industry has nothing published', function () {
    // An empty lineup poster is worse than none — the band waits for the work.
    expect($this->get('/')->assertOk()->getContent())->not->toContain('data-artist-band');
});

it('renders no artist band when the cover-artist industry is gone', function () {
    Industry::where('slug', 'cover-artist')->get()->each->delete();

    expect($this->get('/')->assertOk()->getContent())
        ->not->toContain('data-artist-band');
});
