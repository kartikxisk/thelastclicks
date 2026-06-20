<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'title' => fake()->sentence(6),
            'excerpt' => fake()->sentence(),
            'body' => fake()->paragraphs(4, true),
            'status' => 'draft',
            'published_at' => null,
        ];
    }
}
