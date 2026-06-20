# Plan 3 — Content Resources + SEO Hardening + Deploy Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship admin CRUD for all remaining content models (Post, Portfolio, Service, Industry, Crew, Category, Tag) with full media-library integration, switch every public Blade view from seeded fixtures to live DB-edited content, add SEO infrastructure (`sitemap.xml`, response-cache invalidation), and round out production readiness with queue worker setup, backups, CI workflow, Sentry, and deployment notes. After this plan, TheLastClicks is a fully editable, deploy-ready site.

**Architecture:** Continues the Laravel 11 + Filament v3 monolith. Each new Resource follows the same skeleton as `QuoteResource` (form + table + policy + navigation group). Resources with attached media use `spatie/laravel-medialibrary` collections defined on the model (Plan 1) — Filament's `SpatieMediaLibraryFileUpload` component handles uploads. SEO via `spatie/laravel-sitemap` (cron'd) + `spatie/laravel-responsecache` (model-observer invalidation). Deploy infra adds a CI workflow, Sentry, a `spatie/laravel-backup` config, and a queue worker recipe.

**Tech Stack:** Filament v3 · `spatie/laravel-medialibrary` ^11 · `spatie/laravel-sitemap` ^7 · `spatie/laravel-responsecache` ^7 · `spatie/laravel-backup` ^9 · `sentry/sentry-laravel` ^4 · GitHub Actions

**Spec:** [docs/superpowers/specs/2026-05-24-thelastclicks-website-design.md](../specs/2026-05-24-thelastclicks-website-design.md) §4.5, §5.2, §6, §7, §8

**Prerequisite:** Plan 2 complete (73 pest tests green; Filament admin live at `/admin`; QuoteResource + UserResource + SiteSettingsPage shipped).

---

## File Structure

```
thelastclicks/
├── app/
│   ├── Filament/Resources/
│   │   ├── PostResource.php (+ Pages/)
│   │   ├── PortfolioResource.php (+ Pages/, RelationManagers/)
│   │   ├── ServiceResource.php (+ Pages/)
│   │   ├── IndustryResource.php (+ Pages/)
│   │   ├── CrewResource.php (+ Pages/)
│   │   ├── CategoryResource.php (+ Pages/)
│   │   └── TagResource.php (+ Pages/)
│   ├── Policies/
│   │   ├── PostPolicy.php
│   │   ├── PortfolioPolicy.php
│   │   ├── ServicePolicy.php
│   │   ├── IndustryPolicy.php
│   │   ├── CrewPolicy.php
│   │   ├── CategoryPolicy.php
│   │   └── TagPolicy.php
│   ├── Console/Commands/
│   │   └── GenerateSitemap.php
│   └── Observers/
│       ├── PostObserver.php
│       ├── PortfolioObserver.php
│       ├── ServiceObserver.php
│       ├── IndustryObserver.php
│       ├── CrewObserver.php
│       └── SiteSettingObserver.php
├── config/
│   ├── responsecache.php (published)
│   ├── backup.php (published)
│   └── sentry.php (published)
├── routes/
│   └── console.php  (modify — add sitemap + cache invalidation schedules)
├── .github/workflows/
│   └── ci.yml
├── docs/
│   └── DEPLOYMENT.md
└── tests/
    └── Feature/
        ├── Admin/
        │   ├── PostResourceTest.php
        │   ├── PortfolioResourceTest.php
        │   ├── ServiceResourceTest.php
        │   ├── IndustryResourceTest.php
        │   ├── CrewResourceTest.php
        │   └── CategoryTagResourceTest.php
        ├── Seo/
        │   ├── SitemapTest.php
        │   └── ResponseCacheInvalidationTest.php
        └── (existing tests still pass)
```

---

## Phase K — Simple Content Resources

Resources in this phase share the QuoteResource skeleton — a generated Filament resource + a custom Policy + Pest tests. Owner-aware ABAC applies only to `PortfolioResource` and `PostResource` (Plan 2 §3.4); the rest are global "site content" CRUDable by any Editor or Super-admin.

### Task 36: `ServiceResource`

**Files:**
- Create: `app/Filament/Resources/ServiceResource.php` + Pages
- Create: `app/Policies/ServicePolicy.php`
- Create: `tests/Feature/Admin/ServiceResourceTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Admin/ServiceResourceTest.php`:

```php
<?php

use App\Filament\Resources\ServiceResource\Pages\CreateService;
use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Filament\Resources\ServiceResource\Pages\ListServices;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list services', function () {
    Livewire::test(ListServices::class)->assertCanSeeTableRecords(Service::all());
});

it('Super-admin can edit a service', function () {
    $svc = Service::first();
    Livewire::test(EditService::class, ['record' => $svc->getRouteKey()])
        ->fillForm(['hero_copy' => 'New tagline'])
        ->call('save')
        ->assertHasNoFormErrors();
    expect($svc->fresh()->hero_copy)->toBe('New tagline');
});

it('Editor can edit services', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');
    expect($editor->can('update', Service::first()))->toBeTrue();
});

it('Viewer cannot create services', function () {
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');
    expect($viewer->can('create', Service::class))->toBeFalse();
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Admin/ServiceResourceTest.php
```

- [ ] **Step 3: Generate resource**

```bash
cd /Users/Project/Personal/thelastclicks
php artisan make:filament-resource Service --no-interaction
```

- [ ] **Step 4: Replace `form()` in `ServiceResource`**

```php
public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
{
    return $form->schema([
        \Filament\Forms\Components\Section::make()->columns(2)->schema([
            \Filament\Forms\Components\TextInput::make('title')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
            \Filament\Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
        ]),
        \Filament\Forms\Components\Textarea::make('hero_copy')->rows(2)->columnSpanFull(),
        \Filament\Forms\Components\Textarea::make('body')->rows(10)->columnSpanFull(),
        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('hero')
            ->collection('hero')
            ->image()
            ->columnSpanFull(),
        \Filament\Forms\Components\TextInput::make('order')->numeric()->default(0),
    ]);
}
```

- [ ] **Step 5: Replace `table()`**

```php
public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
{
    return $table
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('order')->sortable(),
            \Filament\Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
            \Filament\Tables\Columns\TextColumn::make('slug')->searchable(),
        ])
        ->defaultSort('order')
        ->reorderable('order')
        ->actions([
            \Filament\Tables\Actions\EditAction::make(),
            \Filament\Tables\Actions\DeleteAction::make(),
        ]);
}
```

- [ ] **Step 6: Add navigation group**

Append to `ServiceResource`:

```php
protected static ?string $navigationGroup = 'Site';
```

- [ ] **Step 7: Create `ServicePolicy`**

`app/Policies/ServicePolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool { return $user->can('view_any_service'); }
    public function view(User $user, Service $svc): bool { return $user->can('view_service'); }
    public function create(User $user): bool { return $user->can('create_service'); }
    public function update(User $user, Service $svc): bool { return $user->can('update_service'); }
    public function delete(User $user, Service $svc): bool { return $user->can('delete_service'); }
    public function deleteAny(User $user): bool { return $user->can('delete_any_service'); }
}
```

- [ ] **Step 8: Re-seed so service perms exist**

```bash
php artisan migrate:fresh --seed
```

PermissionsSeeder's filter `preg_match('/_(post|portfolio|service|industry|crew|category|tag)$/', $p)` already grants `*_service` perms to Editor.

- [ ] **Step 9: Run pest**

```bash
./vendor/bin/pest tests/Feature/Admin/ServiceResourceTest.php
./vendor/bin/pest
```

Expected: 4 in ServiceResourceTest; full suite 77 (73+4).

- [ ] **Step 10: Commit**

```bash
git add app/Filament/Resources/ServiceResource* app/Policies/ServicePolicy.php tests/Feature/Admin/ServiceResourceTest.php
git commit -m "feat(admin): service resource + policy + hero media"
```

## Context

- Plan 2 done (73 tests, Filament + QuoteResource + UserResource + SiteSettingsPage).
- `Service` model has slug auto-generation via spatie/sluggable (Plan 1 Task 8) AND `registerMediaCollections()` with single-file `hero` collection.
- The `live(onBlur: true)` + `afterStateUpdated` pattern on title sets slug as user types/blurs — convenience only, slug remains editable.
- PermissionsSeeder already covers Editor → service perms.
- ShieldGenerate respects `--ignore-existing-policies` from Plan 2 Task 30 fix — custom ServicePolicy won't be overwritten.

## Specific concerns

