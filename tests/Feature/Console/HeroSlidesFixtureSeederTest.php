<?php

use App\Models\HeroSlide;
use Database\Seeders\HeroSlidesFixtureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

it('rebuilds the hero from the fixture', function () {
    // Without this a rebuilt database renders no hero background at all, because
    // hero.blade.php omits the whole layer when no slide is active.
    $this->seed(HeroSlidesFixtureSeeder::class);

    expect(HeroSlide::count())->toBeGreaterThan(0)
        ->and(HeroSlide::active()->get()->filter(fn ($s) => filled($s->assetUrl()))->count())
        ->toBeGreaterThan(0);
});

it('restores both the asset and its poster', function () {
    // A video slide without its poster paints black until the film buffers.
    $this->seed(HeroSlidesFixtureSeeder::class);

    $slide = HeroSlide::first();

    expect($slide->assetUrl())->not->toBeNull()
        ->and($slide->posterUrl())->not->toBeNull();
});

it('reuses the original media ids', function () {
    $expected = collect(json_decode((string) file_get_contents(database_path('seeders/data/hero-slides.json')), true))
        ->flatMap(fn (array $s) => $s['media'] ?? [])
        ->pluck('id')->sort()->values()->all();

    $this->seed(HeroSlidesFixtureSeeder::class);

    expect(Media::orderBy('id')->pluck('id')->all())->toBe($expected);
});

it('never overwrites a slide an editor already owns', function () {
    // The hero is admin-managed; a deploy must not undo somebody's upload.
    $this->seed(HeroSlidesFixtureSeeder::class);
    $slide = HeroSlide::first();
    $slide->update(['is_active' => false]);
    $count = HeroSlide::count();

    $this->seed(HeroSlidesFixtureSeeder::class);

    expect(HeroSlide::count())->toBe($count)
        ->and($slide->fresh()->is_active)->toBeFalse();
});
