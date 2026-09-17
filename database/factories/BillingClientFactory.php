<?php

namespace Database\Factories;

use App\Invoicing\StateCodes;
use App\Models\BillingClient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingClient>
 */
class BillingClientFactory extends Factory
{
    protected $model = BillingClient::class;

    public function definition(): array
    {
        return [
            'client_id' => null,
            'name' => fake()->company(),
            // Unregistered by default: most of a studio's clients are, and it is
            // the case with the extra Rule 46(e) requirements, so it is the one
            // worth exercising by accident.
            'gstin' => null,
            'email' => fake()->companyEmail(),
            'phone' => '+91 98100 00000',
            'billing_address_line1' => fake()->streetAddress(),
            'billing_address_city' => 'New Delhi',
            'billing_address_state' => 'Delhi',
            'billing_address_state_code' => '07',
            'billing_address_postal_code' => '110024',
            'billing_address_country' => 'IN',
            'place_of_supply_state_code' => null,
            'currency' => 'INR',
            'is_active' => true,
        ];
    }

    public function registered(string $stateCode = '07'): self
    {
        return $this->state(fn (): array => [
            'gstin' => CompanyFactory::gstinFor($stateCode),
            'billing_address_state_code' => $stateCode,
            'billing_address_state' => StateCodes::name($stateCode),
        ]);
    }

    public function unregistered(): self
    {
        return $this->state(fn (): array => ['gstin' => null]);
    }
}
