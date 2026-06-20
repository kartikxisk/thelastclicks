<?php

use App\Models\Post;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\ResponseCache\Facades\ResponseCache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    ResponseCache::clear();
});

it('post update flushes response cache for blog index', function () {
    $post = Post::published()->first();

    // First request (cache miss → cache populates)
    $this->get('/blog')->assertOk();

    // Update post → observer must clear cache
    $post->update(['title' => 'Updated Title XYZ123']);

    // Next request reflects the update
    $this->get('/blog')->assertOk()->assertSee('Updated Title XYZ123');
});

it('site setting update flushes cache for contact page', function () {
    // Prime cache
    $this->get('/contact')->assertOk();

    // Update setting
    SiteSetting::set('contact_email', 'new@email.com');

    // Next request reflects update
    $this->get('/contact')->assertOk()->assertSee('new@email.com');
});
