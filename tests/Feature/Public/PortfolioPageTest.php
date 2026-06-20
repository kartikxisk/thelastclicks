<?php

use App\Models\Portfolio;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('portfolio index shows only published items', function () {
    Portfolio::factory()->create(['status' => 'draft', 'title' => 'HiddenDraft']);
    $r = $this->get('/portfolio')->assertOk();
    $r->assertDontSee('HiddenDraft');
});

it('portfolio detail renders by slug', function () {
    $p = Portfolio::published()->first();
    $this->get('/portfolio/'.$p->slug)->assertOk()->assertSee($p->title);
});

it('portfolio detail 404 on unknown slug', function () {
    $this->get('/portfolio/nope')->assertNotFound();
});

it('portfolio detail 404 on draft', function () {
    $p = Portfolio::factory()->create(['status' => 'draft']);
    $this->get('/portfolio/'.$p->slug)->assertNotFound();
});
