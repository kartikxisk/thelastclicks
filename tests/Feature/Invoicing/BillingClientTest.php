<?php

use App\Models\BillingClient;
use App\Models\Client;
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
