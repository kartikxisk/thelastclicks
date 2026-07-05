# Work Categories & Testimonials Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restructure the site around 7 real industries containing 22 work categories, add portfolio category filtering, and make testimonials DB-backed and admin-managed.

**Architecture:** Two new tables (`work_categories`, `testimonials`) hanging off the existing `industries` table; a nullable `work_category_id` FK on `portfolios`. Filament resources follow the existing `IndustryResource` pattern. Frontend reworks the existing client-side chip filter on `/portfolio` and loops DB testimonials on home + industry pages.

**Tech Stack:** PHP 8.2, Laravel 11, Filament 3, Spatie sluggable/medialibrary/permission + filament-shield, Pest 3, Livewire test helpers.

**Spec:** `docs/superpowers/specs/2026-07-05-work-categories-testimonials-design.md`

## Global Constraints

- Tests: Pest, `uses(RefreshDatabase::class)` + `beforeEach(fn () => $this->seed())` for Public/Seeder tests (see `tests/Feature/Public/PortfolioPageTest.php`).
- Before every commit: `vendor/bin/pint --dirty` and run the touched test files.
- After all tasks: `php artisan test` (full) and `vendor/bin/phpstan analyse` must pass.
- Slugs: Spatie sluggable generates from `title`; seeders pass explicit slugs via `updateOrCreate(['slug' => …])`.
- Models declare `$fillable`, docblock relation return types (match `app/Models/Portfolio.php` style — larastan enforced).
- Baseline note: `tests/Feature/Public/IndustryPageTest.php` currently expects `/industries/fashion` which does not match the currently seeded slugs. Run `php artisan test` FIRST to record the baseline; do not chase pre-existing failures outside the files this plan touches, but Task 4 fixes this one.

---

### Task 1: `work_categories` table, model, factory, Industry relation

**Files:**
- Create: `database/migrations/2026_07_05_000001_create_work_categories_table.php`
- Create: `app/Models/WorkCategory.php`
- Create: `database/factories/WorkCategoryFactory.php`
- Modify: `app/Models/Industry.php` (add `workCategories()`)
- Test: `tests/Feature/Models/WorkCategoryTest.php`

**Interfaces:**
- Produces: `WorkCategory` model (`industry_id`, `title`, `slug`, `order` fillable), `WorkCategory::industry(): BelongsTo`, `Industry::workCategories(): HasMany` ordered by `order`.

- [ ] **Step 1: Write the failing test**

```php
<?php

use App\Models\Industry;
use App\Models\WorkCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates a slug from the title', function () {
    $cat = WorkCategory::factory()->create(['title' => 'Fashion Show']);
    expect($cat->slug)->toBe('fashion-show');
});

it('belongs to an industry and lists ordered categories', function () {
    $industry = Industry::factory()->create();
    WorkCategory::factory()->create(['industry_id' => $industry->id, 'title' => 'B cat', 'order' => 2]);
    WorkCategory::factory()->create(['industry_id' => $industry->id, 'title' => 'A cat', 'order' => 1]);

    expect($industry->workCategories()->pluck('title')->all())->toBe(['A cat', 'B cat'])
        ->and(WorkCategory::first()->industry)->toBeInstanceOf(Industry::class);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Models/WorkCategoryTest.php`
Expected: FAIL — `Class "App\Models\WorkCategory" not found`.

- [ ] **Step 3: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('industry_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->unsignedSmallInteger('order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_categories');
    }
};
```

- [ ] **Step 4: Create model**

```php
<?php

namespace App\Models;

use Database\Factories\WorkCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class WorkCategory extends Model
{
    /** @use HasFactory<WorkCategoryFactory> */
    use HasFactory, HasSlug;

    protected $fillable = ['industry_id', 'title', 'slug', 'order'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }

    /** @return BelongsTo<Industry, $this> */
    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }
}
```

- [ ] **Step 5: Create factory**

```php
<?php

namespace Database\Factories;

