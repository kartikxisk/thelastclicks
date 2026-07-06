<?php

use App\Filament\Resources\WorkCategoryResource\Pages\EditWorkCategory;
use App\Filament\Resources\WorkCategoryResource\Pages\ListWorkCategories;
use App\Models\User;
use App\Models\WorkCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list work categories', function () {
    Livewire::test(ListWorkCategories::class)
        ->set('tableRecordsPerPage', 50)
        ->assertCanSeeTableRecords(WorkCategory::all());
});

it('Super-admin can rename a work category', function () {
    $cat = WorkCategory::first();
    Livewire::test(EditWorkCategory::class, ['record' => $cat->getRouteKey()])
        ->fillForm(['title' => 'Renamed Cat'])
        ->call('save')
        ->assertHasNoFormErrors();
    expect($cat->fresh()->title)->toBe('Renamed Cat');
});

it('Editor can update work categories', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');
    expect($editor->can('update', WorkCategory::first()))->toBeTrue();
});
