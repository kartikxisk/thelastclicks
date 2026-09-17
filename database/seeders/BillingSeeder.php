<?php

namespace Database\Seeders;

use App\Invoicing\Money;
use App\Models\Company;
use App\Models\ServiceItem;
use App\Support\Nap;
use Illuminate\Database\Seeder;

/**
 * Brings up a billing entity and a starter rate card.
 *
 * Everything tax-related is deliberately left blank. A seeded GSTIN would be a
 * made-up number sitting on a document that is a legal declaration, so the
 * company arrives `is_gst_registered = false` and phase-2 validation refuses to
 * issue a tax invoice until a human has filled in the real registration.
 */
class BillingSeeder extends Seeder
{
    public function run(): void
    {
        $address = Nap::address() ?? [];

        $company = Company::firstOrCreate(
            ['name' => config('app.name')],
            [
                'is_gst_registered' => false,
                'address_line1' => $address['streetAddress'] ?? null,
                'address_city' => $address['addressLocality'] ?? null,
                'address_state' => $address['addressRegion'] ?? null,
                'address_postal_code' => $address['postalCode'] ?? null,
                'address_country' => $address['addressCountry'] ?? 'IN',
                'email' => config('mail.from.address'),
                'invoice_prefix' => 'INV',
                'credit_note_prefix' => 'CRN',
                'proforma_prefix' => 'PRO',
                'receipt_prefix' => 'RCT',
                'default_template' => 'classic',
                'default_payment_terms_days' => 7,
                'is_active' => true,
            ]
        );

        if (! $company->is_default) {
            $company->makeDefault();
        }

        foreach ($this->rateCard() as $item) {
            ServiceItem::firstOrCreate(
                ['company_id' => null, 'name' => $item['name']],
                $item
            );
        }
    }

    /**
     * Starter lines, all at SAC 998383 / 18% — event photography and
     * videography. Rates are placeholders an admin is expected to edit.
     *
     * @return list<array<string, mixed>>
     */
    private function rateCard(): array
    {
        return [
            ['name' => 'Full-day event coverage', 'unit' => 'day', 'rate_paise' => Money::fromRupees('0'), 'sac_code' => '998383', 'sort' => 10],
            ['name' => 'Half-day shoot', 'unit' => 'day', 'rate_paise' => Money::fromRupees('0'), 'sac_code' => '998383', 'sort' => 20],
            ['name' => 'Brand film', 'unit' => 'project', 'rate_paise' => Money::fromRupees('0'), 'sac_code' => '998383', 'sort' => 30],
            ['name' => 'Reel edit', 'unit' => 'item', 'rate_paise' => Money::fromRupees('0'), 'sac_code' => '998386', 'sort' => 40],
            ['name' => 'Travel and accommodation', 'unit' => 'item', 'rate_paise' => Money::fromRupees('0'), 'sac_code' => '998383', 'sort' => 90, 'is_expense' => true],
        ];
    }
}
