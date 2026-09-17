<?php

use App\Models\BillingClient;
use App\Models\Company;
use App\Models\ServiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('gives the Accounts role the billing resources', function () {
    $accounts = User::factory()->create();
    $accounts->assignRole('Accounts');

    expect($accounts->can('viewAny', Company::class))->toBeTrue()
        ->and($accounts->can('create', BillingClient::class))->toBeTrue()
        ->and($accounts->can('update', ServiceItem::factory()->create()))->toBeTrue();
});

it('keeps billing away from Editor', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    expect($editor->can('viewAny', Company::class))->toBeFalse()
        ->and($editor->can('viewAny', BillingClient::class))->toBeFalse();
});

it('keeps billing away from Viewer, despite Viewer holding every other view permission', function () {
    // Viewer is granted every `view_*` permission by a blanket filter. Billing
    // carries bank details and client GSTINs, so it is excluded explicitly —
    // otherwise read-only quietly means "can read our bank account".
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');

    expect($viewer->can('viewAny', Company::class))->toBeFalse()
        ->and($viewer->can('viewAny', BillingClient::class))->toBeFalse()
        ->and($viewer->can('viewAny', ServiceItem::class))->toBeFalse();
});

it('still gives Super-admin everything', function () {
    $admin = User::where('email', config('app.admin_seed_email'))->first();

    expect($admin->can('viewAny', Company::class))->toBeTrue();
});
