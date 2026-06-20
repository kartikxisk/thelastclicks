<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CrewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name(),
            'role' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'social_json' => ['instagram' => 'https://instagram.com/x'],
            'order' => 0,
        ];
    }
}
