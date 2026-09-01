<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

// Title is asserted from a table rather than derived from the slug: the two can
// part company, and did while the service was named Post Production but served
// from /services/editing. The address now matches the name.
it('renders each seeded service page', function (string $slug, string $title) {
    $this->get("/services/{$slug}")->assertOk()->assertSeeText($title);
})->with([
    ['videography', 'Videography'],
    ['photography', 'Photography'],
    ['post-production', 'Post Production'],
]);

it('redirects retired service slugs permanently', function (string $old, string $new) {
    $this->get("/services/{$old}")
        ->assertStatus(301)
        ->assertRedirect("/services/{$new}");
})->with([
    ['weddings', 'videography'],
    // /services/editing is published and indexed, so it redirects onto the
    // renamed address rather than 404ing. This pair used to point the other way.
    ['editing', 'post-production'],
    ['social-content', 'post-production'],
    ['creative-direction', 'post-production'],
]);

it('returns 404 for unknown service slug', function () {
    $this->get('/services/does-not-exist')->assertNotFound();
});

it('returns 404 for the retired talent service', function () {
    $this->get('/services/talent')->assertNotFound();
});
