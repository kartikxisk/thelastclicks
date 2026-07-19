# S3 + CloudFront Media Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Serve all site media (admin uploads + currently static hero/strip videos) from S3 behind CloudFront in every environment, with the static videos converted to admin-managed medialibrary media on Portfolio models.

**Architecture:** Switch spatie/laravel-medialibrary to an `s3` disk whose `url` is the CloudFront domain (private bucket + OAC, so no ACLs are ever sent). A one-shot artisan command imports `public/videos/*` onto the seeded portfolios and migrates any existing public-disk media rows. The homepage hero and film strip stop being hardcoded and read ordered portfolio slugs + copy from `SiteSetting` keys, editable in the existing Filament SiteSettingsPage.

**Tech Stack:** Laravel 11, spatie/laravel-medialibrary v11, Filament v3, league/flysystem-aws-s3-v3 (new dep), Pest.

**Spec:** `docs/superpowers/specs/2026-07-19-s3-cloudfront-media-design.md`

## Global Constraints

- **Do NOT git commit.** User rule: leave all changes uncommitted in the working tree; commit only on explicit ask. Every "Commit" step in the normal template is replaced by "leave uncommitted".
- Private bucket + CloudFront OAC: the `s3` disk in `config/filesystems.php` must never gain a `visibility` key and no code may set ACLs.
- All media URLs must be generated via medialibrary / `Storage::url()` (which resolves through `AWS_URL` = CloudFront domain). Never hand-build CDN URLs.
- Existing tests must keep passing. Test env pins `FILESYSTEM_DISK=local` and `MEDIA_DISK=public`; individual new tests opt into s3 with `config(['media-library.disk_name' => 's3'])` + `Storage::fake('s3')`.
- Views keep existing legacy fallbacks (`?: $item->cover_url` etc.). Hero/strip entries with missing media are skipped, not fallback-rendered.
- Run `vendor/bin/pint --dirty` and `vendor/bin/phpstan analyse` at the end of every task; both must be clean.

---

### Task 1: S3 foundation — composer dep, media-library config, env plumbing

**Files:**
- Modify: `composer.json` / `composer.lock` (via composer CLI)
- Create: `config/media-library.php` (vendor publish, then edit)
- Modify: `phpunit.xml:21-31` (add two env lines)
- Modify: `.env.example:38,60-64` and `.env`
- Test: `tests/Feature/MediaStorageConfigTest.php`

**Interfaces:**
- Produces: `config('media-library.disk_name')` = `env('MEDIA_DISK', 's3')`; `config('media-library.remote.extra_headers')` contains `CacheControl: max-age=31536000, immutable`; working `Storage::disk('s3')` adapter. All later tasks rely on these.

- [ ] **Step 1: Install the S3 flysystem adapter**

Run: `composer require league/flysystem-aws-s3-v3:^3.0 --with-all-dependencies`
Expected: success, `league/flysystem-aws-s3-v3` appears in composer.json require.

- [ ] **Step 2: Publish the medialibrary config**

Run: `php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config"`
Expected: `config/media-library.php` created.

- [ ] **Step 3: Edit the published config**

In `config/media-library.php` change these two entries (leave the rest untouched):

```php
'disk_name' => env('MEDIA_DISK', 's3'),
```

and inside the existing `'remote' => ['extra_headers' => [...]]` block:

```php
'remote' => [
    'extra_headers' => [
        'CacheControl' => 'max-age=31536000, immutable',
    ],
],
```

- [ ] **Step 4: Pin test env**

In `phpunit.xml`, inside `<php>`, after the existing `<env>` lines add:

```xml
<env name="FILESYSTEM_DISK" value="local"/>
<env name="MEDIA_DISK" value="public"/>
```

- [ ] **Step 5: Env files**

`.env.example` — change line 38 and extend the AWS block:

```dotenv
FILESYSTEM_DISK=s3
MEDIA_DISK=s3
...
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_URL=            # CloudFront domain, e.g. https://dxxxxxxxx.cloudfront.net
AWS_USE_PATH_STYLE_ENDPOINT=false
```

`.env` — set the same keys with the real values (user fills `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_URL`; region as appropriate). Do not print secret values into any log or chat output.

- [ ] **Step 6: Write the failing test**

Create `tests/Feature/MediaStorageConfigTest.php`:

