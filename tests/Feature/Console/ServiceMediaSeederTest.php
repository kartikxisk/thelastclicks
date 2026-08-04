<?php

use App\Models\Service;
use Database\Seeders\ServiceMediaSeeder;
use Database\Seeders\ServicesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

it('reattaches service hero media from the fixture', function () {
    // ServicesSeeder rebuilds the rows; the hero upload is a medialibrary row it
    // knows nothing about, so without this the page renders with a blank header.
    $this->seed(ServicesSeeder::class);
    $this->seed(ServiceMediaSeeder::class);

    expect(Media::where('model_type', Service::class)->count())->toBeGreaterThan(0);
});

it('files the hero in its own collection, not the default one', function () {
    // getMedia() defaults to the 'default' collection, which silently exported
    // nothing for a hero. The export is collection-agnostic; this pins that.
    $this->seed(ServicesSeeder::class);
    $this->seed(ServiceMediaSeeder::class);

    expect(Media::where('model_type', Service::class)->pluck('collection_name')->unique()->all())
        ->not->toContain('default');
});

it('is idempotent', function () {
    $this->seed(ServicesSeeder::class);
    $this->seed(ServiceMediaSeeder::class);
    $count = Media::where('model_type', Service::class)->count();

    $this->seed(ServiceMediaSeeder::class);

    expect(Media::where('model_type', Service::class)->count())->toBe($count);
});