use App\Models\Industry;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'industry_id' => Industry::factory(),
            'title' => fake()->unique()->words(2, true),
            'order' => 0,
        ];
    }
}
```

- [ ] **Step 6: Add relation to Industry**

In `app/Models/Industry.php` add import `Illuminate\Database\Eloquent\Relations\HasMany` and method:

```php
    /** @return HasMany<WorkCategory, $this> */
    public function workCategories(): HasMany
    {
        return $this->hasMany(WorkCategory::class)->orderBy('order');
    }
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test tests/Feature/Models/WorkCategoryTest.php`
Expected: PASS (2 tests).

- [ ] **Step 8: Pint + commit**

```bash
vendor/bin/pint --dirty
git add -A && git commit -m "feat: add WorkCategory model under Industry"
```

---

### Task 2: `portfolios.work_category_id` + Portfolio/WorkCategory relations

**Files:**
- Create: `database/migrations/2026_07_05_000002_add_work_category_to_portfolios.php`
- Modify: `app/Models/Portfolio.php` (fillable + relation)
- Modify: `app/Models/WorkCategory.php` (add `portfolios()`)
- Test: `tests/Feature/Models/WorkCategoryTest.php` (append)

**Interfaces:**
- Consumes: `WorkCategory` from Task 1.
- Produces: `Portfolio::workCategory(): BelongsTo` (nullable FK `work_category_id`, nullOnDelete), `WorkCategory::portfolios(): HasMany`.

- [ ] **Step 1: Append failing test**

```php
it('portfolio belongs to a work category, nulled on category delete', function () {
    $cat = WorkCategory::factory()->create();
    $p = \App\Models\Portfolio::factory()->create(['work_category_id' => $cat->id, 'industry_id' => $cat->industry_id]);

    expect($p->workCategory->id)->toBe($cat->id)
        ->and($cat->portfolios()->count())->toBe(1);

    $cat->delete();
    expect($p->fresh()->work_category_id)->toBeNull();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Models/WorkCategoryTest.php`
Expected: FAIL — no column `work_category_id`.

- [ ] **Step 3: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $t) {
            $t->foreignId('work_category_id')->nullable()->after('industry_id')
                ->constrained('work_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $t) {
            $t->dropConstrainedForeignId('work_category_id');
        });
    }
};
```

- [ ] **Step 4: Wire relations**

`app/Models/Portfolio.php`: add `'work_category_id'` to `$fillable` (after `'industry_id'`) and:

```php
    /** @return BelongsTo<WorkCategory, $this> */
    public function workCategory(): BelongsTo
    {
        return $this->belongsTo(WorkCategory::class);
    }
```

`app/Models/WorkCategory.php`: add import `Illuminate\Database\Eloquent\Relations\HasMany` and:

```php
    /** @return HasMany<Portfolio, $this> */
    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test tests/Feature/Models/WorkCategoryTest.php`
Expected: PASS (3 tests).

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty
git add -A && git commit -m "feat: link portfolios to work categories"
```

---

### Task 3: `testimonials` table, model, factory

**Files:**
- Create: `database/migrations/2026_07_05_000003_create_testimonials_table.php`
- Create: `app/Models/Testimonial.php`
- Create: `database/factories/TestimonialFactory.php`
- Modify: `app/Models/Industry.php` (add `testimonials()`)
- Test: `tests/Feature/Models/TestimonialTest.php`

**Interfaces:**
- Produces: `Testimonial` model — fillable `industry_id, quote, client_name, role_company, order, is_published`; `Testimonial::published()` scope; `Testimonial::industry(): BelongsTo`; `Industry::testimonials(): HasMany` ordered by `order`.

- [ ] **Step 1: Write the failing test**

```php
<?php

use App\Models\Industry;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('published scope excludes unpublished rows', function () {
    Testimonial::factory()->create(['is_published' => false, 'client_name' => 'Hidden']);
    Testimonial::factory()->create(['is_published' => true, 'client_name' => 'Shown']);

    expect(Testimonial::published()->pluck('client_name')->all())->toBe(['Shown']);
});

it('optionally belongs to an industry, nulled on industry delete', function () {
    $industry = Industry::factory()->create();
    $t = Testimonial::factory()->create(['industry_id' => $industry->id]);

    expect($industry->testimonials()->count())->toBe(1);

    $industry->delete();
    expect($t->fresh()->industry_id)->toBeNull();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Models/TestimonialTest.php`
Expected: FAIL — `Class "App\Models\Testimonial" not found`.

- [ ] **Step 3: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->foreignId('industry_id')->nullable()->constrained()->nullOnDelete();
            $t->text('quote');
            $t->string('client_name');
            $t->string('role_company')->nullable();
            $t->unsignedSmallInteger('order')->default(0);
            $t->boolean('is_published')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
```

- [ ] **Step 4: Create model**

```php
<?php

namespace App\Models;

use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    protected $fillable = ['industry_id', 'quote', 'client_name', 'role_company', 'order', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];

    /** @param Builder<Testimonial> $q
     * @return Builder<Testimonial>
     */
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    /** @return BelongsTo<Industry, $this> */
    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }
}
```

- [ ] **Step 5: Create factory**

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'industry_id' => null,
            'quote' => fake()->sentence(12),
            'client_name' => fake()->name(),
            'role_company' => fake()->jobTitle().', '.fake()->company(),
            'order' => 0,
            'is_published' => true,
        ];
    }
}
```

- [ ] **Step 6: Add relation to Industry**

```php
    /** @return HasMany<Testimonial, $this> */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class)->orderBy('order');
    }
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test tests/Feature/Models/TestimonialTest.php`
Expected: PASS (2 tests).

- [ ] **Step 8: Pint + commit**

```bash
vendor/bin/pint --dirty
git add -A && git commit -m "feat: add Testimonial model"
```

---

### Task 4: Seeders — 7 industries, 22 work categories, testimonials; fix affected tests

**Files:**
- Modify: `database/seeders/IndustriesSeeder.php` (rewrite rows, delete stale)
- Create: `database/seeders/WorkCategoriesSeeder.php`
- Create: `database/seeders/TestimonialsSeeder.php`
- Modify: `database/seeders/PortfoliosSeeder.php` (attach categories by title)
- Modify: `database/seeders/DatabaseSeeder.php`
- Modify: `tests/Feature/SeederTest.php`
- Modify: `tests/Feature/Public/IndustryPageTest.php`

**Interfaces:**
- Consumes: models from Tasks 1–3.
- Produces: seeded slugs used by all later tasks — industries: `weddings-celebrations, corporate-events, brands-products, fashion-creators, nightlife-entertainment, spaces-interiors, motion-post-production`; 22 work-category slugs (see Step 2); ≥4 published testimonials.

- [ ] **Step 1: Update SeederTest (failing first)**

Add imports `App\Models\Testimonial`, `App\Models\WorkCategory` and extend the existing expectation chain:

```php
        ->and(Industry::count())->toBe(7)
        ->and(Industry::pluck('slug'))->toContain('weddings-celebrations', 'motion-post-production')
        ->and(WorkCategory::count())->toBe(22)
        ->and(Testimonial::published()->count())->toBeGreaterThanOrEqual(4)
