<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

uses(RefreshDatabase::class);

it('renders the json-ld component as a script tag', function () {
    $html = Blade::render(
        '<x-json-ld :data="$d" />',
        ['d' => ['@type' => 'Organization', 'name' => 'X']]
    );
    expect($html)->toContain('<script type="application/ld+json">')
        ->and($html)->toContain('"@type":"Organization"');
});

it('renders card-post with post data', function () {
    $post = Post::factory()->for(User::factory(), 'author')->create(['title' => 'Hello']);
    $html = Blade::render('<x-card-post :post="$p" />', ['p' => $post]);
    expect($html)->toContain('Hello')->and($html)->toContain($post->slug);
});
