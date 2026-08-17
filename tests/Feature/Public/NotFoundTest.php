<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('renders the styled 404 page on unknown route', function () {
    // Asserts the visible headline, not the <title>. The old assertion matched
    // "Page not found" — a string that only ever appeared in the title tag, so
    // the test passed on a page whose body could have been blank, and broke the
    // moment the title's casing changed.
    $this->get('/does-not-exist-anywhere')
        ->assertStatus(404)
        ->assertSee('This frame');
});
