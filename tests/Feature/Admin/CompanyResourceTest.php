<?php

use App\Filament\Resources\CompanyResource\Pages\CreateCompany;
use App\Filament\Resources\CompanyResource\Pages\ListCompanies;
use App\Models\Company;
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
