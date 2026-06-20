<?php

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\TagResource\Pages\CreateTag;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can create a category', function () {
    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => 'Studio Diary'])
        ->call('create')
        ->assertHasNoFormErrors();
    expect(Category::where('name', 'Studio Diary')->exists())->toBeTrue();
});

it('Super-admin can create a tag', function () {
    Livewire::test(CreateTag::class)
        ->fillForm(['name' => 'cinematic'])
        ->call('create')
        ->assertHasNoFormErrors();
    expect(Tag::where('name', 'cinematic')->exists())->toBeTrue();
});
