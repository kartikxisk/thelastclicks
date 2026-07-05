<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'industry_id' => null,
            'quote' => fake()->sentence(12),
            'client_name' => fake()->name(),
            'role_company' => fake()->jobTitle().', '.fake()->company(),
            'order' => 0,
            'is_published' => true,
        ];
    }
}
