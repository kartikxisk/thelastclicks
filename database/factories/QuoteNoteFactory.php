<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quote_id' => Quote::factory(),
            'author_id' => User::factory(),
            'body' => fake()->sentence(),
        ];
    }
}