```

(replace the current `Industry::count())->toBeGreaterThanOrEqual(4)` line).

Run: `php artisan test tests/Feature/SeederTest.php` — Expected: FAIL.

- [ ] **Step 2: Rewrite IndustriesSeeder**

```php
<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustriesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['weddings-celebrations', 'Weddings & Celebrations', 'Weddings, preweddings, anniversaries and birthdays — cinematic coverage for every celebration.', 'https://images.unsplash.com/photo-1519741497674-611481863552?w=1200&q=85'],
            ['corporate-events', 'Corporate & Events', 'Conferences, corporate films, naval ceremonies, anchors and podcast productions.', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&q=85'],
            ['brands-products', 'Brands & Products', 'Brand campaigns, ecommerce, product shoots, liquor industry and store launches.', 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1200&q=85'],
            ['fashion-creators', 'Fashion & Creators', 'Fashion shows, designer portfolios and influencer content.', 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=1200&q=85'],
            ['nightlife-entertainment', 'Nightlife & Entertainment', 'Clubbing, concerts, artists and festivals — high-energy live coverage.', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1200&q=85'],
            ['spaces-interiors', 'Spaces & Interiors', 'Interior and decor shoots for hospitality, retail and residential spaces.', 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1200&q=85'],
            ['motion-post-production', 'Motion & Post-Production', 'Motion graphics and post-production — where every frame gets finished.', 'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=1200&q=85'],
        ];
        foreach ($rows as $i => [$slug, $title, $summary, $image]) {
            Industry::updateOrCreate(['slug' => $slug], [
                'title' => $title, 'summary' => $summary, 'image_url' => $image, 'body' => '', 'order' => $i,
            ]);
        }

        // Retire placeholder industries from earlier seed versions.
        Industry::whereNotIn('slug', array_column($rows, 0))->delete();
    }
}
```

Note: keep `image_url` only if the column exists in the current seeder (it does — copy usage as-is).

- [ ] **Step 3: Create WorkCategoriesSeeder**

```php
<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\WorkCategory;
use Illuminate\Database\Seeder;

class WorkCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'weddings-celebrations' => ['Wedding', 'Prewedding', 'Anniversary', 'Birthday'],
            'corporate-events' => ['Corporate', 'INS Navy', 'Anchor', 'Podcast'],
            'brands-products' => ['Brands', 'Ecommerce', 'Product Shoots', 'Liquor Industry', 'Store & Brand Launch'],
            'fashion-creators' => ['Fashion Show', 'Designer', 'Influencer'],
            'nightlife-entertainment' => ['Clubbing', 'Concert & Artist', 'Festival'],
            'spaces-interiors' => ['Interior Shoots', 'Decor Shoots'],
            'motion-post-production' => ['Motion Graphics'],
        ];

        foreach ($map as $industrySlug => $titles) {
            $industry = Industry::where('slug', $industrySlug)->firstOrFail();
            foreach ($titles as $i => $title) {
                WorkCategory::updateOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($title)],
                    ['industry_id' => $industry->id, 'title' => $title, 'order' => $i],
                );
            }
        }
    }
}
```

(Resulting slugs: `wedding, prewedding, anniversary, birthday, corporate, ins-navy, anchor, podcast, brands, ecommerce, product-shoots, liquor-industry, store-brand-launch, fashion-show, designer, influencer, clubbing, concert-artist, festival, interior-shoots, decor-shoots, motion-graphics`.)

- [ ] **Step 4: Create TestimonialsSeeder** (the 4 quotes currently hardcoded in `resources/views/home.blade.php:333-359`, tied to industries)

```php
<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['The Last Clicks delivered exceptional coverage for our annual conference. Their professionalism and attention to detail made all the difference.', 'Priya Mehta', 'Marketing Head, Fortune 500 FMCG', 'corporate-events'],
            ['From pre-production to final delivery, their team was seamless. The brand films exceeded our expectations.', 'Arjun Kapoor', 'Creative Director, Leading Ad Agency', 'brands-products'],
            ['Incredible wedding coverage. Every moment was captured beautifully — cinematic, emotional, and authentic.', 'Sneha & Rohit', 'Destination Wedding, Udaipur', 'weddings-celebrations'],
            ['Consistent quality every single time. They truly understand the luxury and automotive space.', 'Vikram Singh', 'Brand Manager, Premium Automobile', 'brands-products'],
        ];
        foreach ($rows as $i => [$quote, $name, $role, $industrySlug]) {
            Testimonial::updateOrCreate(
                ['client_name' => $name],
                [
                    'quote' => $quote,
                    'role_company' => $role,
                    'industry_id' => Industry::where('slug', $industrySlug)->value('id'),
                    'order' => $i,
                    'is_published' => true,
                ],
            );
        }
    }
}
```

- [ ] **Step 5: Attach categories in PortfoliosSeeder**

Read `database/seeders/PortfoliosSeeder.php`; after each portfolio row is created/updated, set `industry_id` + `work_category_id` from this title→category-slug map (lookup `WorkCategory::where('slug', …)->first()`, set both `work_category_id` and its `industry_id`). Add at the END of `run()` as a single pass:

```php
        $categoryByTitle = [
            'Atlas — brand film' => 'brands',
            'Udaipur · S & R' => 'wedding',
            'Aurelia GT reveal' => 'brands',
            'Premium beverage — campaign' => 'liquor-industry',
            'Commercial reel' => 'motion-graphics',
            'Editorial — fashion' => 'fashion-show',
            'Goa · M & A' => 'wedding',
            'Tech keynote — Mumbai' => 'corporate',
        ];
        foreach ($categoryByTitle as $title => $slug) {
            $cat = \App\Models\WorkCategory::where('slug', $slug)->first();
            if ($cat) {
                \App\Models\Portfolio::where('title', $title)
                    ->update(['work_category_id' => $cat->id, 'industry_id' => $cat->industry_id]);
            }
        }
