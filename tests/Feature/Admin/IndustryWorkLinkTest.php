<?php

use App\Filament\Resources\IndustryResource\Pages\EditIndustry;
use App\Filament\Resources\WorkResource\Pages\EditWork;
use App\Models\Industry;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->actingAs(User::where('email', config('app.admin_seed_email'))->firstOrFail());
});

/**
 * Editing the industry <-> work link from either end.
 *
 * A project is usually filed while it is open in the portfolio, not by walking
 * back through each industry — but an industry page is the natural place to
 * curate a set, so both forms carry the multi-select.
 */
it('files a project under industries from the work form', function () {
    $work = Work::factory()->create();
    $industry = Industry::first();

    Livewire::test(EditWork::class, ['record' => $work->getRouteKey()])
        ->fillForm(['industries' => [$industry->id]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($work->fresh()->industries->pluck('id')->all())->toBe([$industry->id]);
});

it('attaches a project to an industry from the industry form', function () {
    $industry = Industry::first();
    $work = Work::factory()->create();

    Livewire::test(EditIndustry::class, ['record' => $industry->getRouteKey()])
        ->fillForm(['works' => [$work->id]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($industry->fresh()->works->pluck('id')->all())->toBe([$work->id]);
});

it('keeps an unpublished project listed in the industry form', function () {
    // The relation behind the select is deliberately unfiltered. If it hid
    // drafts, the form would load a reduced set and saving would write that set
    // back — silently detaching a project that was only unpublished.
    $industry = Industry::first();
    $draft = Work::factory()->draft()->create();
    $industry->works()->attach($draft);

    $state = Livewire::test(EditIndustry::class, ['record' => $industry->getRouteKey()])
        ->assertSuccessful()
        ->get('data');

    // Filament carries multi-select state as strings, so compare as strings
    // rather than losing the assertion to a type mismatch.
    expect(array_map('strval', $state['works']))->toContain((string) $draft->id);
});

it('drops the pivot rows when a work is deleted', function () {
    $industry = Industry::first();
    $work = Work::factory()->create();
    $industry->works()->attach($work);

    $work->delete();

    expect($industry->fresh()->works)->toBeEmpty();
});
