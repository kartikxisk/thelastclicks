<?php

use App\Filament\Resources\CrewResource\Pages\CreateCrew;
use App\Filament\Resources\CrewResource\Pages\ListCrew;
use App\Models\Crew;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list crew', function () {
    Livewire::test(ListCrew::class)->assertCanSeeTableRecords(Crew::all());
});

it('Super-admin can create a crew member with social_json', function () {
    Livewire::test(CreateCrew::class)
        ->fillForm([
            'name' => 'Alex Maker',
            'role' => 'Director',
            'bio' => 'Bio paragraph',
            'social_json' => ['instagram' => 'https://instagram.com/alex', 'youtube' => 'https://youtube.com/@alex'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $c = Crew::where('name', 'Alex Maker')->first();
    expect($c)->not->toBeNull()
        ->and($c->social_json['instagram'])->toBe('https://instagram.com/alex');
});
