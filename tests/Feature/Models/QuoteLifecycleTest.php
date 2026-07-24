<?php

use App\Models\Quote;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stamps contacted_at the first time a lead leaves new', function () {
    $quote = Quote::factory()->create(['status' => 'new']);

    expect($quote->contacted_at)->toBeNull();

    $quote->update(['status' => 'contacted']);

    expect($quote->fresh()->contacted_at)->not->toBeNull();
});

it('never overwrites the original response time when a lead moves on', function () {
    $quote = Quote::factory()->create(['status' => 'new']);
    $quote->update(['status' => 'contacted']);

    $firstResponse = $quote->fresh()->contacted_at;

    $this->travel(2)->hours();
    $quote->fresh()->update(['status' => 'qualified']);

    expect($quote->fresh()->contacted_at->timestamp)->toBe($firstResponse->timestamp);
});

it('stamps closed_at on won or lost and clears it when reopened', function (string $closing) {
    $quote = Quote::factory()->create(['status' => 'new']);

    $quote->update(['status' => $closing]);
    expect($quote->fresh()->closed_at)->not->toBeNull();

    $quote->fresh()->update(['status' => 'contacted']);
    expect($quote->fresh()->closed_at)->toBeNull();
})->with(['won', 'lost']);

it('reports a lead overdue only once it passes the response promise', function () {
    $fresh = Quote::factory()->create(['status' => 'new', 'created_at' => now()->subHour()]);
    $stale = Quote::factory()->create(['status' => 'new', 'created_at' => now()->subHours(9)]);

    expect($fresh->isOverdue())->toBeFalse()
        ->and($stale->isOverdue())->toBeTrue();
});

it('does not treat an actioned lead as overdue however old it is', function () {
    $quote = Quote::factory()->create(['status' => 'contacted', 'created_at' => now()->subMonth()]);

    expect($quote->isOverdue())->toBeFalse();
});

it('honours the configurable response promise', function () {
    SiteSetting::set('lead_sla_hours', 24);

    $quote = Quote::factory()->create(['status' => 'new', 'created_at' => now()->subHours(9)]);

    // Overdue at the 4h default, comfortably inside a 24h promise.
    expect(Quote::slaHours())->toBe(24)
        ->and($quote->isOverdue())->toBeFalse();
});

it('measures response time in minutes, or null when never contacted', function () {
    $never = Quote::factory()->create(['status' => 'new']);
    expect($never->responseMinutes())->toBeNull();

    $quote = Quote::factory()->create(['status' => 'new', 'created_at' => now()->subMinutes(90)]);
    $quote->update(['status' => 'contacted']);

    expect($quote->fresh()->responseMinutes())->toBe(90);
});

it('scopes leads so Sales only ever sees their own', function () {
    $this->seed(RolesSeeder::class);

    $sales = User::factory()->create();
    $sales->assignRole('Sales');
    $other = User::factory()->create();

    Quote::factory()->create(['assigned_to' => $sales->id]);
    Quote::factory()->create(['assigned_to' => $other->id]);
    Quote::factory()->create(['assigned_to' => null]);

    expect(Quote::query()->visibleTo($sales)->count())->toBe(1)
        ->and(Quote::query()->visibleTo($other)->count())->toBe(3);
});

it('separates open, unactioned and overdue leads', function () {
    Quote::factory()->create(['status' => 'new', 'created_at' => now()->subHours(9)]);
    Quote::factory()->create(['status' => 'new', 'created_at' => now()->subMinutes(5)]);
    Quote::factory()->create(['status' => 'qualified']);
    Quote::factory()->create(['status' => 'won']);

    expect(Quote::query()->open()->count())->toBe(3)
        ->and(Quote::query()->unactioned()->count())->toBe(2)
        ->and(Quote::query()->overdue()->count())->toBe(1);
});