1. **`SpatieMediaLibraryFileUpload`** is auto-detected by Filament when spatie/medialibrary is installed. If the class is missing, install the official Filament+Spatie bridge: `composer require filament/spatie-laravel-media-library-plugin:^3.2`.
2. **`reorderable('order')`** enables drag-and-drop reordering on the table view.
3. **`live(onBlur: true)`** — Filament v3 syntax for "trigger livewire update on blur". Without it, the afterStateUpdated callback won't fire.

## Self-Review

- ServiceResource generated + customised
- ServicePolicy created
- All 4 tests pass
- pest 77 green
- Single commit

## Report

- Status
- Step 9 final lines
- `git show --stat HEAD`
- Did you have to install `filament/spatie-laravel-media-library-plugin`?
- Concerns

---

### Task 37: `IndustryResource`

**Files:**
- Create: `app/Filament/Resources/IndustryResource.php` + Pages
- Create: `app/Policies/IndustryPolicy.php`
- Create: `tests/Feature/Admin/IndustryResourceTest.php`

Identical pattern to ServiceResource. Substitute model names + add `summary` field to form.

- [ ] **Step 1: Write failing test**

`tests/Feature/Admin/IndustryResourceTest.php`:

```php
<?php

use App\Filament\Resources\IndustryResource\Pages\ListIndustries;
use App\Filament\Resources\IndustryResource\Pages\EditIndustry;
use App\Models\Industry;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list industries', function () {
    Livewire::test(ListIndustries::class)->assertCanSeeTableRecords(Industry::all());
});

it('Super-admin can edit an industry summary', function () {
    $ind = Industry::first();
    Livewire::test(EditIndustry::class, ['record' => $ind->getRouteKey()])
        ->fillForm(['summary' => 'New summary'])
        ->call('save')
        ->assertHasNoFormErrors();
    expect($ind->fresh()->summary)->toBe('New summary');
});

it('Editor can edit industries', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');
    expect($editor->can('update', Industry::first()))->toBeTrue();
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Admin/IndustryResourceTest.php
```

- [ ] **Step 3: Generate resource**

```bash
php artisan make:filament-resource Industry --no-interaction
```

- [ ] **Step 4: Replace `form()` and `table()`**

In `app/Filament/Resources/IndustryResource.php`:

```php
public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
{
    return $form->schema([
        \Filament\Forms\Components\Section::make()->columns(2)->schema([
            \Filament\Forms\Components\TextInput::make('title')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
            \Filament\Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
        ]),
        \Filament\Forms\Components\TextInput::make('summary')->columnSpanFull(),
        \Filament\Forms\Components\Textarea::make('body')->rows(10)->columnSpanFull(),
        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('hero')
            ->collection('hero')
            ->image()
            ->columnSpanFull(),
        \Filament\Forms\Components\TextInput::make('order')->numeric()->default(0),
    ]);
}

public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
{
    return $table
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('order')->sortable(),
            \Filament\Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
            \Filament\Tables\Columns\TextColumn::make('slug')->searchable(),
        ])
        ->defaultSort('order')
        ->reorderable('order')
        ->actions([
            \Filament\Tables\Actions\EditAction::make(),
            \Filament\Tables\Actions\DeleteAction::make(),
        ]);
}
```

Also append:

```php
protected static ?string $navigationGroup = 'Site';
```

- [ ] **Step 5: Create `IndustryPolicy`**

`app/Policies/IndustryPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\Industry;
use App\Models\User;

class IndustryPolicy
{
    public function viewAny(User $user): bool { return $user->can('view_any_industry'); }
    public function view(User $user, Industry $ind): bool { return $user->can('view_industry'); }
    public function create(User $user): bool { return $user->can('create_industry'); }
    public function update(User $user, Industry $ind): bool { return $user->can('update_industry'); }
    public function delete(User $user, Industry $ind): bool { return $user->can('delete_industry'); }
    public function deleteAny(User $user): bool { return $user->can('delete_any_industry'); }
}
```

- [ ] **Step 6: Re-seed**

```bash
php artisan migrate:fresh --seed
```

- [ ] **Step 7: Run pest**

```bash
./vendor/bin/pest tests/Feature/Admin/IndustryResourceTest.php
./vendor/bin/pest
```

Expected: 3 in test; full suite 80 (77+3).

- [ ] **Step 8: Commit**

```bash
git add app/Filament/Resources/IndustryResource* app/Policies/IndustryPolicy.php tests/Feature/Admin/IndustryResourceTest.php
git commit -m "feat(admin): industry resource + policy"
```

## Context

Same as Task 36. Industry differs from Service only by including a `summary` field.

## Self-Review

- Resource + policy + tests + green pest

## Report

- Status, step 7 final lines, git show, concerns

---

### Task 38: `CrewResource`

**Files:**
- Create: `app/Filament/Resources/CrewResource.php` + Pages
- Create: `app/Policies/CrewPolicy.php`
- Create: `tests/Feature/Admin/CrewResourceTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Admin/CrewResourceTest.php`:

```php
<?php

use App\Filament\Resources\CrewResource\Pages\CreateCrew;
use App\Filament\Resources\CrewResource\Pages\ListCrew;
use App\Models\Crew;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list crew', function () {
    Livewire::test(ListCrew::class)->assertCanSeeTableRecords(Crew::all());
});

it('Super-admin can create a crew member with social_json', function () {
    Livewire::test(CreateCrew::class)
        ->fillForm([
            'name'        => 'Alex Maker',
            'role'        => 'Director',
            'bio'         => 'Bio paragraph',
            'social_json' => ['instagram' => 'https://instagram.com/alex', 'youtube' => 'https://youtube.com/@alex'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $c = Crew::where('name', 'Alex Maker')->first();
    expect($c)->not->toBeNull()
        ->and($c->social_json['instagram'])->toBe('https://instagram.com/alex');
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Admin/CrewResourceTest.php
```

- [ ] **Step 3: Generate resource**

```bash
php artisan make:filament-resource Crew --no-interaction
```

If the generator complains about the irregular plural ("crew" is uncountable), Filament should still resolve via the model's `$table` property. Verify the pages dir is `app/Filament/Resources/CrewResource/Pages/`.

- [ ] **Step 4: Replace `form()` and `table()`**

`CrewResource.php`:

```php
public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
{
    return $form->schema([
        \Filament\Forms\Components\Section::make()->columns(2)->schema([
            \Filament\Forms\Components\TextInput::make('name')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
            \Filament\Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            \Filament\Forms\Components\TextInput::make('role')->required(),
            \Filament\Forms\Components\TextInput::make('order')->numeric()->default(0),
        ]),
        \Filament\Forms\Components\Textarea::make('bio')->rows(6)->columnSpanFull(),
        \Filament\Forms\Components\KeyValue::make('social_json')
            ->label('Social links')
            ->keyLabel('Platform')
            ->valueLabel('URL')
            ->columnSpanFull(),
        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('headshot')
            ->collection('headshot')
            ->image()
            ->columnSpanFull(),
    ]);
}

public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
{
    return $table
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('order')->sortable(),
            \Filament\Tables\Columns\SpatieMediaLibraryImageColumn::make('headshot')->collection('headshot')->circular(),
            \Filament\Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            \Filament\Tables\Columns\TextColumn::make('role'),
        ])
        ->defaultSort('order')
        ->reorderable('order')
        ->actions([
            \Filament\Tables\Actions\EditAction::make(),
            \Filament\Tables\Actions\DeleteAction::make(),
        ]);
}
```

Append:

```php
protected static ?string $navigationGroup = 'Site';
protected static ?string $modelLabel = 'Crew Member';
protected static ?string $pluralModelLabel = 'Crew';
```

- [ ] **Step 5: Create `CrewPolicy`**

`app/Policies/CrewPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\Crew;
use App\Models\User;

class CrewPolicy
{
    public function viewAny(User $user): bool { return $user->can('view_any_crew'); }
    public function view(User $user, Crew $c): bool { return $user->can('view_crew'); }
    public function create(User $user): bool { return $user->can('create_crew'); }
    public function update(User $user, Crew $c): bool { return $user->can('update_crew'); }
    public function delete(User $user, Crew $c): bool { return $user->can('delete_crew'); }
    public function deleteAny(User $user): bool { return $user->can('delete_any_crew'); }
}
```

- [ ] **Step 6: Re-seed + pest**

```bash
php artisan migrate:fresh --seed
./vendor/bin/pest tests/Feature/Admin/CrewResourceTest.php
./vendor/bin/pest
```

Expected: 2 in test; full suite 82 (80+2).

