<?php

use App\Models\Industry;
use App\Models\Portfolio;
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

it('edited industry summary appears on its detail page', function () {
    $ind = Industry::where('slug', 'fashion-creators')->first();
    $ind->update(['summary' => 'Edited industry summary XYZ789']);

    $this->get('/industries/fashion-creators')
        ->assertOk()
        ->assertSeeText('Edited industry summary XYZ789');
});

it('edited portfolio title appears on public detail', function () {
    $p = Portfolio::published()->first();
    $p->update(['title' => 'Renamed Case Study QWERTY']);

    $this->get('/portfolio/'.$p->slug)
        ->assertOk()
        ->assertSeeText('Renamed Case Study QWERTY');
});

it('edited blog post body appears on its detail page', function () {
    $post = Post::published()->first();
    $post->update(['body' => '<p>Edited body content ZZZ123</p>']);

    $this->get('/blog/'.$post->slug)
        ->assertOk()
        ->assertSee('Edited body content ZZZ123');
});
