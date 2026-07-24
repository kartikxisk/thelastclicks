# Industry Media + Shared Media Items Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Generalize the Work media system into one polymorphic `media_items` table shared by Work and Industry, give Industry an admin-managed media array, and render industries as cards on `/industries` that open the existing lightbox.

**Architecture:** `work_media` becomes `media_items` with `mediable_type`/`mediable_id`; `WorkMedia` becomes `MediaItem`. A `HasMediaItems` trait carries `mediaItems()`, `coverUrl()` and `mediaPayload()` for both models, with the cover collection and a legacy fallback overridable per model. The Filament relation manager and the grid component become shared.

**Tech Stack:** Laravel 11, Filament v3, spatie/laravel-medialibrary v11 (S3/CloudFront), Pest.

**Spec:** `docs/superpowers/specs/2026-07-23-industry-media-design.md`

## Global Constraints

- **Do NOT git commit.** Leave all changes uncommitted; commit only on explicit ask.
- Media via medialibrary only — no ACLs, no hand-built media URLs (`getFirstMediaUrl()`).
- YouTube: embeds `https://www.youtube-nocookie.com/embed/{id}`, thumbs `https://img.youtube.com/vi/{id}/hqdefault.jpg`.
- **Never seed media rows.** Industries display their existing seeded cover/summary until an admin adds media.
- Media-touching tests set `config(['media-library.disk_name' => 's3'])` + `Storage::fake('s3')` (phpunit pins `MEDIA_DISK=public`).
- Existing Work tests are the regression guard for the refactor — they must keep passing (updated only for renamed classes).
- **Keep the existing `data-work-tile` / `data-work-media` / `data-work-lightbox` attribute names and the `.work-grid` / `.work-tile` / `.wlb` CSS classes.** Only PHP/Blade names change. This keeps `work-lightbox.js` and its CSS untouched (that CSS recently had a class-collision bug — don't reopen it).
- Run the suite with `php -d memory_limit=2G vendor/bin/pest`. After every task: suite green + `vendor/bin/pint --dirty` + `vendor/bin/phpstan analyse --memory-limit=512M`. Rebuild with `npm run build` when CSS/JS/Blade changes.

---

### Task 1: Polymorphic `media_items` + `HasMediaItems` trait

**Files:**
- Create: `database/migrations/2026_07_23_000002_create_media_items_table.php`
- Create: `app/Models/MediaItem.php`; Delete: `app/Models/WorkMedia.php`
- Create: `app/Models/Concerns/HasMediaItems.php`
- Modify: `app/Models/Work.php` (use the trait, delete the now-duplicated methods)
- Create: `app/Observers/MediaItemObserver.php`; Delete: `app/Observers/WorkMediaObserver.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `tests/Feature/Models/WorkTest.php`, `tests/Feature/Public/WorksPageTest.php`, `tests/Feature/Admin/WorkResourceTest.php` (rename `WorkMedia` → `MediaItem`)

**Interfaces (Tasks 2–3 depend on these):**
- `App\Models\MediaItem` — table `media_items`; fillable `mediable_type, mediable_id, type, youtube_url, caption, order`; `file` singleFile collection; `mediable(): MorphTo`; `youtubeId()`, `embedUrl()`, `thumbnailUrl()`, `resolvedUrl()` unchanged.
- `App\Models\Concerns\HasMediaItems` — `mediaItems(): MorphMany` (ordered `order`,`id`); `coverUrl(): ?string`; `mediaPayload(): list<array{type,url,caption}>`; overridable `protected function mediaCoverCollection(): string` (default `'cover'`) and `protected function mediaCoverFallback(): ?string` (default `null`).

- [ ] **Step 1: Write the failing migration test**

Create `tests/Feature/Models/MediaItemTest.php`:

```php
<?php

use App\Models\MediaItem;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
});

it('stores media items polymorphically', function () {
    expect(Schema::hasTable('media_items'))->toBeTrue()
        ->and(Schema::hasColumns('media_items', ['mediable_type', 'mediable_id', 'type', 'youtube_url', 'caption', 'order']))->toBeTrue()
        ->and(Schema::hasTable('work_media'))->toBeFalse();
});

