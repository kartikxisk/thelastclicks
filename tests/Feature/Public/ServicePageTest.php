<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

// Title is asserted from a table rather than derived from the slug: the two part
// company once a service is renamed without moving its published URL, which is
// exactly what happened when Editing became Post Production at /services/editing.
it('renders each seeded service page', function (string $slug, string $title) {
    $this->get("/services/{$slug}")->assertOk()->assertSeeText($title);
})->with([
    ['videography', 'Videography'],
    ['photography', 'Photography'],
    ['editing', 'Post Production'],
]);

it('redirects retired service slugs permanently', function (string $old, string $new) {
    $this->get("/services/{$old}")
        ->assertStatus(301)
        ->assertRedirect("/services/{$new}");
})->with([
    ['weddings', 'videography'],
    // The service is called Post Production again, but /services/editing is the
    // address it has been published at and the slug does not follow the title —
    // so this redirect still runs in this direction, and must keep doing so.
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
