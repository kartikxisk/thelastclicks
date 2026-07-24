<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\LeadDesk;
use App\Filament\Pages\LeadPipeline;
use App\Filament\Resources\QuoteResource;
use App\Filament\Widgets\LeadStatsWidget;
use App\Filament\Widgets\NeedsAttentionTable;
use App\Filament\Widgets\SiteOverviewStats;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('renders the site dashboard', function () {
    Livewire::test(Dashboard::class)->assertOk();
});

it('renders the lead desk', function () {
    Livewire::test(LeadDesk::class)->assertOk();
});

it('keeps site widgets off the lead desk and lead widgets off the dashboard', function () {
    $dashboard = (new Dashboard)->getWidgets();
    $leadDesk = (new LeadDesk)->getWidgets();

    expect($dashboard)->toContain(SiteOverviewStats::class)
        ->and($dashboard)->not->toContain(LeadStatsWidget::class)
        ->and($leadDesk)->toContain(LeadStatsWidget::class)
        ->and($leadDesk)->not->toContain(SiteOverviewStats::class);
});

it('renders the pipeline board with every status column', function () {
    Quote::factory()->create(['status' => 'new', 'name' => 'Board Lead']);

    Livewire::test(LeadPipeline::class)
        ->assertOk()
        ->assertSee('Board Lead')
        ->assertSee('Qualified');
});

it('moves a lead between columns and stamps the lifecycle', function () {
    $quote = Quote::factory()->create(['status' => 'new']);

    Livewire::test(LeadPipeline::class)->call('moveQuote', $quote->id, 'contacted');

    $quote->refresh();
    expect($quote->status)->toBe('contacted')
        ->and($quote->contacted_at)->not->toBeNull();
});

it('refuses a drop that skips a stage', function () {
    $quote = Quote::factory()->create(['status' => 'new']);

    Livewire::test(LeadPipeline::class)->call('moveQuote', $quote->id, 'won');

    expect($quote->fresh()->status)->toBe('new');
});

it('refuses a drop back onto new', function () {
    $quote = Quote::factory()->create(['status' => 'qualified']);

    Livewire::test(LeadPipeline::class)->call('moveQuote', $quote->id, 'new');

    expect($quote->fresh()->status)->toBe('qualified');
});

it('reopens a closed lead from the board', function () {
    $quote = Quote::factory()->create(['status' => 'new']);
    $quote->transitionTo('contacted');
    $quote->transitionTo('qualified');
    $quote->transitionTo('lost');

    Livewire::test(LeadPipeline::class)->call('reopenQuote', $quote->id);

    expect($quote->fresh()->status)->toBe('qualified')
        ->and($quote->fresh()->closed_at)->toBeNull();
});

it('adds a comment from the board at the current stage', function () {
    $quote = Quote::factory()->create(['status' => 'new']);
    $quote->transitionTo('contacted');

    Livewire::test(LeadPipeline::class)
        ->mountAction('comment', ['quote' => $quote->id])
        ->setActionData(['body' => 'Chased on WhatsApp.'])
        ->callMountedAction();

    $note = $quote->fresh()->notes()->latest('id')->first();

    expect($note->body)->toBe('Chased on WhatsApp.')
        ->and($note->stage)->toBe('contacted');
});

it('will not let a Viewer comment on a lead', function () {
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');
    $this->actingAs($viewer);

    $quote = Quote::factory()->create(['status' => 'new']);

    Livewire::test(LeadPipeline::class)
        ->mountAction('comment', ['quote' => $quote->id])
        ->setActionData(['body' => 'Sneaky note.'])
        ->callMountedAction();

    expect($quote->fresh()->notes()->count())->toBe(0);
});

it('ignores a move to a status that does not exist', function () {
    $quote = Quote::factory()->create(['status' => 'new']);

    Livewire::test(LeadPipeline::class)->call('moveQuote', $quote->id, 'archived');

    expect($quote->fresh()->status)->toBe('new');
});

