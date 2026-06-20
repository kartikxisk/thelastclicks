<?php

use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Filament\Resources\ServiceResource\Pages\ListServices;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list services', function () {
    Livewire::test(ListServices::class)->assertCanSeeTableRecords(Service::all());
});

it('Super-admin can edit a service', function () {
    $svc = Service::first();
    Livewire::test(EditService::class, ['record' => $svc->getRouteKey()])
        ->fillForm(['hero_copy' => 'New tagline'])
        ->call('save')
        ->assertHasNoFormErrors();
    expect($svc->fresh()->hero_copy)->toBe('New tagline');
});

it('Editor can edit services', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');
    expect($editor->can('update', Service::first()))->toBeTrue();
});

it('Viewer cannot create services', function () {
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');
    expect($viewer->can('create', Service::class))->toBeFalse();
});