- [ ] **Step 7: Commit**

```bash
git add app/Filament/Resources/CrewResource* app/Policies/CrewPolicy.php tests/Feature/Admin/CrewResourceTest.php
git commit -m "feat(admin): crew resource + policy + headshot"
```

## Context

- `Crew` model has `protected $table = 'crew'`, `protected $casts = ['social_json' => 'array']`. The KeyValue Filament component reads/writes arrays directly to the cast.
- `SpatieMediaLibraryImageColumn` displays a thumbnail in the table.

## Self-Review

- Resource + policy + tests + green pest

## Report

- Status, final lines, git show, concerns (especially Filament's handling of uncountable plurals)

---

### Task 39: `CategoryResource` + `TagResource` (combined, both trivial)

**Files:**
- Create: `app/Filament/Resources/CategoryResource.php` + Pages
- Create: `app/Filament/Resources/TagResource.php` + Pages
- Create: `app/Policies/CategoryPolicy.php`
- Create: `app/Policies/TagPolicy.php`
- Create: `tests/Feature/Admin/CategoryTagResourceTest.php`

These two are simple slug+name CRUDs. Combined into one task because each is ~5 lines of form.

- [ ] **Step 1: Write failing test**

`tests/Feature/Admin/CategoryTagResourceTest.php`:

```php
<?php

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\TagResource\Pages\CreateTag;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can create a category', function () {
    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => 'Studio Diary'])
        ->call('create')
        ->assertHasNoFormErrors();
    expect(Category::where('name', 'Studio Diary')->exists())->toBeTrue();
});

it('Super-admin can create a tag', function () {
    Livewire::test(CreateTag::class)
        ->fillForm(['name' => 'cinematic'])
        ->call('create')
        ->assertHasNoFormErrors();
    expect(Tag::where('name', 'cinematic')->exists())->toBeTrue();
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Admin/CategoryTagResourceTest.php
```

- [ ] **Step 3: Generate both resources**

```bash
php artisan make:filament-resource Category --no-interaction
php artisan make:filament-resource Tag --no-interaction
```

- [ ] **Step 4: Replace `form()` and `table()` in each**

`CategoryResource.php`:

```php
public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
{
    return $form->schema([
        \Filament\Forms\Components\TextInput::make('name')->required(),
        \Filament\Forms\Components\TextInput::make('slug')->unique(ignoreRecord: true),
    ]);
}

public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
{
    return $table
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            \Filament\Tables\Columns\TextColumn::make('slug'),
            \Filament\Tables\Columns\TextColumn::make('posts_count')->counts('posts')->label('Posts'),
        ])
        ->actions([
            \Filament\Tables\Actions\EditAction::make(),
            \Filament\Tables\Actions\DeleteAction::make(),
        ]);
}
```

Append `protected static ?string $navigationGroup = 'Content';` and `protected static ?int $navigationSort = 30;`.

`TagResource.php` — identical pattern, just substitute "Tag":

```php
public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
{
    return $form->schema([
        \Filament\Forms\Components\TextInput::make('name')->required(),
        \Filament\Forms\Components\TextInput::make('slug')->unique(ignoreRecord: true),
    ]);
}

public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
{
    return $table
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            \Filament\Tables\Columns\TextColumn::make('slug'),
            \Filament\Tables\Columns\TextColumn::make('posts_count')->counts('posts')->label('Posts'),
        ])
        ->actions([
            \Filament\Tables\Actions\EditAction::make(),
            \Filament\Tables\Actions\DeleteAction::make(),
        ]);
}
```

Append same nav group + sort.

- [ ] **Step 5: Policies**

`app/Policies/CategoryPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool { return $user->can('view_any_category'); }
    public function view(User $user, Category $c): bool { return $user->can('view_category'); }
    public function create(User $user): bool { return $user->can('create_category'); }
    public function update(User $user, Category $c): bool { return $user->can('update_category'); }
    public function delete(User $user, Category $c): bool { return $user->can('delete_category'); }
    public function deleteAny(User $user): bool { return $user->can('delete_any_category'); }
}
```

`app/Policies/TagPolicy.php` — same pattern with `Tag` + `tag` substitutions:

```php
<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool { return $user->can('view_any_tag'); }
    public function view(User $user, Tag $t): bool { return $user->can('view_tag'); }
    public function create(User $user): bool { return $user->can('create_tag'); }
    public function update(User $user, Tag $t): bool { return $user->can('update_tag'); }
    public function delete(User $user, Tag $t): bool { return $user->can('delete_tag'); }
    public function deleteAny(User $user): bool { return $user->can('delete_any_tag'); }
}
```

- [ ] **Step 6: Re-seed + pest**

```bash
php artisan migrate:fresh --seed
./vendor/bin/pest tests/Feature/Admin/CategoryTagResourceTest.php
./vendor/bin/pest
```

Expected: 2 in test; full suite 84 (82+2).

- [ ] **Step 7: Commit**

```bash
git add app/Filament/Resources/CategoryResource* app/Filament/Resources/TagResource* app/Policies/CategoryPolicy.php app/Policies/TagPolicy.php tests/Feature/Admin/CategoryTagResourceTest.php
git commit -m "feat(admin): category + tag resources + policies"
```

## Self-Review

- Both resources + both policies + 1 test file
- Full pest 84 green

## Report

- Status, final lines, git show, concerns

---

## Phase L — Rich Content Resources (with media)

### Task 40: `PortfolioResource` (cover + gallery + service/industry FKs)

**Files:**
- Create: `app/Filament/Resources/PortfolioResource.php` + Pages
- Create: `app/Policies/PortfolioPolicy.php`
- Create: `tests/Feature/Admin/PortfolioResourceTest.php`

This Resource has ownership ABAC (Editor edits only own per spec §3.3).

- [ ] **Step 1: Write failing test**

`tests/Feature/Admin/PortfolioResourceTest.php`:

```php
<?php

use App\Filament\Resources\PortfolioResource\Pages\CreatePortfolio;
use App\Filament\Resources\PortfolioResource\Pages\EditPortfolio;
use App\Filament\Resources\PortfolioResource\Pages\ListPortfolios;
use App\Models\Industry;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list portfolios', function () {
    Livewire::test(ListPortfolios::class)->assertCanSeeTableRecords(Portfolio::all());
});

it('Super-admin can create a portfolio with service+industry+year', function () {
    Livewire::test(CreatePortfolio::class)
        ->fillForm([
            'title'       => 'Launch Film 2026',
            'client'      => 'Acme Co',
            'year'        => 2026,
            'service_id'  => Service::where('slug','videography')->first()->id,
            'industry_id' => Industry::where('slug','fashion')->first()->id,
            'status'      => 'published',
            'body'        => 'Project body',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $p = Portfolio::where('title','Launch Film 2026')->first();
    expect($p)->not->toBeNull()
        ->and($p->owner_id)->toBe($this->admin->id)
        ->and($p->status)->toBe('published');
});

it('Editor can edit only their own portfolio (ownership ABAC)', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    $mine  = Portfolio::factory()->for($editor, 'owner')->create();
    $other = Portfolio::factory()->create();

    expect($editor->can('update', $mine))->toBeTrue()
        ->and($editor->can('update', $other))->toBeFalse();
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Admin/PortfolioResourceTest.php
```

- [ ] **Step 3: Generate resource**

```bash
php artisan make:filament-resource Portfolio --no-interaction
```

- [ ] **Step 4: Replace `form()`**

```php
public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
{
    return $form->schema([
        \Filament\Forms\Components\Section::make()->columns(2)->schema([
            \Filament\Forms\Components\TextInput::make('title')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
            \Filament\Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            \Filament\Forms\Components\TextInput::make('client'),
            \Filament\Forms\Components\TextInput::make('year')->numeric()->minValue(2000)->maxValue(2100),
            \Filament\Forms\Components\Select::make('service_id')->relationship('service','title')->searchable()->preload(),
            \Filament\Forms\Components\Select::make('industry_id')->relationship('industry','title')->searchable()->preload(),
        ]),
        \Filament\Forms\Components\Section::make('Status & Owner')->columns(2)->schema([
            \Filament\Forms\Components\Select::make('status')->options([
                'draft' => 'Draft', 'published' => 'Published',
            ])->required()->default('draft'),
            \Filament\Forms\Components\Select::make('owner_id')
                ->relationship('owner','name')->searchable()->preload()
                ->default(auth()->id())->required(),
        ]),
        \Filament\Forms\Components\Textarea::make('body')->rows(8)->columnSpanFull(),
        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('cover')
            ->collection('cover')
            ->image()
            ->columnSpanFull(),
        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')
            ->collection('gallery')
            ->image()
            ->multiple()
            ->reorderable()
            ->columnSpanFull(),
    ]);
}
```

- [ ] **Step 5: Replace `table()`**

```php
public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
{
    return $table
        ->columns([
            \Filament\Tables\Columns\SpatieMediaLibraryImageColumn::make('cover')->collection('cover'),
            \Filament\Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
            \Filament\Tables\Columns\TextColumn::make('client'),
            \Filament\Tables\Columns\TextColumn::make('year')->sortable(),
            \Filament\Tables\Columns\TextColumn::make('owner.name')->label('Owner'),
            \Filament\Tables\Columns\BadgeColumn::make('status')->colors([
                'gray' => 'draft', 'success' => 'published',
            ]),
        ])
        ->filters([
            \Filament\Tables\Filters\SelectFilter::make('service_id')->relationship('service','title'),
            \Filament\Tables\Filters\SelectFilter::make('industry_id')->relationship('industry','title'),
            \Filament\Tables\Filters\SelectFilter::make('status')->options(['draft'=>'Draft','published'=>'Published']),
        ])
        ->defaultSort('created_at','desc')
        ->actions([
            \Filament\Tables\Actions\EditAction::make(),
            \Filament\Tables\Actions\DeleteAction::make(),
        ]);
}
```

Append nav group:

```php
protected static ?string $navigationGroup = 'Content';
```

- [ ] **Step 6: `PortfolioPolicy` with ownership ABAC**

`app/Policies/PortfolioPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\Portfolio;
use App\Models\User;

class PortfolioPolicy
{
    public function viewAny(User $user): bool { return $user->can('view_any_portfolio'); }
    public function view(User $user, Portfolio $p): bool { return $user->can('view_portfolio'); }
    public function create(User $user): bool { return $user->can('create_portfolio'); }

    public function update(User $user, Portfolio $p): bool
    {
        if ($user->hasRole('Super-admin')) return true;
        if (! $user->can('update_portfolio')) return false;
        if ($user->hasRole('Editor')) return $p->owner_id === $user->id;
        return true;
    }

    public function delete(User $user, Portfolio $p): bool
    {
        if ($user->hasRole('Super-admin')) return true;
        if (! $user->can('delete_portfolio')) return false;
        if ($user->hasRole('Editor')) return $p->owner_id === $user->id;
        return true;
    }

    public function deleteAny(User $user): bool { return $user->can('delete_any_portfolio'); }
}
```

- [ ] **Step 7: Re-seed + pest**

```bash
php artisan migrate:fresh --seed
./vendor/bin/pest tests/Feature/Admin/PortfolioResourceTest.php
./vendor/bin/pest
```

Expected: 3 in test; full suite 87 (84+3).

- [ ] **Step 8: Commit**

```bash
git add app/Filament/Resources/PortfolioResource* app/Policies/PortfolioPolicy.php tests/Feature/Admin/PortfolioResourceTest.php
git commit -m "feat(admin): portfolio resource + ownership policy + media gallery"
```

## Context

- `Portfolio` model has `cover` (singleFile) + `gallery` (multi) media collections.
- `service_id` + `industry_id` FK nullable with `nullOnDelete` (Plan 1 Task 8).
- Editor role gets `*_portfolio` perms via PermissionsSeeder filter.

## Self-Review

- Resource + policy + tests + green pest
- `owner_id` auto-sets to creator via form default

## Report

- Status, final lines, git show, concerns

---

### Task 41: `PostResource` (rich editor + cover + categories + tags)

**Files:**
- Create: `app/Filament/Resources/PostResource.php` + Pages
- Create: `app/Policies/PostPolicy.php`
- Create: `tests/Feature/Admin/PostResourceTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Admin/PostResourceTest.php`:

```php
<?php

use App\Filament\Resources\PostResource\Pages\CreatePost;
use App\Filament\Resources\PostResource\Pages\EditPost;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list posts', function () {
    Livewire::test(ListPosts::class)->assertCanSeeTableRecords(Post::all());
});

it('Super-admin can create a published post with categories + tags', function () {
    $cat = Category::factory()->create();
    $tag = Tag::factory()->create();

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title'        => 'Hello from Test',
            'excerpt'      => 'A new post',
            'body'         => '<p>Body content</p>',
            'status'       => 'published',
            'published_at' => now()->toDateTimeString(),
            'categories'   => [$cat->id],
            'tags'         => [$tag->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $p = Post::where('title','Hello from Test')->first();
    expect($p)->not->toBeNull()
        ->and($p->author_id)->toBe($this->admin->id)
        ->and($p->status)->toBe('published')
        ->and($p->categories->pluck('id')->all())->toBe([$cat->id])
        ->and($p->tags->pluck('id')->all())->toBe([$tag->id]);
});

it('Editor can edit only their own posts', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    $mine  = Post::factory()->for($editor,'author')->create();
    $other = Post::factory()->create();

    expect($editor->can('update', $mine))->toBeTrue()
        ->and($editor->can('update', $other))->toBeFalse();
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Admin/PostResourceTest.php
```

- [ ] **Step 3: Generate**

```bash
php artisan make:filament-resource Post --no-interaction
```

- [ ] **Step 4: Replace `form()`**

```php
public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
{
    return $form->schema([
        \Filament\Forms\Components\Section::make()->columns(2)->schema([
            \Filament\Forms\Components\TextInput::make('title')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
            \Filament\Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
        ]),
        \Filament\Forms\Components\Textarea::make('excerpt')->rows(2)->columnSpanFull(),
        \Filament\Forms\Components\RichEditor::make('body')->columnSpanFull(),
        \Filament\Forms\Components\Section::make('Publishing')->columns(2)->schema([
            \Filament\Forms\Components\Select::make('status')->options([
                'draft' => 'Draft', 'published' => 'Published',
            ])->required()->default('draft'),
            \Filament\Forms\Components\DateTimePicker::make('published_at'),
            \Filament\Forms\Components\Select::make('author_id')
                ->relationship('author','name')
                ->default(auth()->id())->required()->searchable(),
        ]),
        \Filament\Forms\Components\Section::make('Taxonomy')->columns(2)->schema([
            \Filament\Forms\Components\Select::make('categories')
                ->relationship('categories','name')
                ->multiple()->preload()->createOptionForm([
                    \Filament\Forms\Components\TextInput::make('name')->required(),
                ]),
            \Filament\Forms\Components\Select::make('tags')
                ->relationship('tags','name')
                ->multiple()->preload()->createOptionForm([
                    \Filament\Forms\Components\TextInput::make('name')->required(),
                ]),
        ]),
        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('cover')
            ->collection('cover')
            ->image()
            ->columnSpanFull(),
        \Filament\Forms\Components\Section::make('SEO')->columns(1)->schema([
            \Filament\Forms\Components\TextInput::make('seo_title'),
            \Filament\Forms\Components\Textarea::make('seo_description')->rows(2),
        ]),
    ]);
}
```

- [ ] **Step 5: Replace `table()`**

```php
public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
{
    return $table
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
            \Filament\Tables\Columns\TextColumn::make('author.name')->label('Author'),
            \Filament\Tables\Columns\BadgeColumn::make('status')->colors([
                'gray' => 'draft', 'success' => 'published',
            ]),
            \Filament\Tables\Columns\TextColumn::make('published_at')->dateTime()->sortable(),
        ])
        ->filters([
            \Filament\Tables\Filters\SelectFilter::make('status')->options(['draft'=>'Draft','published'=>'Published']),
            \Filament\Tables\Filters\SelectFilter::make('categories')->relationship('categories','name'),
        ])
        ->defaultSort('published_at','desc')
        ->actions([
            \Filament\Tables\Actions\EditAction::make(),
            \Filament\Tables\Actions\DeleteAction::make(),
        ]);
}
```

Append:

```php
protected static ?string $navigationGroup = 'Content';
protected static ?int $navigationSort = 10;
```

- [ ] **Step 6: `PostPolicy` with ownership ABAC**

`app/Policies/PostPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool { return $user->can('view_any_post'); }
    public function view(User $user, Post $p): bool { return $user->can('view_post'); }
    public function create(User $user): bool { return $user->can('create_post'); }

    public function update(User $user, Post $p): bool
    {
        if ($user->hasRole('Super-admin')) return true;
        if (! $user->can('update_post')) return false;
        if ($user->hasRole('Editor')) return $p->author_id === $user->id;
        return true;
    }

    public function delete(User $user, Post $p): bool
    {
        if ($user->hasRole('Super-admin')) return true;
        if (! $user->can('delete_post')) return false;
        if ($user->hasRole('Editor')) return $p->author_id === $user->id;
        return true;
    }

    public function deleteAny(User $user): bool { return $user->can('delete_any_post'); }
}
```

- [ ] **Step 7: Re-seed + pest**

```bash
php artisan migrate:fresh --seed
./vendor/bin/pest tests/Feature/Admin/PostResourceTest.php
./vendor/bin/pest
```

Expected: 3 in test; full suite 90 (87+3).

- [ ] **Step 8: Commit**

```bash
git add app/Filament/Resources/PostResource* app/Policies/PostPolicy.php tests/Feature/Admin/PostResourceTest.php
git commit -m "feat(admin): post resource with rich editor + ownership policy"
```

## Context

- `Post` model uses `published_at` cast + `scopePublished()` (Plan 1 Task 6).
- `categories` + `tags` are M:N via `post_category` + `post_tag` pivot tables.
- `createOptionForm` on Select lets admins create new categories/tags inline.

## Self-Review

- Resource + policy + tests pass

## Report

- Status, final lines, git show, concerns

---

## Phase M — Switch Public Views to DB-Driven Content

The seeders already populate Service/Industry/Crew/Portfolio/Post rows in the DB, and Plan 1 controllers ALREADY read from those models (HomeController, ServiceController, etc). So most public views are already DB-driven. This phase verifies that and surfaces any seed-fixture leakage (hardcoded strings in views that should bind to model attributes).

### Task 42: Audit public views for hardcoded content + media bindings

**Files to modify (audit pass):**
- `resources/views/home.blade.php`
- `resources/views/services/show.blade.php`
- `resources/views/industries/{index,show}.blade.php`
- `resources/views/portfolio/{index,show}.blade.php`
- `resources/views/blog/{index,show}.blade.php`
- `resources/views/crew/{index,show}.blade.php`
- `resources/views/contact.blade.php`

**Tests:**
- Create: `tests/Feature/Public/AdminEditedContentTest.php`

- [ ] **Step 1: Write failing test that proves DB-edited content surfaces on public site**

`tests/Feature/Public/AdminEditedContentTest.php`:

```php
<?php

use App\Models\Industry;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('edited service hero_copy appears on its public page', function () {
    $svc = Service::where('slug','photography')->first();
    $svc->update(['hero_copy' => 'Editor-set tagline ABC123']);

    $this->get('/services/photography')
        ->assertOk()
        ->assertSeeText('Editor-set tagline ABC123');
});

it('edited industry summary appears on its detail page', function () {
    $ind = Industry::where('slug','fashion')->first();
    $ind->update(['summary' => 'Edited industry summary XYZ789']);

    $this->get('/industries/fashion')
        ->assertOk()
        ->assertSeeText('Edited industry summary XYZ789');
});

it('edited portfolio title appears on public detail', function () {
    $p = Portfolio::published()->first();
    $p->update(['title' => 'Renamed Case Study QWERTY']);

    $this->get('/portfolio/'.$p->slug)
        ->assertOk()
        ->assertSeeText('Renamed Case Study QWERTY');
});

it('edited blog post body appears on its detail page', function () {
    $post = Post::published()->first();
    $post->update(['body' => '<p>Edited body content ZZZ123</p>']);

    $this->get('/blog/'.$post->slug)
        ->assertOk()
        ->assertSee('Edited body content ZZZ123');
});
```

- [ ] **Step 2: Run, expect — most should PASS**

```bash
./vendor/bin/pest tests/Feature/Public/AdminEditedContentTest.php
```

If any fail, the corresponding view has hardcoded content from the original design HTML. Trace to the file and replace the literal with the model attribute.

- [ ] **Step 3: Fix any failing assertions**

For each failure, open the Blade view and replace the hardcoded design text with the model attribute. Examples:
- `services/show.blade.php`: ensure `{{ $service->hero_copy }}` (not a literal sentence).
- `industries/show.blade.php`: `{{ $industry->summary }}`.
- `portfolio/show.blade.php`: `{{ $item->title }}`.
- `blog/show.blade.php`: `{!! $post->body !!}`.

If a view interleaves design copy with model output (e.g. "Capturing X, creating Y." with X+Y as variables), keep the design framing but ensure dynamic parts bind to model attrs.

- [ ] **Step 4: Add cover image rendering to relevant views**

Plan 1's `card-portfolio`, `card-post`, `card-crew` components already conditionally render `getFirstMediaUrl('cover'|'headshot')`. Verify:

```bash
grep -n "getFirstMediaUrl" resources/views/components/card-*.blade.php
```

If the show pages don't render hero media, add:

`services/show.blade.php` near the `<h1>{{ $service->title }}</h1>` block:

```blade
@if ($hero = $service->getFirstMediaUrl('hero'))
    <img src="{{ $hero }}" alt="" class="service-hero-img">
@endif
```

Same pattern for industries/show, crew/show, portfolio/show (with `cover` collection), blog/show (with `cover` collection).

- [ ] **Step 5: Run pest**

```bash
./vendor/bin/pest tests/Feature/Public/AdminEditedContentTest.php
./vendor/bin/pest
```

Expected: 4 in the new test; full suite 94 (90+4).

- [ ] **Step 6: Commit**

```bash
git add resources/views tests/Feature/Public/AdminEditedContentTest.php
git commit -m "feat(public): bind public views to admin-edited content + media"
```

## Context

- Plan 1 wrote controllers that pass `$service`, `$industry`, `$item`, `$post`, `$member` into views. The views already use `{{ $obj->attr }}` in most places.
- This task ensures NO design literal masquerades as DB content. Any failing assertion identifies a leak.

## Self-Review

- 4 assertions in the new test pass
- Public pages render media when present
- pest 94 green

## Report

- Status
- Step 2 result (count failed)
- Which view files needed fixes
- final lines, git show, concerns

---

## Phase N — SEO Infrastructure

### Task 43: `sitemap.xml` generator + scheduled command

**Files:**
- Create: `app/Console/Commands/GenerateSitemap.php`
- Modify: `routes/console.php`
- Create: `tests/Feature/Seo/SitemapTest.php`

- [ ] **Step 1: Verify `spatie/laravel-sitemap` is installed**

```bash
cd /Users/Project/Personal/thelastclicks
composer show spatie/laravel-sitemap 2>&1 | grep -i version || composer require spatie/laravel-sitemap:^7
```

- [ ] **Step 2: Write failing test**

`tests/Feature/Seo/SitemapTest.php`:

```php
<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('generates a sitemap.xml file with public urls', function () {
    \Illuminate\Support\Facades\Artisan::call('sitemap:generate');

    $path = public_path('sitemap.xml');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)
        ->toContain('<urlset')
        ->toContain('<loc>'.url('/').'</loc>')
        ->toContain('<loc>'.url('/contact').'</loc>')
        ->toContain('<loc>'.url('/services/photography').'</loc>')
        ->toContain('<loc>'.url('/blog').'</loc>');

    @unlink($path);
});
```

- [ ] **Step 3: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Seo/SitemapTest.php
```

- [ ] **Step 4: Create the command**

`app/Console/Commands/GenerateSitemap.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\Industry;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate public/sitemap.xml';

    public function handle(): int
    {
        $sitemap = Sitemap::create();

        $statics = [
            '/', '/about', '/our-process', '/portfolio', '/blog', '/crew', '/industries',
            '/contact', '/privacy-policy', '/terms-of-service', '/cookie-policy', '/disclaimer',
        ];
        foreach ($statics as $path) {
            $sitemap->add(Url::create(url($path))->setLastModificationDate(now()));
        }

        foreach (Service::all() as $svc) {
            $sitemap->add(Url::create(url('/services/'.$svc->slug))->setLastModificationDate($svc->updated_at));
        }
        foreach (Industry::all() as $ind) {
            $sitemap->add(Url::create(url('/industries/'.$ind->slug))->setLastModificationDate($ind->updated_at));
        }
        foreach (Portfolio::published()->get() as $p) {
            $sitemap->add(Url::create(url('/portfolio/'.$p->slug))->setLastModificationDate($p->updated_at));
        }
        foreach (Post::published()->get() as $p) {
            $sitemap->add(Url::create(url('/blog/'.$p->slug))->setLastModificationDate($p->updated_at));
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));
        $this->info('sitemap.xml generated');
        return self::SUCCESS;
    }
}
```

- [ ] **Step 5: Schedule it weekly**

Edit `routes/console.php`. Append:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('sitemap:generate')->weekly();
```