```

(One seeded title contains an escaped quote — `Annual Conference '…` — leave it unmapped; unmapped tiles render under "Other".)

- [ ] **Step 6: Wire DatabaseSeeder**

In `database/seeders/DatabaseSeeder.php` insert `WorkCategoriesSeeder::class` directly after `IndustriesSeeder::class`, and `TestimonialsSeeder::class` after `WorkCategoriesSeeder::class`. (Both must run before `PortfoliosSeeder::class`? No — `PortfoliosSeeder` must run BEFORE nothing; the category-attach pass in Step 5 lives inside `PortfoliosSeeder`, which already runs after industries. Ensure final order: `IndustriesSeeder, WorkCategoriesSeeder, TestimonialsSeeder, CrewSeeder, PortfoliosSeeder, …`.)

- [ ] **Step 7: Fix IndustryPageTest slugs**

```php
it('industry index lists seeded industries', function () {
    $this->get('/industries')->assertOk()->assertSee('Fashion');
});

it('industry detail renders by slug', function () {
    $this->get('/industries/fashion-creators')->assertOk()->assertSee('Fashion');
});
```

(keep the 404 test unchanged).

- [ ] **Step 8: Run tests**

Run: `php artisan test tests/Feature/SeederTest.php tests/Feature/Public/IndustryPageTest.php`
Expected: PASS.

- [ ] **Step 9: Pint + commit**

```bash
vendor/bin/pint --dirty
git add -A && git commit -m "feat: seed 7 real industries, 22 work categories, testimonials"
```

---

### Task 5: Filament — WorkCategoryResource, TestimonialResource, Portfolio dependent select, Shield perms

**Files:**
- Create: `app/Filament/Resources/WorkCategoryResource.php` + `WorkCategoryResource/Pages/{ListWorkCategories,CreateWorkCategory,EditWorkCategory}.php`
- Create: `app/Filament/Resources/TestimonialResource.php` + `TestimonialResource/Pages/{ListTestimonials,CreateTestimonial,EditTestimonial}.php`
- Modify: `app/Filament/Resources/PortfolioResource.php:41-42`
- Modify: `database/seeders/PermissionsSeeder.php` (Editor regex)
- Test: `tests/Feature/Admin/WorkCategoryResourceTest.php`, `tests/Feature/Admin/TestimonialResourceTest.php`, `tests/Feature/Admin/ShieldPermissionTest.php` (append)

