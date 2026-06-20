<?php

use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('Super-admin can update any quote', function () {
    $u = User::factory()->create();
    $u->assignRole('Super-admin');
    $q = Quote::factory()->create();
    expect($u->can('update', $q))->toBeTrue();
});

it('Sales can update only quotes assigned to them', function () {
    $sales = User::factory()->create();
    $sales->assignRole('Sales');

    $mine = Quote::factory()->create(['assigned_to' => $sales->id]);
    $other = Quote::factory()->create();

    expect($sales->can('update', $mine))->toBeTrue()
        ->and($sales->can('update', $other))->toBeFalse();
});

it('Viewer cannot delete a quote', function () {
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');
    $q = Quote::factory()->create();
    expect($viewer->can('delete', $q))->toBeFalse();
});

it('Editor cannot delete a quote (Quote is Sales scope)', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');
    $q = Quote::factory()->create();
    expect($editor->can('delete', $q))->toBeFalse();
});