- [ ] **Step 6: Run pest**

```bash
./vendor/bin/pest tests/Feature/Seo/SitemapTest.php
./vendor/bin/pest
```

Expected: 1 in test; full suite 95 (94+1).

- [ ] **Step 7: Commit**

```bash
git add app/Console/Commands/GenerateSitemap.php routes/console.php tests/Feature/Seo/SitemapTest.php
git commit -m "feat(seo): sitemap.xml generator + weekly schedule"
```

## Context

- Spatie's Sitemap package writes XML-compliant sitemap files.
- Public path is `public/sitemap.xml` → served at `https://thelastclicks.com/sitemap.xml`.
- Schedule runs weekly via Laravel's scheduler (needs a cron entry calling `php artisan schedule:run` every minute on the server).

## Self-Review

- Command exists, schedule registered, test passes

## Report

- Status, final lines, git show, concerns

---

### Task 44: response-cache + invalidation observers

**Files:**
- Modify: `app/Providers/AppServiceProvider.php` (register observers + responsecache routes)
- Create: `app/Observers/PostObserver.php`
- Create: `app/Observers/PortfolioObserver.php`
- Create: `app/Observers/ServiceObserver.php`
- Create: `app/Observers/IndustryObserver.php`
- Create: `app/Observers/CrewObserver.php`
- Create: `app/Observers/SiteSettingObserver.php`
- Create: `tests/Feature/Seo/ResponseCacheInvalidationTest.php`

