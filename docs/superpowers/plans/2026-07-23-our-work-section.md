# "Our Work" Section Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** An admin-managed "Our Work" showcase — items holding any mix of uploaded images, uploaded videos and YouTube embeds — shown as a small featured section on the homepage and a full masonry `/our-works` page, with items opening in a JS lightbox.

**Architecture:** Two tables: `works` (title/slug/meta/flags + a `cover` media collection) and `work_media` (child rows typed `image|video|youtube`, each image/video row carrying one uploaded file via medialibrary, YouTube rows carrying a URL). One child table means the three media kinds interleave in a single drag-sortable order. The Work model exposes `coverUrl()` and `mediaPayload()` so views and the lightbox share one resolved, already-filtered data shape.

**Tech Stack:** Laravel 11, Filament v3 (Resource + Relation Manager), spatie/laravel-medialibrary v11 on the S3/CloudFront disk, spatie/laravel-sluggable, Pest, vanilla JS.

**Spec:** `docs/superpowers/specs/2026-07-23-our-work-section-design.md`

## Global Constraints

- **Do NOT git commit.** User rule: leave all changes uncommitted; commit only on explicit ask. Every "Commit" step is replaced by "leave uncommitted".
- Media uploads go through medialibrary on the configured disk (S3 behind CloudFront). Never set ACLs, never hand-build media URLs — use `getFirstMediaUrl()`.
- Media-touching tests must set `config(['media-library.disk_name' => 's3'])` + `Storage::fake('s3')` (phpunit pins `MEDIA_DISK=public`).
- Never fabricate showcase content — no seeded works. The section/page simply render nothing when no published works exist.
- YouTube embeds use `https://www.youtube-nocookie.com/embed/{id}`; thumbnails use `https://img.youtube.com/vi/{id}/hqdefault.jpg`.
- Hover effects only under `@media (hover: hover)`; scroll animation reuses the site's existing `reveal` class (its observer already honours `prefers-reduced-motion`).
- Run the suite with `php -d memory_limit=2G vendor/bin/pest` (it OOMs at the default 128M). After every task: suite green + `vendor/bin/pint --dirty` + `vendor/bin/phpstan analyse --memory-limit=512M`.
- Rebuild assets with `npm run build` in any task touching CSS/JS.

---

### Task 1: Data layer — migrations, models, observer

**Files:**
- Create: `database/migrations/2026_07_23_000001_create_works_tables.php`
- Create: `app/Models/Work.php`, `app/Models/WorkMedia.php`
- Create: `app/Observers/WorkObserver.php`
- Modify: `app/Providers/AppServiceProvider.php` (register observer)
- Test: `tests/Feature/Models/WorkTest.php`

**Interfaces (later tasks depend on these exact names):**
- `Work::scopePublished(Builder $q)` — `where('is_published', true)`
- `Work::mediaItems(): HasMany<WorkMedia>` — ordered by `order` then `id`
- `Work::coverUrl(): ?string` — `cover` media → first image row's file → first YouTube row's thumbnail → `null`
- `Work::mediaPayload(): array` — `list<array{type:string,url:string,caption:?string}>`, in order, skipping rows with no resolvable URL
- `WorkMedia::youtubeId(): ?string`, `WorkMedia::embedUrl(): ?string`, `WorkMedia::thumbnailUrl(): ?string`, `WorkMedia::resolvedUrl(): ?string`
- Work media collection: `cover` (singleFile). WorkMedia collection: `file` (singleFile).

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/Models/WorkTest.php`:

```php
<?php

use App\Models\Work;
use App\Models\WorkMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
});

it('generates a slug and scopes published works', function () {
    $published = Work::create(['title' => 'Navy Film', 'is_published' => true]);
    Work::create(['title' => 'Hidden One', 'is_published' => false]);

    expect($published->slug)->toBe('navy-film')
        ->and(Work::published()->pluck('title')->all())->toBe(['Navy Film']);
});