```php
<?php

use Illuminate\Support\Facades\Storage;

it('medialibrary disk is env-driven and defaults to s3 outside tests', function () {
    // phpunit.xml pins MEDIA_DISK=public for the suite
    expect(config('media-library.disk_name'))->toBe('public');
});

it('medialibrary sends immutable cache headers for remote disks', function () {
    expect(config('media-library.remote.extra_headers.CacheControl'))
        ->toBe('max-age=31536000, immutable');
});

it('s3 urls resolve through the configured CloudFront domain', function () {
    config([
        'filesystems.disks.s3.url' => 'https://cdn.example.com',
        'filesystems.disks.s3.bucket' => 'bucket',
        'filesystems.disks.s3.region' => 'us-east-1',
        'filesystems.disks.s3.key' => 'k',
        'filesystems.disks.s3.secret' => 's',
    ]);

    expect(Storage::disk('s3')->url('media/1/film.mp4'))
        ->toBe('https://cdn.example.com/media/1/film.mp4');
});
```

- [ ] **Step 7: Run the test**

Run: `php artisan test tests/Feature/MediaStorageConfigTest.php`
Expected: PASS (steps 1–5 already satisfy it; if the URL test errors with "Class League\Flysystem\AwsS3V3\... not found", step 1 failed).

- [ ] **Step 8: Full suite + static analysis**

Run: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse`
Expected: all green. Leave changes uncommitted.

---

### Task 2: `media:import-local` artisan command

**Files:**
- Create: `app/Console/Commands/ImportLocalMedia.php`
- Test: `tests/Feature/ImportLocalMediaTest.php`

**Interfaces:**
- Consumes: Portfolio media collections `cover` (singleFile) and `gallery` (from `App\Models\Portfolio::registerMediaCollections()`), seeded portfolio slugs from `PortfoliosSeeder`.
- Produces: command `media:import-local {--source=}`; after running, seeded portfolios have `cover` (poster jpg) and `gallery` (mp4) media on the configured media disk, and any `media` rows with `disk = 'public'` are moved to that disk. Exit code 0 on success, 1 if any file failed.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/ImportLocalMediaTest.php`:

```php
<?php

use App\Models\Portfolio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');

    $this->source = storage_path('framework/testing/legacy-videos');
    File::ensureDirectoryExists($this->source.'/posters');
    File::put($this->source.'/ins-navy-blackdog.mp4', 'fake-video');
    File::put($this->source.'/posters/ins-navy-blackdog.jpg', 'fake-poster');

    $this->seed();
});

afterEach(fn () => File::deleteDirectory($this->source));

it('attaches legacy files to the mapped portfolio and uploads to the media disk', function () {
    $this->artisan('media:import-local', ['--source' => $this->source])->assertSuccessful();

    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();

    expect($portfolio->getFirstMedia('cover'))->not->toBeNull()
        ->and($portfolio->getFirstMedia('gallery'))->not->toBeNull()
        ->and($portfolio->getFirstMedia('gallery')->disk)->toBe('s3');

    Storage::disk('s3')->assertExists(
        $portfolio->getFirstMedia('gallery')->getPathRelativeToRoot()
    );
});

it('is idempotent — second run attaches nothing new', function () {
    $this->artisan('media:import-local', ['--source' => $this->source])->assertSuccessful();
    $this->artisan('media:import-local', ['--source' => $this->source])->assertSuccessful();

    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    expect($portfolio->getMedia('gallery'))->toHaveCount(1)
        ->and($portfolio->getMedia('cover'))->toHaveCount(1);
});

it('skips portfolios whose source files are missing without failing', function () {
    // source dir only contains ins-navy files; the other 8 mapped portfolios must be skipped
    $this->artisan('media:import-local', ['--source' => $this->source])->assertSuccessful();

    expect(Portfolio::where('slug', 'range-rover')->firstOrFail()->getMedia('gallery'))->toHaveCount(0);
});

it('migrates existing public-disk media rows to the media disk', function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $portfolio = Portfolio::where('slug', 'diwali-motion')->firstOrFail();
    $portfolio->addMedia(UploadedFile::fake()->image('old.jpg'))->toMediaCollection('cover');

    config(['media-library.disk_name' => 's3']);
    $this->artisan('media:import-local', ['--source' => $this->source])->assertSuccessful();

    $media = $portfolio->fresh()->getFirstMedia('cover');
    expect($media->disk)->toBe('s3')
        ->and($media->conversions_disk)->toBe('s3');
    Storage::disk('s3')->assertExists($media->getPathRelativeToRoot());
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/ImportLocalMediaTest.php`
Expected: FAIL — "There are no commands defined in the 'media' namespace."

- [ ] **Step 3: Implement the command**

