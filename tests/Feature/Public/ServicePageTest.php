<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('renders each seeded service page', function (string $slug) {
    $this->get("/services/{$slug}")->assertOk()->assertSeeText(ucwords(str_replace('-', ' ', $slug)));
})->with([
    'videography', 'photography', 'editing',
]);

it('redirects retired service slugs permanently', function (string $old, string $new) {
    $this->get("/services/{$old}")
        ->assertStatus(301)
        ->assertRedirect("/services/{$new}");
})->with([
    ['weddings', 'videography'],
    // Post Production was renamed to Editing and its slug moved with it, so the
    // old URL redirects like any other retired service.
    ['post-production', 'editing'],
    ['social-content', 'editing'],
    ['creative-direction', 'editing'],
]);

it('returns 404 for unknown service slug', function () {
    $this->get('/services/does-not-exist')->assertNotFound();
});

it('returns 404 for the retired talent service', function () {
    $this->get('/services/talent')->assertNotFound();
});