**Interfaces:**
- Consumes: models from Tasks 1–3, seeders from Task 4.
- Produces: admin CRUD for both models; `PortfolioResource` form field `work_category_id` filtered by chosen `industry_id`.

- [ ] **Step 1: Write failing resource tests** (mirror `tests/Feature/Admin/IndustryResourceTest.php`)

`tests/Feature/Admin/WorkCategoryResourceTest.php`:

```php
<?php

use App\Filament\Resources\WorkCategoryResource\Pages\EditWorkCategory;
use App\Filament\Resources\WorkCategoryResource\Pages\ListWorkCategories;
use App\Models\User;
use App\Models\WorkCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list work categories', function () {
    Livewire::test(ListWorkCategories::class)->assertCanSeeTableRecords(WorkCategory::limit(10)->get());
});

it('Super-admin can rename a work category', function () {
    $cat = WorkCategory::first();
    Livewire::test(EditWorkCategory::class, ['record' => $cat->getRouteKey()])
        ->fillForm(['title' => 'Renamed Cat'])
        ->call('save')
        ->assertHasNoFormErrors();
    expect($cat->fresh()->title)->toBe('Renamed Cat');
});

it('Editor can update work categories', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');
    expect($editor->can('update', WorkCategory::first()))->toBeTrue();
});
```

`tests/Feature/Admin/TestimonialResourceTest.php` — same shape: `ListTestimonials` sees `Testimonial::all()`, `EditTestimonial` updates `client_name` to `'New Client'`, Editor `can('update', Testimonial::first())`.

Run: `php artisan test tests/Feature/Admin/WorkCategoryResourceTest.php tests/Feature/Admin/TestimonialResourceTest.php`
Expected: FAIL — resource classes missing.

