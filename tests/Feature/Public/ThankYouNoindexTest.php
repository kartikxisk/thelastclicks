<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('marks /thank-you noindex', function () {
    // The page opens without any submission, so it can be indexed and show up
    // as a search result promising a confirmation nobody asked for.
    $this->get('/thank-you')
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex, follow">', false);
});

it('leaves an ordinary page indexable', function () {
    $this->get('/about')->assertOk()->assertDontSee('name="robots"', false);
});
