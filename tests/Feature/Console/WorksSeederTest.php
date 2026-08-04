<?php

use App\Models\MediaItem;
use App\Models\Work;
use Database\Seeders\WorksSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

it('rebuilds works and their media from the fixture', function () {
    // The fixture is the repo's own export, so this also fails if someone
    // commits a malformed works.json.
    expect(file_exists(database_path('seeders/data/works.json')))->toBeTrue();

    $this->seed(WorksSeeder::class);

    expect(Work::count())->toBeGreaterThan(0)
        ->and(Media::count())->toBeGreaterThan(0);
});

it('reuses the original media ids so files are not re-uploaded', function () {
    // Medialibrary's path is {media id}/{file name}. If the seeder let the ids
    // auto-increment, every URL would point at a directory that does not exist
    // on S3 and the whole archive would render broken.
    $rows = json_decode((string) file_get_contents(database_path('seeders/data/works.json')), true);

    $expected = collect($rows)
        ->flatMap(fn (array $w) => array_merge(
            $w['media'] ?? [],
            collect($w['media_items'] ?? [])->flatMap(fn ($i) => $i['media'] ?? [])->all(),
        ))
        ->pluck('id')
        ->filter()
        ->sort()
        ->values();

    $this->seed(WorksSeeder::class);

    expect(Media::orderBy('id')->pluck('id')->values()->all())->toBe($expected->all());
});

it('attaches media items to their work in fixture order', function () {
    $this->seed(WorksSeeder::class);

    $work = Work::has('mediaItems')->with('mediaItems')->first();

    expect($work)->not->toBeNull()
        ->and($work->mediaItems->pluck('order')->all())
        ->toBe($work->mediaItems->pluck('order')->sort()->values()->all());
});

it('is idempotent — a second run neither duplicates works nor media', function () {
    $this->seed(WorksSeeder::class);
    $works = Work::count();
    $media = Media::count();

    $this->seed(WorksSeeder::class);

    expect(Work::count())->toBe($works)
        ->and(Media::count())->toBe($media)
        ->and(MediaItem::count())->toBeGreaterThan(0);
});

it('never overwrites a work that already exists', function () {
    // This seeder runs from db:seed, which runs on every deploy. Works are
    // admin-managed content, so a re-run must not undo an editor's changes —
    // updateOrCreate here would silently revert every title and delete every
    // media item they had rearranged.
    $this->seed(WorksSeeder::class);

    $work = Work::firstOrFail();
    $work->update(['title' => 'Edited In The Admin']);
    $itemsBefore = $work->mediaItems()->count();

    $this->seed(WorksSeeder::class);

    expect($work->fresh()->title)->toBe('Edited In The Admin')
        ->and($work->fresh()->mediaItems()->count())->toBe($itemsBefore);
});

it('does nothing when the fixture is absent', function () {
    // A fresh clone that has not run app:export-works must not fatal mid-seed.
    $real = database_path('seeders/data/works.json');
    $backup = $real.'.testbak';
    rename($real, $backup);

    try {
        $this->seed(WorksSeeder::class);
        expect(Work::count())->toBe(0);
    } finally {
        rename($backup, $real);
    }
});
