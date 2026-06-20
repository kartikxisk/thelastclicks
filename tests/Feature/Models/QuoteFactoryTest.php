<?php

use App\Models\Quote;
use App\Models\QuoteNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a quote with defaults', function () {
    $q = Quote::factory()->create();
    expect($q->status)->toBe('new')
        ->and($q->email)->toBeString()
        ->and($q->source_page)->toBeString();
});

it('logs notes against a quote', function () {
    $author = User::factory()->create();
    $q = Quote::factory()->create();
    $note = QuoteNote::factory()->for($q)->for($author, 'author')->create(['body' => 'follow up']);
    expect($q->notes()->count())->toBe(1)
        ->and($note->author->id)->toBe($author->id);
});
