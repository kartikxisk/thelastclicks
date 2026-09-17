<?php

use App\Models\BillingClient;
use App\Models\Company;
use App\Models\ServiceItem;
use App\Models\User;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Database\Seeders\PermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Fixtures\Filament\Resources\InvoiceResource;

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

/**
 * The Billing nav group as the panel actually reports it, mapped to the
 * permission suffix shield derives from each resource class name.
 */
function billingIdentifiers(): array
{
    return collect(Filament::getPanel('admin')->getResources())
        ->filter(fn (string $resource): bool => $resource::getNavigationGroup() === 'Billing')
        ->map(fn (string $resource): string => FilamentShield::getPermissionIdentifier($resource))
        ->values()
        ->all();
}

it('holds nothing for any resource in the Billing navigation group', function () {
    // The property is "Viewer holds nothing for anything in Billing", not
    // "Viewer holds nothing for these three names". Reading the group off the
    // panel is what keeps this test true when phase 2 adds a fourth resource
    // without anyone remembering to extend it.
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');

    $identifiers = billingIdentifiers();

    expect($identifiers)->not->toBeEmpty();

    foreach ($identifiers as $identifier) {
        $held = Permission::pluck('name')
            ->filter(fn (string $p): bool => str_ends_with($p, '_'.$identifier))
            ->filter(fn (string $p): bool => $viewer->can($p))
            ->values()
            ->all();

        expect($held)->toBe([]);
    }
});

it('keeps a phase-2 billing resource away from Viewer without anyone updating a list', function () {
    // The regression test that has teeth: register a resource that does not
    // exist yet into the Billing group, create the permissions shield would
    // create for it, and re-run the role assignment. Derived from the panel,
    // `invoice` is excluded automatically. Against a hand-maintained list of
    // today's three resources it is not, and Viewer's blanket `view_*` grant
    // silently hands every read-only account the invoice resource — bank
    // details, client GSTINs, revenue.
    Filament::getPanel('admin')->resources([InvoiceResource::class]);

    Permission::findOrCreate('view_invoice', 'web');
    Permission::findOrCreate('view_any_invoice', 'web');

    (new PermissionsSeeder)->assignRolePermissions();

    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');

    expect(billingIdentifiers())->toContain('invoice')
        ->and($viewer->can('view_invoice'))->toBeFalse()
        ->and($viewer->can('view_any_invoice'))->toBeFalse();
});
