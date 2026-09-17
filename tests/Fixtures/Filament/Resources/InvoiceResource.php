<?php

namespace Tests\Fixtures\Filament\Resources;

use App\Models\Company;
use Filament\Resources\Resource;

/**
 * A stand-in for a phase-2 billing resource that does not exist yet.
 *
 * Registered onto the admin panel by BillingPermissionsTest so the Viewer
 * carve-out can be proved against a resource whose permission names nobody has
 * written down. Shield derives the identifier from the class name after
 * `Resources\`, so this lands as `invoice` — exactly what phase 2 will produce.
 *
 * The model is irrelevant to that derivation; Company is borrowed only because
 * a Resource must name one.
 */
class InvoiceResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationGroup = 'Billing';
}
