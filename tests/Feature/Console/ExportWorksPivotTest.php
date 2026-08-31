<?php

use App\Models\Industry;
use App\Models\Service;
use App\Models\Work;
use Database\Seeders\WorksSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->fixture = base_path('tests/tmp-works-'.uniqid().'.json');
});

afterEach(function () {
    File::delete($this->fixture);
});

/**
 * The fixture has to carry which industries a project is filed under.
 *
 * It did not, and the omission was invisible until a rebuild: `migrate:fresh
 * --seed` restored all 47 works with their media intact and every industry page
 * empty, because industry_work is a pivot and the exporter only walked
 * attributes, media and media items. A fixture that silently drops a
 * relationship is worse than one that fails loudly.
 */
it('exports the industries a work is filed under', function () {
    $industry = Industry::orderBy('order')->firstOrFail();
    $work = Work::factory()->create(['title' => 'Atlas Refinery']);
    $work->industries()->attach($industry);

    Artisan::call('app:export-works', ['--path' => $this->fixture]);

    $rows = json_decode(File::get($this->fixture), true);
    $row = collect($rows)->firstWhere('attributes.slug', 'atlas-refinery');

    expect($row)->not->toBeNull()
        ->and($row['industries'])->toBe([$industry->slug]);
});

it('restores the industry links when the fixture is seeded back', function () {
    $industry = Industry::orderBy('order')->firstOrFail();
    Work::factory()->create(['title' => 'Atlas Refinery'])->industries()->attach($industry);

    Artisan::call('app:export-works', ['--path' => $this->fixture]);

    // Rebuild: the works are gone, the fixture is all that is left.
    Work::query()->get()->each->delete();
    expect(Work::count())->toBe(0);

    $seeder = new WorksSeeder;
    $seeder->fixturePath = $this->fixture;
    $seeder->run();

    $restored = Work::where('slug', 'atlas-refinery')->firstOrFail();

    expect($restored->industries->pluck('slug')->all())->toBe([$industry->slug]);
});

it('exports the services a work is attached to', function () {
    // Same failure mode, same fix — service_work is empty today, so a fixture
    // that drops it would lose the links the first time anyone populates them.
    $service = Service::firstOrFail();
    $work = Work::factory()->create(['title' => 'Atlas Refinery']);
    $work->services()->attach($service);

    Artisan::call('app:export-works', ['--path' => $this->fixture]);

    $row = collect(json_decode(File::get($this->fixture), true))
        ->firstWhere('attributes.slug', 'atlas-refinery');

    expect($row['services'])->toBe([$service->slug]);
});

it('skips a pivot slug the target environment does not have', function () {
    // Industries are seeded content and can differ between environments; an
    // unknown slug must be ignored rather than abort the whole seed.
    $work = Work::factory()->create(['title' => 'Atlas Refinery']);
    Artisan::call('app:export-works', ['--path' => $this->fixture]);

    $rows = json_decode(File::get($this->fixture), true);

    foreach ($rows as $i => $row) {
        if (($row['attributes']['slug'] ?? null) === 'atlas-refinery') {
            $rows[$i]['industries'] = ['an-industry-that-does-not-exist'];
        }
    }

    File::put($this->fixture, json_encode($rows));
    Work::query()->get()->each->delete();

    $seeder = new WorksSeeder;
    $seeder->fixturePath = $this->fixture;
    $seeder->run();

    expect(Work::where('slug', 'atlas-refinery')->firstOrFail()->industries)->toBeEmpty();
});

it('repairs missing industry links on a work that already exists', function () {
    // The seeder is create-only: attributes and media on an existing record
    // belong to whoever edited them. A pivot row is different — attaching one is
    // additive and cannot clobber anything, and this is the case that matters,
    // because a database rebuilt from a fixture that predates the pivot has all
    // 47 works and no industry filed against any of them.
    $industry = Industry::orderBy('order')->firstOrFail();
    $work = Work::factory()->create(['title' => 'Atlas Refinery']);
    $work->industries()->attach($industry);

    Artisan::call('app:export-works', ['--path' => $this->fixture]);

    // Simulate the broken state: the work survives, its pivot rows do not.
    $work->industries()->detach();
    expect($work->fresh()->industries)->toBeEmpty();

    $seeder = new WorksSeeder;
    $seeder->fixturePath = $this->fixture;
    $seeder->run();

    expect($work->fresh()->industries->pluck('slug')->all())->toBe([$industry->slug]);
});

it('leaves an existing work title and media alone while repairing pivots', function () {
    $industry = Industry::orderBy('order')->firstOrFail();
    $work = Work::factory()->create(['title' => 'Atlas Refinery', 'summary' => 'Exported copy']);
    $work->industries()->attach($industry);

    Artisan::call('app:export-works', ['--path' => $this->fixture]);

    // An editor has since rewritten the summary. The seeder must not undo that.
    $work->update(['summary' => 'Edited in the admin']);
    $work->industries()->detach();

    $seeder = new WorksSeeder;
    $seeder->fixturePath = $this->fixture;
    $seeder->run();

    expect($work->fresh()->summary)->toBe('Edited in the admin')
        ->and($work->fresh()->industries->pluck('slug')->all())->toBe([$industry->slug]);
});
