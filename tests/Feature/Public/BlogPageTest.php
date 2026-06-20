<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('blog index lists published posts only', function () {
    Post::factory()->for(User::first(), 'author')->create(['status' => 'draft', 'title' => 'HiddenDraft']);
    $this->get('/blog')->assertOk()->assertDontSee('HiddenDraft');
});

it('blog detail renders published post', function () {
    $p = Post::published()->first();
    $this->get('/blog/'.$p->slug)->assertOk()->assertSee($p->title);
});

it('blog detail 404 on draft slug', function () {
    $p = Post::factory()->for(User::first(), 'author')->create(['status' => 'draft']);
    $this->get('/blog/'.$p->slug)->assertNotFound();
});

it('blog detail emits Article JSON-LD', function () {
    $p = Post::published()->first();
    $this->get('/blog/'.$p->slug)->assertSee('"@type":"Article"', false);
});
