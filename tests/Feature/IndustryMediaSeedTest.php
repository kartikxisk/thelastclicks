<?php

use App\Models\Industry;
use App\Models\MediaItem;
use Database\Seeders\IndustryMediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

/**
 * Industry imagery survives a rebuilt database.
 *
 * Uploads made in the admin are medialibrary rows, which IndustriesSeeder knows
 * nothing about — so production came up with the industry rows present and every
 * cover falling back to the seeded stock path, and the homepage artist reel
 * (which reads the cover-artist media items) rendering not at all. Same fixture
 * trick ServiceMediaSeeder uses: rows replay under their original ids, so the
 * URL resolves to the object already in the bucket.
 */
it('replays an industry cover upload onto a rebuilt database', function () {
    $industry = Industry::where('slug', 'alcobev')->firstOrFail();
    $industry->addMedia(UploadedFile::fake()->image('cover.jpg'))->toMediaCollection('hero');

    $url = $industry->fresh()->coverUrl();
    $path = base_path('tests/tmp-industry-media.json');

    $this->artisan('app:export-industry-media', ['--path' => $path])->assertSuccessful();

    // A rebuilt database loses the rows; the bucket keeps every object. Delete
    // through the query builder rather than clearMediaCollection(), which would
    // take the files with it and simulate the wrong disaster.
    Media::query()->where('model_type', $industry->getMorphClass())->delete();
    expect($industry->fresh()->coverUrl())->not->toBe($url);

    (new IndustryMediaSeeder($path))->run();

    expect($industry->fresh()->coverUrl())->toBe($url);

    unlink($path);
});

it('replays the cover-artist reel frames, which the homepage band needs', function () {
    $industry = Industry::where('slug', 'cover-artist')->firstOrFail();
    $item = $industry->mediaItems()->create(['type' => 'image', 'order' => 0]);
    $item->addMedia(UploadedFile::fake()->image('frame.jpg'))->toMediaCollection('file');

    $url = $item->fresh()->resolvedUrl();
    $path = base_path('tests/tmp-industry-media.json');

    $this->artisan('app:export-industry-media', ['--path' => $path])->assertSuccessful();

    Media::query()->where('model_type', (new MediaItem)->getMorphClass())->delete();
    $industry->mediaItems()->getQuery()->delete();
    expect($industry->fresh()->mediaItems()->count())->toBe(0);

    (new IndustryMediaSeeder($path))->run();

    $restored = $industry->fresh()->mediaItems()->where('type', 'image')->get();

    expect($restored)->toHaveCount(1)
        ->and($restored->first()->resolvedUrl())->toBe($url);

    unlink($path);
});

it('leaves imagery an editor already replaced alone', function () {
    // Re-running on a live database must not duplicate rows or undo an edit.
    $industry = Industry::where('slug', 'alcobev')->firstOrFail();
    $industry->addMedia(UploadedFile::fake()->image('cover.jpg'))->toMediaCollection('hero');

    $path = base_path('tests/tmp-industry-media.json');
    $this->artisan('app:export-industry-media', ['--path' => $path])->assertSuccessful();

    $before = $industry->fresh()->coverUrl();
    (new IndustryMediaSeeder($path))->run();

    expect($industry->fresh()->coverUrl())->toBe($before)
        ->and($industry->fresh()->getMedia('hero'))->toHaveCount(1);

    unlink($path);
});

it('repairs media items a failed restore left with no file behind them', function () {
    // Exactly the state production is in: the first deploy created the reel's
    // items but skipped their media rows (the ids were taken by client logos),
    // so six items exist with nothing behind them. A re-run must repair those,
    // not stack a second set of items beside them.
    $industry = Industry::where('slug', 'cover-artist')->firstOrFail();
    $item = $industry->mediaItems()->create(['type' => 'image', 'order' => 0]);
    $item->addMedia(UploadedFile::fake()->image('frame.jpg'))->toMediaCollection('file');

    $path = base_path('tests/tmp-industry-media.json');
    $this->artisan('app:export-industry-media', ['--path' => $path])->assertSuccessful();

    // Strip the media row, leaving the orphaned item — the broken production state.
    Media::query()->where('model_type', (new MediaItem)->getMorphClass())->delete();
    expect($industry->fresh()->mediaItems)->toHaveCount(1)
        ->and($industry->fresh()->mediaItems->first()->resolvedUrl())->toBeNull();

    (new IndustryMediaSeeder($path))->run();

    $items = $industry->fresh()->mediaItems;

    expect($items)->toHaveCount(1)
        ->and($items->first()->resolvedUrl())->not->toBeNull();

    unlink($path);
});

it('leaves a youtube item alone, which legitimately has no upload', function () {
    // A youtube row carries a URL and no file, so "no media" cannot be the test
    // for a broken item on its own.
    $industry = Industry::where('slug', 'cover-artist')->firstOrFail();
    $industry->mediaItems()->create([
        'type' => 'youtube',
        'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
        'order' => 0,
    ]);

    $path = base_path('tests/tmp-industry-media.json');
    $this->artisan('app:export-industry-media', ['--path' => $path])->assertSuccessful();

    (new IndustryMediaSeeder($path))->run();

    expect($industry->fresh()->mediaItems()->where('type', 'youtube')->count())->toBe(1);

    unlink($path);
});
