<?php

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * The flow section states a sequence, never a duration.
 *
 * Each stage used to carry a figure — "Day 1–5", "+10 days". The studio cannot
 * commit to one before it knows the scope: the same five stages run over a
 * single-day product shoot and a multi-unit campaign, so any number is wrong for
 * one end of that range. Quoting three bands instead only made the page wrong in
 * three sizes. The sequence is the promise; dates are set in the quote.
 */
it('states the phases without attaching a duration to any of them', function () {
    foreach (['photography', 'videography', 'editing'] as $slug) {
        $service = Service::firstWhere('slug', $slug);

        foreach ($service->phases as $phase) {
            expect($phase)->toHaveKeys(['num', 'title', 'desc']);
            // Neither the single figure nor the per-size bands that replaced it.
            expect($phase)->not->toHaveKey('time', $slug);
            foreach (['compact', 'standard', 'extended'] as $band) {
                expect($phase)->not->toHaveKey('time_'.$band, $slug);
            }
        }
    }
});

it('renders no duration markup on a service page', function () {
    $html = $this->get('/services/videography')->assertOk()->getContent();

    // The project-size toggle and every element that carried a figure.
    foreach (['pp-scale', 'pp-phase__time', 'pp-total', 'Project size'] as $gone) {
        expect($html)->not->toContain($gone);
    }
});

it('still lists every phase in order', function () {
    // Removing the durations must not remove the stages with them.
    $service = Service::firstWhere('slug', 'videography');
    $html = $this->get('/services/videography')->assertOk()->getContent();

    expect($service->phases)->toHaveCount(5);
    foreach ($service->phases as $phase) {
        expect($html)->toContain($phase['title']);
    }
});

it('says where the timeline actually lives', function () {
    // Without this the section reads as though the studio simply declines to
    // answer "how long".
    $html = $this->get('/services/videography')->assertOk()->getContent();

    expect($html)->toContain('fixed in the quote');
});

it('lets a service override the closing note', function () {
    $service = Service::firstWhere('slug', 'videography');
    $service->update(['sections' => array_replace_recursive(
        $service->sections,
        ['flow' => ['note' => 'Dates are agreed at kick-off.']]
    )]);

    $html = $this->get('/services/videography')->assertOk()->getContent();

    expect($html)->toContain('Dates are agreed at kick-off.');
});
