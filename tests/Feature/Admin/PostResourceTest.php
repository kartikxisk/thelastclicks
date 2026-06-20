<?php

use App\Filament\Resources\PostResource\Pages\CreatePost;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list posts', function () {
    Livewire::test(ListPosts::class)->assertCanSeeTableRecords(Post::all());
});

it('Super-admin can create a published post with categories + tags', function () {
    $cat = Category::factory()->create();
    $tag = Tag::factory()->create();

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Hello from Test',
            'excerpt' => 'A new post',
            'body' => '<p>Body content</p>',
            'status' => 'published',
            'published_at' => now()->toDateTimeString(),
            'categories' => [$cat->id],
            'tags' => [$tag->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $p = Post::where('title', 'Hello from Test')->first();
    expect($p)->not->toBeNull()
        ->and($p->author_id)->toBe($this->admin->id)
        ->and($p->status)->toBe('published')
        ->and($p->categories->pluck('id')->all())->toBe([$cat->id])
        ->and($p->tags->pluck('id')->all())->toBe([$tag->id]);
});

it('Editor can edit only their own posts', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    $mine = Post::factory()->for($editor, 'author')->create();
    $other = Post::factory()->create();

    expect($editor->can('update', $mine))->toBeTrue()
        ->and($editor->can('update', $other))->toBeFalse();
});
