<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('renders the styled 404 page on unknown route', function () {
    $this->get('/does-not-exist-anywhere')
        ->assertStatus(404)
        ->assertSee('Page not found');
});