Create `app/Console/Commands/ImportLocalMedia.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\Portfolio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class ImportLocalMedia extends Command
{
    protected $signature = 'media:import-local
        {--source= : Directory holding the legacy video files (defaults to public/videos)}';

    protected $description = 'Attach legacy public/videos files to seeded portfolios as media and move public-disk media rows to the configured media disk';

    /** Seeded portfolio slug => legacy video basename (mirrors PortfoliosSeeder). */
    protected array $videoMap = [
        'ins-navy' => 'ins-navy-blackdog',
        'salesforce-blr' => 'salesforce-blr',
        'rahul-dravid-teaser' => 'rahul-dravid-teaser',
        'range-rover' => 'range-rover',
        'black-label' => 'black-label',
        'pramod-pooja-prewedding' => 'prewedding-pramod-pooja',
        'birthday-reel' => 'birthday-reel',
        'jw-fashion-show' => 'jw-fashion-show',
        'diwali-motion' => 'diwali-motion',
    ];

    public function handle(): int
    {
        $source = rtrim($this->option('source') ?: public_path('videos'), '/');
        $failures = 0;

        foreach ($this->videoMap as $slug => $video) {
            $portfolio = Portfolio::where('slug', $slug)->first();

            if (! $portfolio) {
                $this->warn("skip {$slug}: portfolio not found");

                continue;
            }

            $failures += $this->attach($portfolio, 'cover', "{$source}/posters/{$video}.jpg");
            $failures += $this->attach($portfolio, 'gallery', "{$source}/{$video}.mp4");
        }

        $failures += $this->migratePublicDiskMedia();

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }

    protected function attach(Portfolio $portfolio, string $collection, string $path): int
    {
        if ($portfolio->getMedia($collection)->isNotEmpty()) {
            $this->line("skip {$portfolio->slug}/{$collection}: already has media");

            return 0;
        }

        if (! is_file($path)) {
            $this->warn("skip {$portfolio->slug}/{$collection}: {$path} missing");

            return 0;
        }

        try {
            $portfolio->addMedia($path)->preservingOriginal()->toMediaCollection($collection);
            $this->info("attached {$portfolio->slug}/{$collection}: ".basename($path));

            return 0;
        } catch (Throwable $e) {
            $this->error("FAILED {$portfolio->slug}/{$collection}: {$e->getMessage()}");

            return 1;
        }
    }

    protected function migratePublicDiskMedia(): int
    {
        $target = config('media-library.disk_name');

        if ($target === 'public') {
            return 0;
        }

        $failures = 0;

        Media::query()->where('disk', 'public')->each(function (Media $media) use ($target, &$failures) {
            try {
                $relative = $media->getPathRelativeToRoot();
                $stream = Storage::disk('public')->readStream($relative);

                if (! is_resource($stream)) {
                    throw new \RuntimeException('source file unreadable');
                }

                Storage::disk($target)->writeStream($relative, $stream);
                $media->update(['disk' => $target, 'conversions_disk' => $target]);
                $this->info("moved media #{$media->id} to {$target}");
            } catch (Throwable $e) {
                $failures++;
                $this->error("FAILED media #{$media->id}: {$e->getMessage()}");
            }
        });

        return $failures;
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/ImportLocalMediaTest.php`
Expected: 4 passing.

- [ ] **Step 5: Full suite + static analysis**

Run: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse`
Expected: all green. Leave changes uncommitted.

---

### Task 3: Seeder changes — stop writing `/videos/...` paths, seed strip/hero settings

**Files:**
- Modify: `database/seeders/PortfoliosSeeder.php:96-97` (drop legacy path writes)
- Modify: `database/seeders/SiteSettingsSeeder.php` (append two settings)
- Test: `tests/Feature/SeederTest.php` (extend)

**Interfaces:**
- Consumes: `SiteSetting::set(string $key, mixed $value)`.
- Produces: `SiteSetting::get('home_strip')` = ordered array of `['portfolio_slug' => string, 'tag' => string, 'title' => string (HTML), 'meta' => string]`; `SiteSetting::get('hero_videos')` = ordered array of portfolio slug strings. Tasks 4, 5, 7 rely on these exact shapes and key names.

- [ ] **Step 1: Extend SeederTest (failing first)**

Add to `tests/Feature/SeederTest.php` (inside the file, after the existing `it(...)` block):

```php
it('seeds homepage strip and hero video settings', function () {
    $this->seed();

    $strip = SiteSetting::get('home_strip');
    expect($strip)->toHaveCount(6)
        ->and($strip[0]['portfolio_slug'])->toBe('ins-navy')
        ->and($strip[0])->toHaveKeys(['portfolio_slug', 'tag', 'title', 'meta']);

    expect(SiteSetting::get('hero_videos'))
        ->toBe(['ins-navy', 'salesforce-blr', 'rahul-dravid-teaser']);
});

