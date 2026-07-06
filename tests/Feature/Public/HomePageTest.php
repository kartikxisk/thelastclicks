<?php

use App\Models\Testimonial;
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

it('homepage shows seeded testimonials from the database', function () {
    $this->get('/')->assertOk()->assertSee('Priya Mehta');
});

it('homepage hides testimonial section when none published', function () {
    Testimonial::query()->update(['is_published' => false]);
    $this->get('/')->assertOk()->assertDontSee('What our');
});
