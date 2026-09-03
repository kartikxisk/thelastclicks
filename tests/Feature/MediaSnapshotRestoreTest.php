<?php

use App\Models\Industry;
use App\Models\Service;
use App\Support\MediaSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

/**
 * Replaying a fixture onto a database that has been living its own life.
 *
 * The id is the storage directory, which is what lets a fixture re-attach a
 * file without moving a byte — but it also assumes ids are unique across every
 * environment, and they are not. Local uploads and production uploads both
 * auto-increment, so production had client logos sitting on the very ids the
 * homepage artist frames were exported under: six of seven frames silently
 * restored with no file behind them and the reel rendered one image.
 */
it('restores a row whose id production already gave to something else', function () {
    // Something else owns the id the fixture wants.
    $squatter = Service::firstOrFail();
    $squatter->addMedia(UploadedFile::fake()->image('logo.png'))->toMediaCollection('gallery');
    $takenId = $squatter->getFirstMedia('gallery')->id;

    Storage::disk('s3')->put("{$takenId}-source/frame.jpg", 'frame-bytes');
    // The object the fixture describes lives under the id it was exported with.
    Storage::disk('s3')->put("{$takenId}/frame.jpg", 'frame-bytes');

    $industry = Industry::where('slug', 'cover-artist')->firstOrFail();

    $created = MediaSnapshot::restore($industry, [[
        'id' => $takenId,
        'collection_name' => 'hero',
        'name' => 'frame',
        'file_name' => 'frame.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 's3',
        'size' => 12,
        'order_column' => 1,
    ]]);

    expect($created)->toBe(1);

    $media = $industry->fresh()->getFirstMedia('hero');

    expect($media)->not->toBeNull()
        ->and($media->id)->not->toBe($takenId)
        // The bytes must follow: a row under a new id points at a new directory.
        ->and(Storage::disk('s3')->exists("{$media->id}/frame.jpg"))->toBeTrue()
        // ...and the squatter keeps its own file.
        ->and(Media::whereKey($takenId)->first()->model_type)->toBe($squatter->getMorphClass());
});

it('drops a stale row whose file is gone from the disk', function () {
    // What broke Post Production: an id-1 row from an older fixture, pointing at
    // an object that is not in the bucket. `hero` is singleFile, so two rows is
    // a state the admin cannot produce, and the dead one sorted first.
    $service = Service::where('slug', 'post-production')->firstOrFail();

    Media::query()->create([
        'id' => 1,
        'model_type' => $service->getMorphClass(),
        'model_id' => $service->getKey(),
        'uuid' => (string) Str::uuid(),
        'collection_name' => 'hero',
        'name' => 'ghost',
        'file_name' => 'ghost.jpg',
        'disk' => 's3',
        'size' => 10,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
        'order_column' => 1,
    ]);

    Storage::disk('s3')->put('900/real.jpg', 'real-bytes');

    MediaSnapshot::restore($service, [[
        'id' => 900,
        'collection_name' => 'hero',
        'name' => 'real',
        'file_name' => 'real.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 's3',
        'size' => 10,
        'order_column' => 2,
    ]]);

    expect($service->fresh()->getMedia('hero'))->toHaveCount(1)
        ->and($service->fresh()->getFirstMedia('hero')->file_name)->toBe('real.jpg');
});

it('keeps a row it has already restored rather than duplicating it', function () {
    $industry = Industry::where('slug', 'alcobev')->firstOrFail();
    Storage::disk('s3')->put('901/cover.jpg', 'bytes');

    $row = [[
        'id' => 901, 'collection_name' => 'hero', 'name' => 'cover',
        'file_name' => 'cover.jpg', 'mime_type' => 'image/jpeg',
        'disk' => 's3', 'size' => 5, 'order_column' => 1,
    ]];

    MediaSnapshot::restore($industry, $row);
    MediaSnapshot::restore($industry, $row);

    expect($industry->fresh()->getMedia('hero'))->toHaveCount(1);
});

it('survives a file it cannot copy instead of taking the deploy down', function () {
    // Production's IAM user is not granted s3:GetObjectAcl, so Flysystem's
    // copy() threw AccessDenied mid-`db:seed` and the whole deploy stopped with
    // the site half-repaired. A file that cannot be moved is a row that must
    // not exist; it is never a reason to abort.
    $squatter = Service::firstOrFail();
    $squatter->addMedia(UploadedFile::fake()->image('logo.png'))->toMediaCollection('gallery');
    $takenId = $squatter->getFirstMedia('gallery')->id;

    $industry = Industry::where('slug', 'alcobev')->firstOrFail();

    // No object behind the fixture row: the copy has nothing to move.
    $created = MediaSnapshot::restore($industry, [[
        'id' => $takenId,
        'collection_name' => 'hero',
        'name' => 'ghost',
        'file_name' => 'ghost.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 's3',
        'size' => 1,
        'order_column' => 1,
    ]]);

    expect($created)->toBe(0)
        // No half-made row left behind, and the squatter is untouched.
        ->and($industry->fresh()->getMedia('hero'))->toHaveCount(0)
        ->and(Media::whereKey($takenId)->exists())->toBeTrue();
});
