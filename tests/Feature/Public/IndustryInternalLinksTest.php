<?php

use App\Models\Industry;
use App\Models\Service;
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
