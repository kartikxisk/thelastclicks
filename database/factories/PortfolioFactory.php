<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortfolioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'title' => fake()->sentence(4),
            'client' => fake()->company(),
            'year' => fake()->numberBetween(2020, 2026),
            'body' => fake()->paragraphs(3, true),
            'status' => 'draft',
        ];
    }
}
