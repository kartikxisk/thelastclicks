<?php

use App\Filament\Resources\ClientResource\Pages\ListClients;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\ClientsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
});

it('seeds every client logo as an active, ordered row', function () {
    $this->seed(ClientsSeeder::class);

    expect(Client::count())->toBe(count(ClientsSeeder::CLIENTS))
        ->and(Client::active()->count())->toBe(count(ClientsSeeder::CLIENTS))
        ->and(Client::where('name', 'BMW')->value('url'))->toBe('https://www.bmw.in')
        ->and(Client::orderBy('order')->first()->name)->toBe('DLF');
});

it('gives every seeded client a working logo without any upload', function () {
    $this->seed(ClientsSeeder::class);

    $resolved = Client::all()->filter(fn (Client $c): bool => $c->logoUrl() !== null);

    expect($resolved)->toHaveCount(count(ClientsSeeder::CLIENTS))
        ->and(Client::where('name', 'BMW')->firstOrFail()->logoUrl())->toContain('clients/bmw.png');
});

it('resolves a logo path relative to public, and passes an absolute URL through', function () {
    $relative = Client::create(['name' => 'Relative', 'logo_path' => 'clients/bmw.png']);
    $absolute = Client::create(['name' => 'Absolute', 'logo_path' => 'https://cdn.example.com/a.png']);
    $none = Client::create(['name' => 'None']);

    expect($relative->logoUrl())->toBe(asset('clients/bmw.png'))
        ->and($absolute->logoUrl())->toBe('https://cdn.example.com/a.png')
        ->and($none->logoUrl())->toBeNull();
});

it('prefers an uploaded logo over the configured path', function () {
    $client = Client::create(['name' => 'Both', 'logo_path' => 'clients/bmw.png']);
    $client->addMedia(UploadedFile::fake()->image('upload.png'))->toMediaCollection('logo');

    expect($client->fresh()->logoUrl())
        ->toBe($client->fresh()->getFirstMediaUrl('logo'))
        ->not->toContain('clients/bmw.png');
});

it('re-seeding does not duplicate clients', function () {
    $this->seed(ClientsSeeder::class);
    $this->seed(ClientsSeeder::class);

    expect(Client::count())->toBe(count(ClientsSeeder::CLIENTS));
});

it('maps a logo filename to the seeded display name', function () {
    expect(ClientsSeeder::nameForFile('bmw.png'))->toBe('BMW')
        ->and(ClientsSeeder::nameForFile('/abs/path/taj-hotels.png'))->toBe('Taj Hotels')
        ->and(ClientsSeeder::nameForFile('johnnie-walker.png'))->toBe('Johnnie Walker')
        // Unknown files still get a sensible name rather than blowing up.
        ->and(ClientsSeeder::nameForFile('some-new-brand.png'))->toBe('Some New Brand');
});

it('attaches an uploaded logo to the seeded row instead of creating a duplicate', function () {
    $this->seed(ClientsSeeder::class);
    $before = Client::count();

    $bmw = Client::where('name', 'BMW')->firstOrFail();
    $bmw->addMedia(UploadedFile::fake()->image('bmw.png'))->toMediaCollection('logo');

    expect(Client::count())->toBe($before)
        ->and(Client::where('name', 'Bmw')->exists())->toBeFalse()
        ->and($bmw->fresh()->logoUrl())->not->toBeNull();
});

it('renders every column in the admin table, thumbnail included', function () {
    $this->seed();
    $this->actingAs(User::where('email', config('app.admin_seed_email'))->firstOrFail());

    // Two columns keyed `logo_path` would silently collide and drop one.
    Livewire::test(ListClients::class)
        ->assertCanRenderTableColumn('logo_preview')
        ->assertCanRenderTableColumn('logo_path')
        ->assertCanRenderTableColumn('logo')
        ->assertCanRenderTableColumn('name');
});

it('renders admin-managed logos on the marquee', function () {
    $client = Client::create(['name' => 'Uploaded Brand', 'order' => 1, 'is_active' => true]);
    $client->addMedia(UploadedFile::fake()->image('brand.png'))->toMediaCollection('logo');

    $this->get('/')
        ->assertOk()
        ->assertSee($client->fresh()->logoUrl(), false)
        ->assertSee('Uploaded Brand', false);
});

it('hides a deactivated client from the marquee', function () {
    $client = Client::create(['name' => 'Hidden Brand', 'order' => 1, 'is_active' => false]);
    $client->addMedia(UploadedFile::fake()->image('hidden.png'))->toMediaCollection('logo');

    $this->get('/')->assertOk()->assertDontSee('Hidden Brand', false);
});

it('falls back to the bundled logos while nothing has been uploaded yet', function () {
    $this->seed(ClientsSeeder::class);

    // Rows exist but carry no media, so the strip must not render empty.
    $this->get('/')->assertOk()->assertSee('marquee--logos', false);
});

it('imports the bundled logo files onto the seeded rows', function () {
    $this->seed(ClientsSeeder::class);

    Artisan::call('clients:import-legacy');

    $withLogos = Client::all()->filter(fn (Client $c): bool => $c->logoUrl() !== null);

    expect(Client::count())->toBe(count(ClientsSeeder::CLIENTS))
        ->and($withLogos)->not->toBeEmpty()
        ->and(Client::where('name', 'BMW')->firstOrFail()->logoUrl())->not->toBeNull();
})->skip(
    fn () => count(glob(public_path('clients/*.png')) ?: []) === 0,
    'No bundled logos in public/clients/',
);