- [ ] **Step 1: Verify `spatie/laravel-responsecache` installed**

```bash
composer show spatie/laravel-responsecache 2>&1 | grep -i version || composer require spatie/laravel-responsecache:^7
php artisan vendor:publish --provider="Spatie\ResponseCache\ResponseCacheServiceProvider"
```

- [ ] **Step 2: Wire response-cache middleware to public routes only**

Edit `bootstrap/app.php`. In the `withMiddleware()` closure, add a middleware alias:

```php
->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware) {
    $middleware->alias([
        'cacheResponse' => \Spatie\ResponseCache\Middlewares\CacheResponse::class,
    ]);
})
```

Then wrap public GET routes. Edit `routes/web.php` and group all current public routes under `Route::middleware('cacheResponse')->group(function () { ... })` EXCEPT the `POST /contact` route (mutations must not be cached).

The cleanest move is to add `->middleware('cacheResponse')` to each `Route::get` definition individually, OR wrap them in a `Route::middleware('cacheResponse')->group(...)`.

- [ ] **Step 3: Write failing test**

`tests/Feature/Seo/ResponseCacheInvalidationTest.php`:

```php
<?php

use App\Models\Post;
use Spatie\ResponseCache\Facades\ResponseCache;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    ResponseCache::clear();
});

it('post update flushes response cache', function () {
    // first hit populates cache
    $post = Post::published()->first();
    $this->get('/blog')->assertOk();
    expect(ResponseCache::hasBeenCached(\request()))->toBeFalse(); // request was just made, not a hit

    // update post → observer must flush
    $post->update(['title' => 'Updated Title XYZ123']);

    // next hit should reflect update
    $this->get('/blog')->assertOk()->assertSee('Updated Title XYZ123');
});

it('site setting update flushes cache', function () {
    \App\Models\SiteSetting::set('contact_email', 'new@email.com');
    $this->get('/contact')->assertOk()->assertSee('new@email.com');
});
```

