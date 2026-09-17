<?php

namespace Database\Seeders;

use App\Invoicing\Money;
use App\Invoicing\StateCodes;
use App\Models\Company;
use App\Models\ServiceItem;
use App\Support\Nap;
use Illuminate\Database\Seeder;

/**
 * Bootstraps an empty database with a billing entity and a starter rate card.
 * It deliberately does not top up or repair a database that already has one.
 *
 * A lookup keyed on `config('app.name')` would silently create a *second*
 * company the day `APP_NAME` differs from whatever it was when the database
 * was first seeded — a rebrand, or simply an environment configured
 * differently, both of which this project has a recorded history of, and
 * `companies.name` carries no unique index to stop it. And promoting whatever
 * company this seeder finds to default, on every deploy, would silently
 * revert an admin's later choice of a different, real, GST-registered company
 * as the one `Company::default()` hands every new invoice — the seeder has no
 * way to tell "this row has never been default" apart from "an admin
 * deliberately demoted it". Testing existence instead of identity is what
 * avoids both: once the database has a company or a shared rate card, this
 * seeder leaves them exactly as it finds them.
 *
 * Everything tax-related is deliberately left blank on the seeded company. A
 * seeded GSTIN would be a made-up number sitting on a document that is a
 * legal declaration, so the company arrives `is_gst_registered = false` and
 * phase-2 validation refuses to issue a tax invoice until a human has filled
 * in the real registration.
 */
class BillingSeeder extends Seeder
{
    public function run(): void
    {
        if (Company::count() === 0) {
            $company = Company::create($this->companyAttributes());

            // CompanyObserver auto-promotes the very first company created, so
            // this is usually a no-op — kept for the edge case where it somehow
            // isn't, since a company with no default anywhere leaves every
            // invoice form blank.
            if (Company::default() === null) {
                $company->makeDefault();
            }
        }

        if (! ServiceItem::whereNull('company_id')->exists()) {
            foreach ($this->rateCard() as $item) {
                ServiceItem::create($item);
            }
        }
    }

    /** @return array<string, mixed> */
    private function companyAttributes(): array
    {
        $address = Nap::address() ?? [];

        // addressRegion is free text out of Site Settings, so the code has to be
        // derived from it. Seeding the name alone left the printed field filled
        // and the tax-deciding field empty on a fresh production database.
        //
        // When the name matches no state, both fields are left null rather than
        // storing a name with no code: a half-filled pair looks complete on the
        // form while the intra-state vs inter-state split silently has no input,
        // and one visibly empty field is the better thing for an admin to meet.
        $stateName = $address['addressRegion'] ?? null;
        $stateCode = StateCodes::codeFor($stateName);

        return [
            'name' => config('app.name'),
            'is_gst_registered' => false,
            'address_line1' => $address['streetAddress'] ?? null,
            'address_city' => $address['addressLocality'] ?? null,
            'address_state' => $stateCode === null ? null : $stateName,
            'address_state_code' => $stateCode,
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
        ];
    }

    /**
     * Starter lines at 18%, mostly SAC 998383 — event photography and
     * videography. The reel edit is 998386 (film post-production), because that
     * is the service it actually is. Rates are placeholders an admin is
     * expected to edit.
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
