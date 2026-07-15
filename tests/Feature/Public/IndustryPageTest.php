<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('industry index lists seeded industries', function () {
    $this->get('/industries')->assertOk()->assertSee('Fashion');
});

it('industry detail slugs permanently redirect to the list', function (string $slug) {
    $this->get('/industries/'.$slug)->assertRedirect('/industries')->assertStatus(301);
})->with(['weddings-celebrations', 'destination-weddings', 'anything-else']);
