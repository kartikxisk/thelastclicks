<?php

use App\Models\Company;
use App\Rules\Gstin;
use Database\Factories\CompanyFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

it('mints fixtures whose GSTIN would survive the real rule', function () {
    // The factory builds a GSTIN by computing its own checksum. If that ever
    // drifts from the rule, every downstream test would pass against a GSTIN no
    // client could actually have.
    $company = Company::factory()->inState('27')->create();

    expect(Gstin::isValid($company->gstin))->toBeTrue()
        ->and(substr($company->gstin, 0, 2))->toBe('27');
});

it('drops blank lines out of the printed address', function () {
    $company = Company::factory()->create([
        'address_line1' => 'B-12, Lajpat Nagar',
        'address_line2' => null,
        'address_city' => 'New Delhi',
        'address_state' => 'Delhi',
        'address_postal_code' => '110024',
    ]);

    expect($company->addressLines())->toBe([
        'B-12, Lajpat Nagar',
        'New Delhi, Delhi 110024',
    ]);
});

it('labels its state from the code', function () {
    $company = Company::factory()->create(['address_state_code' => '07']);

    expect($company->stateLabel())->toBe('Delhi');
});

it('scopes to active companies', function () {
    Company::factory()->create(['is_active' => true]);
    Company::factory()->create(['is_active' => false]);

    expect(Company::active()->count())->toBe(1);
});

it('registers the three branding collections an invoice needs', function () {
    $company = Company::factory()->create();

    expect(collect($company->getRegisteredMediaCollections())->pluck('name')->all())
        ->toBe(['logo', 'signature', 'stamp']);
});

it('can be unregistered, in which case it carries no GSTIN', function () {
    $company = Company::factory()->unregistered()->create();

    expect($company->is_gst_registered)->toBeFalse()
        ->and($company->gstin)->toBeNull();
});

it('uppercases a GSTIN, PAN and IFSC assigned straight onto the model', function () {
    // The Filament forms already uppercase on dehydration, and the Gstin rule
    // uppercases a local copy for its own checks — so a lowercase GSTIN now
    // passes validation while nothing off the form path guarantees what is
    // stored. An importer, an artisan command or a hand-written seeder could
    // persist a lowercase GSTIN, and that is the number filed in GSTR-1.
    // Normalising at the model boundary makes storage correct on every path
    // and leaves the form closures as harmless redundancy.
    $company = Company::factory()->create([
        'gstin' => strtolower(CompanyFactory::gstinFor('07')),
        'pan' => 'aapfu0939f',
        'bank_ifsc' => 'hdfc0001234',
    ]);

    expect($company->fresh()->gstin)->toBe(CompanyFactory::gstinFor('07'))
        ->and($company->fresh()->pan)->toBe('AAPFU0939F')
        ->and($company->fresh()->bank_ifsc)->toBe('HDFC0001234');
});

it('leaves an absent GSTIN, PAN and IFSC null rather than storing an empty string', function () {
    $company = Company::factory()->unregistered()->create(['pan' => '', 'bank_ifsc' => null]);

    expect($company->fresh()->gstin)->toBeNull()
        ->and($company->fresh()->pan)->toBeNull()
        ->and($company->fresh()->bank_ifsc)->toBeNull();
});

it('keeps the signature and the stamp off the publicly-served media disk', function () {
    // MEDIA_DISK is s3 in production with CloudFront in front of it, serving the
    // bucket anonymously — it is the same bucket the public marketing site loads
    // its imagery from — and medialibrary's DefaultPathGenerator writes to a
    // guessable {media_id}/{file_name}. A scanned signature and a company seal
    // sitting there are public documents, and ->visibility('private') does not
    // change that: it sets an object ACL, and CloudFront is what serves the
    // object. See SiteSettingsPage for the same finding recorded on branding.
    $company = Company::factory()->create();

    $disks = collect($company->getRegisteredMediaCollections())
        ->mapWithKeys(fn ($collection): array => [$collection->name => $collection->diskName]);

    $publicDisk = config('media-library.disk_name');

    expect($disks['signature'])->not->toBe('')->not->toBe($publicDisk)
        ->and($disks['stamp'])->not->toBe('')->not->toBe($publicDisk)
        // The logo is deliberately public — it prints on every invoice, and a
        // blank collection disk means "the default media disk".
        ->and($disks['logo'] === '' ? $publicDisk : $disks['logo'])->toBe($publicDisk);
});

it('clears the GSTIN when the company stops being GST registered', function () {
    // Two columns that can disagree about registration is one too many — the
    // sibling BillingClient refuses to carry a second flag for exactly this
    // reason. A hidden Filament field is not dehydrated, so the edit form never
    // sends the key and the stale number survives the untick.
    $company = Company::factory()->create([
        'is_gst_registered' => true,
        'gstin' => CompanyFactory::gstinFor('07'),
        'address_state_code' => '07',
    ]);

    $company->update(['is_gst_registered' => false]);

    expect($company->fresh()->gstin)->toBeNull();
});

it('records a change to the UPI ID', function () {
    // The UPI ID becomes the QR code on every invoice PDF, so editing it
    // silently redirects customer payments.
    $company = Company::factory()->create(['upi_id' => 'studio@hdfcbank']);

    $company->update(['upi_id' => 'someone-else@ybl']);

    $activity = Activity::query()
        ->where('subject_type', $company->getMorphClass())
        ->where('subject_id', $company->getKey())
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->changes()['attributes']['upi_id'])->toBe('someone-else@ybl');
});

it('keeps the bank block out of a serialised company', function () {
    // Phase 2 freezes a party_snapshot and queues mail; a queued job payload is
    // a database row, and a PDF render is another serialisation point.
    $company = Company::factory()->create();

    expect(array_keys($company->toArray()))
        ->not->toContain('bank_account_number')
        ->not->toContain('bank_ifsc')
        ->not->toContain('upi_id');
});
