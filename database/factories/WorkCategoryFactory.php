<?php

namespace Database\Factories;

use App\Models\Industry;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'industry_id' => Industry::factory(),
            'title' => fake()->unique()->words(2, true),
            'order' => 0,
        ];
    }
}