it('attaches media items to a work through the shared relation', function () {
    $work = Work::create(['title' => 'Poly Work']);
    $work->mediaItems()->create(['type' => 'youtube', 'order' => 1, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);

    $item = MediaItem::firstOrFail();

    expect($item->mediable_type)->toBe(Work::class)
        ->and($item->mediable_id)->toBe($work->id)
        ->and($item->mediable->is($work))->toBeTrue()
        ->and($work->fresh()->mediaPayload())->toHaveCount(1);
});

it('keeps medialibrary files attached across the rename', function () {
    $work = Work::create(['title' => 'With File']);
    $item = $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $item->addMedia(UploadedFile::fake()->image('a.jpg'))->toMediaCollection('file');

    expect($item->fresh()->getFirstMediaUrl('file'))->not->toBe('')
        ->and($work->fresh()->coverUrl())->toBe($item->fresh()->getFirstMediaUrl('file'));
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Models/MediaItemTest.php`
Expected: FAIL — `Class "App\Models\MediaItem" not found`.

- [ ] **Step 3: Migration**

Create `database/migrations/2026_07_23_000002_create_media_items_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->string('type')->default('image');
            $table->string('youtube_url')->nullable();
            $table->string('caption')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->index(['mediable_type', 'mediable_id', 'order']);
        });

        // Carry over any existing rows from the Work-only table.
        if (Schema::hasTable('work_media')) {
            DB::table('work_media')->orderBy('id')->each(function (object $row) {
                DB::table('media_items')->insert([
                    'id' => $row->id,
                    'mediable_type' => \App\Models\Work::class,
                    'mediable_id' => $row->work_id,
                    'type' => $row->type,
                    'youtube_url' => $row->youtube_url,
                    'caption' => $row->caption,
                    'order' => $row->order,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            });

            Schema::drop('work_media');
        }

        // medialibrary rows point at the old model class — repoint them, or every
        // uploaded file silently detaches from its (renamed) owner.
        DB::table('media')
            ->where('model_type', 'App\\Models\\WorkMedia')
            ->update(['model_type' => 'App\\Models\\MediaItem']);
    }

    public function down(): void
    {
        DB::table('media')
            ->where('model_type', 'App\\Models\\MediaItem')
            ->update(['model_type' => 'App\\Models\\WorkMedia']);

        Schema::create('work_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('image');
            $table->string('youtube_url')->nullable();
            $table->string('caption')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        DB::table('media_items')->where('mediable_type', \App\Models\Work::class)->orderBy('id')
            ->each(function (object $row) {
                DB::table('work_media')->insert([
                    'id' => $row->id,
                    'work_id' => $row->mediable_id,
                    'type' => $row->type,
                    'youtube_url' => $row->youtube_url,
                    'caption' => $row->caption,
                    'order' => $row->order,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            });

        Schema::dropIfExists('media_items');
    }
};
```

- [ ] **Step 4: MediaItem model**

Create `app/Models/MediaItem.php` — same body as the current `app/Models/WorkMedia.php`, with these changes: class name `MediaItem`, `protected $table = 'media_items';`, fillable `['mediable_type', 'mediable_id', 'type', 'youtube_url', 'caption', 'order']`, and the `work()` relation replaced by:

```php
    /** @return MorphTo<Model, $this> */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
```

(import `Illuminate\Database\Eloquent\Relations\MorphTo` and `Illuminate\Database\Eloquent\Model`; drop the `BelongsTo` import). Keep `registerMediaCollections()`, `youtubeId()`, `embedUrl()`, `thumbnailUrl()`, `resolvedUrl()` byte-identical.

Then delete `app/Models/WorkMedia.php`.

- [ ] **Step 5: HasMediaItems trait**

Create `app/Models/Concerns/HasMediaItems.php`:

```php
<?php

namespace App\Models\Concerns;

use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Shared media-array behaviour: an ordered list of mixed image / video /
 * YouTube rows, plus the cover and lightbox payload derived from them.
 */
trait HasMediaItems
{
    /** @return MorphMany<MediaItem, $this> */
    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable')->orderBy('order')->orderBy('id');
    }

    /** Collection holding this model's explicit cover image. */
    protected function mediaCoverCollection(): string
    {
        return 'cover';
    }

    /** Last-resort cover when the model has no media at all. */
    protected function mediaCoverFallback(): ?string
    {
        return null;
    }

    /** Grid thumbnail: explicit cover, else first image row, else first YouTube thumbnail. */
    public function coverUrl(): ?string
    {
        if ($url = $this->getFirstMediaUrl($this->mediaCoverCollection())) {
            return $url;
        }

        foreach ($this->mediaItems as $item) {
            if ($item->type === 'image' && ($url = $item->getFirstMediaUrl('file'))) {
                return $url;
            }
        }

        foreach ($this->mediaItems as $item) {
            if ($item->type === 'youtube' && ($thumb = $item->thumbnailUrl())) {
                return $thumb;
            }
        }

        return $this->mediaCoverFallback();
    }

    /**
     * Ordered, render-ready media for the lightbox. Rows without a usable file
     * or a parseable YouTube URL are dropped so the front end never gets holes.
     *
     * @return list<array{type: string, url: string, caption: string|null}>
     */
    public function mediaPayload(): array
    {
        $out = [];

        foreach ($this->mediaItems as $item) {
            $url = $item->resolvedUrl();

            if (! $url) {
                continue;
            }

            $out[] = ['type' => $item->type, 'url' => $url, 'caption' => $item->caption];
        }

        return $out;
    }
}
```

- [ ] **Step 6: Work uses the trait**

In `app/Models/Work.php`: add `use App\Models\Concerns\HasMediaItems;`, change the `use` statement inside the class to `use HasMediaItems, HasSlug, InteractsWithMedia;`, and **delete** the now-duplicated `mediaItems()`, `coverUrl()` and `mediaPayload()` methods. Remove the now-unused `HasMany` import. Keep `scopePublished`, `getSlugOptions`, `registerMediaCollections`, `$fillable`, `$casts` unchanged.

- [ ] **Step 7: Observer rename**

Create `app/Observers/MediaItemObserver.php` with the same body as the current `app/Observers/WorkMediaObserver.php`, typed against `App\Models\MediaItem`. Delete `app/Observers/WorkMediaObserver.php`.

In `app/Providers/AppServiceProvider.php` replace the `WorkMedia::observe(WorkMediaObserver::class);` line with `MediaItem::observe(MediaItemObserver::class);` and update the two imports accordingly (alphabetical grouping).

- [ ] **Step 8: Update existing tests for the rename**

In `tests/Feature/Models/WorkTest.php`, `tests/Feature/Public/WorksPageTest.php` and `tests/Feature/Admin/WorkResourceTest.php`, replace `use App\Models\WorkMedia;` with `use App\Models\MediaItem;` and every `WorkMedia::` / `new WorkMedia(` with `MediaItem::` / `new MediaItem(`. Do not change any assertion values — these tests are the regression guard.

- [ ] **Step 9: Run tests, then full checks**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Models/MediaItemTest.php tests/Feature/Models/WorkTest.php`
Expected: all passing.
Then: `php -d memory_limit=2G vendor/bin/pest && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: green. Leave uncommitted.

---

### Task 2: Industry gains the media array + shared relation manager

**Files:**
- Modify: `app/Models/Industry.php`
- Modify: `app/Observers/IndustryObserver.php`
- Create: `app/Filament/RelationManagers/MediaItemsRelationManager.php`; Delete: `app/Filament/Resources/WorkResource/RelationManagers/MediaItemsRelationManager.php`
- Modify: `app/Filament/Resources/WorkResource.php` (point at the new class)
- Modify: `app/Filament/Resources/IndustryResource.php` (register the relation manager)
- Test: `tests/Feature/Models/IndustryMediaTest.php` (new), `tests/Feature/Admin/IndustryResourceTest.php` (extend)

**Interfaces:**
- Consumes: `HasMediaItems`, `MediaItem` from Task 1.
- Produces: `Industry` with `mediaItems()`, `coverUrl()` (hero → first image → YouTube thumb → legacy `image_url`), `mediaPayload()`. Task 3 renders these.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/Models/IndustryMediaTest.php`:

```php
<?php

use App\Models\Industry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

it('builds an ordered media payload for an industry', function () {
    $industry = Industry::firstOrFail();

    $img = $industry->mediaItems()->create(['type' => 'image', 'order' => 1, 'caption' => 'On set']);
    $img->addMedia(UploadedFile::fake()->image('set.jpg'))->toMediaCollection('file');
    $industry->mediaItems()->create(['type' => 'youtube', 'order' => 2, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    $industry->mediaItems()->create(['type' => 'video', 'order' => 3]); // no file -> skipped

    $payload = $industry->fresh()->mediaPayload();

    expect($payload)->toHaveCount(2)
        ->and($payload[0]['caption'])->toBe('On set')
        ->and($payload[1]['url'])->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
});

it('falls back through hero media, first image, youtube thumb, then legacy image_url', function () {
    $industry = Industry::firstOrFail();

    // seeded industries carry a legacy image_url and no media
    expect($industry->coverUrl())->toBe($industry->image_url);

    $yt = $industry->mediaItems()->create(['type' => 'youtube', 'order' => 2, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    expect($industry->fresh()->coverUrl())->toBe('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg');

    $img = $industry->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $img->addMedia(UploadedFile::fake()->image('first.jpg'))->toMediaCollection('file');
    expect($industry->fresh()->coverUrl())->toBe($img->fresh()->getFirstMediaUrl('file'));

    $industry->addMedia(UploadedFile::fake()->image('hero.jpg'))->toMediaCollection('hero');
    expect($industry->fresh()->coverUrl())->toBe($industry->fresh()->getFirstMediaUrl('hero'));
});

it('removes an industry media rows and their files when the industry is deleted', function () {
    $industry = Industry::firstOrFail();
    $item = $industry->mediaItems()->create(['type' => 'image']);
    $item->addMedia(UploadedFile::fake()->image('gone.jpg'))->toMediaCollection('file');

    $industry->delete();

    expect(\App\Models\MediaItem::count())->toBe(0)
        ->and(\Spatie\MediaLibrary\MediaCollections\Models\Media::count())->toBe(0);
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Models/IndustryMediaTest.php`
Expected: FAIL — `Call to undefined method App\Models\Industry::mediaItems()`.

- [ ] **Step 3: Industry model**

In `app/Models/Industry.php`: add `use App\Models\Concerns\HasMediaItems;`, add `HasMediaItems` to the class's `use` list, and add these two overrides (the industry's cover lives in `hero`, and seeded rows only have the legacy URL column):

```php
    protected function mediaCoverCollection(): string
    {
        return 'hero';
    }

    protected function mediaCoverFallback(): ?string
    {
        return $this->image_url ?: null;
    }
```

- [ ] **Step 4: IndustryObserver cascade**

In `app/Observers/IndustryObserver.php` add the same Eloquent-cascade hook Work uses, so medialibrary cleanup runs for child rows:

```php
    /**
     * Delete child media rows through Eloquent so medialibrary's own cleanup
     * runs and no files are orphaned on S3.
     */
    public function deleting(Industry $i): void
    {
        $i->mediaItems->each->delete();
    }
```

- [ ] **Step 5: Move the relation manager**

Create `app/Filament/RelationManagers/MediaItemsRelationManager.php` with the exact body of the current `app/Filament/Resources/WorkResource/RelationManagers/MediaItemsRelationManager.php`, changing only the namespace to `App\Filament\RelationManagers`. Delete the old file.

In `app/Filament/Resources/WorkResource.php` change the import to `use App\Filament\RelationManagers\MediaItemsRelationManager;` (the `getRelations()` body stays `[MediaItemsRelationManager::class]`).

- [ ] **Step 6: Register on IndustryResource**

In `app/Filament/Resources/IndustryResource.php` add `use App\Filament\RelationManagers\MediaItemsRelationManager;` and replace the existing `getRelations()` method body with:

```php
    public static function getRelations(): array
    {
        return [MediaItemsRelationManager::class];
    }
```

- [ ] **Step 7: Extend the admin test**

Add to `tests/Feature/Admin/IndustryResourceTest.php` (match the file's existing seed/actingAs setup):

```php
it('stores mixed media rows against an industry in order', function () {
    $industry = \App\Models\Industry::firstOrFail();

    $industry->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $industry->mediaItems()->create(['type' => 'youtube', 'order' => 2, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    $industry->mediaItems()->create(['type' => 'video', 'order' => 3]);

    expect($industry->fresh()->mediaItems->pluck('type')->all())
        ->toBe(['image', 'youtube', 'video']);
});
```

- [ ] **Step 8: Run tests, then full checks**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Models/IndustryMediaTest.php tests/Feature/Admin/IndustryResourceTest.php`
Expected: all passing.
Then: `php -d memory_limit=2G vendor/bin/pest && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: green. Leave uncommitted.

---

### Task 3: Shared grid component + industry cards on `/industries`

**Files:**
- Create: `resources/views/components/media-grid.blade.php`; Delete: `resources/views/components/work-grid.blade.php`
- Modify: `resources/views/home.blade.php:143`, `resources/views/works/index.blade.php:16`
- Modify: `app/Http/Controllers/Public/IndustryController.php`
- Modify: `resources/views/industries/index.blade.php`
- Test: `tests/Feature/Public/IndustryPageTest.php` (extend)

**Interfaces:**
- Consumes: `coverUrl()`, `mediaPayload()` (Tasks 1–2).
- Produces: `<x-media-grid :items="$collection" :meta="?callable" />`. Markup contract is unchanged from the old component — same `.work-grid` / `.work-tile` classes and same `data-work-grid` / `data-work-tile` / `data-work-media` / `data-work-lightbox` hooks, so `work-lightbox.js` and its CSS need no edits.

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/Public/IndustryPageTest.php` (match its existing setup; add `config(['media-library.disk_name' => 's3']); Storage::fake('s3');` inside the media test, plus imports for `Illuminate\Http\UploadedFile` and `Illuminate\Support\Facades\Storage`):

```php
it('renders each seeded industry with its title and summary', function () {
    $industry = \App\Models\Industry::orderBy('order')->firstOrFail();

    $this->get('/industries')
        ->assertOk()
        ->assertSee($industry->title)
        ->assertSee($industry->summary);
});

it('marks an industry with media as an interactive tile and one without as plain', function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');

    $industry = \App\Models\Industry::orderBy('order')->firstOrFail();
    $item = $industry->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $item->addMedia(UploadedFile::fake()->image('a.jpg'))->toMediaCollection('file');

    $response = $this->get('/industries')->assertOk();

    $response->assertSee('data-work-tile', false)
        ->assertSee($item->fresh()->getFirstMediaUrl('file'), false);
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Public/IndustryPageTest.php`
Expected: FAIL — the industries page renders no industry data.

- [ ] **Step 3: Create the shared component**

Create `resources/views/components/media-grid.blade.php` — the current `work-grid.blade.php` with the prop renamed and a pluggable meta line:

```blade
@props(['items', 'meta' => null])

<div class="work-grid" data-work-grid>
    @foreach ($items as $item)
        @php
            $cover = $item->coverUrl();
            $payload = $item->mediaPayload();
            $metaText = $meta
                ? $meta($item)
                : collect([$item->client ?? null, $item->year ?? null])->filter()->implode(' · ');
        @endphp
        <{{ $payload ? 'button' : 'div' }}
            class="work-tile reveal"
            data-delay="{{ $loop->index % 4 }}"
            @if ($payload)
                type="button"
                data-work-tile
                data-work-media='@json($payload)'
                aria-label="View {{ $item->title }}"
            @endif
        >
            @if ($cover)
                <img src="{{ $cover }}" alt="{{ $item->title }}" loading="lazy" decoding="async">
            @endif
            <span class="work-tile__scrim" aria-hidden="true"></span>
            <span class="work-tile__body">
                <span class="work-tile__title">{{ $item->title }}</span>
                @if ($metaText)
                    <span class="work-tile__meta">{{ $metaText }}</span>
                @endif
            </span>
        </{{ $payload ? 'button' : 'div' }}>
    @endforeach
</div>

@once
<div class="wlb" data-work-lightbox hidden role="dialog" aria-modal="true" aria-label="Work media">
    <button class="wlb__close" data-wlb-close aria-label="Close">&times;</button>
    <button class="wlb__nav wlb__nav--prev" data-wlb-prev aria-label="Previous">&#8249;</button>
    <div class="wlb__stage" data-wlb-stage></div>
    <button class="wlb__nav wlb__nav--next" data-wlb-next aria-label="Next">&#8250;</button>
    <p class="wlb__caption" data-wlb-caption></p>
</div>
@endonce
```

Delete `resources/views/components/work-grid.blade.php`.

- [ ] **Step 4: Update the two existing usages**

`resources/views/home.blade.php` line 143: `<x-work-grid :works="$featuredWorks" />` → `<x-media-grid :items="$featuredWorks" />`
`resources/views/works/index.blade.php` line 16: `<x-work-grid :works="$works" />` → `<x-media-grid :items="$works" />`

- [ ] **Step 5: Controller eager-loads media**

Replace the `index()` body in `app/Http/Controllers/Public/IndustryController.php`:

```php
    public function index(): View
    {
        return view('industries.index', [
            'industries' => Industry::orderBy('order')
                ->with(['media', 'mediaItems.media'])
                ->get(),
        ]);
    }
```

- [ ] **Step 6: Render industries on the page**

In `resources/views/industries/index.blade.php`, insert this section immediately after the "WHO WE WORK WITH" section (before the marquee), leaving the existing clients-grid, marquee and CTA sections untouched:

```blade
    {{-- INDUSTRIES --}}
    @if ($industries->isNotEmpty())
        <section class="section" data-screen-label="02 Industries">
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow">Industries</span>
                        <h2 class="section__title" data-split>Sectors we <em>shoot.</em></h2>
                    </div>
                </div>
                <x-media-grid :items="$industries" :meta="fn ($industry) => $industry->summary" />
            </x-container>
        </section>
    @endif
```

- [ ] **Step 7: Run tests, build, full checks**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Public/IndustryPageTest.php tests/Feature/Public/WorksPageTest.php tests/Feature/Public/HomeWorkSectionTest.php`
Expected: all passing.
Then: `npm run build && php -d memory_limit=2G vendor/bin/pest && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: green. Leave uncommitted.
