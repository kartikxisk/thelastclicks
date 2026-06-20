<?php

use App\Filament\Resources\QuoteResource\Pages\EditQuote;
use App\Filament\Resources\QuoteResource\Pages\ListQuotes;
use App\Filament\Resources\QuoteResource\RelationManagers\NotesRelationManager;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list quotes', function () {
    Quote::factory()->count(3)->create();
    Livewire::test(ListQuotes::class)
        ->assertCanSeeTableRecords(Quote::all());
});

it('Sales user sees only quotes assigned to them', function () {
    $sales = User::factory()->create();
    $sales->assignRole('Sales');
    $mine = Quote::factory()->create(['assigned_to' => $sales->id]);
    Quote::factory()->count(3)->create();
    $this->actingAs($sales);

    Livewire::test(ListQuotes::class)
        ->assertCanSeeTableRecords([$mine])
        ->assertCanNotSeeTableRecords(Quote::where('id', '!=', $mine->id)->get());
});

it('Super-admin can change a quote status', function () {
    $q = Quote::factory()->create(['status' => 'new']);
    Livewire::test(EditQuote::class, ['record' => $q->getRouteKey()])
        ->fillForm(['status' => 'contacted'])
        ->call('save')
        ->assertHasNoFormErrors();
    expect($q->fresh()->status)->toBe('contacted');
});

it('admin can add a note to a quote', function () {
    $q = Quote::factory()->create();
    Livewire::test(
        NotesRelationManager::class,
        ['ownerRecord' => $q, 'pageClass' => EditQuote::class]
    )
        ->callTableAction('create', data: ['body' => 'Followed up via phone.'])
        ->assertHasNoTableActionErrors();

    expect($q->notes()->count())->toBe(1)
        ->and($q->notes()->first()->author_id)->toBe($this->admin->id);
});