- [ ] **Step 2: Create WorkCategoryResource** (pattern: `IndustryResource`)

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkCategoryResource\Pages;
use App\Models\WorkCategory;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WorkCategoryResource extends Resource
{
    protected static ?string $model = WorkCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Site';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('title')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
            ]),
            Select::make('industry_id')->relationship('industry', 'title')->required()->preload(),
            TextInput::make('order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('industry.title')->sortable(),
                TextColumn::make('slug')->searchable(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkCategories::route('/'),
            'create' => Pages\CreateWorkCategory::route('/create'),
            'edit' => Pages\EditWorkCategory::route('/{record}/edit'),
        ];
    }
}
```

Pages (copy the three-page pattern from `IndustryResource/Pages/*` — `ListWorkCategories extends ListRecords`, `CreateWorkCategory extends CreateRecord`, `EditWorkCategory extends EditRecord`, each with `protected static string $resource = WorkCategoryResource::class;` and List having the CreateAction header action if the Industry pages do).

- [ ] **Step 3: Create TestimonialResource**

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Site';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Textarea::make('quote')->required()->rows(4)->columnSpanFull(),
            Section::make()->columns(2)->schema([
                TextInput::make('client_name')->required(),
                TextInput::make('role_company')->label('Role / company'),
                Select::make('industry_id')->relationship('industry', 'title')->preload()
                    ->helperText('Shown on this industry page as well as the homepage.'),
                TextInput::make('order')->numeric()->default(0),
            ]),
            Toggle::make('is_published')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->sortable(),
                TextColumn::make('client_name')->searchable()->sortable(),
                TextColumn::make('quote')->limit(60)->wrap(),
                TextColumn::make('industry.title')->sortable(),
                IconColumn::make('is_published')->boolean(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
```

Pages: same three-page pattern.

- [ ] **Step 4: Dependent select in PortfolioResource**

Replace line 42 (`Select::make('industry_id')…`) block:

```php
                Select::make('industry_id')->relationship('industry', 'title')->searchable()->preload()->live(),
                Select::make('work_category_id')
                    ->label('Work category')
                    ->options(fn (\Filament\Forms\Get $get): array => \App\Models\WorkCategory::query()
                        ->when($get('industry_id'), fn ($q, $id) => $q->where('industry_id', $id))
                        ->orderBy('order')->pluck('title', 'id')->all())
                    ->searchable()
                    ->helperText('Pick an industry to narrow this list.'),
```

- [ ] **Step 5: Editor permissions**

`database/seeders/PermissionsSeeder.php` — extend both Editor regexes. Shield may emit `work_category`, `work::category`, or similar depending on its separator config; cover both:

```php
        $editorPattern = '/_(post|portfolio|service|industry|crew|category|tag|testimonial|work_category|work::category)$/';
        $editor->syncPermissions(array_filter($all, fn ($p) => preg_match($editorPattern, $p) === 1
            || preg_match('/_any_(post|portfolio|service|industry|crew|category|tag|testimonial|work_category|work::category)$/', $p) === 1
        ));
```

Append to `tests/Feature/Admin/ShieldPermissionTest.php`:

```php
it('seeds shield permissions for WorkCategory and Testimonial resources', function () {
    $this->seed();
    expect(Permission::where('name', 'like', '%work%category%')->exists())->toBeTrue()
        ->and(Permission::where('name', 'like', '%testimonial%')->exists())->toBeTrue();
});
```

After running the seeder once, check the actual generated names (`Permission::pluck('name')`) and tighten the Editor regex to the real spelling if `work::category` is used.

- [ ] **Step 6: Run tests**

Run: `php artisan test tests/Feature/Admin/WorkCategoryResourceTest.php tests/Feature/Admin/TestimonialResourceTest.php tests/Feature/Admin/ShieldPermissionTest.php tests/Feature/Admin/PortfolioResourceTest.php`
Expected: PASS.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty
git add -A && git commit -m "feat: admin CRUD for work categories and testimonials"
```

---

### Task 6: Homepage testimonials from DB

**Files:**
- Modify: `app/Http/Controllers/Public/HomeController.php`
- Modify: `resources/views/home.blade.php:325-360` (slides) and the `.car__dots` block (~line 363)
- Test: `tests/Feature/Public/HomePageTest.php` (append)

**Interfaces:**
- Consumes: `Testimonial::published()` from Task 3, seeded quotes from Task 4.
- Produces: view variable `$testimonials` (Collection) on `home`.

- [ ] **Step 1: Append failing tests**

```php
it('homepage shows seeded testimonials from the database', function () {
    $this->get('/')->assertOk()->assertSee('Priya Mehta');
});

it('homepage hides testimonial section when none published', function () {
    \App\Models\Testimonial::query()->update(['is_published' => false]);
    $this->get('/')->assertOk()->assertDontSee('What our');
});
```

Run: `php artisan test tests/Feature/Public/HomePageTest.php` — first new test may already pass via hardcoded HTML; the second MUST fail (section is hardcoded). That failing test is the driver.

- [ ] **Step 2: Controller**

```php
        return view('home', [
            'services' => Service::orderBy('order')->get(),
            'portfolio' => Portfolio::published()->latest()->take(6)->get(),
            'testimonials' => Testimonial::published()->orderBy('order')->get(),
        ]);
```

(add `use App\Models\Testimonial;`).

- [ ] **Step 3: Blade rework**

Wrap the whole `<!-- TESTIMONIALS -->` `<section class="car" …>` in `@if ($testimonials->isNotEmpty()) … @endif`. Replace the four hardcoded `.car__slide` divs with:

```blade
            @foreach ($testimonials as $t)
                <div class="car__slide {{ $loop->first ? 'is-on' : '' }}">
                    <div class="car__quote">"{{ $t->quote }}"</div>
                    <div class="who">
                        <span class="av">{{ collect(explode(' ', $t->client_name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}</span>
                        <span>{{ $t->client_name }}{{ $t->role_company ? ' · '.$t->role_company : '' }}</span>
                    </div>
                </div>
            @endforeach
```

Replace the four hardcoded `.car__dot` buttons with:

```blade
                @foreach ($testimonials as $t)
                    <button class="car__dot {{ $loop->first ? 'is-on' : '' }}" data-cursor="{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}"></button>
                @endforeach
```

Check `resources/js/core.js:369` carousel init — it queries slides/dots from the DOM, so dynamic counts work; verify no hardcoded `4`.

- [ ] **Step 4: Run tests**

Run: `php artisan test tests/Feature/Public/HomePageTest.php`
Expected: PASS.

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty
git add -A && git commit -m "feat: homepage testimonials from database"
```

---

### Task 7: Industry page — work categories + testimonials

**Files:**
- Modify: `app/Http/Controllers/Public/IndustryController.php` (show)
- Modify: `resources/views/industries/show.blade.php`
- Test: `tests/Feature/Public/IndustryPageTest.php` (append)

**Interfaces:**
- Consumes: `Industry::workCategories()`, `Industry::testimonials()`, seeded data.
- Produces: view vars `$categories`, `$testimonials` on `industries.show`.

- [ ] **Step 1: Append failing tests**

```php
it('industry page lists its work categories', function () {
    $this->get('/industries/weddings-celebrations')->assertOk()
        ->assertSee('Wedding')->assertSee('Prewedding')->assertSee('Anniversary')->assertSee('Birthday');
});

it('industry page shows its own testimonials', function () {
    $this->get('/industries/weddings-celebrations')->assertOk()->assertSee('Sneha');
});
```

Run: `php artisan test tests/Feature/Public/IndustryPageTest.php` — Expected: new tests FAIL.

- [ ] **Step 2: Controller**

```php
    public function show(string $slug): View
    {
        $industry = Industry::where('slug', $slug)->firstOrFail();
        $work = Portfolio::published()->where('industry_id', $industry->id)->latest()->take(12)->get();
        $categories = $industry->workCategories;
        $testimonials = $industry->testimonials()->where('is_published', true)->get();

        return view('industries.show', compact('industry', 'work', 'categories', 'testimonials'));
    }
```

- [ ] **Step 3: Blade — insert after the HERO section** (before RICH BODY):

```blade
    {{-- WHAT WE SHOOT --}}
    @if ($categories->isNotEmpty())
        <section class="section" data-screen-label="What we shoot">
            <div class="wrap">
                <span class="section__eyebrow" data-scramble>What we shoot</span>
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:24px">
                    @foreach ($categories as $cat)
                        <a href="{{ url('/portfolio?category='.$cat->slug) }}"
                           style="padding:9px 16px;border:1px solid var(--line);border-radius:100px;font-family:var(--f-mono);font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:var(--paper-dim)">
                            {{ $cat->title }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
```

And insert before the CTA STRIP:

```blade
    {{-- CLIENT WORDS --}}
    @if ($testimonials->isNotEmpty())
        <section class="section" data-screen-label="Client words">
            <div class="wrap">
                <span class="section__eyebrow" data-scramble>Client words</span>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:32px;margin-top:32px">
                    @foreach ($testimonials as $t)
                        <blockquote style="border-left:2px solid var(--red);padding-left:20px">
                            <p style="font-size:17px;line-height:1.6">"{{ $t->quote }}"</p>
                            <footer style="margin-top:14px;font-family:var(--f-mono);font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:var(--paper-dim)">
                                {{ $t->client_name }}{{ $t->role_company ? ' · '.$t->role_company : '' }}
                            </footer>
                        </blockquote>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
```

(Inline styles match the existing file's idiom — it already uses inline styles for one-off sections.)

- [ ] **Step 4: Run tests**

Run: `php artisan test tests/Feature/Public/IndustryPageTest.php`
Expected: PASS.

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty
git add -A && git commit -m "feat: industry pages list work categories and testimonials"
```

---

### Task 8: Portfolio index — industry chips + `?category=` deep link

**Files:**
- Modify: `app/Http/Controllers/Public/PortfolioController.php` (eager loads)
- Modify: `resources/views/portfolio/index.blade.php` (lines ~460-473 chips, ~520-544 tiles, ~653-682 script)
- Test: `tests/Feature/Public/PortfolioPageTest.php` (append)

**Interfaces:**
- Consumes: `Portfolio::workCategory`/`industry` relations, seeded category assignments from Task 4 Step 5.
- Produces: tiles carry `data-ind` (industry slug) and `data-cat` (work-category slug); chips filter on `data-ind`; `?category={work-category-slug}` pre-filters on load.

- [ ] **Step 1: Append failing tests**

```php
it('portfolio tiles carry industry and category data attributes', function () {
    $r = $this->get('/portfolio')->assertOk();
    $r->assertSee('data-ind="weddings-celebrations"', false);
    $r->assertSee('data-cat="wedding"', false);
});

it('portfolio filter chips list industries', function () {
    $this->get('/portfolio')->assertOk()->assertSee('Weddings');
});
```

Run: `php artisan test tests/Feature/Public/PortfolioPageTest.php` — Expected: FAIL.

- [ ] **Step 2: Eager-load relations in controller**

In `PortfolioController::index()` add `->with(['service', 'industry', 'workCategory'])` to the `$itemsByYear` query chain (after `Portfolio::published()`).

- [ ] **Step 3: Chips — replace the `@php` block and chip loop (lines 460-473)**

```blade
    @php
        $allTiles = collect($itemsByYear)->flatten(1);
        $indGroups = $allTiles->groupBy(fn ($i) => $i->industry?->slug ?? 'other');
        $indLabels = $allTiles->mapWithKeys(fn ($i) => [($i->industry?->slug ?? 'other') => ($i->industry?->title ?? 'Other')]);
    @endphp
    <section class="pf-filter" data-screen-label="02 Filter">
        <div class="pf-filter__inner">
            <span class="pf-filter__label">Filter</span>
            <button class="pf-chip is-on" data-ind="all">All<span class="count">{{ str_pad((string) $allTiles->count(), 2, '0', STR_PAD_LEFT) }}</span></button>
            @foreach ($indGroups as $ind => $items)
                <button class="pf-chip" data-ind="{{ $ind }}">{{ $indLabels[$ind] }}<span class="count">{{ str_pad((string) $items->count(), 2, '0', STR_PAD_LEFT) }}</span></button>
            @endforeach
        </div>
    </section>
```

- [ ] **Step 4: Tiles — update the `<a class="pf-tile …">` attributes (line ~526-529)**

```blade
                    <a class="pf-tile {{ $tileSize }}" href="{{ url('/portfolio/'.$portfolioItem->slug) }}"
                       data-ind="{{ $portfolioItem->industry?->slug ?? 'other' }}"
                       data-cat="{{ $portfolioItem->workCategory?->slug ?? '' }}"
                       data-cursor="VIEW">
                        <span class="pf-tile__tag">{{ $portfolioItem->workCategory?->title ?? $portfolioItem->service?->title ?? 'Film' }} · {{ $portfolioItem->year }}</span>
```

(rest of tile markup unchanged).

- [ ] **Step 5: Script — replace the chip-filter IIFE body (lines 655-673)**

```js
      const chips = document.querySelectorAll('.pf-chip');
      const tiles = document.querySelectorAll('.pf-tile[data-ind]');

      function showTile(t, match) {
        t.style.transition = 'opacity 0.4s, transform 0.4s';
        if (match) {
          t.classList.remove('is-hidden');
          requestAnimationFrame(() => { t.style.opacity = '1'; t.style.transform = 'scale(1)'; });
        } else {
          t.style.opacity = '0'; t.style.transform = 'scale(0.97)';
          setTimeout(() => t.classList.add('is-hidden'), 380);
        }
      }

      function filterBy(attr, value) {
        tiles.forEach(t => showTile(t, value === 'all' || t.dataset[attr] === value));
      }

      chips.forEach(c => c.addEventListener('click', () => {
        chips.forEach(o => o.classList.remove('is-on'));
        c.classList.add('is-on');
        filterBy('ind', c.dataset.ind);
      }));

      // Deep link: /portfolio?category=wedding filters to one work category
      const wanted = new URLSearchParams(location.search).get('category');
      if (wanted) {
        const first = document.querySelector('.pf-tile[data-cat="' + CSS.escape(wanted) + '"]');
        if (first) {
          filterBy('cat', wanted);
          chips.forEach(o => o.classList.toggle('is-on', o.dataset.ind === first.dataset.ind));
        }
      }
```

(keep the IntersectionObserver discipline-bars block below it unchanged).

- [ ] **Step 6: Run tests**

Run: `php artisan test tests/Feature/Public/PortfolioPageTest.php`
Expected: PASS.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty
git add -A && git commit -m "feat: portfolio filters by industry with category deep links"
```

---

### Task 9: 301 redirects for retired industry slugs

**Files:**
- Modify: `routes/web.php` (inside the `cacheResponse` group, above the `/industries/{slug}` route)
- Test: `tests/Feature/Public/IndustryRedirectTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects retired industry slugs permanently', function (string $old, string $new) {
    $this->get('/industries/'.$old)
        ->assertStatus(301)
        ->assertRedirect('/industries/'.$new);
})->with([
    ['corporate-conferences', 'corporate-events'],
    ['brand-launches', 'brands-products'],
    ['automobile-showcases', 'brands-products'],
    ['lifestyle-beverage', 'brands-products'],
    ['destination-weddings', 'weddings-celebrations'],
    ['commercial-productions', 'motion-post-production'],
]);
```

Run: `php artisan test tests/Feature/Public/IndustryRedirectTest.php` — Expected: FAIL (404s).

- [ ] **Step 2: Add routes** (directly above `Route::get('/industries/{slug}', …)`, with a comment matching the services-redirect style)

```php
    // Retired industry slugs — the site now groups work into 7 real industries.
    Route::redirect('/industries/corporate-conferences', '/industries/corporate-events', 301);
    Route::redirect('/industries/brand-launches', '/industries/brands-products', 301);
    Route::redirect('/industries/automobile-showcases', '/industries/brands-products', 301);
    Route::redirect('/industries/lifestyle-beverage', '/industries/brands-products', 301);
    Route::redirect('/industries/destination-weddings', '/industries/weddings-celebrations', 301);
    Route::redirect('/industries/commercial-productions', '/industries/motion-post-production', 301);
```

- [ ] **Step 3: Run test**

Run: `php artisan test tests/Feature/Public/IndustryRedirectTest.php`
Expected: PASS (6 cases).

- [ ] **Step 4: Pint + commit**

```bash
vendor/bin/pint --dirty
git add -A && git commit -m "feat: 301 redirects for retired industry slugs"
```

---

### Task 10: Full verification + deploy note

**Files:**
- Modify: `docs/DEPLOYMENT.md` (migration/seed note)

- [ ] **Step 1: Full suite**

Run: `php artisan test`
Expected: PASS (compare against the Task-0 baseline; no new failures).

- [ ] **Step 2: Static analysis + style**

Run: `vendor/bin/phpstan analyse` and `vendor/bin/pint --test`
Expected: no errors.

- [ ] **Step 3: Deploy note**

Append to the deploy steps in `docs/DEPLOYMENT.md` (match its existing format): this release requires `php artisan migrate --force` and `php artisan db:seed --force` (idempotent seeders) before the mandated `php artisan responsecache:clear`.

- [ ] **Step 4: Commit**

```bash
git add -A && git commit -m "chore: deploy notes for work categories release"
```
