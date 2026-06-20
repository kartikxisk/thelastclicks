<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(2, true),
            'hero_copy' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'order' => 0,
        ];
    }
}
