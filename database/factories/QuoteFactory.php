<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company' => fake()->optional()->company(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'project_type' => fake()->randomElement(['Brand film / commercial', 'Wedding', 'Editorial / photography', 'Other']),
            'budget' => fake()->randomElement(['Under ₹5L', '₹5L – ₹15L', '₹15L – ₹50L']),
            'timeline' => 'Flexible',
            'message' => fake()->paragraph(),
            'source_page' => '/contact',
            'ip' => fake()->ipv4(),
            'ua' => fake()->userAgent(),
            'status' => 'new',
        ];
    }
}
