<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::first();
        $cat = Category::factory()->create(['name' => 'Behind the scenes']);
        $tag = Tag::factory()->create(['name' => 'craft']);
        Post::factory()->count(5)->for($author, 'author')
            ->state(['status' => 'published', 'published_at' => now()->subDays(rand(1, 30))])
            ->create()
            ->each(function ($p) use ($cat, $tag) {
                $p->categories()->sync([$cat->id]);
                $p->tags()->sync([$tag->id]);
            });
    }
}
