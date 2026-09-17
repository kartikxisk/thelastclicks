<?php

namespace Database\Factories;

use App\Invoicing\Money;
use App\Models\ServiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceItem>
 */
class ServiceItemFactory extends Factory
{
    protected $model = ServiceItem::class;

    public function definition(): array
    {
        return [
            'company_id' => null,
            'name' => fake()->randomElement(['Full-day event coverage', 'Half-day shoot', 'Reel edit', 'Brand film']),
            'description' => fake()->sentence(),
            'sac_code' => '998383',
            'unit' => 'project',
            'rate_paise' => Money::fromRupees(fake()->numberBetween(5000, 200000)),
            'tax_rate_bps' => 1800,
            'is_expense' => false,
            'sort' => 0,
            'is_active' => true,
        ];
    }

    public function expense(): self
    {
        return $this->state(fn (): array => [
            'name' => 'Travel and accommodation',
            'is_expense' => true,
        ]);
    }
}