it('extracts youtube ids from every common url form', function (string $url) {
    $m = new WorkMedia(['type' => 'youtube', 'youtube_url' => $url]);

    expect($m->youtubeId())->toBe('dQw4w9WgXcQ')
        ->and($m->embedUrl())->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->and($m->thumbnailUrl())->toBe('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg');
})->with([
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'https://www.youtube.com/watch?list=xyz&v=dQw4w9WgXcQ',
    'https://youtu.be/dQw4w9WgXcQ',
    'https://www.youtube.com/embed/dQw4w9WgXcQ',
    'https://www.youtube.com/shorts/dQw4w9WgXcQ',
]);

it('returns null for an unparseable youtube url', function () {
    $m = new WorkMedia(['type' => 'youtube', 'youtube_url' => 'https://example.com/nope']);

    expect($m->youtubeId())->toBeNull()
        ->and($m->embedUrl())->toBeNull();
});

it('builds an ordered media payload and skips unresolvable rows', function () {
    $work = Work::create(['title' => 'Mixed Reel']);

    $img = $work->mediaItems()->create(['type' => 'image', 'order' => 1, 'caption' => 'Still']);
    $img->addMedia(UploadedFile::fake()->image('shot.jpg'))->toMediaCollection('file');

    $work->mediaItems()->create(['type' => 'youtube', 'order' => 2, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    // no file attached -> skipped
    $work->mediaItems()->create(['type' => 'video', 'order' => 3]);
    // unparseable -> skipped
    $work->mediaItems()->create(['type' => 'youtube', 'order' => 4, 'youtube_url' => 'nope']);

    $payload = $work->fresh()->mediaPayload();

    expect($payload)->toHaveCount(2)
        ->and($payload[0]['type'])->toBe('image')
        ->and($payload[0]['caption'])->toBe('Still')
        ->and($payload[1]['type'])->toBe('youtube')
        ->and($payload[1]['url'])->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
});

it('falls back through cover, first image, then youtube thumbnail', function () {
    $work = Work::create(['title' => 'Fallbacks']);
    expect($work->coverUrl())->toBeNull();

    $yt = $work->mediaItems()->create(['type' => 'youtube', 'order' => 2, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    expect($work->fresh()->coverUrl())->toBe('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg');

    $img = $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $img->addMedia(UploadedFile::fake()->image('first.jpg'))->toMediaCollection('file');
    expect($work->fresh()->coverUrl())->toBe($img->fresh()->getFirstMediaUrl('file'));

    $work->addMedia(UploadedFile::fake()->image('cover.jpg'))->toMediaCollection('cover');
    expect($work->fresh()->coverUrl())->toBe($work->fresh()->getFirstMediaUrl('cover'));
});

it('deletes child media rows with the work', function () {
    $work = Work::create(['title' => 'Cascade']);
    $work->mediaItems()->create(['type' => 'image']);

    $work->delete();

    expect(WorkMedia::count())->toBe(0);
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Models/WorkTest.php`
Expected: FAIL — `Class "App\Models\Work" not found`.

- [ ] **Step 3: Migration**

Create `database/migrations/2026_07_23_000001_create_works_tables.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->string('client')->nullable();
            $table->string('year')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('work_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('image');
            $table->string('youtube_url')->nullable();
            $table->string('caption')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_media');
        Schema::dropIfExists('works');
    }
};
```

- [ ] **Step 4: WorkMedia model**

Create `app/Models/WorkMedia.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class WorkMedia extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'work_media';

    protected $fillable = ['work_id', 'type', 'youtube_url', 'caption', 'order'];

    /** @return BelongsTo<Work, $this> */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->singleFile();
    }

    /** The 11-character YouTube id, or null when the URL is absent/unparseable. */
    public function youtubeId(): ?string
    {
        if (! $this->youtube_url) {
            return null;
        }

        preg_match(
            '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~',
            $this->youtube_url,
            $m
        );

        return $m[1] ?? null;
    }

    public function embedUrl(): ?string
    {
        $id = $this->youtubeId();

        return $id ? "https://www.youtube-nocookie.com/embed/{$id}" : null;
    }

    public function thumbnailUrl(): ?string
    {
        $id = $this->youtubeId();

        return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : null;
    }

    /** Playable/displayable URL for this row, or null when nothing usable is attached. */
    public function resolvedUrl(): ?string
    {
        if ($this->type === 'youtube') {
            return $this->embedUrl();
        }

        return $this->getFirstMediaUrl('file') ?: null;
    }
}
```

- [ ] **Step 5: Work model**

Create `app/Models/Work.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Work extends Model implements HasMedia
{
    use HasSlug, InteractsWithMedia;

    protected $fillable = [
        'title', 'slug', 'summary', 'client', 'year', 'order', 'is_published', 'is_featured',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    /** @return HasMany<WorkMedia, $this> */
    public function mediaItems(): HasMany
    {
        return $this->hasMany(WorkMedia::class)->orderBy('order')->orderBy('id');
    }

    /** @param Builder<Work> $q
     * @return Builder<Work>
     */
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    /** Grid thumbnail: explicit cover, else first image row, else first YouTube thumbnail. */
    public function coverUrl(): ?string
    {
        if ($url = $this->getFirstMediaUrl('cover')) {
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

        return null;
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

- [ ] **Step 6: Observer + registration**

Create `app/Observers/WorkObserver.php`:

```php
<?php

namespace App\Observers;

use App\Models\Work;
use Spatie\ResponseCache\Facades\ResponseCache;

class WorkObserver
{
    public function saved(Work $w): void
    {
        ResponseCache::clear();
    }

    public function deleted(Work $w): void
    {
        ResponseCache::clear();
    }
}
```

In `app/Providers/AppServiceProvider.php` add the imports `use App\Models\Work;` and `use App\Observers\WorkObserver;` (keeping the file's alphabetical grouping) and add to `boot()` next to the other observers:

```php
Work::observe(WorkObserver::class);
```

- [ ] **Step 7: Run tests, then full checks**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Models/WorkTest.php`
Expected: all passing.
Then: `php -d memory_limit=2G vendor/bin/pest && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: green. Leave uncommitted.

---

### Task 2: Filament admin — WorkResource + media relation manager

**Files:**
- Create: `app/Filament/Resources/WorkResource.php`
- Create: `app/Filament/Resources/WorkResource/Pages/ListWorks.php`, `CreateWork.php`, `EditWork.php`
- Create: `app/Filament/Resources/WorkResource/RelationManagers/MediaItemsRelationManager.php`
- Modify: `database/seeders/PermissionsSeeder.php:39` (add `work` to the editor resource list)
- Test: `tests/Feature/Admin/WorkResourceTest.php`

**Interfaces:**
- Consumes: `Work`, `WorkMedia`, `Work::mediaItems()` from Task 1.
- Produces: admin CRUD. Later tasks don't depend on it.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/Admin/WorkResourceTest.php`:

```php
<?php

use App\Filament\Resources\WorkResource\Pages\CreateWork;
use App\Filament\Resources\WorkResource\Pages\ListWorks;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->actingAs(User::where('email', config('app.admin_seed_email'))->firstOrFail());
});

it('Super-admin can list works', function () {
    Work::create(['title' => 'Listed Work']);

    Livewire::test(ListWorks::class)->assertCanSeeTableRecords(Work::all());
});

it('Super-admin can create a work', function () {
    Livewire::test(CreateWork::class)
        ->fillForm([
            'title' => 'Navy Film',
            'slug' => 'navy-film',
            'client' => 'Indian Navy',
            'year' => '2026',
            'is_published' => true,
            'is_featured' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $work = Work::where('slug', 'navy-film')->firstOrFail();
    expect($work->client)->toBe('Indian Navy')
        ->and($work->is_featured)->toBeTrue();
});

it('stores mixed media rows against a work in order', function () {
    $work = Work::create(['title' => 'Mixed']);

    $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $work->mediaItems()->create(['type' => 'youtube', 'order' => 2, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    $work->mediaItems()->create(['type' => 'video', 'order' => 3]);

    expect($work->fresh()->mediaItems->pluck('type')->all())
        ->toBe(['image', 'youtube', 'video']);
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Admin/WorkResourceTest.php`
Expected: FAIL — `Class "App\Filament\Resources\WorkResource\Pages\CreateWork" not found`.

- [ ] **Step 3: WorkResource**

Create `app/Filament/Resources/WorkResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkResource\Pages;
use App\Filament\Resources\WorkResource\RelationManagers\MediaItemsRelationManager;
use App\Models\Work;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WorkResource extends Resource
{
    protected static ?string $model = Work::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Our Work';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('title')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                TextInput::make('client'),
                TextInput::make('year'),
                TextInput::make('order')->numeric()->default(0),
            ]),
            Textarea::make('summary')->rows(3)->columnSpanFull(),
            SpatieMediaLibraryFileUpload::make('cover')
                ->collection('cover')
                ->image()
                ->helperText('Optional. Falls back to the first image, then a YouTube thumbnail.')
                ->columnSpanFull(),
            Section::make()->columns(2)->schema([
                Toggle::make('is_published')->default(true),
                Toggle::make('is_featured')->label('Show on homepage'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover'),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('client')->searchable(),
                TextColumn::make('year')->sortable(),
                TextColumn::make('media_items_count')->counts('mediaItems')->label('Media'),
                IconColumn::make('is_published')->boolean(),
                IconColumn::make('is_featured')->boolean()->label('Homepage'),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [MediaItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorks::route('/'),
            'create' => Pages\CreateWork::route('/create'),
            'edit' => Pages\EditWork::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 4: Resource pages**

Create `app/Filament/Resources/WorkResource/Pages/ListWorks.php`:

```php
<?php

namespace App\Filament\Resources\WorkResource\Pages;

use App\Filament\Resources\WorkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorks extends ListRecords
{
    protected static string $resource = WorkResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
```

Create `app/Filament/Resources/WorkResource/Pages/CreateWork.php`:

```php
<?php

namespace App\Filament\Resources\WorkResource\Pages;

use App\Filament\Resources\WorkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWork extends CreateRecord
{
    protected static string $resource = WorkResource::class;
}
```

Create `app/Filament/Resources/WorkResource/Pages/EditWork.php`:

```php
<?php

namespace App\Filament\Resources\WorkResource\Pages;

use App\Filament\Resources\WorkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWork extends EditRecord
{
    protected static string $resource = WorkResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
```

- [ ] **Step 5: Media relation manager**

Create `app/Filament/Resources/WorkResource/RelationManagers/MediaItemsRelationManager.php`:

```php
<?php

namespace App\Filament\Resources\WorkResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'mediaItems';

    protected static ?string $title = 'Media';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('type')
                ->options([
                    'image' => 'Image upload',
                    'video' => 'Video upload',
                    'youtube' => 'YouTube embed',
                ])
                ->default('image')
                ->required()
                ->live(),
            // One upload bound to the single `file` collection; the accepted
            // types follow the chosen row type. (Two components pointing at the
            // same collection would fight over the same state path.)
            SpatieMediaLibraryFileUpload::make('file')
                ->collection('file')
                ->acceptedFileTypes(fn ($get) => $get('type') === 'video'
                    ? ['video/mp4']
                    : ['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(153600)
                ->visible(fn ($get) => in_array($get('type'), ['image', 'video'], true))
                ->columnSpanFull(),
            TextInput::make('youtube_url')
                ->label('YouTube URL')
                ->url()
                ->required(fn ($get) => $get('type') === 'youtube')
                ->visible(fn ($get) => $get('type') === 'youtube')
                ->helperText('Any YouTube link — watch, youtu.be, embed or shorts.')
                ->columnSpanFull(),
            TextInput::make('caption')->columnSpanFull(),
            TextInput::make('order')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('caption')
            ->columns([
                TextColumn::make('order')->sortable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('caption')->limit(40),
                TextColumn::make('youtube_url')->limit(40)->toggleable(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }
}
```

- [ ] **Step 6: Permissions**

In `database/seeders/PermissionsSeeder.php` line 39, add `work` to the editor resource list so Editors can manage it:

```php
$editorResources = 'post|service|industry|category|tag|testimonial|work';
```

- [ ] **Step 7: Run tests, then full checks**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Admin/WorkResourceTest.php`
Expected: 3 passing.
Then: `php -d memory_limit=2G vendor/bin/pest && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: green. (If the Shield permission test asserts an exact permission list, update it for the new `work` resource and note it in the report.) Leave uncommitted.

---

### Task 3: Masonry grid component + lightbox

**Files:**
- Create: `resources/views/components/work-grid.blade.php`
- Modify: `resources/css/pages.css` (append the work-grid + lightbox styles)
- Test: covered by Tasks 4 and 5 page tests (this task ships the shared component they render)

**Interfaces:**
- Consumes: `Work::coverUrl()`, `Work::mediaPayload()` from Task 1.
- Produces: `<x-work-grid :works="$works" />`. Markup contract used by tests and JS:
  - grid wrapper `<div class="work-grid" data-work-grid>`
  - tile `<button class="work-tile reveal" data-work-tile data-work-media='<json>'>` (button only when the work has media; otherwise a non-interactive `<div class="work-tile reveal">`)
  - lightbox root `<div class="wlb" data-work-lightbox hidden>` rendered once at the end of the component

- [ ] **Step 1: Create the component**

Create `resources/views/components/work-grid.blade.php`:

```blade
@props(['works'])

<div class="work-grid" data-work-grid>
    @foreach ($works as $work)
        @php
            $cover = $work->coverUrl();
            $payload = $work->mediaPayload();
        @endphp
        <{{ $payload ? 'button' : 'div' }}
            class="work-tile reveal"
            data-delay="{{ $loop->index % 4 }}"
            @if ($payload)
                type="button"
                data-work-tile
                data-work-media='@json($payload)'
                aria-label="View {{ $work->title }}"
            @endif
        >
            @if ($cover)
                <img src="{{ $cover }}" alt="{{ $work->title }}" loading="lazy" decoding="async">
            @endif
            <span class="work-tile__scrim" aria-hidden="true"></span>
            <span class="work-tile__body">
                <span class="work-tile__title">{{ $work->title }}</span>
                @if ($work->client || $work->year)
                    <span class="work-tile__meta">{{ collect([$work->client, $work->year])->filter()->implode(' · ') }}</span>
                @endif
            </span>
        </{{ $payload ? 'button' : 'div' }}>
    @endforeach
</div>

<div class="wlb" data-work-lightbox hidden>
    <button class="wlb__close" data-wlb-close aria-label="Close">&times;</button>
    <button class="wlb__nav wlb__nav--prev" data-wlb-prev aria-label="Previous">&#8249;</button>
    <div class="wlb__stage" data-wlb-stage></div>
    <button class="wlb__nav wlb__nav--next" data-wlb-next aria-label="Next">&#8250;</button>
    <p class="wlb__caption" data-wlb-caption></p>
</div>
```

- [ ] **Step 2: Styles**

Append to `resources/css/pages.css`:

```css
/* ---------- Our Work: masonry grid + lightbox ---------- */
.work-grid { columns: 3; column-gap: 16px; }
@media (max-width: 980px) { .work-grid { columns: 2; } }
@media (max-width: 600px) { .work-grid { columns: 1; } }

.work-tile {
  break-inside: avoid;
  display: block;
  position: relative;
  width: 100%;
  margin: 0 0 16px;
  padding: 0;
  border: 1px solid var(--line);
  border-radius: 14px;
  overflow: hidden;
  background: var(--ink-3);
  color: #fff;
  text-align: left;
  cursor: pointer;
}
.work-tile img { display: block; width: 100%; height: auto; transition: transform 0.8s var(--ease); }
.work-tile__scrim {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.78), transparent 62%);
  transition: background 0.5s var(--ease);
}
.work-tile__body { position: absolute; left: 18px; right: 18px; bottom: 16px; display: grid; gap: 4px; }
.work-tile__title { font-family: var(--f-display); font-weight: 600; font-size: 19px; letter-spacing: -0.02em; }
.work-tile__meta {
  font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.15em;
  text-transform: uppercase; color: rgba(255,255,255,0.75);
}
@media (hover: hover) {
  .work-tile:hover img { transform: scale(1.06); }
  .work-tile:hover .work-tile__scrim { background: linear-gradient(to top, rgba(0,0,0,0.85), rgba(232,15,3,0.22) 60%, transparent 88%); }
}

.wlb {
  position: fixed; inset: 0; z-index: 200;
  display: grid; grid-template-columns: 64px 1fr 64px; align-items: center;
  background: rgba(0,0,0,0.92);
}
.wlb[hidden] { display: none; }
.wlb__stage { display: grid; place-items: center; max-height: 82vh; }
.wlb__stage img, .wlb__stage video, .wlb__stage iframe {
  max-width: 100%; max-height: 82vh; width: auto; height: auto; border: 0;
}
.wlb__stage iframe { width: min(90vw, 1200px); aspect-ratio: 16/9; height: auto; }
.wlb__nav, .wlb__close {
  background: transparent; border: 0; color: #fff; font-size: 34px; line-height: 1; cursor: pointer;
}
.wlb__close { position: absolute; top: 22px; right: 26px; font-size: 30px; }
.wlb__caption {
  position: absolute; left: 0; right: 0; bottom: 22px; text-align: center;
  font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.16em;
  text-transform: uppercase; color: rgba(255,255,255,0.72);
}
@media (max-width: 600px) { .wlb { grid-template-columns: 40px 1fr 40px; } }
```

- [ ] **Step 3: Build + full checks**

Run: `npm run build && php -d memory_limit=2G vendor/bin/pest && vendor/bin/pint --dirty`
Expected: green. Leave uncommitted.

---

### Task 4: `/our-works` page

**Files:**
- Create: `app/Http/Controllers/Public/WorkController.php`
- Create: `resources/views/works/index.blade.php`
- Modify: `routes/web.php` (add the route inside the `cacheResponse` group)
- Test: `tests/Feature/Public/WorksPageTest.php`

**Interfaces:**
- Consumes: `<x-work-grid>` (Task 3), `Work::published()` (Task 1).
- Produces: route name `works`, path `/our-works`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Public/WorksPageTest.php`:

```php
<?php

use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

it('renders published works and hides drafts', function () {
    Work::create(['title' => 'Public Reel', 'is_published' => true]);
    Work::create(['title' => 'Secret Reel', 'is_published' => false]);

    $this->get('/our-works')
        ->assertOk()
        ->assertSee('Public Reel')
        ->assertDontSee('Secret Reel');
});

it('renders a masonry grid and a clickable tile carrying its media payload', function () {
    $work = Work::create(['title' => 'Has Media']);
    $item = $work->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $item->addMedia(UploadedFile::fake()->image('a.jpg'))->toMediaCollection('file');

    $response = $this->get('/our-works')->assertOk();

    $response->assertSee('data-work-grid', false)
        ->assertSee('data-work-tile', false)
        ->assertSee($item->fresh()->getFirstMediaUrl('file'), false);
});

it('renders a work without media as a non-interactive tile', function () {
    Work::create(['title' => 'No Media Yet']);

    $this->get('/our-works')
        ->assertOk()
        ->assertSee('No Media Yet')
        ->assertDontSee('data-work-tile', false);
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Public/WorksPageTest.php`
Expected: FAIL — 404, route not defined.

- [ ] **Step 3: Controller**

Create `app/Http/Controllers/Public/WorkController.php`:

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Work;
use Illuminate\View\View;

class WorkController extends Controller
{
    public function index(): View
    {
        return view('works.index', [
            'works' => Work::published()
                ->with(['media', 'mediaItems.media'])
                ->orderBy('order')->orderByDesc('id')
                ->get(),
        ]);
    }
}
```

- [ ] **Step 4: Route**

In `routes/web.php` add the import `use App\Http\Controllers\Public\WorkController;` and, inside the `cacheResponse` group next to the other page routes:

```php
Route::get('/our-works', [WorkController::class, 'index'])->name('works');
```

- [ ] **Step 5: View**

Create `resources/views/works/index.blade.php`:

```blade
<x-layouts.app
    title="Our Work — TheLastClicks"
    description="Selected films and photography from TheLastClicks."
    :canonical="url('/our-works')"
>
    {{-- HEADER --}}
    <section class="page-header page-header--media" data-screen-label="01 Header" style="--ph-bg:url('https://images.unsplash.com/photo-1485846234645-a62644f84728?w=1800&q=80')">
        <x-page-header-video />
        <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Our Work</span></div>
        <h1 data-split>Our <em>work.</em></h1>
    </section>

    {{-- GRID --}}
    <section class="section" data-screen-label="02 Work">
        <x-container>
            @if ($works->isNotEmpty())
                <x-work-grid :works="$works" />
            @endif
        </x-container>
    </section>
</x-layouts.app>
```

- [ ] **Step 6: Run tests, then full checks**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Public/WorksPageTest.php`
Expected: 3 passing.
Then: `npm run build && php -d memory_limit=2G vendor/bin/pest && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: green. Leave uncommitted.

---

### Task 5: Homepage section, lightbox JS, nav/footer links

**Files:**
- Modify: `app/Http/Controllers/Public/HomeController.php`
- Modify: `resources/views/home.blade.php` (add the section before the CTA)
- Modify: `resources/js/chrome.js` (NAV_LINKS + footer Studio column)
- Create: `resources/js/work-lightbox.js`; Modify: `resources/js/core.js` (import it)
- Test: `tests/Feature/Public/HomeWorkSectionTest.php`

**Interfaces:**
- Consumes: `<x-work-grid>` (Task 3), `Work::published()` (Task 1), route `works` (Task 4).
- Produces: view var `$featuredWorks`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Public/HomeWorkSectionTest.php`:

```php
<?php

use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

it('hides the work section when nothing is published', function () {
    $this->get('/')->assertOk()->assertDontSee('data-work-grid', false);
});

it('shows featured works with a link to the full page', function () {
    Work::create(['title' => 'Featured Reel', 'is_featured' => true]);
    Work::create(['title' => 'Plain Reel']);

    $this->get('/')
        ->assertOk()
        ->assertSee('data-work-grid', false)
        ->assertSee('Featured Reel')
        ->assertSee('/our-works', false);
});

it('falls back to recent works when none are featured', function () {
    Work::create(['title' => 'Recent Reel']);

    $this->get('/')->assertOk()->assertSee('Recent Reel');
});

it('caps the homepage grid at six works', function () {
    foreach (range(1, 8) as $i) {
        Work::create(['title' => "Reel {$i}"]);
    }

    $response = $this->get('/')->assertOk();

    expect(substr_count($response->getContent(), 'work-tile reveal'))->toBe(6);
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Public/HomeWorkSectionTest.php`
Expected: FAIL — homepage has no work grid.

- [ ] **Step 3: HomeController**

Replace `app/Http/Controllers/Public/HomeController.php` contents with:

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Work;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'services' => Service::orderBy('order')->with('media')->get(),
            'testimonials' => Testimonial::published()->orderBy('order')->get(),
            'featuredWorks' => $this->featuredWorks(),
        ]);
    }

    /**
     * Featured works for the homepage strip; falls back to the most recent
     * published works so the section is never empty just because nobody
     * ticked "Show on homepage".
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Work>
     */
    protected function featuredWorks(): \Illuminate\Database\Eloquent\Collection
    {
        $base = fn () => Work::published()->with(['media', 'mediaItems.media']);

        $featured = $base()->where('is_featured', true)
            ->orderBy('order')->orderByDesc('id')->take(6)->get();

        return $featured->isNotEmpty()
            ? $featured
            : $base()->orderBy('order')->orderByDesc('id')->take(6)->get();
    }
}
```

- [ ] **Step 4: Homepage section**

In `resources/views/home.blade.php`, insert this section immediately before the `<section class="cta-strip" ...>` block:

```blade
    <!-- OUR WORK -->
    @if ($featuredWorks->isNotEmpty())
    <section class="section" data-screen-label="07 Work">
        <x-container>
            <div class="services__head">
                <div>
                    <span class="section__eyebrow" data-scramble>Our Work</span>
                    <h2 class="section__title" data-split>Selected <em>work.</em></h2>
                </div>
                <a class="btn btn--ghost" href="{{ url('/our-works') }}" data-cursor="VIEW">View all work <span class="arr"></span></a>
            </div>
            <x-work-grid :works="$featuredWorks" />
        </x-container>
    </section>
    @endif
```

- [ ] **Step 5: Lightbox JS**

Create `resources/js/work-lightbox.js`:

```js
/* Work lightbox: opens a carousel of a tile's media (image / video / YouTube). */
export function initWorkLightbox() {
  const box = document.querySelector('[data-work-lightbox]');
  const tiles = [...document.querySelectorAll('[data-work-tile]')];
  if (!box || !tiles.length) return;

  const stage = box.querySelector('[data-wlb-stage]');
  const caption = box.querySelector('[data-wlb-caption]');
  let items = [];
  let index = 0;

  function render() {
    const item = items[index];
    stage.innerHTML = '';
    if (!item) return;

    let el;
    if (item.type === 'youtube') {
      el = document.createElement('iframe');
      el.src = item.url;
      el.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; picture-in-picture';
      el.allowFullscreen = true;
    } else if (item.type === 'video') {
      el = document.createElement('video');
      el.src = item.url;
      el.controls = true;
      el.playsInline = true;
    } else {
      el = document.createElement('img');
      el.src = item.url;
      el.alt = item.caption || '';
    }
    stage.appendChild(el);
    caption.textContent = item.caption || '';
  }

  function open(payload) {
    items = payload;
    index = 0;
    box.hidden = false;
    document.body.style.overflow = 'hidden';
    render();
  }

  function close() {
    box.hidden = true;
    document.body.style.overflow = '';
    stage.innerHTML = ''; // stops video + unloads the iframe
    caption.textContent = '';
  }

  const step = (n) => { if (items.length) { index = (index + n + items.length) % items.length; render(); } };

  tiles.forEach((tile) => {
    tile.addEventListener('click', () => {
      try { open(JSON.parse(tile.dataset.workMedia || '[]')); } catch (e) { /* malformed payload: ignore */ }
    });
  });

  box.querySelector('[data-wlb-close]').addEventListener('click', close);
  box.querySelector('[data-wlb-prev]').addEventListener('click', () => step(-1));
  box.querySelector('[data-wlb-next]').addEventListener('click', () => step(1));
  box.addEventListener('click', (e) => { if (e.target === box) close(); });
  document.addEventListener('keydown', (e) => {
    if (box.hidden) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') step(-1);
    if (e.key === 'ArrowRight') step(1);
  });
}
```

In `resources/js/core.js` add the import at the top of the file:

```js
import { initWorkLightbox } from './work-lightbox';
```

and call it inside the same IIFE/DOM-ready block that wires the other behaviours (alongside the existing nav/reveal setup):

```js
  initWorkLightbox();
```

- [ ] **Step 6: Nav + footer links**

In `resources/js/chrome.js`, add to `NAV_LINKS` after the Industries entry:

```js
    { href: '/our-works', label: 'Our Work' },
```

and in `footerHTML()`'s Studio column, after the Industries link:

```html
            <a href="/our-works">Our Work</a>
```

- [ ] **Step 7: Run tests, build, full checks**

Run: `php -d memory_limit=2G vendor/bin/pest tests/Feature/Public/HomeWorkSectionTest.php`
Expected: 4 passing.
Then: `npm run build && php -d memory_limit=2G vendor/bin/pest && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: green. Leave uncommitted.
