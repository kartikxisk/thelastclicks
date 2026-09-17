<?php

use App\Filament\Resources\CompanyResource\Pages\CreateCompany;
use App\Filament\Resources\CompanyResource\Pages\EditCompany;
use App\Filament\Resources\CompanyResource\Pages\ListCompanies;
use App\Models\Company;
use App\Models\ServiceItem;
use App\Models\User;
use Database\Factories\CompanyFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list companies', function () {
    Livewire::test(ListCompanies::class)->assertCanSeeTableRecords(Company::all());
});

it('creates a company with a valid GSTIN', function () {
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'TheLastClicks Studio',
            'is_gst_registered' => true,
            'gstin' => CompanyFactory::gstinFor('07'),
            'address_state_code' => '07',
            'invoice_prefix' => 'TLC',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Company::where('name', 'TheLastClicks Studio')->exists())->toBeTrue();
});

it('rejects a GSTIN whose checksum is wrong', function () {
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'Typo Studio',
            'is_gst_registered' => true,
            'gstin' => '07AAACT2727Q1ZW',
            'address_state_code' => '07',
        ])
        ->call('create')
        ->assertHasFormErrors(['gstin']);
});

it('rejects a GSTIN from a different state than the address', function () {
    // The invoice reads the state code to decide CGST+SGST versus IGST. A GSTIN
    // and an address that disagree make that decision wrong in a way nobody
    // notices until a return is filed.
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'Mismatched Studio',
            'is_gst_registered' => true,
            'gstin' => CompanyFactory::gstinFor('27'),
            'address_state_code' => '07',
        ])
        ->call('create')
        ->assertHasFormErrors(['gstin']);
});

it('rejects a prefix that would overflow the 16-character invoice number', function () {
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'Long Prefix Studio',
            'is_gst_registered' => false,
            'invoice_prefix' => 'TOOLONG',
        ])
        ->call('create')
        ->assertHasFormErrors(['invoice_prefix']);
});

it('switches the default from the table', function () {
    // BillingSeeder already created a default company, and CompanyObserver
    // only auto-defaults the very first company ever created — so $first is
    // never the default on its own. Making it the default explicitly first is
    // what makes the later assertions mean anything: without this, $first
    // being "not default" afterwards would be true whether or not the table
    // action demoted anyone.
    $first = Company::factory()->create();
    $second = Company::factory()->create();
    $first->makeDefault();

    expect($first->fresh()->is_default)->toBeTrue();

    Livewire::test(ListCompanies::class)
        ->callTableAction('makeDefault', $second);

    expect($second->fresh()->is_default)->toBeTrue()
        ->and($first->fresh()->is_default)->toBeFalse()
        // The assertion that actually catches a broken demotion: one that adds
        // a new default without clearing the old one would leave two rows
        // with is_default = true, and the two checks above alone wouldn't
        // notice.
        ->and(Company::where('is_default', true)->count())->toBe(1);
});

it('refuses a GST-registered company with no state code', function () {
    // address_state_code is the input to the intra-state vs inter-state split.
    // Left blank it is also not passed to the Gstin rule, so the state
    // cross-check is skipped entirely — and phase 2 then evaluates
    // company.state_code == place_of_supply as null == '07', which is false,
    // so every invoice goes out as IGST. That is the wrong tax on a document
    // the law does not allow us to edit afterwards.
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'Stateless Studio',
            'is_gst_registered' => true,
            'gstin' => CompanyFactory::gstinFor('07'),
            'address_state_code' => null,
            'invoice_prefix' => 'TLC',
        ])
        ->call('create')
        ->assertHasFormErrors(['address_state_code']);
});

