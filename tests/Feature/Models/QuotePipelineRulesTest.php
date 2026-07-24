<?php

use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('walks the pipeline forwards one stage at a time', function () {
    $quote = Quote::factory()->create(['status' => 'new']);

    expect($quote->transitionTo('contacted'))->toBeTrue()
        ->and($quote->transitionTo('qualified'))->toBeTrue()
        ->and($quote->transitionTo('won'))->toBeTrue()
        ->and($quote->fresh()->status)->toBe('won');
});

it('refuses to skip a stage', function (string $from, string $to) {
    $quote = Quote::factory()->create(['status' => $from]);

    expect($quote->canTransitionTo($to))->toBeFalse()
        ->and($quote->transitionTo($to))->toBeFalse()
        ->and($quote->fresh()->status)->toBe($from);
})->with([
    ['new', 'qualified'],
    ['new', 'won'],
    ['contacted', 'won'],
]);

it('never lets a lead go back to new', function (string $from) {
    $quote = Quote::factory()->create(['status' => $from]);

    expect($quote->transitionTo('new'))->toBeFalse()
        ->and($quote->fresh()->status)->toBe($from);
})->with(['contacted', 'qualified', 'won', 'lost']);

it('never moves a lead backwards', function () {
    $quote = Quote::factory()->create(['status' => 'qualified']);

    expect($quote->transitionTo('contacted'))->toBeFalse()
        ->and($quote->fresh()->status)->toBe('qualified');
});

it('can lose a lead at any open stage', function (string $from) {
    $quote = Quote::factory()->create(['status' => $from]);

    expect($quote->transitionTo('lost'))->toBeTrue()
        ->and($quote->fresh()->status)->toBe('lost');
})->with(['new', 'contacted', 'qualified']);

it('freezes a closed lead until it is reopened', function (string $closed) {
    $quote = Quote::factory()->create(['status' => $closed]);

    expect($quote->allowedTransitions())->toBe([])
        ->and($quote->transitionTo('contacted'))->toBeFalse();
})->with(['won', 'lost']);

it('reopens a lead back into the stage it closed from', function () {
    $quote = Quote::factory()->create(['status' => 'new']);
    $quote->transitionTo('contacted');
    $quote->transitionTo('qualified');
    $quote->transitionTo('won');

    expect($quote->reopen())->toBeTrue()
        ->and($quote->fresh()->status)->toBe('qualified')
        ->and($quote->fresh()->closed_at)->toBeNull();
});

it('reopens a lead lost straight from new into contacted, never back to new', function () {
    $quote = Quote::factory()->create(['status' => 'new']);
    $quote->transitionTo('lost');

    $quote->reopen();

    expect($quote->fresh()->status)->toBe('contacted');
});

it('refuses to reopen a lead that is still open', function () {
    $quote = Quote::factory()->create(['status' => 'contacted']);

    expect($quote->reopen())->toBeFalse()
        ->and($quote->fresh()->status)->toBe('contacted');
});

it('tags a comment with the stage it was written at', function () {
    $author = User::factory()->create();
    $quote = Quote::factory()->create(['status' => 'new']);

    $quote->comment('First call made.', $author);
    $quote->transitionTo('contacted', 'Moving on.', $author);

    $stages = $quote->fresh()->notes->pluck('stage', 'body');

    expect($stages['First call made.'])->toBe('new')
        ->and($stages['Moving on.'])->toBe('contacted');
});

it('keeps a comment pinned to its original stage when the lead moves on', function () {
    $quote = Quote::factory()->create(['status' => 'new']);
    $note = $quote->comment('Written while new.');

    $quote->transitionTo('contacted');
    $quote->transitionTo('qualified');

    expect($note->fresh()->stage)->toBe('new');
});

it('shows the stage of each comment on the timeline', function () {
    $quote = Quote::factory()->create(['status' => 'new']);
    $quote->transitionTo('contacted', 'Spoke to them.');

    $note = $quote->fresh()->timeline()->firstWhere('type', 'note');

    expect($note['stage'])->toBe('contacted')
        ->and($note['body'])->toBe('Spoke to them.');
});
