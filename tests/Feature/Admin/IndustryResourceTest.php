<?php

use App\Filament\Resources\IndustryResource\Pages\EditIndustry;
use App\Filament\Resources\IndustryResource\Pages\ListIndustries;
use App\Models\Industry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list industries', function () {
    Livewire::test(ListIndustries::class)->assertCanSeeTableRecords(Industry::all());
});

it('Super-admin can edit an industry summary', function () {
    $ind = Industry::first();
    Livewire::test(EditIndustry::class, ['record' => $ind->getRouteKey()])
        ->fillForm(['summary' => 'New summary'])
        ->call('save')
        ->assertHasNoFormErrors();
    expect($ind->fresh()->summary)->toBe('New summary');
});

it('Editor can edit industries', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');
    expect($editor->can('update', Industry::first()))->toBeTrue();
});
