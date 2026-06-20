<?php

use App\Filament\Resources\PortfolioResource\Pages\CreatePortfolio;
use App\Filament\Resources\PortfolioResource\Pages\ListPortfolios;
use App\Models\Industry;
use App\Models\Portfolio;
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

it('Super-admin can list portfolios', function () {
    Livewire::test(ListPortfolios::class)->assertCanSeeTableRecords(Portfolio::all());
});

it('Super-admin can create a portfolio with service+industry+year', function () {
    Livewire::test(CreatePortfolio::class)
        ->fillForm([
            'title' => 'Launch Film 2026',
            'client' => 'Acme Co',
            'year' => 2026,
            'service_id' => Service::where('slug', 'videography')->first()->id,
            'industry_id' => Industry::where('slug', 'fashion')->first()->id,
            'status' => 'published',
            'body' => 'Project body',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $p = Portfolio::where('title', 'Launch Film 2026')->first();
    expect($p)->not->toBeNull()
        ->and($p->owner_id)->toBe($this->admin->id)
        ->and($p->status)->toBe('published');
});

it('Editor can edit only their own portfolio (ownership ABAC)', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    $mine = Portfolio::factory()->for($editor, 'owner')->create();
    $other = Portfolio::factory()->create();

    expect($editor->can('update', $mine))->toBeTrue()
        ->and($editor->can('update', $other))->toBeFalse();
});