- [ ] **Step 4: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Seo/ResponseCacheInvalidationTest.php
```

- [ ] **Step 5: Create observers**

For each of Post, Portfolio, Service, Industry, Crew, SiteSetting — create `app/Observers/<Model>Observer.php`:

`app/Observers/PostObserver.php`:

```php
<?php

namespace App\Observers;

use App\Models\Post;
use Spatie\ResponseCache\Facades\ResponseCache;

class PostObserver
{
    public function saved(Post $post): void { ResponseCache::clear(); }
    public function deleted(Post $post): void { ResponseCache::clear(); }
}
```

Repeat the same pattern for PortfolioObserver, ServiceObserver, IndustryObserver, CrewObserver, SiteSettingObserver (substituting the model + import).

`app/Observers/PortfolioObserver.php`:

```php
<?php

namespace App\Observers;

use App\Models\Portfolio;
use Spatie\ResponseCache\Facades\ResponseCache;

class PortfolioObserver
{
    public function saved(Portfolio $p): void { ResponseCache::clear(); }
    public function deleted(Portfolio $p): void { ResponseCache::clear(); }
}
```

`app/Observers/ServiceObserver.php`:

```php
<?php

namespace App\Observers;

use App\Models\Service;
use Spatie\ResponseCache\Facades\ResponseCache;

class ServiceObserver
{
    public function saved(Service $s): void { ResponseCache::clear(); }
    public function deleted(Service $s): void { ResponseCache::clear(); }
}
```

`app/Observers/IndustryObserver.php`:

```php
<?php

namespace App\Observers;

use App\Models\Industry;
use Spatie\ResponseCache\Facades\ResponseCache;

class IndustryObserver
{
    public function saved(Industry $i): void { ResponseCache::clear(); }
    public function deleted(Industry $i): void { ResponseCache::clear(); }
}
```

`app/Observers/CrewObserver.php`:

```php
<?php

namespace App\Observers;

use App\Models\Crew;
use Spatie\ResponseCache\Facades\ResponseCache;

class CrewObserver
{
    public function saved(Crew $c): void { ResponseCache::clear(); }
    public function deleted(Crew $c): void { ResponseCache::clear(); }
}
```

`app/Observers/SiteSettingObserver.php`:

```php
<?php

namespace App\Observers;

use App\Models\SiteSetting;
use Spatie\ResponseCache\Facades\ResponseCache;

class SiteSettingObserver
{
    public function saved(SiteSetting $s): void { ResponseCache::clear(); }
    public function deleted(SiteSetting $s): void { ResponseCache::clear(); }
}
```

- [ ] **Step 6: Register observers in `AppServiceProvider::boot()`**

Edit `app/Providers/AppServiceProvider.php`. Add to `boot()`:

```php
\App\Models\Post::observe(\App\Observers\PostObserver::class);
\App\Models\Portfolio::observe(\App\Observers\PortfolioObserver::class);
\App\Models\Service::observe(\App\Observers\ServiceObserver::class);
\App\Models\Industry::observe(\App\Observers\IndustryObserver::class);
\App\Models\Crew::observe(\App\Observers\CrewObserver::class);
\App\Models\SiteSetting::observe(\App\Observers\SiteSettingObserver::class);
```

- [ ] **Step 7: Run pest**

```bash
./vendor/bin/pest tests/Feature/Seo/ResponseCacheInvalidationTest.php
./vendor/bin/pest
```

Expected: 2 in test; full suite 97 (95+2).

- [ ] **Step 8: Commit**

```bash
git add app/Observers app/Providers/AppServiceProvider.php bootstrap/app.php routes/web.php tests/Feature/Seo/ResponseCacheInvalidationTest.php config/responsecache.php
git commit -m "feat(seo): response cache + observers for invalidation"
```

## Context

- `spatie/laravel-responsecache` caches full HTTP response bodies keyed by URL. Subsequent identical requests skip Laravel routing/Blade entirely.
- `ResponseCache::clear()` flushes the entire cache. For a small site this is fine; for a big site you'd clear by tag.
- Observers fire on `saved` (covers create + update) and `deleted`. That's the right granularity.

## Specific concerns

1. **Cache key includes URL but NOT auth state** — public site has no auth, so no leakage risk.
2. **Don't cache POST/PUT/DELETE** — Filament admin routes are POST/Livewire; the `cacheResponse` middleware only applies to GET responses by default but the explicit middleware-on-routes approach scoping to public routes is safer.
3. **In tests, `RefreshDatabase` rolls back but the cache driver state persists** — `ResponseCache::clear()` in `beforeEach` resets it.

## Self-Review

- 6 observer files + AppServiceProvider wiring + responsecache config
- 2 tests pass
- pest 97 green

## Report

- Status, final lines, git show, concerns (especially anything about middleware wiring breaking other routes)

---

## Phase O — Deploy Infrastructure

### Task 45: Queue worker config (Horizon) + `.env` defaults

**Files:**
- Modify: `.env` + `.env.example`
- Modify: `config/queue.php` (if needed)
- Create: `docs/DEPLOYMENT.md` (initial draft, expanded in Task 49)

- [ ] **Step 1: Switch dev `.env` to `QUEUE_CONNECTION=sync` for now**

Edit `/Users/Project/Personal/thelastclicks/.env`:

```env
QUEUE_CONNECTION=sync
```

Set the same in `.env.example`. This makes dev usage frictionless — `Mail::queue()` runs synchronously. Production uses `database` or `redis` driver + a real queue worker.

- [ ] **Step 2: Decide on Horizon vs `queue:work`**

For Plan 3, ship the `queue:work` recipe. Horizon adds dashboard UI but isn't strictly required. Document the option in DEPLOYMENT.md.

Create `docs/DEPLOYMENT.md` (initial sections — expanded in Task 49):

```markdown
# Deployment

