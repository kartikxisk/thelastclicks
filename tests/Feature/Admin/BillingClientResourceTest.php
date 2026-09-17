<?php

use App\Filament\Resources\BillingClientResource\Pages\CreateBillingClient;
use App\Filament\Resources\BillingClientResource\Pages\ListBillingClients;
use App\Models\BillingClient;
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

it('Super-admin can list billing clients', function () {
    BillingClient::factory()->count(2)->create();

    Livewire::test(ListBillingClients::class)->assertCanSeeTableRecords(BillingClient::all());
});

it('creates an unregistered client without a GSTIN', function () {
    // Most of a studio clients are individuals with no registration. Requiring
    // a GSTIN here would make the common case the hard one.
    Livewire::test(CreateBillingClient::class)
        ->fillForm([
            'name' => 'Ananya Sharma',
            'email' => 'ananya@example.test',
            'billing_address_state_code' => '07',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(BillingClient::where('name', 'Ananya Sharma')->first()->isRegistered())->toBeFalse();
});

it('rejects a malformed GSTIN', function () {
    Livewire::test(CreateBillingClient::class)
        ->fillForm([
            'name' => 'Acme Events',
            'gstin' => '27AAPFU0939F1ZZ',
            'billing_address_state_code' => '27',
        ])
        ->call('create')
        ->assertHasFormErrors(['gstin']);
});

it('accepts a valid GSTIN from any state, because the client may be anywhere', function () {
    // Deliberately disagreeing: a client can be registered in one state and
    // billed at an address in another, and their GSTIN's state is what
    // determines the place of supply rather than something to validate
    // against. If this form's GSTIN rule were ever coupled to the billing
    // state address — new Gstin($get('billing_address_state_code')) — this
    // is the test that would catch it.
    Livewire::test(CreateBillingClient::class)
        ->fillForm([
            'name' => 'Acme Events',
            'gstin' => CompanyFactory::gstinFor('29'),
            'billing_address_state_code' => '07',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('uppercases a lowercase GSTIN and PAN rather than failing the format check', function () {
    // Same reason as the company form: the characters in a pasted GSTIN are
    // right and only the case is wrong, so "format is invalid" names the wrong
    // problem.
    Livewire::test(CreateBillingClient::class)
        ->fillForm([
            'name' => 'Lowercase Events',
            'gstin' => strtolower(CompanyFactory::gstinFor('29')),
            'pan' => 'aapfu0939f',
            'billing_address_state_code' => '07',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $client = BillingClient::where('name', 'Lowercase Events')->first();

    expect($client->gstin)->toBe(CompanyFactory::gstinFor('29'))
        ->and($client->pan)->toBe('AAPFU0939F');
});
