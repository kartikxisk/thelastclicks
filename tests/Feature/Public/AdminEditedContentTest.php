<?php

use App\Models\Post;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('edited service hero_copy appears on its public page', function () {
    $svc = Service::where('slug', 'photography')->first();
    $svc->update(['hero_copy' => 'Editor-set tagline ABC123']);

    $this->get('/services/photography')
        ->assertOk()
        ->assertSeeText('Editor-set tagline ABC123');
});

it('edited blog post body appears on its detail page', function () {
    $post = Post::published()->first();
    $post->update(['body' => '<p>Edited body content ZZZ123</p>']);

    $this->get('/blog/'.$post->slug)
        ->assertOk()
        ->assertSee('Edited body content ZZZ123');
});
