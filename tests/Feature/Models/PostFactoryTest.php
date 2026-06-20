<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a post with author and pivots', function () {
    $u = User::factory()->create();
    $p = Post::factory()->for($u, 'author')->create(['status' => 'published']);
    $p->categories()->sync([Category::factory()->create()->id]);
    $p->tags()->sync([Tag::factory()->create()->id]);

    expect($p->slug)->not->toBeEmpty()
        ->and($p->categories)->toHaveCount(1)
        ->and($p->tags)->toHaveCount(1);
});

it('scopes published posts only', function () {
    Post::factory()->create(['status' => 'draft']);
    Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);
    expect(Post::published()->count())->toBe(1);
});
