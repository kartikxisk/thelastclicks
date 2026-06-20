<?php

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a portfolio item', function () {
    $u = User::factory()->create();
    $p = Portfolio::factory()->for($u, 'owner')->create();
    expect($p->slug)->not->toBeEmpty()
        ->and($p->status)->toBe('draft')
        ->and($p->owner->id)->toBe($u->id);
});
