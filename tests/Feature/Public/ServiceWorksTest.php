<?php

use App\Models\Service;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\ResponseCache\Facades\ResponseCache;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * Work shown on a service page.
 *
 * The service page could only show `gallery_urls` — loose image URLs with no
 * title, client or category behind them. The studio's actual projects already
 * live in Work; this links the two so a service page can make its case with real
 * work rather than anonymous frames.
 */
it('links a service to works in both directions', function () {
    $service = Service::firstWhere('slug', 'photography');
    $work = Work::factory()->create(['is_published' => true]);

    $service->works()->attach($work);

    expect($service->fresh()->works->pluck('id'))->toContain($work->id);
    // The same row read from the other side — the admin edits it from either.
    expect($work->fresh()->services->pluck('id'))->toContain($service->id);
});

it('shows the attached work on the service page', function () {
    $service = Service::firstWhere('slug', 'photography');
    $work = Work::factory()->create(['title' => 'Atlas Refinery', 'is_published' => true]);
    $service->works()->attach($work);

    $html = $this->get('/services/photography')->assertOk()->getContent();

    expect($html)->toContain('Atlas Refinery');
});

it('never leaks an unpublished project onto a live page', function () {
    $service = Service::firstWhere('slug', 'photography');
    $draft = Work::factory()->create(['title' => 'Unannounced Campaign', 'is_published' => false]);
    $service->works()->attach($draft);

    expect($service->fresh()->publishedWorks->pluck('id'))->not->toContain($draft->id);
    // Still linked, though — the admin must keep showing it, or saving the
    // service would silently detach a project that was only unpublished.
    expect($service->fresh()->works->pluck('id'))->toContain($draft->id);
    $this->get('/services/photography')->assertOk()->assertDontSee('Unannounced Campaign');
});

it('orders the work by the order field, not by when it was attached', function () {
    $service = Service::firstWhere('slug', 'editing');
    $second = Work::factory()->create(['title' => 'Runs Second', 'order' => 2, 'is_published' => true]);
    $first = Work::factory()->create(['title' => 'Runs First', 'order' => 1, 'is_published' => true]);

    // Attached in the wrong order on purpose.
    $service->works()->attach([$second->id, $first->id]);

    expect($service->fresh()->publishedWorks->pluck('title')->all())
        ->toBe(['Runs First', 'Runs Second']);
});

it('falls back to the frames gallery when no work is attached', function () {
    // A service nobody has curated yet must not lose its media section.
    $service = Service::firstWhere('slug', 'videography');
    $service->update(['gallery_urls' => ['https://example.com/frame-one.jpg']]);

    expect($service->fresh()->publishedWorks)->toBeEmpty();

    $html = $this->get('/services/videography')->assertOk()->getContent();
    expect($html)->toContain('frame-one.jpg');
});

it('prefers the work grid over the frames gallery when both are set', function () {
    $service = Service::firstWhere('slug', 'videography');
    $service->update(['gallery_urls' => ['https://example.com/frame-one.jpg']]);
    $work = Work::factory()->create(['title' => 'Meridian Launch', 'is_published' => true]);
    $service->works()->attach($work);

    $html = $this->get('/services/videography')->assertOk()->getContent();

    expect($html)->toContain('Meridian Launch');
    // Two media grids back to back compete; the real projects win.
    expect($html)->not->toContain('frame-one.jpg');
});

it('drops the pivot row when either side is deleted', function () {
    $service = Service::firstWhere('slug', 'photography');
    $work = Work::factory()->create(['is_published' => true]);
    $service->works()->attach($work);

    $work->delete();

    expect($service->fresh()->works)->toBeEmpty();
});

it('clears the response cache when only the pivot changed', function () {
    // The trap CLAUDE.md documents for media-only uploads, in a new place:
    // attaching a work does not dirty the Service row, so a cached service page
    // would keep rendering without the work that was just added to it.
    $service = Service::firstWhere('slug', 'photography');
    $work = Work::factory()->create(['is_published' => true]);

    ResponseCache::shouldReceive('clear')->atLeast()->once();

    $service->works()->sync([$work->id]);
    $service->touch();
});

it('lets a service override the work section heading', function () {
    $service = Service::firstWhere('slug', 'photography');
    $service->update(['sections' => array_replace_recursive($service->sections ?? [], [
        'work' => ['title' => 'Shot by <em>us.</em>', 'lead' => 'A sample of recent frames.'],
    ])]);
    $service->works()->attach(Work::factory()->create(['is_published' => true]));

    $html = $this->get('/services/photography')->assertOk()->getContent();

    expect($html)->toContain('Shot by <em>us.</em>');
    expect($html)->toContain('A sample of recent frames.');
});
