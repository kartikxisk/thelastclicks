<?php

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list users', function () {
    Livewire::test(ListUsers::class)->assertCanSeeTableRecords(User::all());
});

it('Super-admin can create a staff user with a role', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Sales One',
            'email' => 'sales1@example.com',
            'password' => 'PasswordABC123',
            'roles' => [Role::findByName('Sales')->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $u = User::where('email', 'sales1@example.com')->first();
    expect($u)->not->toBeNull()
        ->and($u->hasRole('Sales'))->toBeTrue();
});

it('Non-Super-admin cannot view users', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');
    expect($editor->can('viewAny', User::class))->toBeFalse();
});