it('ignores a move for a lead that does not exist', function () {
    Livewire::test(LeadPipeline::class)->call('moveQuote', 999999, 'won')->assertOk();
});

it('refuses to move a lead for a user who cannot update quotes', function () {
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');
    $this->actingAs($viewer);

    $quote = Quote::factory()->create(['status' => 'new']);

    Livewire::test(LeadPipeline::class)->call('moveQuote', $quote->id, 'won');

    // The board is readable, but the drop must not take effect server-side.
    expect($quote->fresh()->status)->toBe('new');
});

it('refuses to move a lead assigned to someone else when the user is Sales', function () {
    $sales = User::factory()->create();
    $sales->assignRole('Sales');
    $this->actingAs($sales);

    $foreign = Quote::factory()->create(['status' => 'new', 'assigned_to' => $this->admin->id]);
    $own = Quote::factory()->create(['status' => 'new', 'assigned_to' => $sales->id]);

    Livewire::test(LeadPipeline::class)
        ->call('moveQuote', $foreign->id, 'won')
        ->call('moveQuote', $own->id, 'contacted');

    expect($foreign->fresh()->status)->toBe('new')
        ->and($own->fresh()->status)->toBe('contacted');
});

it('keeps the lead desk and pipeline away from Editors', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');
    $this->actingAs($editor);

    expect(LeadPipeline::canAccess())->toBeFalse()
        ->and(LeadDesk::canAccess())->toBeFalse();
});

it('grants the lead desk and pipeline to Sales through RBAC', function () {
    $sales = User::factory()->create();
    $sales->assignRole('Sales');
    $this->actingAs($sales);

    expect(LeadPipeline::canAccess())->toBeTrue()
        ->and(LeadDesk::canAccess())->toBeTrue();
});

it('lets a Viewer read the lead desk but not move a card', function () {
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');
    $this->actingAs($viewer);

    $quote = Quote::factory()->create(['status' => 'new']);

    expect(LeadDesk::canAccess())->toBeTrue();

    Livewire::test(LeadPipeline::class)->call('moveQuote', $quote->id, 'won');

    expect($quote->fresh()->status)->toBe('new');
});

it('revokes the lead desk when the permission is taken off the role', function () {
    $sales = User::factory()->create();
    $sales->assignRole('Sales');
    $this->actingAs($sales);

    expect(LeadDesk::canAccess())->toBeTrue();

    // Managed from the panel's Roles screen in real use.
    Role::findByName('Sales')->revokePermissionTo('page_LeadDesk');
    $sales->forgetCachedPermissions();
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    expect(LeadDesk::canAccess())->toBeFalse();
});

it('counts unactioned leads on the quotes nav badge and reddens it when overdue', function () {
    Quote::factory()->create(['status' => 'new', 'created_at' => now()->subMinutes(5)]);
    expect(QuoteResource::getNavigationBadge())->toBe('1')
        ->and(QuoteResource::getNavigationBadgeColor())->toBe('warning');

    Quote::factory()->create(['status' => 'new', 'created_at' => now()->subHours(9)]);
    expect(QuoteResource::getNavigationBadge())->toBe('2')
        ->and(QuoteResource::getNavigationBadgeColor())->toBe('danger');
});

it('lists only unactioned and contacted leads in the needs-attention widget', function () {
    $waiting = Quote::factory()->create(['status' => 'new']);
    $chasing = Quote::factory()->create(['status' => 'contacted']);
    $done = Quote::factory()->create(['status' => 'won']);

    Livewire::test(NeedsAttentionTable::class)
        ->assertCanSeeTableRecords([$waiting, $chasing])
        ->assertCanNotSeeTableRecords([$done]);
});

it('renders lead stats', function () {
    Quote::factory()->count(3)->create(['status' => 'new']);

    Livewire::test(LeadStatsWidget::class)->assertOk();
});

it('hides lead widgets from users without quote access', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');
    $this->actingAs($editor);

    expect(LeadStatsWidget::canView())->toBeFalse()
        ->and(NeedsAttentionTable::canView())->toBeFalse();
});