## Environment

Required env vars beyond Laravel defaults:
- `ADMIN_SEED_EMAIL`, `ADMIN_SEED_PASSWORD` — for initial Super-admin user
- `QUEUE_CONNECTION=database` (or `redis` for higher throughput)
- `MAIL_MAILER=smtp` (or `resend` once driver is configured)
- `SENTRY_LARAVEL_DSN` — error tracking
- `RESPONSE_CACHE_ENABLED=true`

## Queue worker

After deploy, start a worker:

\`\`\`bash
php artisan queue:work --tries=3 --max-time=3600
\`\`\`

On Forge/Ploi/Supervisor, configure as a daemon. Example supervisor config:

\`\`\`ini
[program:thelastclicks-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/forge/thelastclicks.com/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2
user=forge
\`\`\`

## Scheduler

Add a system cron entry:

\`\`\`cron
* * * * * cd /home/forge/thelastclicks.com && php artisan schedule:run >> /dev/null 2>&1
\`\`\`

This drives `sitemap:generate` (weekly) and `backup:run` (daily).
```

- [ ] **Step 3: Confirm pest still green**

```bash
./vendor/bin/pest
```

Expected: 97 passing.

- [ ] **Step 4: Commit**

```bash
git add .env.example docs/DEPLOYMENT.md
git commit -m "chore(deploy): default dev queue to sync, deployment.md draft"
```

(Note: `.env` is gitignored, only `.env.example` is committed.)

## Self-Review

- `.env` switched to sync for dev
- DEPLOYMENT.md exists with queue + scheduler recipe
- pest still 97

## Report

- Status, pest final line, git show, concerns

---

### Task 46: Backup config (`spatie/laravel-backup`)

**Files:**
- Modify: `composer.json` (require backup package)
- Create: `config/backup.php` (publish)
- Modify: `routes/console.php` (schedule)
- Create: `tests/Feature/Deploy/BackupConfigTest.php`

- [ ] **Step 1: Install package**

```bash
cd /Users/Project/Personal/thelastclicks
composer require spatie/laravel-backup:^9
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

- [ ] **Step 2: Write smoke test**

`tests/Feature/Deploy/BackupConfigTest.php`:

```php
<?php

it('backup config publishes and lists thelastclicks as application name', function () {
    expect(file_exists(config_path('backup.php')))->toBeTrue();
    config(['backup.backup.name' => 'TheLastClicks']);
    expect(config('backup.backup.name'))->toBe('TheLastClicks');
});

it('backup:run --only-files dry-runs without error', function () {
    // dry-run check: command exists and is registered
    expect(\Illuminate\Support\Facades\Artisan::all())->toHaveKey('backup:run');
});
```

- [ ] **Step 3: Configure `config/backup.php`**

Edit `config/backup.php`. Update key values:

```php
'name' => env('APP_NAME', 'TheLastClicks'),

'source' => [
    'files' => [
        'include' => [
            base_path('storage/app/public'),  // uploaded media
        ],
    ],
    'databases' => [
        'mysql',
    ],
],

'destination' => [
    'disks' => [
        'local',
    ],
],

'notifications' => [
    'mail' => [
        'to' => env('BACKUP_NOTIFY_EMAIL', 'admin@thelastclicks.com'),
    ],
],
```

For production, change `destination.disks` to `['s3']` or `['r2']` after wiring those filesystems in `config/filesystems.php`. Document this in DEPLOYMENT.md (next task).

- [ ] **Step 4: Schedule backup daily**

Edit `routes/console.php`. Append:

```php
Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');
```

- [ ] **Step 5: Run pest**

```bash
./vendor/bin/pest tests/Feature/Deploy/BackupConfigTest.php
./vendor/bin/pest
```

Expected: 2 in test; full suite 99 (97+2).

- [ ] **Step 6: Commit**

```bash
git add composer.json composer.lock config/backup.php routes/console.php tests/Feature/Deploy/BackupConfigTest.php
git commit -m "feat(deploy): laravel-backup config + daily schedule"
```

## Context

- `spatie/laravel-backup` zips files + dumps MySQL via `mysqldump`. `mysqldump` must be on the production server's PATH.
- Local disk storage is fine for v1; deploy moves to S3/R2.

## Self-Review

- Package installed, config customised, schedule registered, tests pass

## Report

- Status, final lines, git show, concerns (especially about mysqldump availability)

---

### Task 47: Sentry integration

**Files:**
- Modify: `composer.json` (sentry/sentry-laravel)
- Modify: `bootstrap/app.php` or `config/sentry.php` (wire)
- Modify: `.env.example`
- Create: `tests/Feature/Deploy/SentryConfigTest.php`

- [ ] **Step 1: Install**

```bash
cd /Users/Project/Personal/thelastclicks
composer require sentry/sentry-laravel:^4
php artisan vendor:publish --tag=sentry-config
```

- [ ] **Step 2: Write smoke test**

`tests/Feature/Deploy/SentryConfigTest.php`:

```php
<?php

it('sentry config publishes', function () {
    expect(file_exists(config_path('sentry.php')))->toBeTrue();
});

it('sentry DSN env var is recognised', function () {
    config(['sentry.dsn' => 'https://example@sentry.io/123']);
    expect(config('sentry.dsn'))->toBe('https://example@sentry.io/123');
});
```

- [ ] **Step 3: Add env vars to `.env.example`**

Append:

```env
SENTRY_LARAVEL_DSN=
SENTRY_TRACES_SAMPLE_RATE=0.1
```

In real `.env` for dev, leave `SENTRY_LARAVEL_DSN` empty (so Sentry no-ops). Production sets the actual DSN.

- [ ] **Step 4: Run pest**

```bash
./vendor/bin/pest tests/Feature/Deploy/SentryConfigTest.php
./vendor/bin/pest
```

Expected: 2 in test; full suite 101 (99+2).

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.lock config/sentry.php .env.example tests/Feature/Deploy/SentryConfigTest.php
git commit -m "feat(deploy): sentry/sentry-laravel integration"
```

## Self-Review

- Package installed, config published, env vars documented, tests pass

## Report

- Status, final lines, git show, concerns

---

### Task 48: CI workflow (GitHub Actions)

**Files:**
- Create: `.github/workflows/ci.yml`

- [ ] **Step 1: Write the workflow**

`.github/workflows/ci.yml`:

```yaml
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-24.04

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: thelastclicks_test
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping -uroot -proot"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=10

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo_mysql, intl, gd, zip
          coverage: none

      - name: Cache composer
        uses: actions/cache@v4
        with:
          path: vendor
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}

      - name: Composer install
        run: composer install --no-progress --prefer-dist --optimize-autoloader

      - name: Cache npm
        uses: actions/cache@v4
        with:
          path: node_modules
          key: ${{ runner.os }}-node-${{ hashFiles('**/package-lock.json') }}

      - name: npm install + build
        run: |
          npm install
          npm run build

      - name: Configure .env
        run: |
          cp .env.example .env
          php artisan key:generate
          sed -i 's/DB_DATABASE=thelastclicks/DB_DATABASE=thelastclicks_test/' .env
          sed -i 's/DB_PASSWORD=/DB_PASSWORD=root/' .env

      - name: Migrate
        run: php artisan migrate --force

      - name: Pint (format check)
        run: ./vendor/bin/pint --test

      - name: PHPStan
        run: ./vendor/bin/phpstan analyse --memory-limit=512M --no-progress

      - name: Pest
        run: ./vendor/bin/pest --no-coverage
```

- [ ] **Step 2: Verify locally that all three commands pass before pushing**

```bash
cd /Users/Project/Personal/thelastclicks
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
./vendor/bin/pest
```

If any fail, fix before commit.

