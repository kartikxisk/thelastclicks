<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('renders each seeded service page', function (string $slug) {
    $this->get("/services/{$slug}")->assertOk()->assertSeeText(ucwords(str_replace('-', ' ', $slug)));
})->with([
    'videography', 'photography', 'weddings', 'post-production',
    'social-content', 'creative-direction', 'talent',
]);

it('returns 404 for unknown service slug', function () {
    $this->get('/services/does-not-exist')->assertNotFound();
});
