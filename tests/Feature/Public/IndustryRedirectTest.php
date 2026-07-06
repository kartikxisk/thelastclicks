<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects retired industry slugs permanently', function (string $old, string $new) {
    $this->get('/industries/'.$old)
        ->assertStatus(301)
        ->assertRedirect('/industries/'.$new);
})->with([
    ['corporate-conferences', 'corporate-events'],
    ['brand-launches', 'brands-products'],
    ['automobile-showcases', 'brands-products'],
    ['lifestyle-beverage', 'brands-products'],
    ['destination-weddings', 'weddings-celebrations'],
    ['commercial-productions', 'motion-post-production'],
]);
