<?php

use App\Models\Quote;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('always opens with the enquiry itself', function () {
    $quote = Quote::factory()->create(['project_type' => 'Wedding', 'budget' => 'Under ₹5L']);

    $events = $quote->timeline();

    expect($events)->toHaveCount(1)
        ->and($events->first()['type'])->toBe('received')
        ->and($events->first()['title'])->toBe('Enquiry received')
        ->and($events->first()['body'])->toContain('Wedding');
});

it('records each status change with where it moved from and to', function () {
    $quote = Quote::factory()->create(['status' => 'new']);
    $quote->update(['status' => 'contacted']);
    $quote->update(['status' => 'qualified']);
    $quote->update(['status' => 'won']);

    $titles = $quote->fresh()->timeline()->pluck('title')->all();

    // Newest first, enquiry last.
    expect($titles)->toBe([
        'Qualified → Won',
        'Contacted → Qualified',
        'Responded',
        'Enquiry received',
    ]);
});

it('labels the first response with how long the lead waited', function () {
    $quote = Quote::factory()->create(['status' => 'new', 'created_at' => now()->subHours(3)]);
    $quote->update(['status' => 'contacted']);

    $responded = $quote->fresh()->timeline()->firstWhere('title', 'Responded');

    expect($responded['body'])->toContain('Waited')
        ->and($responded['body'])->toContain('3 hours');
});

it('colours a won lead green and a lost lead red', function (string $status, string $colour) {
    $quote = Quote::factory()->create(['status' => 'new']);
    $quote->update(['status' => $status]);

    expect($quote->fresh()->timeline()->first()['color'])->toBe($colour);
})->with([['won', 'green'], ['lost', 'red']]);

it('names who a lead was assigned to, and who did it', function () {
    $this->seed(RolesSeeder::class);
    $actor = User::factory()->create(['name' => 'Desk Admin']);
    $owner = User::factory()->create(['name' => 'Priya Sales']);
    $this->actingAs($actor);

    $quote = Quote::factory()->create();
    $quote->update(['assigned_to' => $owner->id]);

    $event = $quote->fresh()->timeline()->firstWhere('type', 'owner');

    expect($event['title'])->toBe('Assigned to Priya Sales')
        ->and($event['actor'])->toBe('Desk Admin');
});

it('folds notes into the same stream', function () {
    $author = User::factory()->create(['name' => 'Note Writer']);
    $quote = Quote::factory()->create();
    $quote->notes()->create(['author_id' => $author->id, 'body' => 'Called, wants a treatment.']);

    $note = $quote->fresh()->timeline()->firstWhere('type', 'note');

    expect($note['title'])->toBe('Note added')
        ->and($note['body'])->toBe('Called, wants a treatment.')
        ->and($note['actor'])->toBe('Note Writer');
});

it('orders the whole stream newest first', function () {
    $author = User::factory()->create();
    $quote = Quote::factory()->create(['created_at' => now()->subDays(3)]);
    $quote->notes()->create(['author_id' => $author->id, 'body' => 'Older note', 'created_at' => now()->subDays(2)]);
    $quote->notes()->create(['author_id' => $author->id, 'body' => 'Newer note', 'created_at' => now()->subDay()]);

    $stream = $quote->fresh()->timeline();

    expect($stream->pluck('at')->all())->toEqual($stream->pluck('at')->sortDesc()->values()->all());
});
