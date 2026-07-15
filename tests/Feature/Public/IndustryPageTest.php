<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('industry index lists seeded industries', function () {
    $this->get('/industries')->assertOk()->assertSee('Fashion');
});

it('industry detail renders by slug', function () {
    $this->get('/industries/fashion-creators')->assertOk()->assertSee('Fashion');
});

it('industry detail 404 on unknown slug', function () {
    $this->get('/industries/nope')->assertNotFound();
});

it('industry page shows its own testimonials', function () {
    $this->get('/industries/weddings-celebrations')->assertOk()->assertSee('Sneha');
});