it('still allows an unregistered company with no state code', function () {
    // Only the GST-registered case needs the state: an unregistered entity
    // issues no tax invoice, so there is no split to get wrong.
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'Unregistered Studio',
            'is_gst_registered' => false,
            'address_state_code' => null,
            'invoice_prefix' => 'UNR',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('refuses to delete the last active company', function () {
    // Spec 4.1: the policy refuses to delete or deactivate the last active
    // company. The deactivate half lives in CompanyObserver; this is the other
    // half. Delete is one click from the EditCompany header action, and
    // service_items.company_id is cascadeOnDelete, so the company-scoped rate
    // card goes with it silently.
    $admin = User::where('email', config('app.admin_seed_email'))->first();
    Company::query()->delete();
    $only = Company::factory()->create();

    expect($admin->can('delete', $only))->toBeFalse();
});

it('allows deleting a company when another active one remains', function () {
    $admin = User::where('email', config('app.admin_seed_email'))->first();
    Company::query()->delete();
    Company::factory()->create();
    $second = Company::factory()->create();

    expect($admin->can('delete', $second))->toBeTrue();
});

it('counts only active companies as survivors', function () {
    // An inactive company cannot be Company::default(), so it is not a
    // survivor: deleting the last ACTIVE one still leaves every invoice form
    // with no company to prefill from.
    $admin = User::where('email', config('app.admin_seed_email'))->first();
    Company::query()->delete();
    $only = Company::factory()->create();
    Company::factory()->create()->forceFill(['is_active' => false])->saveQuietly();

    expect($admin->can('delete', $only))->toBeFalse();
});

it('names the rate-card rows a company delete takes with it', function () {
    // service_items.company_id is cascadeOnDelete. Without this the admin is
    // asked "are you sure?" about a company and silently loses its rate card.
    Company::query()->delete();
    Company::factory()->create();
    $doomed = Company::factory()->create();
    ServiceItem::factory()->count(2)->create(['company_id' => $doomed->id]);
    ServiceItem::factory()->create(['company_id' => null]);

    Livewire::test(EditCompany::class, ['record' => $doomed->getRouteKey()])
        ->mountAction('delete')
        // Two scoped rows, not three: the shared row belongs to no company and
        // survives the delete.
        ->assertSee('deletes 2 rate-card row(s)');
});

it('uppercases a lowercase GSTIN, PAN and IFSC rather than failing the format check', function () {
    // GSTIN, PAN and IFSC are uppercase by definition, and a paste out of an
    // email is routinely not. Failing it with "format is invalid" names the
    // wrong problem — the characters are right, the case is not — so the case
    // is corrected on the way into the column instead.
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'Lowercase Studio',
            'is_gst_registered' => true,
            'gstin' => strtolower(CompanyFactory::gstinFor('07')),
            'address_state_code' => '07',
            'pan' => 'aapfu0939f',
            'bank_ifsc' => 'hdfc0001234',
            'invoice_prefix' => 'TLC',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $company = Company::where('name', 'Lowercase Studio')->first();

    expect($company->gstin)->toBe(CompanyFactory::gstinFor('07'))
        ->and($company->pan)->toBe('AAPFU0939F')
        ->and($company->bank_ifsc)->toBe('HDFC0001234');
});

it('refuses a negative default payment term', function () {
    // The column is unsignedInteger. A bare ->numeric() only adds the `numeric`
    // rule, which passes -5 — and MySQL answers an out-of-range value with
    // error 1264, a 500 for the admin, while SQLite stores the negative
    // silently. The branch already learned this for rate_paise; this field was
    // missed.
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'Negative Terms Studio',
            'is_gst_registered' => false,
            'invoice_prefix' => 'NEG',
            'default_payment_terms_days' => -5,
        ])
        ->call('create')
        ->assertHasFormErrors(['default_payment_terms_days']);
});

it('hides Make default from an account that can see companies but not change them', function () {
    // A plain Action::make() is not auto-wired to a policy the way EditAction
    // is, so without ->authorize('update') this write action sits unguarded on
    // a screen someone may only be able to read. No role today reaches that —
    // Accounts holds the whole billing surface and Viewer holds none of it —
    // so this pins the guard against the first narrower billing role rather
    // than a bug anyone can hit now.
    $readOnly = User::factory()->create();
    $readOnly->givePermissionTo(['view_any_company', 'view_company']);

    $company = Company::factory()->create();

    $this->actingAs($readOnly);

    Livewire::test(ListCompanies::class)
        ->assertTableActionHidden('makeDefault', $company);
});

it('still offers Make default to an account that can update companies', function () {
    // The other half: the authorize() must not have hidden the action from
    // everyone, which a wrong ability name would do silently.
    $company = Company::factory()->create();

    Livewire::test(ListCompanies::class)
        ->assertTableActionVisible('makeDefault', $company);
});

it('clears the stored GSTIN when the admin unticks GST registered', function () {
    // The GSTIN field is ->visible() on the toggle, and a hidden Filament field
    // is not dehydrated — so EditRecord's update() never receives the key and
    // the old number stays behind a false is_gst_registered.
    $company = Company::factory()->create([
        'is_gst_registered' => true,
        'gstin' => CompanyFactory::gstinFor('07'),
        'address_state_code' => '07',
    ]);

    Livewire::test(EditCompany::class, ['record' => $company->getRouteKey()])
        ->fillForm(['is_gst_registered' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($company->fresh()->gstin)->toBeNull();
});
