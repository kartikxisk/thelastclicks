<?php

namespace Database\Factories;

use App\Invoicing\StateCodes;
use App\Models\Company;
use App\Rules\Gstin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $stateCode = '07';

        return [
            'name' => fake()->company(),
            'legal_name' => fake()->company().' Private Limited',
            'is_gst_registered' => true,
            'gstin' => self::gstinFor($stateCode),
            'pan' => 'AAPFU0939F',
            'address_line1' => fake()->streetAddress(),
            'address_city' => 'New Delhi',
            'address_state' => StateCodes::name($stateCode),
            'address_state_code' => $stateCode,
            'address_postal_code' => '110024',
            'address_country' => 'IN',
            'email' => fake()->companyEmail(),
            'phone' => '+91 98100 00000',
            'bank_name' => 'HDFC Bank',
            'bank_account_name' => fake()->company(),
            'bank_account_number' => (string) fake()->numberBetween(10000000000, 99999999999),
            'bank_ifsc' => 'HDFC0001234',
            'upi_id' => 'studio@hdfcbank',
            'invoice_prefix' => 'TLC',
            'credit_note_prefix' => 'TLCC',
            'proforma_prefix' => 'TLCP',
            'receipt_prefix' => 'TLCR',
            'is_default' => false,
            'is_active' => true,
        ];
    }

    public function unregistered(): self
    {
        return $this->state(fn (): array => [
            'is_gst_registered' => false,
            'gstin' => null,
        ]);
    }

    /**
     * Build a GSTIN that passes the real rule, by computing the same check digit
     * the rule verifies. A hardcoded fixture would either be one real
     * registration repeated everywhere or, worse, a made-up string that only
     * passes because the rule is broken.
     */
    public static function gstinFor(string $stateCode): string
    {
        $first14 = $stateCode.'AAPFU0939F1Z';

        return $first14.Gstin::checksum($first14);
    }

    /** Keep the GSTIN's state in step when a test picks a different one. */
    public function inState(string $stateCode): self
    {
        return $this->state(fn (): array => [
            'address_state_code' => $stateCode,
            'address_state' => StateCodes::name($stateCode),
            'gstin' => self::gstinFor($stateCode),
        ]);
    }
}
