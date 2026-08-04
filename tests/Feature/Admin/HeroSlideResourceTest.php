<?php

use App\Filament\Resources\HeroSlideResource;
use App\Filament\Resources\HeroSlideResource\Pages\CreateHeroSlide;
use App\Filament\Resources\HeroSlideResource\Pages\EditHeroSlide;
use App\Filament\Resources\HeroSlideResource\Pages\ListHeroSlides;
use App\Models\HeroSlide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('is labelled Hero in the admin navigation', function () {
    expect(HeroSlideResource::getNavigationLabel())->toBe('Hero')
        ->and(HeroSlideResource::getNavigationGroup())->toBe('Content');
});

it('lists hero slides', function () {
    $slide = HeroSlide::create(['label' => 'Opening reel', 'order' => 0, 'is_active' => true]);

    Livewire::test(ListHeroSlides::class)->assertCanSeeTableRecords([$slide]);
});

it('creates a hero slide with an uploaded video', function () {
    Livewire::test(CreateHeroSlide::class)
        ->fillForm([
            'label' => 'Showreel',
            'order' => 0,
            'is_active' => true,
            'asset' => [UploadedFile::fake()->create('showreel.mp4', 64, 'video/mp4')],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $slide = HeroSlide::firstOrFail();

    expect($slide->label)->toBe('Showreel')
        ->and($slide->isVideo())->toBeTrue()
        ->and($slide->assetUrl())->not->toBeNull();
});

it('creates a hero slide with an uploaded image', function () {
    Livewire::test(CreateHeroSlide::class)
        ->fillForm([
            'label' => 'Still',
            'order' => 1,
            'is_active' => true,
            'asset' => [UploadedFile::fake()->image('still.jpg', 1920, 1080)],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $slide = HeroSlide::firstOrFail();

    expect($slide->isVideo())->toBeFalse()
        ->and($slide->assetUrl())->not->toBeNull();
});

it('refuses a slide with no asset', function () {
    // The asset is the whole point of the row, and an assetless slide is filtered
    // out of the hero anyway — better to reject it at the form than to let an
    // editor save a row that silently never renders.
    Livewire::test(CreateHeroSlide::class)
        ->fillForm(['label' => 'No file', 'order' => 0, 'is_active' => true])
        ->call('create')
        ->assertHasFormErrors(['asset']);
});

it('edits a slide label and active state', function () {
    $slide = HeroSlide::create(['label' => 'Before', 'order' => 0, 'is_active' => true]);
    // The asset is required, so an edit only saves once the row actually has one.
    $slide->addMedia(UploadedFile::fake()->create('before.mp4', 64, 'video/mp4'))
        ->toMediaCollection('asset');

    Livewire::test(EditHeroSlide::class, ['record' => $slide->getRouteKey()])
        ->fillForm(['label' => 'After', 'is_active' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($slide->fresh()->label)->toBe('After')
        ->and($slide->fresh()->is_active)->toBeFalse();
});

it('deletes a slide from the table', function () {
    $slide = HeroSlide::create(['label' => 'Doomed', 'order' => 0, 'is_active' => true]);

    Livewire::test(ListHeroSlides::class)
        ->callTableAction('delete', $slide);

    expect(HeroSlide::find($slide->id))->toBeNull();
});

it('lets an Editor manage hero slides', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    $slide = HeroSlide::create(['label' => 'Editable', 'order' => 0, 'is_active' => true]);

    expect($editor->can('viewAny', HeroSlide::class))->toBeTrue()
        ->and($editor->can('create', HeroSlide::class))->toBeTrue()
        ->and($editor->can('update', $slide))->toBeTrue()
        ->and($editor->can('delete', $slide))->toBeTrue();
});
