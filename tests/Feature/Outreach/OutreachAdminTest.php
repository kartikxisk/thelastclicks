<?php

use App\Filament\Imports\OutreachContactImporter;
use App\Filament\Resources\OutreachContactResource;
use App\Filament\Resources\OutreachContactResource\Pages\ListOutreachContacts;
use App\Models\OutreachContact;
use App\Models\OutreachSuppression;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    $this->admin = User::firstWhere('email', config('app.admin_seed_email'))
        ?? User::factory()->create();
    $this->admin->assignRole(Role::firstWhere('name', 'Super-admin'));
    $this->actingAs($this->admin);
});

it('renders the outreach list in the admin', function () {
    OutreachContact::create(['email' => 'a@example.com', 'organizer' => 'Acme Expo']);

    Livewire::test(ListOutreachContacts::class)
        ->assertOk()
        ->assertCanSeeTableRecords(OutreachContact::all());
});

it('shows the sales role the outreach resource', function () {
    // Sales is the role that actually runs the campaign. Shield names the
    // permission "outreach::contact", and matching the underscore form instead
    // grants nothing while leaving the resource looking wired up.
    $sales = Role::firstWhere('name', 'Sales');

    expect($sales->permissions->pluck('name'))
        ->toContain('view_any_outreach::contact', 'update_outreach::contact');
});

it('keeps the pipeline moving only through the first three statuses', function () {
    $contact = OutreachContact::create(['email' => 'a@example.com', 'status' => 'Not Contacted']);
    expect($contact->dueStep())->toBe('first-touch');

    $contact->update(['status' => 'Replied']);
    expect($contact->fresh()->dueStep())->toBeNull();

    $contact->update(['status' => 'Won']);
    expect($contact->fresh()->dueStep())->toBeNull();
});

it('holds a follow-up until the wait has actually elapsed', function () {
    $contact = OutreachContact::create([
        'email' => 'a@example.com',
        'status' => 'Email Sent',
        'email_sent_on' => now()->subDay(),
    ]);

    // config('outreach.follow_up_days.first-touch') is 4.
    expect($contact->dueStep())->toBeNull();

    $contact->update(['email_sent_on' => now()->subDays(5)]);
    expect($contact->fresh()->dueStep())->toBe('follow-up-1');
});

it('holds a follow-up when the status claims a send but no date was recorded', function () {
    // Otherwise the cadence has no way to know whether the wait has passed and
    // would fire immediately.
    $contact = OutreachContact::create([
        'email' => 'a@example.com',
        'status' => 'Email Sent',
        'email_sent_on' => null,
    ]);

    expect($contact->dueStep())->toBeNull();
});

it('excludes suppressed addresses from the due scope', function () {
    OutreachContact::create(['email' => 'a@example.com', 'status' => 'Not Contacted']);
    OutreachContact::create(['email' => 'b@example.com', 'status' => 'Not Contacted']);
    OutreachSuppression::create(['email' => 'b@example.com']);

    expect(OutreachContact::query()->due()->pluck('email')->all())->toBe(['a@example.com']);
});

it('treats the send log as the authority on what went out', function () {
    $contact = OutreachContact::create(['email' => 'a@example.com']);
    expect($contact->hasSent('first-touch'))->toBeFalse();

    $contact->sends()->create(['step' => 'first-touch', 'status' => 'sent', 'sent_at' => now()]);
    expect($contact->fresh()->hasSent('first-touch'))->toBeTrue();

    // A failure is not a send, and must stay retryable.
    $other = OutreachContact::create(['email' => 'b@example.com']);
    $other->sends()->create(['step' => 'first-touch', 'status' => 'failed', 'sent_at' => now()]);
    expect($other->fresh()->hasSent('first-touch'))->toBeFalse();
});

it('turns a legal entity name into something you can greet', function () {
    expect(OutreachContactImporter::greetingFor('MESSE FRANKFURT TRADE FAIRS INDIA PRIVATE LIMITED'))
        ->toBe('Messe Frankfurt team')
        ->and(OutreachContactImporter::greetingFor('Informa Markets India Private Limited'))
        ->toBe('Informa team')
        ->and(OutreachContactImporter::greetingFor(''))
        ->toBe('there');
});

it('suppresses an address and stops its sequence from the table', function () {
    $contact = OutreachContact::create(['email' => 'a@example.com', 'status' => 'Email Sent']);

    Livewire::test(ListOutreachContacts::class)
        ->callTableBulkAction('suppress', [$contact])
        ->assertHasNoTableBulkActionErrors();

    expect(OutreachSuppression::where('email', 'a@example.com')->exists())->toBeTrue()
        ->and($contact->fresh()->status)->toBe('Not Relevant')
        ->and(OutreachContact::query()->due()->count())->toBe(0);
});

it('colours every status in the pipeline', function () {
    foreach (OutreachContact::STATUSES as $status) {
        expect(OutreachContactResource::statusColour($status))->not->toBeEmpty();
    }
});
