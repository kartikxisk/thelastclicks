<?php

use App\Models\Industry;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');

    $this->fixtures = base_path('tests/tmp-drive-'.uniqid());
    File::makeDirectory($this->fixtures, 0755, true);

    $this->manifest = $this->fixtures.'/manifest.json';

    /** Build a project directory holding the three renditions. */
    $this->renditions = function (string $dir): void {
        $path = $this->fixtures.'/'.$dir;
        File::makeDirectory($path, 0755, true);
        File::put($path.'/poster.jpg', 'jpeg-bytes');
        File::put($path.'/preview.mp4', 'preview-bytes');
        File::put($path.'/full.mp4', 'full-bytes');
    };

    /** @param list<array<string,mixed>> $works */
    $this->writeManifest = function (array $works): void {
        File::put($this->manifest, json_encode([
            'groups' => ['alcobev' => 'Alcobev', 'podcast' => 'Podcast'],
            'works' => $works,
        ]));
    };
});

afterEach(function () {
    File::deleteDirectory($this->fixtures);
});

function importDrive(string $manifest, string $dir, array $opts = []): int
{
    return Artisan::call('works:import-drive', array_merge([
        '--manifest' => $manifest,
        '--dir' => $dir,
    ], $opts));
}

it('publishes a project that has its renditions, and attaches all three', function () {
    ($this->renditions)('tanqueray');
    ($this->writeManifest)([
        ['title' => 'Tanqueray', 'groups' => ['alcobev'], 'drive' => 'x'],
    ]);

    expect(importDrive($this->manifest, $this->fixtures))->toBe(0);

    $work = Work::where('slug', 'tanqueray')->firstOrFail();

    expect($work->is_published)->toBeTrue()
        ->and($work->getMedia('cover'))->toHaveCount(1)
        // The lightbox plays a `video` MediaItem, not the cover collection.
        ->and($work->mediaItems()->where('type', 'video')->count())->toBe(1)
        // The hover loop is a plain object, referenced by absolute URL.
        ->and($work->preview_video_url)->toContain('portfolio/previews/tanqueray.mp4');

    Storage::disk('s3')->assertExists('portfolio/previews/tanqueray.mp4');
});

it('creates a project with no renditions unpublished and without media', function () {
    // Several Drive links were not shareable. A row waiting in the admin beats a
    // silent omission — but publishing it would put a blank tile on a live grid.
    ($this->writeManifest)([
        ['title' => 'CapitaLand', 'groups' => ['alcobev'], 'drive' => 'x'],
    ]);

    expect(importDrive($this->manifest, $this->fixtures))->toBe(0);

    $work = Work::where('slug', 'capitaland')->firstOrFail();

    expect($work->is_published)->toBeFalse()
        ->and($work->getMedia('cover'))->toBeEmpty()
        ->and($work->preview_video_url)->toBeNull();
});

it('files a project under every industry the manifest names', function () {
    ($this->renditions)('dlf-thrive');
    ($this->writeManifest)([
        ['title' => 'DLF Thrive', 'groups' => ['alcobev', 'podcast'], 'drive' => 'x'],
    ]);

    importDrive($this->manifest, $this->fixtures);

    $slugs = Work::where('slug', 'dlf-thrive')->firstOrFail()->industries->pluck('slug');

    expect($slugs)->toHaveCount(2)->toContain('alcobev')->toContain('podcast');
});

it('honours a dir override when the folder name is not the title slug', function () {
    // The download script spells `&` as "and"; Str::slug drops it. Without the
    // override "Haldi & Mehandi" would look for haldi-mehandi and find nothing.
    ($this->renditions)('haldi-and-mehandi');
    ($this->writeManifest)([
        ['title' => 'Haldi & Mehandi', 'groups' => [], 'drive' => 'x', 'dir' => 'haldi-and-mehandi'],
    ]);

    importDrive($this->manifest, $this->fixtures);

    expect(Work::where('slug', 'haldi-mehandi')->firstOrFail()->is_published)->toBeTrue();
});

it('re-running uploads nothing twice and moves a reassigned project', function () {
    ($this->renditions)('patron');
    ($this->writeManifest)([
        ['title' => 'Patron', 'groups' => ['alcobev'], 'drive' => 'x'],
    ]);
    importDrive($this->manifest, $this->fixtures);

    // A corrected manifest: same project, different industry.
    ($this->writeManifest)([
        ['title' => 'Patron', 'groups' => ['podcast'], 'drive' => 'x'],
    ]);
    importDrive($this->manifest, $this->fixtures);

    $work = Work::where('slug', 'patron')->firstOrFail();

    expect(Work::where('slug', 'patron')->count())->toBe(1)
        // Duplicated media would leave the tile showing whichever won the sort
        // and the lightbox playing the same file twice.
        ->and($work->getMedia('cover'))->toHaveCount(1)
        ->and($work->mediaItems()->where('type', 'video')->count())->toBe(1)
        // sync, not attach: a reassignment moves the project rather than adding.
        ->and($work->industries->pluck('slug')->all())->toBe(['podcast']);
});

it('refuses a manifest naming an industry that is not in the database', function () {
    ($this->writeManifest)([]);
    File::put($this->manifest, json_encode([
        'groups' => ['not-an-industry' => 'Nope'],
        'works' => [],
    ]));

    expect(importDrive($this->manifest, $this->fixtures))->toBe(1)
        ->and(Industry::where('slug', 'not-an-industry')->exists())->toBeFalse();
});

it('writes nothing on a dry run', function () {
    ($this->renditions)('macallan');
    ($this->writeManifest)([
        ['title' => 'Macallan', 'groups' => ['alcobev'], 'drive' => 'x'],
    ]);

    importDrive($this->manifest, $this->fixtures, ['--dry-run' => true]);

    expect(Work::where('slug', 'macallan')->exists())->toBeFalse();
    Storage::disk('s3')->assertMissing('portfolio/previews/macallan.mp4');
});