- [ ] **Step 3: Commit**

```bash
git add .github/workflows/ci.yml
git commit -m "ci: github actions workflow (mysql + php 8.3 + pest/pint/phpstan)"
```

## Context

- CI runs on push to main + PRs. Spins up MySQL 8.0 service container, sets up PHP 8.3, runs the same QA gate locally.
- Doesn't auto-deploy — that's a Forge webhook step the user wires in their hosting panel.

## Self-Review

- Workflow exists, all 3 CI commands pass locally
- Single commit

## Report

- Status, output of local `pint --test`, `phpstan analyse`, `pest` (verify clean before commit), git show, concerns

---

### Task 49: Finalize `docs/DEPLOYMENT.md`

**Files:**
- Modify: `docs/DEPLOYMENT.md`

- [ ] **Step 1: Expand DEPLOYMENT.md with full deployment recipe**

Append to existing `docs/DEPLOYMENT.md`:

```markdown
## Hosting target

Recommended: Laravel Forge or Ploi on a VPS (DigitalOcean / Hetzner). Nginx + PHP-FPM 8.3 + MySQL 8 + Redis.

## Provisioning checklist

1. Provision Ubuntu 24.04 VPS (1 vCPU / 2GB RAM minimum; 2 vCPU / 4GB for prod load).
2. Forge connects, installs PHP 8.3, MySQL 8, Redis, Composer, Node 22, Nginx.
3. Set up site pointing to the repo. Branch: `main`.
4. Configure `.env` per the `.env.example` keys. Required:
   - `APP_KEY` (generate via `php artisan key:generate`)
   - `APP_URL=https://thelastclicks.com`
   - `DB_*` connecting to managed MySQL (or local if VPS-only)
   - `MAIL_MAILER=smtp` + `MAIL_HOST`/`MAIL_USERNAME`/`MAIL_PASSWORD` (or `resend` driver)
   - `QUEUE_CONNECTION=database` (or `redis`)
   - `SENTRY_LARAVEL_DSN=<from-sentry.io>`
   - `ADMIN_SEED_EMAIL` + `ADMIN_SEED_PASSWORD` (change after first login)
5. Run initial deploy script:

\`\`\`bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
npm install
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
\`\`\`

6. Add the supervisor config for `queue:work` (see top of this file).
7. Add the system cron entry (see top).
8. Configure SSL via Let's Encrypt (Forge button).
9. Point DNS A record at the VPS IP.
10. First login: `/admin/login` with the seeded credentials. CHANGE PASSWORD.

## CI/CD

GitHub Actions runs pest + pint + phpstan on every PR. Forge auto-deploys on push to `main` via Forge webhook (configure in Forge → Site → Auto deploy).

## Monitoring

- **Errors:** Sentry (configured via `SENTRY_LARAVEL_DSN`).
- **Uptime:** Better Stack hitting `/up` (Laravel 11 built-in health endpoint).
- **Logs:** `storage/logs/laravel.log` — rotate via daily channel or ship to Logtail.

## Storage migration to S3/R2 (future)

Currently `FILESYSTEM_DISK=public` (local). To switch:

1. Add S3/R2 credentials to `.env`:

\`\`\`env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=auto
AWS_BUCKET=thelastclicks
AWS_ENDPOINT=https://<account>.r2.cloudflarestorage.com
AWS_URL=https://media.thelastclicks.com
\`\`\`

2. Migrate existing media: `php artisan media-library:cloud-migrate` (or manual rsync to bucket).
3. Update `config/backup.php` `destination.disks` to `['s3']`.
```

- [ ] **Step 2: Commit**

```bash
git add docs/DEPLOYMENT.md
git commit -m "docs(deploy): full deployment recipe in DEPLOYMENT.md"
```

## Report

- Status, DEPLOYMENT.md word count or section list, git show

---

## Phase P — Final QA

### Task 50: Plan 3 QA gate

- [ ] **Step 1: Full pest**

```bash
cd /Users/Project/Personal/thelastclicks
./vendor/bin/pest
```

Expected: 101 passing. If anything fails, debug + fix.

- [ ] **Step 2: Pint**

```bash
./vendor/bin/pint
```

Auto-fix and re-run pest.

- [ ] **Step 3: PHPStan**

```bash
./vendor/bin/phpstan analyse --memory-limit=512M
```

Expected: 0 errors. Filament adds many typed classes — most fall in already.

- [ ] **Step 4: Headless smoke — full lap**

```bash
php artisan migrate:fresh --seed
npm run build
php artisan serve --port=8000 > /tmp/serve.log 2>&1 &
SERVE_PID=$!
sleep 3

# All public routes still 200
for url in / /about /our-process /portfolio /blog /crew /contact /industries /services/photography; do
  echo -n "$url: "
  curl -s -o /dev/null -w "%{http_code}\n" "http://localhost:8000$url"
done

# Admin login renders
curl -s -o /dev/null -w "/admin/login: %{http_code}\n" http://localhost:8000/admin/login

# Sitemap regenerates
php artisan sitemap:generate
ls -la public/sitemap.xml && grep -c '<loc>' public/sitemap.xml

# Cache invalidation: edit a post, hit /blog twice, see edit
php artisan tinker --execute='
  $p = \App\Models\Post::published()->first();
  $p->update(["title" => "Cache-bust test " . time()]);
  echo "updated post id={$p->id}";
'
curl -s http://localhost:8000/blog | grep -c "Cache-bust test" || echo "miss"

kill $SERVE_PID 2>/dev/null || true
```

Expected: every public route 200, /admin/login 200, sitemap.xml has many `<loc>` entries, /blog shows the edited title.

- [ ] **Step 5: Final pest**

```bash
./vendor/bin/pest
```

Expected: 101 passing.

- [ ] **Step 6: Commit any cleanup**

```bash
git status
git add -A && git commit -m "chore: plan 3 QA pass" || echo "nothing to commit"
```

- [ ] **Step 7: Print final summary**

Report:
- pest count (101)
- Pint result
- PHPStan result
- Smoke test summary
- Git log of last 16 commits (Plan 3 work)
- Total state: pest count, lines of code added across Plan 3

## Self-Review

- pest 101 green
- Pint clean
- PHPStan 0 errors
- Smoke test passes
- Single final commit (or nothing to commit)

## Report

- Status: DONE | DONE_WITH_CONCERNS | BLOCKED
- Final stats
- Plan 3 commit list
- Concerns

---

## Self-Review (Spec coverage check)

| Spec § | Requirement | Plan task |
|---|---|---|
| §4.5 | sitemap.xml | Task 43 |
| §4.5 | response cache + invalidation | Task 44 |
| §4.5 | per-page JSON-LD | Plan 1 (already shipped) |
| §5.2 | PostResource | Task 41 |
| §5.2 | PortfolioResource | Task 40 |
| §5.2 | ServiceResource | Task 36 |
| §5.2 | IndustryResource | Task 37 |
| §5.2 | CrewResource | Task 38 |
| §5.2 | CategoryResource / TagResource | Task 39 |
| §5.2 | UserResource / RoleResource | Plan 2 (UserResource); RoleResource is shield-provided (Plan 2 Task 25) |
| §5.2 | SiteSettingsPage | Plan 2 Task 34 |
| §6.1 | spatie/laravel-sitemap | Task 43 |
| §6.1 | spatie/laravel-responsecache | Task 44 |
| §6.1 | spatie/laravel-backup | Task 46 |
| §6.1 | sentry/sentry-laravel | Task 47 |
| §7 | Pest coverage per resource + policy | Tasks 36-41 each ship tests |
| §8 | CI workflow | Task 48 |
| §8 | Deployment recipe | Tasks 45 + 49 |
| §8 | Backup strategy | Task 46 |
| §8 | Monitoring (Sentry, /up) | Task 47 |
| Out-of-scope explicit | Filament login styling polish, Quote reply tab, Quote kanban, bulk-assign | Deferred — flagged in Plan 2 self-review |

**Total new test count (Plan 3 estimate):** ~28 new pest cases.
**Total pest at Plan 3 close:** 101 (73 + 28).
**Tasks:** 15 (36-50).
**Phases:** K (simple resources), L (rich resources w/ media), M (DB-driven public switch), N (SEO), O (deploy infra), P (final QA).

After Plan 3, TheLastClicks is feature-complete per the spec: public site is fully editable through Filament admin, RBAC + ownership ABAC enforced, leads flow through QuoteResource workflow, SEO infra in place, deploy-ready with CI + backups + monitoring docs.
