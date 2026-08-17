<?php

namespace Database\Factories;

use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Work>
 */
class WorkFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(2, true),
            'summary' => fake()->sentence(),
            'client' => fake()->company(),
            'category' => fake()->randomElement(array_keys(Work::CATEGORIES)),
            'year' => (string) fake()->numberBetween(2021, 2026),
            'order' => 0,
            // Published by default: the interesting assertions are about work that
            // is live, and a factory whose default is invisible makes every such
            // test carry the same override.
            'is_published' => true,
        ];
    }

    /** A project that exists in the admin but must never reach a public page. */
    public function draft(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