it('portfolio seeder no longer writes legacy /videos paths', function () {
    $this->seed();

    $p = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    expect($p->cover_url)->toBeNull()
        ->and($p->gallery_urls)->toBeNull();
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test tests/Feature/SeederTest.php`
Expected: FAIL — `home_strip` is null; `cover_url` is `/videos/posters/ins-navy-blackdog.jpg`.

- [ ] **Step 3: Edit PortfoliosSeeder**

In `database/seeders/PortfoliosSeeder.php`, inside the `updateOrCreate` payload, delete these two lines:

```php
'cover_url' => '/videos/posters/'.$c['video'].'.jpg',
'gallery_urls' => ['/videos/'.$c['video'].'.mp4'],
```

Keep the `'video' => ...` keys in `$cases` (documentation of the mapping; the import command has its own copy).

Note: `updateOrCreate` without those keys leaves existing DB values untouched on reseeds of old databases; fresh databases get `null`, which the views' `?:` fallbacks and Task 4/5 skip-logic handle. The new SeederTest expectation (`toBeNull`) holds because tests run on a fresh in-memory database.

- [ ] **Step 4: Append settings to SiteSettingsSeeder**

In `database/seeders/SiteSettingsSeeder.php`, inside `run()`, append (adjusting to the file's existing style of `SiteSetting::set(...)` calls):

```php
SiteSetting::set('home_strip', [
    ['portfolio_slug' => 'ins-navy', 'tag' => '001 · Defence · 2026', 'title' => 'Indian <em>Navy.</em>', 'meta' => 'Official event film'],
    ['portfolio_slug' => 'salesforce-blr', 'tag' => '002 · Corporate · 2026', 'title' => 'Salesforce · <em>Bengaluru.</em>', 'meta' => 'Multi-cam recap film'],
    ['portfolio_slug' => 'rahul-dravid-teaser', 'tag' => '003 · Campaign · 2026', 'title' => 'Rahul Dravid · <em>teaser.</em>', 'meta' => 'Brand campaign film'],
    ['portfolio_slug' => 'range-rover', 'tag' => '004 · Automotive · 2026', 'title' => 'Range <em>Rover.</em>', 'meta' => 'Platform-first reel'],
    ['portfolio_slug' => 'black-label', 'tag' => '005 · Brands · 2026', 'title' => 'Black <em>Label.</em>', 'meta' => 'Regulated-category reel'],
    ['portfolio_slug' => 'pramod-pooja-prewedding', 'tag' => '006 · Wedding · 2026', 'title' => 'Pramod &amp; <em>Pooja.</em>', 'meta' => 'Pre-wedding film'],
]);

SiteSetting::set('hero_videos', ['ins-navy', 'salesforce-blr', 'rahul-dravid-teaser']);
```

If the seeder guards against overwriting admin-edited values (e.g. only sets when missing), follow that same pattern for both keys.

- [ ] **Step 5: Run tests**

Run: `php artisan test tests/Feature/SeederTest.php`
Expected: PASS.

- [ ] **Step 6: Full suite + static analysis**

Run: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse`
Expected: all green (watch for other tests asserting `cover_url` values — update any that assert the seeded `/videos/...` paths to expect media-based rendering instead). Leave changes uncommitted.

---

### Task 4: Data-driven film strip (HomeController + home.blade.php)

**Files:**
- Modify: `app/Http/Controllers/Public/HomeController.php`
- Modify: `resources/views/home.blade.php:48-77` (strip section)
- Test: `tests/Feature/Public/HomeStripTest.php` (new)

**Interfaces:**
- Consumes: `SiteSetting::get('home_strip')` shape from Task 3; Portfolio media collections.
- Produces: view variable `$stripCards` — list of arrays `['video_url' => string, 'poster_url' => string, 'tag' => string, 'title' => string, 'meta' => string]`, only for portfolios that have gallery media. Also `$heroVideos` (consumed by Task 5): list of `['video_url' => string, 'poster_url' => string]`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Public/HomeStripTest.php`:

```php
<?php

use App\Models\Portfolio;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

function attachFilm(Portfolio $portfolio): void
{
    $portfolio->addMedia(UploadedFile::fake()->create('film.mp4', 100, 'video/mp4'))
        ->toMediaCollection('gallery');
    $portfolio->addMedia(UploadedFile::fake()->image('poster.jpg'))
        ->toMediaCollection('cover');
}

it('renders strip cards from settings using media urls', function () {
    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    attachFilm($portfolio);

    $this->get('/')
        ->assertOk()
        ->assertSee($portfolio->getFirstMediaUrl('gallery'), false)
        ->assertSee($portfolio->getFirstMediaUrl('cover'), false)
        ->assertSee('001 · Defence · 2026');
});

it('skips strip entries whose portfolio has no media', function () {
    // no media attached at all -> no strip cards, page still renders
    $this->get('/')
        ->assertOk()
        ->assertDontSee('data-strip-video');
});

it('skips strip entries whose slug does not resolve', function () {
    SiteSetting::set('home_strip', [
        ['portfolio_slug' => 'nope', 'tag' => 'X', 'title' => 'X', 'meta' => 'X'],
    ]);

    $this->get('/')->assertOk()->assertDontSee('data-strip-video');
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test tests/Feature/Public/HomeStripTest.php`
Expected: first test FAILS (media URL not in page — page still shows hardcoded `asset('videos/...')`); second/third FAIL on `assertDontSee('data-strip-video')`.

- [ ] **Step 3: Implement HomeController**

Replace `app/Http/Controllers/Public/HomeController.php` contents with:

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $stripSettings = collect(SiteSetting::get('home_strip', []));
        $heroSlugs = collect(SiteSetting::get('hero_videos', []));

        $portfolios = Portfolio::published()
            ->whereIn('slug', $stripSettings->pluck('portfolio_slug')->merge($heroSlugs)->unique())
            ->with('media')
            ->get()
            ->keyBy('slug');

        return view('home', [
            'services' => Service::orderBy('order')->with('media')->get(),
            'testimonials' => Testimonial::published()->orderBy('order')->get(),
            'stripCards' => $this->stripCards($stripSettings, $portfolios),
            'heroVideos' => $this->heroVideos($heroSlugs, $portfolios),
        ]);
    }

    /**
     * @param  Collection<int, array<string, string>>  $settings
     * @param  Collection<string, Portfolio>  $portfolios
     * @return list<array<string, string>>
     */
    protected function stripCards(Collection $settings, Collection $portfolios): array
    {
        return $settings
            ->map(function (array $entry) use ($portfolios): ?array {
                $portfolio = $portfolios->get($entry['portfolio_slug'] ?? '');
                $videoUrl = $portfolio?->getFirstMediaUrl('gallery');

                if (! $videoUrl) {
                    return null;
                }

                return [
                    'video_url' => $videoUrl,
                    'poster_url' => $portfolio->getFirstMediaUrl('cover'),
                    'tag' => $entry['tag'] ?? '',
                    'title' => $entry['title'] ?? '',
                    'meta' => $entry['meta'] ?? '',
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, string>  $slugs
     * @param  Collection<string, Portfolio>  $portfolios
     * @return list<array<string, string>>
     */
    protected function heroVideos(Collection $slugs, Collection $portfolios): array
    {
        return $slugs
            ->map(function (string $slug) use ($portfolios): ?array {
                $portfolio = $portfolios->get($slug);
                $videoUrl = $portfolio?->getFirstMediaUrl('gallery');

                if (! $videoUrl) {
                    return null;
                }

                return [
                    'video_url' => $videoUrl,
                    'poster_url' => $portfolio->getFirstMediaUrl('cover'),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
```

- [ ] **Step 4: Rewire the strip in home.blade.php**

In `resources/views/home.blade.php`, delete the whole `@php $stripCards = [...] @endphp` block (the hardcoded array) and change the loop to use controller data. The card markup stays identical except the two URL attributes and the removed `slug` usage:

```blade
@foreach ($stripCards as $i => $card)
    <article class="strip__card {{ $i === 0 ? 'is-on' : '' }}" data-i="{{ $i }}">
        <span class="strip__tag">{{ $card['tag'] }}</span>
        <video data-strip-video src="{{ $card['video_url'] }}"
               poster="{{ $card['poster_url'] }}"
               muted loop playsinline preload="none"></video>
        <button class="strip__mute is-muted" data-card-mute aria-label="Unmute this film" aria-pressed="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M11 5L6.5 9H3v6h3.5L11 19V5z"/>
                <path class="snd-on" d="M15.5 9a4.5 4.5 0 0 1 0 6M18 6.5a8 8 0 0 1 0 11"/>
                <path class="snd-off" d="M16 9.5l5 5M21 9.5l-5 5"/>
            </svg>
        </button>
        <div class="strip__body">
            <h3>{!! $card['title'] !!}</h3>
            <span>{{ $card['meta'] }}</span>
        </div>
    </article>
@endforeach
```

(`{!! $card['title'] !!}` keeps existing behavior; titles are editable only by Super-admins via SiteSettingsPage.)

- [ ] **Step 5: Run tests**

Run: `php artisan test tests/Feature/Public/HomeStripTest.php tests/Feature/Public/HomePageTest.php`
Expected: PASS.

- [ ] **Step 6: Full suite + static analysis**

Run: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse`
Expected: all green. Leave changes uncommitted.

---

### Task 5: Data-driven hero background

**Files:**
- Modify: `resources/views/home.blade.php:18` (pass prop)
- Modify: `resources/views/components/hero.blade.php:1-17` (tiles)
- Test: `tests/Feature/Public/HomeStripTest.php` (extend)

**Interfaces:**
- Consumes: `$heroVideos` from Task 4 — list of `['video_url' => string, 'poster_url' => string]`.
- Produces: `<x-hero :videos="...">` component prop `videos` (defaults to `[]`, so other future usages without the prop render no video tiles).

- [ ] **Step 1: Extend the test (failing first)**

Add to `tests/Feature/Public/HomeStripTest.php`:

```php
it('renders hero tiles from settings using media urls', function () {
    $portfolio = Portfolio::where('slug', 'salesforce-blr')->firstOrFail();
    attachFilm($portfolio);

    $response = $this->get('/')->assertOk();

    $response->assertSee($portfolio->getFirstMediaUrl('gallery'), false);
    expect(substr_count($response->getContent(), '<div class="tile">'))->toBe(2); // 1 video tile + the static img tile
});

it('hero renders without video tiles when no media exists', function () {
    $this->get('/')->assertOk()->assertDontSee('videos/ins-navy-blackdog.mp4');
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test tests/Feature/Public/HomeStripTest.php`
Expected: new tests FAIL (hero still hardcodes `asset('videos/...')`, 4 tiles always present).

- [ ] **Step 3: Pass the prop**

In `resources/views/home.blade.php` line 18 change `<x-hero />` to:

```blade
<x-hero :videos="$heroVideos" />
```

- [ ] **Step 4: Rewire hero tiles**

In `resources/views/components/hero.blade.php` replace the props line and the `hero__bg` block:

```blade
@props(['title' => null, 'subtitle' => null, 'videos' => []])
<section class="hero" data-screen-label="01 Hero">
    <div class="hero__bg">
      @foreach (array_slice($videos, 0, 1) as $v)
      <div class="tile">
        <video src="{{ $v['video_url'] }}" autoplay muted loop playsinline preload="metadata" poster="{{ $v['poster_url'] }}"></video>
      </div>
      @endforeach
      <div class="tile">
        <img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=1600&q=85" alt="" decoding="async">
      </div>
      @foreach (array_slice($videos, 1) as $v)
      <div class="tile">
        <video src="{{ $v['video_url'] }}" autoplay muted loop playsinline preload="metadata" poster="{{ $v['poster_url'] }}"></video>
      </div>
      @endforeach
    </div>
```

(The rest of the component — `hero__top` onward — is unchanged. Tile order preserved: video, img, video, video when three videos are configured.)

- [ ] **Step 5: Run tests**

Run: `php artisan test tests/Feature/Public/HomeStripTest.php tests/Feature/Public/ComponentsRenderTest.php`
Expected: PASS. If `ComponentsRenderTest` renders `<x-hero />` directly, the `[]` default keeps it green.

- [ ] **Step 6: Full suite + static analysis**

Run: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse`
Expected: all green. Leave changes uncommitted.

---

### Task 6: Portfolio show gallery from media + Filament video uploads

**Files:**
- Modify: `resources/views/portfolio/show.blade.php:99-116` (gallery section)
- Modify: `app/Filament/Resources/PortfolioResource.php:57-62` (gallery upload accepts video)
- Test: `tests/Feature/Public/PortfolioPageTest.php` (extend)

**Interfaces:**
- Consumes: Portfolio `gallery` media (mp4 or images), `cover` media URL as video poster.
- Produces: media-backed gallery rendering; admin can upload mp4 to `gallery`.

- [ ] **Step 1: Extend the test (failing first)**

Add to `tests/Feature/Public/PortfolioPageTest.php` (match the file's existing `uses`/`beforeEach`; add `config(['media-library.disk_name' => 's3']); Storage::fake('s3');` inside the new tests if the file's beforeEach doesn't do it):

```php
it('renders gallery videos from media with the cover as poster', function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');

    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    $portfolio->addMedia(UploadedFile::fake()->create('film.mp4', 100, 'video/mp4'))
        ->toMediaCollection('gallery');
    $portfolio->addMedia(UploadedFile::fake()->image('poster.jpg'))
        ->toMediaCollection('cover');

    $this->get('/portfolio/'.$portfolio->slug)
        ->assertOk()
        ->assertSee($portfolio->getFirstMediaUrl('gallery'), false)
        ->assertSee('poster="'.$portfolio->getFirstMediaUrl('cover').'"', false)
        ->assertDontSee('/videos/posters/', false);
});
```

Add the needed imports at the top if missing: `use Illuminate\Http\UploadedFile;` and `use Illuminate\Support\Facades\Storage;`.

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test tests/Feature/Public/PortfolioPageTest.php`
Expected: new test FAILS (gallery renders from `gallery_urls`, media ignored).

- [ ] **Step 3: Rewire the gallery section**

In `resources/views/portfolio/show.blade.php` replace the `{{-- GALLERY --}}` section with:

```blade
    {{-- GALLERY --}}
    <section class="gallery">
        @php
            $spans = ['g--6', 'g--12', 'g--4', 'g--8'];
            $galleryMedia = $item->getMedia('gallery');
        @endphp
        @if ($galleryMedia->isNotEmpty())
            @foreach ($galleryMedia as $i => $media)
                @if (str_starts_with((string) $media->mime_type, 'video/'))
                    <div class="g g--12 g--video reveal">
                        <video src="{{ $media->getUrl() }}" controls playsinline preload="metadata"
                               @if ($cover) poster="{{ $cover }}" @endif></video>
                    </div>
                @else
                    <div class="g {{ $spans[$i % count($spans)] }} reveal">
                        <img src="{{ $media->getUrl() }}" alt="" loading="lazy" decoding="async">
                    </div>
                @endif
            @endforeach
        @else
            @forelse ($item->gallery_urls ?? [] as $i => $src)
                @if (str_ends_with($src, '.mp4'))
                    <div class="g g--12 g--video reveal">
                        <video src="{{ $src }}" controls playsinline preload="metadata"
                               poster="{{ str_replace(['/videos/', '.mp4'], ['/videos/posters/', '.jpg'], $src) }}"></video>
                    </div>
                @else
                    <div class="g {{ $spans[$i % count($spans)] }} reveal">
                        <img src="{{ $src }}" alt="" loading="lazy" decoding="async">
                    </div>
                @endif
            @empty
                @if ($cover)
                    <div class="g g--12 reveal">
                        <img src="{{ $cover }}" alt="{{ $item->title }}" loading="lazy" decoding="async">
                    </div>
                @endif
            @endforelse
        @endif
    </section>
```

(Legacy `gallery_urls` branch kept verbatim as fallback for admin-entered URL rows; media branch wins whenever media exists.)

- [ ] **Step 4: Allow video uploads in Filament**

In `app/Filament/Resources/PortfolioResource.php` change the gallery upload field:

```php
SpatieMediaLibraryFileUpload::make('gallery')
    ->collection('gallery')
    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'video/mp4'])
    ->maxSize(153600) // 150 MB — largest current film is ~65 MB
    ->multiple()
    ->reorderable()
    ->columnSpanFull(),
```

(Replaces `->image()`, which blocked mp4 uploads.)

- [ ] **Step 5: Run tests**

Run: `php artisan test tests/Feature/Public/PortfolioPageTest.php`
Expected: PASS.

- [ ] **Step 6: Full suite + static analysis**

Run: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse`
Expected: all green. Leave changes uncommitted.

---

### Task 7: SiteSettingsPage — Homepage tab (strip + hero editors)

**Files:**
- Modify: `app/Filament/Pages/SiteSettingsPage.php` (mount, form schema, save)
- Test: `tests/Feature/Admin/SiteSettingsHomepageTest.php` (new; mirror auth setup from existing tests in `tests/Feature/Admin/`)

**Interfaces:**
- Consumes: setting shapes from Task 3 (`home_strip` entry keys: `portfolio_slug`, `tag`, `title`, `meta`; `hero_videos`: flat slug list).
- Produces: admin-editable settings with identical shapes (front-end from Tasks 4–5 reads them unchanged).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/SiteSettingsHomepageTest.php` (copy the acting-as-Super-admin setup from an existing test in `tests/Feature/Admin/` — e.g. how it seeds roles and logs in):

```php
<?php

use App\Filament\Pages\SiteSettingsPage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $admin = User::where('email', config('app.admin_seed_email'))->firstOrFail();
    $this->actingAs($admin);
});

it('loads existing homepage settings into the form', function () {
    Livewire::test(SiteSettingsPage::class)
        ->assertFormSet(function (array $state) {
            expect($state['home_strip'])->toHaveCount(6)
                ->and($state['hero_videos'])->toHaveCount(3);

            return true;
        });
});

it('saves strip and hero settings', function () {
    Livewire::test(SiteSettingsPage::class)
        ->fillForm([
            'home_strip' => [
                ['portfolio_slug' => 'range-rover', 'tag' => '001 · Auto · 2026', 'title' => 'Range <em>Rover.</em>', 'meta' => 'Reel'],
            ],
            'hero_videos' => ['range-rover'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(SiteSetting::get('home_strip'))->toHaveCount(1)
        ->and(SiteSetting::get('home_strip')[0]['portfolio_slug'])->toBe('range-rover')
        ->and(SiteSetting::get('hero_videos'))->toBe(['range-rover']);
});
```

Note: `fillForm`/`assertFormSet` come from Filament's Livewire testing helpers. If `Repeater` state includes UUID keys, normalize with `array_values()` in the assertion. If the page's `canAccess()` blocks the test user, assign the `Super-admin` role explicitly in `beforeEach`.

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test tests/Feature/Admin/SiteSettingsHomepageTest.php`
Expected: FAIL — form has no `home_strip` component.

- [ ] **Step 3: Implement the Homepage tab**

In `app/Filament/Pages/SiteSettingsPage.php`:

Add to `mount()` fill array:

```php
'home_strip' => SiteSetting::get('home_strip', []),
'hero_videos' => SiteSetting::get('hero_videos', []),
```

Add a tab to the `Tabs::make('settings')->tabs([...])` array:

```php
Forms\Components\Tabs\Tab::make('Homepage')
    ->schema([
        Forms\Components\Repeater::make('home_strip')
            ->label('Film strip')
            ->schema([
                Forms\Components\Select::make('portfolio_slug')
                    ->label('Portfolio')
                    ->options(fn () => \App\Models\Portfolio::published()->orderBy('title')->pluck('title', 'slug')->all())
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('tag')->required(),
                Forms\Components\TextInput::make('title')
                    ->helperText('HTML allowed, e.g. Indian <em>Navy.</em>')
                    ->required(),
                Forms\Components\TextInput::make('meta')->required(),
            ])
            ->columns(2)
            ->reorderable()
            ->defaultItems(0),
        Forms\Components\Repeater::make('hero_videos')
            ->label('Hero background films (in order)')
            ->simple(
                Forms\Components\Select::make('portfolio_slug')
                    ->label('Portfolio')
                    ->options(fn () => \App\Models\Portfolio::published()->orderBy('title')->pluck('title', 'slug')->all())
                    ->searchable()
                    ->required(),
            )
            ->reorderable()
            ->defaultItems(0),
    ]),
```

Add to `save()`:

```php
SiteSetting::set('home_strip', array_values($data['home_strip'] ?? []));
SiteSetting::set('hero_videos', array_values($data['hero_videos'] ?? []));
```

(`SiteSettingObserver` already clears the response cache on save — no extra cache work needed.)

- [ ] **Step 4: Run tests**

Run: `php artisan test tests/Feature/Admin/SiteSettingsHomepageTest.php`
Expected: PASS.

- [ ] **Step 5: Full suite + static analysis**

Run: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse`
Expected: all green. Leave changes uncommitted.

---

### Task 8: Deployment runbook + local verification

**Files:**
- Create: `docs/deploy/s3-cloudfront-media.md`
- No code changes.

**Interfaces:** none (documentation + manual verification).

- [ ] **Step 1: Write the runbook**

Create `docs/deploy/s3-cloudfront-media.md`:

```markdown
# S3 + CloudFront media — deployment runbook

## Prerequisites
- S3 bucket (private, Object Ownership: bucket owner enforced) with CloudFront
  distribution using Origin Access Control (OAC) pointed at the bucket.
- IAM user/role for the app with `s3:PutObject`, `s3:GetObject`, `s3:DeleteObject`,
  `s3:ListBucket` on the bucket.

## Env (production and local)
    FILESYSTEM_DISK=s3
    MEDIA_DISK=s3
    AWS_ACCESS_KEY_ID=...
    AWS_SECRET_ACCESS_KEY=...
    AWS_DEFAULT_REGION=...
    AWS_BUCKET=...
    AWS_URL=https://<cloudfront-domain>

## Deploy order
1. Deploy code; `composer install`.
2. `php artisan config:clear && php artisan db:seed` (adds home_strip / hero_videos
   settings; portfolio seeder no longer writes /videos paths).
3. `php artisan media:import-local` — uploads public/videos films + posters to S3
   and attaches them to portfolios; migrates any existing public-disk media rows.
   Idempotent; re-run until exit code 0.
4. Verify: homepage hero + strip and portfolio pages serve `https://<cloudfront-domain>/...`
   URLs; spot-check a video plays.
5. `php artisan responsecache:clear`.

## Cleanup (only after step 4 verified in production)
- Delete `public/videos/` from the server and from git (`git rm -r public/videos`)
  — removes 289 MB. Old `/videos/...` paths in the DB stay harmless: media-backed
  rendering wins everywhere media exists.

## Notes
- Media URLs are immutable (`Cache-Control: max-age=31536000, immutable`);
  medialibrary paths are unique per media id, so no CloudFront invalidation needed.
- Filament/Livewire temporary uploads stay on the local disk by design.
- Admin uploads (portfolio cover/gallery, service hero, post cover, industry hero)
  now land on S3 automatically.
```

- [ ] **Step 2: Local end-to-end verification (requires real AWS creds in .env)**

Run: `php artisan config:clear && php artisan db:seed && php artisan media:import-local`
Expected: "attached ..." lines for 9 portfolios (cover + gallery each), exit 0.

Then: `php artisan serve` and load `/` — hero + strip videos must load from the CloudFront domain (check network tab / page source for `AWS_URL` prefix). Load one portfolio page — gallery video from CloudFront with poster.

If creds are not available at implementation time, skip this step, note it in the final report, and rely on the fake-disk test coverage.

- [ ] **Step 3: Final full check**

Run: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse`
Expected: all green. Leave all changes uncommitted (user reviews in VSCode changes panel).
