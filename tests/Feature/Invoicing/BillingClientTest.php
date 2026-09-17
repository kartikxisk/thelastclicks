<?php

use App\Models\BillingClient;
use App\Models\Client;
use Database\Factories\CompanyFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reads registration off the GSTIN rather than a second column', function () {
    // Two columns that can disagree is one column too many: a client with a
    // GSTIN is registered, by definition.
    expect(BillingClient::factory()->registered('27')->create()->isRegistered())->toBeTrue()
        ->and(BillingClient::factory()->unregistered()->create()->isRegistered())->toBeFalse();
});

it('falls back to the billing state when no place of supply is set', function () {
    $client = BillingClient::factory()->create([
        'place_of_supply_state_code' => null,
        'billing_address_state_code' => '29',
    ]);

    expect($client->placeOfSupplyStateCode())->toBe('29');
});

it('prefers an explicit place of supply over the billing state', function () {
    // A Bengaluru-registered client can commission a Delhi shoot. The override
    // exists because the two genuinely differ, and §12(7) makes which one wins
    // a judgement call rather than a derivation.
    $client = BillingClient::factory()->create([
        'place_of_supply_state_code' => '07',
        'billing_address_state_code' => '29',
    ]);

    expect($client->placeOfSupplyStateCode())->toBe('07');
});

it('falls through an empty-string place of supply override to the billing state', function () {
    // A Filament Select saves an untouched field as '', not null. `??` only
    // treats null as absent, so an empty-string override used to be returned
    // as-is instead of falling through — a blank that silently picked the
    // wrong tax split.
    $client = BillingClient::factory()->unregistered()->create([
        'place_of_supply_state_code' => '',
        'billing_address_state_code' => '29',
    ]);

    expect($client->placeOfSupplyStateCode())->toBe('29');
});

it('returns null when the override, the GSTIN and the billing state are all blank', function () {
    $client = BillingClient::factory()->unregistered()->create([
        'place_of_supply_state_code' => null,
        'billing_address_state_code' => null,
    ]);

    expect($client->placeOfSupplyStateCode())->toBeNull();
});

it('prefers the GSTIN state over the billing state when no override is set', function () {
    // The factory's registered() state always keeps the GSTIN and billing
    // state in step, so nothing previously exercised a registered client
    // whose GSTIN was issued in a different state than their billing address.
    $client = BillingClient::factory()->create([
        'gstin' => CompanyFactory::gstinFor('27'),
        'billing_address_state_code' => '29',
        'place_of_supply_state_code' => null,
    ]);

    expect($client->placeOfSupplyStateCode())->toBe('27');
});

it('optionally points at a logo-wall client and survives its deletion', function () {
    $logo = Client::create(['name' => 'DLF', 'is_active' => true]);
    $billing = BillingClient::factory()->create(['client_id' => $logo->id]);

    expect($billing->client->name)->toBe('DLF');

    $logo->delete();

    expect($billing->fresh())->not->toBeNull()
        ->and($billing->fresh()->client_id)->toBeNull();
});

it('stores extra recipients as a list', function () {
    $client = BillingClient::factory()->create(['cc_emails' => ['accounts@acme.test', 'cfo@acme.test']]);

    expect($client->fresh()->cc_emails)->toBe(['accounts@acme.test', 'cfo@acme.test']);
});

it('uppercases a GSTIN and PAN assigned straight onto the model', function () {
    // Same reason as Company: the Gstin rule accepts a lowercase GSTIN and has
    // no channel to change what is persisted, so only the Filament form
    // normalised storage. Anything that writes this model without a form —
    // an import, a command, a seeder — would have stored the lowercase value
    // that then gets filed.
    $client = BillingClient::factory()->create([
        'gstin' => strtolower(CompanyFactory::gstinFor('29')),
        'pan' => 'aapfu0939f',
    ]);

    expect($client->fresh()->gstin)->toBe(CompanyFactory::gstinFor('29'))
        ->and($client->fresh()->pan)->toBe('AAPFU0939F');
});

it('leaves an absent GSTIN and PAN null rather than storing an empty string', function () {
    $client = BillingClient::factory()->unregistered()->create(['pan' => '']);

    expect($client->fresh()->gstin)->toBeNull()
        ->and($client->fresh()->pan)->toBeNull();
});

it('scopes to active clients', function () {
    // Company and ServiceItem both have this test; BillingClient did not, so
    // nothing would have caught its scope being dropped or inverted.
    BillingClient::factory()->create(['is_active' => true]);
    BillingClient::factory()->create(['is_active' => false]);

    expect(BillingClient::active()->count())->toBe(1);
});
