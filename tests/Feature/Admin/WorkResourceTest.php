<?php

use App\Filament\RelationManagers\MediaItemsRelationManager;
use App\Filament\Resources\WorkResource\Pages\CreateWork;
use App\Filament\Resources\WorkResource\Pages\EditWork;
use App\Filament\Resources\WorkResource\Pages\ListWorks;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->actingAs(User::where('email', config('app.admin_seed_email'))->firstOrFail());
});

it('Super-admin can list works', function () {
    Work::create(['title' => 'Listed Work']);

    Livewire::test(ListWorks::class)->assertCanSeeTableRecords(Work::all());
});

it('Super-admin can create a work', function () {
    Livewire::test(CreateWork::class)
        ->fillForm([
            'title' => 'Navy Film',
            'slug' => 'navy-film',
            'client' => 'Indian Navy',
            'year' => '2026',
            'is_published' => true,
            'is_featured' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $work = Work::where('slug', 'navy-film')->firstOrFail();
    expect($work->client)->toBe('Indian Navy')
        ->and($work->is_featured)->toBeTrue();
});

it('stores mixed media rows against a work in order', function () {
    $work = Work::create(['title' => 'Mixed']);

    $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $work->mediaItems()->create(['type' => 'youtube', 'order' => 2, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    $work->mediaItems()->create(['type' => 'video', 'order' => 3]);

    expect($work->fresh()->mediaItems->pluck('type')->all())
        ->toBe(['image', 'youtube', 'video']);
});

it('retyping an image row to youtube through the admin form detaches its stale file', function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');

    $work = Work::create(['title' => 'Retype Orphan']);
    $row = $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $row->addMedia(UploadedFile::fake()->image('shot.jpg'))->toMediaCollection('file');

    expect($row->fresh()->getFirstMedia('file'))->not->toBeNull();

    Livewire::test(MediaItemsRelationManager::class, ['ownerRecord' => $work, 'pageClass' => EditWork::class])
        ->callTableAction('edit', $row, data: [
            'type' => 'youtube',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])
        ->assertHasNoTableActionErrors();

    $row->refresh();
    expect($row->type)->toBe('youtube')
        ->and($row->getFirstMedia('file'))->toBeNull();
});
