<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('renders the homepage with key copy', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('TheLastClicks')
        ->assertSee('Capturing', false);
});

it('homepage emits Organization JSON-LD', function () {
    $this->get('/')->assertSee('"@type":"Organization"', false);
});
