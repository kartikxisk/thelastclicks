<?php

use App\Models\Industry;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');

    $this->photos = base_path('tests/tmp-photos-'.uniqid());
    File::makeDirectory($this->photos, 0755, true);
    File::put($this->photos.'/DSC00250.jpg', 'jpeg-bytes');
    File::put($this->photos.'/DSC00261.jpg', 'jpeg-bytes');

    $this->industry = Industry::factory()->create(['slug' => 'cover-artist']);
    $this->work = Work::factory()->create(['slug' => 'javed-ali']);
    $this->work->industries()->attach($this->industry);
});

afterEach(function () {
    File::deleteDirectory($this->photos);
});

function importPhotos(string $slug, string $dir, array $opts = []): int
{
    return Artisan::call('works:import-photos', array_merge([
        'slug' => $slug,
        '--dir' => $dir,
    ], $opts));
}

it('attaches every photo as an ordered image row', function () {
    expect(importPhotos('javed-ali', $this->photos))->toBe(0);

    $items = $this->work->refresh()->mediaItems;

    expect($items)->toHaveCount(2)
        ->and($items->pluck('type')->unique()->all())->toBe(['image'])
        ->and($items->pluck('order')->all())->toBe([1, 2])
        ->and($items->first()->getFirstMedia('file')->file_name)->toBe('javed-ali-dsc00250.jpg');
});

it('adds nothing on a second run', function () {
    importPhotos('javed-ali', $this->photos);
    expect(importPhotos('javed-ali', $this->photos))->toBe(0);

    expect($this->work->refresh()->mediaItems)->toHaveCount(2);
});

it('appends only the new file when the folder grows', function () {
    importPhotos('javed-ali', $this->photos);
    File::put($this->photos.'/DSC00999.jpg', 'jpeg-bytes');

    importPhotos('javed-ali', $this->photos);

    expect($this->work->refresh()->mediaItems)->toHaveCount(3);
});

it('orders new rows after media the work already had', function () {
    $this->work->mediaItems()->create(['type' => 'video', 'order' => 1]);

    importPhotos('javed-ali', $this->photos);

    expect($this->work->refresh()->mediaItems()->where('type', 'image')->pluck('order')->all())
        ->toBe([2, 3]);
});

it('puts the featured frame on the industry reel the homepage band renders', function () {
    importPhotos('javed-ali', $this->photos, ['--feature' => 'DSC00261.jpg']);

    $frames = $this->industry->refresh()->mediaItems;

    expect($frames)->toHaveCount(1)
        ->and($frames->first()->type)->toBe('image')
        ->and($frames->first()->getFirstMedia('file')->file_name)->toBe('javed-ali-dsc00261.jpg');
});

it('does not put the same frame on the reel twice', function () {
    importPhotos('javed-ali', $this->photos, ['--feature' => 'DSC00261.jpg']);
    importPhotos('javed-ali', $this->photos, ['--feature' => 'DSC00261.jpg']);

    expect($this->industry->refresh()->mediaItems)->toHaveCount(1);
});

it('writes nothing on a dry run', function () {
    expect(importPhotos('javed-ali', $this->photos, ['--dry-run' => true]))->toBe(0);

    expect($this->work->refresh()->mediaItems)->toHaveCount(0);
});

it('fails on an unknown slug rather than creating a work', function () {
    expect(importPhotos('nobody', $this->photos))->toBe(1)
        ->and(Work::where('slug', 'nobody')->exists())->toBeFalse();
});

it('fails when --feature names a file that is not in the folder', function () {
    expect(importPhotos('javed-ali', $this->photos, ['--feature' => 'missing.jpg']))->toBe(1)
        ->and($this->work->refresh()->mediaItems)->toHaveCount(0);
});
