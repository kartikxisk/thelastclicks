<?php

use App\Models\Crew;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('crew index lists seeded members', function () {
    $first = Crew::first();
    $this->get('/crew')->assertOk()->assertSee($first->name);
});

it('crew detail renders by slug', function () {
    $first = Crew::first();
    $this->get('/crew/'.$first->slug)->assertOk()->assertSee($first->name);
});

it('crew detail 404 on unknown slug', function () {
    $this->get('/crew/nope')->assertNotFound();
});
