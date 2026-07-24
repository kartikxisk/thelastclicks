# Portfolio Revamp Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the static portfolio with a cinematic video-first index plus a trust layer (results, linked testimonials, CTA) on case pages, with multi-service portfolios editable in Filament.

**Architecture:** A `portfolio_service` pivot (backfilled from `service_id`) powers multi-service data. The index becomes a reel stack: poster-first full-width rows whose `<video>` gets its `src` assigned by an IntersectionObserver only while ≥60% visible (one playing at a time; touch devices get a tap-to-preview button instead). Case pages append conditional Result facts, a linked testimonial, and a CTA band. Trust sections render only when real content exists.

**Tech Stack:** Laravel 11, Filament v3, spatie/laravel-medialibrary (media already on S3/CloudFront), Pest, vanilla JS (inline script, matching the home-strip pattern).

**Spec:** `docs/superpowers/specs/2026-07-19-portfolio-revamp-design.md`

## Global Constraints

- **Do NOT git commit.** User rule: leave all changes uncommitted; commit only on explicit ask. Every template "Commit" step is replaced by "leave uncommitted".
- **Never fabricate trust content.** No seeded results, no seeded testimonial-to-portfolio links, no invented numbers. Derived facts (client names, counts, year window) come from live DB only.
- Media URLs only via medialibrary (`getFirstMediaUrl`) with legacy `cover_url`/`gallery_urls` columns as fallback, matching existing views.
- Hero copy (shipped defaults, verbatim): headline `Films that make <em>people act.</em>`; sub-line `Cinema-grade films and photography for brands that need to be remembered.`
- Test env: phpunit pins `MEDIA_DISK=public`; tests needing media set `config(['media-library.disk_name' => 's3'])` + `Storage::fake('s3')`.
- After every task: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M` — all green.
- Videos in rows: `muted loop playsinline preload="none"` and **no `src` attribute in HTML** — `data-src` only; JS assigns `src` on activation.

---

### Task 1: Multi-service pivot

**Files:**
- Create: `database/migrations/2026_07_19_000001_create_portfolio_service_table.php`
- Modify: `app/Models/Portfolio.php` (add `services()` relation)
- Modify: `database/seeders/PortfoliosSeeder.php` (attach pivot)
- Test: `tests/Feature/Models/PortfolioServicesTest.php` (new)

**Interfaces:**
- Produces: `Portfolio::services(): BelongsToMany<Service>` (pivot table `portfolio_service`, columns `portfolio_id`, `service_id`, unique pair, no timestamps). Existing `service()` BelongsTo stays (legacy). Seeder guarantees every seeded portfolio has its service in the pivot. Later tasks rely on `services` relation name exactly.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Models/PortfolioServicesTest.php`:

```php
<?php

use App\Models\Portfolio;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('portfolio belongs to many services via pivot', function () {
    $this->seed();

    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    $extra = Service::where('slug', '!=', 'videography')->firstOrFail();

    expect($portfolio->services->pluck('slug'))->toContain('videography');

    $portfolio->services()->syncWithoutDetaching([$extra->id]);
    expect($portfolio->fresh()->services)->toHaveCount(2);
});

it('migration backfills pivot from legacy service_id', function () {
    $this->seed();

    // every seeded portfolio with a service_id has a matching pivot row
    Portfolio::whereNotNull('service_id')->get()->each(function (Portfolio $p) {
        expect($p->services->pluck('id'))->toContain($p->service_id);
    });
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test tests/Feature/Models/PortfolioServicesTest.php`
Expected: FAIL — `Call to undefined relationship [services]` (or missing table `portfolio_service`).

- [ ] **Step 3: Create the migration**

Create `database/migrations/2026_07_19_000001_create_portfolio_service_table.php`:

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
        Schema::create('portfolio_service', function (Blueprint $table) {
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unique(['portfolio_id', 'service_id']);
        });

        // Backfill from the legacy single-service column (prod data).
        DB::table('portfolios')->whereNotNull('service_id')->orderBy('id')
            ->each(function (object $row) {
                DB::table('portfolio_service')->insertOrIgnore([
                    'portfolio_id' => $row->id,
                    'service_id' => $row->service_id,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_service');
    }
};
```

- [ ] **Step 4: Add the relation to the model**

In `app/Models/Portfolio.php` add the import `use Illuminate\Database\Eloquent\Relations\BelongsToMany;` and, below the existing `service()` method:

```php
/** @return BelongsToMany<Service> */
public function services(): BelongsToMany
{
    return $this->belongsToMany(Service::class);
}
```

(If PHPStan complains about the generics arity on this Laravel version, use the annotation it suggests — e.g. `@return BelongsToMany<Service, $this>` — and note it in the report.)

- [ ] **Step 5: Seeder attaches the pivot**

In `database/seeders/PortfoliosSeeder.php`, inside the `foreach ($cases as $c)` loop, capture the model and sync after `updateOrCreate` (replace the bare `Portfolio::updateOrCreate([...])` statement):

```php
$portfolio = Portfolio::updateOrCreate(['slug' => $c['slug']], [
    // ... existing payload unchanged ...
]);

if ($sid = $serviceIds[$c['service']] ?? null) {
    $portfolio->services()->syncWithoutDetaching([$sid]);
}
```

- [ ] **Step 6: Run tests**

Run: `php artisan test tests/Feature/Models/PortfolioServicesTest.php`
Expected: 2 passing. (Note: with RefreshDatabase the backfill runs on an empty table; the second test still validates end state because the seeder writes both `service_id` and the pivot. State this in the report — prod backfill is exercised by the migration itself at deploy.)

- [ ] **Step 7: Full checks**

Run: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: all green. Leave uncommitted.

---

### Task 2: Schema for trust content + Filament admin

**Files:**
- Create: `database/migrations/2026_07_19_000002_add_results_and_testimonial_case_link.php`
- Create: `app/Observers/TestimonialObserver.php`
- Modify: `app/Models/Portfolio.php` (fillable + cast `results`)
- Modify: `app/Models/Testimonial.php` (fillable `portfolio_id`, `portfolio()` relation)
- Modify: `app/Providers/AppServiceProvider.php` (register TestimonialObserver)
- Modify: `app/Filament/Resources/PortfolioResource.php` (services multi-select, results repeater, table filter)
- Modify: `app/Filament/Resources/TestimonialResource.php` (portfolio select)
- Test: `tests/Feature/Admin/PortfolioAdminTest.php` (new), extend `tests/Feature/Models/PortfolioServicesTest.php`

**Interfaces:**
- Consumes: `Portfolio::services()` from Task 1.
- Produces: `portfolios.results` nullable json, cast `'results' => 'array'`, shape `[['label' => string, 'value' => string], ...]`; `testimonials.portfolio_id` nullable FK (nullOnDelete), `Testimonial::portfolio(): BelongsTo<Portfolio>`. Tasks 3–4 rely on these names/shapes exactly.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/Admin/PortfolioAdminTest.php`:

```php
<?php

use App\Filament\Resources\PortfolioResource;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->actingAs(User::where('email', config('app.admin_seed_email'))->firstOrFail());
});

it('saves a portfolio with multiple services and results facts', function () {
    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    $serviceIds = Service::pluck('id')->take(2)->all();

    Livewire::test(PortfolioResource\Pages\EditPortfolio::class, ['record' => $portfolio->id])
        ->fillForm([
            'services' => $serviceIds,
            'results' => [
                ['label' => 'Deliverables', 'value' => '14 films'],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $portfolio->fresh();
    expect($fresh->services)->toHaveCount(2)
        ->and($fresh->results)->toBe([['label' => 'Deliverables', 'value' => '14 films']]);
});

it('links a testimonial to a portfolio', function () {
    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    $t = Testimonial::firstOrFail();

    $t->update(['portfolio_id' => $portfolio->id]);

    expect($t->fresh()->portfolio->slug)->toBe('ins-navy');
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test tests/Feature/Admin/PortfolioAdminTest.php`
Expected: FAIL — no `services`/`results` form components; unknown column `portfolio_id`.

- [ ] **Step 3: Migration**

Create `database/migrations/2026_07_19_000002_add_results_and_testimonial_case_link.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->json('results')->nullable()->after('credits');
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->foreignId('portfolio_id')->nullable()->after('industry_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('portfolio_id');
        });
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn('results');
        });
    }
};
```

- [ ] **Step 4: Model updates**

`app/Models/Portfolio.php`: add `'results'` to `$fillable`; add `'results' => 'array'` to `$casts`.

`app/Models/Testimonial.php`: add `'portfolio_id'` to `$fillable` and:

```php
/** @return BelongsTo<Portfolio, $this> */
public function portfolio(): BelongsTo
{
    return $this->belongsTo(Portfolio::class);
}
```

- [ ] **Step 5: TestimonialObserver (responsecache)**

Create `app/Observers/TestimonialObserver.php` mirroring `app/Observers/SiteSettingObserver.php` exactly (same two methods, `saved` and `deleted`, each calling `ResponseCache::clear()`), with class name `TestimonialObserver` and model import `App\Models\Testimonial` in the docblock if the sibling observers have one. Register in `app/Providers/AppServiceProvider.php` next to the existing `observe` calls:

```php
Testimonial::observe(TestimonialObserver::class);
```

(add both imports following the file's alphabetical grouping).

- [ ] **Step 6: Filament — PortfolioResource**

Replace the `service_id` select:

```php
Select::make('services')
    ->relationship('services', 'title')
    ->multiple()
    ->searchable()
    ->preload()
    ->helperText('All disciplines used on this production.'),
```

In the "Design content" section, after the `credits` KeyValue, add:

```php
Repeater::make('results')
    ->label('Result facts')
    ->helperText('Real outcomes only — shown on the case page when filled.')
    ->schema([
        TextInput::make('label')->required(),
        TextInput::make('value')->required(),
    ])
    ->columns(2)
    ->defaultItems(0)
    ->columnSpanFull(),
```

Add the import `use Filament\Forms\Components\Repeater;`. In `table()`, replace `SelectFilter::make('service_id')->relationship('service', 'title')` with `SelectFilter::make('services')->relationship('services', 'title')`.

- [ ] **Step 7: Filament — TestimonialResource**

In the two-column section, after the `industry_id` select:

```php
Select::make('portfolio_id')
    ->label('Attach to case')
    ->relationship('portfolio', 'title', fn ($query) => $query->where('status', 'published'))
    ->searchable()
    ->preload()
    ->helperText('Quote shows on this case page.'),
```

- [ ] **Step 8: All admin uploads to S3 — Filament default disk**

Filament's own default disk (`config('filament.default_filesystem_disk')`) is `public`, so RichEditor image attachments bypass S3. Fix via env:

- `.env.example`: add `FILAMENT_FILESYSTEM_DISK=s3` next to `MEDIA_DISK=s3`.
- `.env`: add the same line (do not touch other values, never print secrets).
- `phpunit.xml`: add `<env name="FILAMENT_FILESYSTEM_DISK" value="public"/>` next to the existing `MEDIA_DISK` pin.
- Append one line to the runbook env block in `docs/deploy/s3-cloudfront-media.md`: `FILAMENT_FILESYSTEM_DISK=s3` with comment `# RichEditor attachments`.

- [ ] **Step 9: Run tests, then full checks**

Run: `php artisan test tests/Feature/Admin/PortfolioAdminTest.php`
Expected: 2 passing.
Then: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: all green. Leave uncommitted.

---

### Task 3: Cinematic index — controller + reel stack view

**Files:**
- Modify: `app/Http/Controllers/Public/PortfolioController.php` (index method)
- Modify: `resources/views/portfolio/index.blade.php` (full rewrite of body sections; keep `<x-layouts.app>` wrapper + `<x-slot name="head">` style pattern)
- Test: `tests/Feature/Public/PortfolioIndexTest.php` (new)

**Interfaces:**
- Consumes: `Portfolio::services()`, media collections, legacy `cover_url` fallback.
- Produces: view vars — `$reels` (Collection of Portfolio, published, ordered year desc then latest, eager `services`, `media`), `$chipServices` (Collection of Service having ≥1 published portfolio), `$clients` (Collection of distinct non-empty client strings), `$stats = ['count' => int, 'yearMin' => ?int, 'yearMax' => ?int]`. Blade data attributes contract (JS + tests): row `<a data-reel data-services="slug slug">`, video `<video data-reel-video data-src="URL" muted loop playsinline preload="none" poster="...">` (poster attr only when non-empty), chips `<button data-chip="all|slug">`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Public/PortfolioIndexTest.php`:

```php
<?php

use App\Models\Portfolio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

it('renders a reel row per published portfolio with lazy video markup', function () {
    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    $portfolio->addMedia(UploadedFile::fake()->create('film.mp4', 100, 'video/mp4'))
        ->withAttributes(['mime_type' => 'video/mp4'])
        ->toMediaCollection('gallery');

    $response = $this->get('/portfolio')->assertOk();

    $response->assertSee('data-reel', false)
        ->assertSee('data-src="'.$portfolio->getFirstMediaUrl('gallery').'"', false)
        ->assertSee('preload="none"', false)
        ->assertSee($portfolio->client);

    // videos must not eager-load: no bare src= on reel videos
    expect(substr_count($response->getContent(), '<video data-reel-video src='))->toBe(0);
});

it('renders outcome-led hero with derived proof', function () {
    $this->get('/portfolio')
        ->assertOk()
        ->assertSee('people act', false)
        ->assertSee((string) Portfolio::published()->count());
});

it('renders service chips only for services with published work', function () {
    $this->get('/portfolio')
        ->assertOk()
        ->assertSee('data-chip="all"', false)
        ->assertSee('data-chip="videography"', false);
});

it('renders client marquee from real client names', function () {
    $this->get('/portfolio')->assertOk()->assertSee('Salesforce');
});

it('skips draft portfolios', function () {
    Portfolio::query()->update(['status' => 'draft']);
    $this->get('/portfolio')->assertOk()->assertDontSee('data-reel-video', false);
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test tests/Feature/Public/PortfolioIndexTest.php`
Expected: FAIL — old view has no `data-reel` markup.

- [ ] **Step 3: Rewrite `PortfolioController::index`**

```php
public function index(): View
{
    $reels = Portfolio::published()
        ->with(['services', 'industry', 'media'])
        ->orderByDesc('year')
        ->latest()
        ->get();

    $years = $reels->pluck('year')->filter();

    return view('portfolio.index', [
        'reels' => $reels,
        'chipServices' => Service::whereHas('portfolios', fn ($q) => $q->where('status', 'published'))
            ->orderBy('order')->get(),
        'clients' => $reels->pluck('client')->filter()->unique()->values(),
        'stats' => [
            'count' => $reels->count(),
            'yearMin' => $years->min(),
            'yearMax' => $years->max(),
        ],
    ]);
}
```

Add `use App\Models\Service;`. This requires the inverse relation — add to `app/Models/Service.php`:

```php
/** @return BelongsToMany<Portfolio> */
public function portfolios(): BelongsToMany
{
    return $this->belongsToMany(Portfolio::class);
}
```

(with `use Illuminate\Database\Eloquent\Relations\BelongsToMany;`; same PHPStan-generics note as Task 1.)

- [ ] **Step 4: Rewrite the index view**

Replace the body of `resources/views/portfolio/index.blade.php` (keep the `<x-layouts.app ...>` opening with title/description/canonical, keep a `<x-slot name="head"><style>...</style></x-slot>` block, replace all old sections). New structure:

```blade
    {{-- 01 HERO --}}
    <section class="pfx-hero" data-screen-label="01 Intent">
        <div class="pfx-hero__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Portfolio</span></div>
        <h1 data-split>Films that make <em>people act.</em></h1>
        <p class="pfx-hero__sub">Cinema-grade films and photography for brands that need to be remembered.</p>
        @if ($stats['count'])
            <p class="pfx-hero__proof">
                {{ $stats['count'] }} productions{{ $stats['yearMax'] ? ' · '.($stats['yearMin'] === $stats['yearMax'] ? $stats['yearMax'] : $stats['yearMin'].'—'.$stats['yearMax']) : '' }}
            </p>
        @endif
    </section>

    {{-- 02 CLIENT MARQUEE --}}
    @if ($clients->isNotEmpty())
        <section class="pfx-marquee" aria-label="Clients">
            <div class="pfx-marquee__track">
                @foreach ([$clients, $clients] as $loop_set)
                    @foreach ($loop_set as $client)
                        <span>{{ $client }}</span><i>·</i>
                    @endforeach
                @endforeach
            </div>
        </section>
    @endif

    {{-- 03 CHIPS --}}
    @if ($chipServices->count() > 1)
        <div class="pfx-chips" role="tablist" aria-label="Filter by service">
            <button class="is-on" data-chip="all">All</button>
            @foreach ($chipServices as $svc)
                <button data-chip="{{ $svc->slug }}">{{ $svc->title }}</button>
            @endforeach
        </div>
    @endif

    {{-- 04 REEL STACK --}}
    <section class="pfx-stack" data-screen-label="02 Work">
        @foreach ($reels as $item)
            @php
                $poster = $item->getFirstMediaUrl('cover') ?: $item->cover_url;
                $film = $item->getFirstMediaUrl('gallery');
                $slugs = $item->services->pluck('slug')->implode(' ');
            @endphp
            <a class="pfx-reel reveal" data-reel data-services="{{ $slugs }}" href="{{ url('/portfolio/'.$item->slug) }}">
                @if ($film)
                    <video data-reel-video data-src="{{ $film }}" muted loop playsinline preload="none"
                           @if ($poster) poster="{{ $poster }}" @endif></video>
                @elseif ($poster)
                    <img src="{{ $poster }}" alt="{{ $item->title }}" loading="lazy" decoding="async">
                @endif
                <span class="pfx-reel__scrim" aria-hidden="true"></span>
                @if ($film)
                    <button class="pfx-reel__play" data-reel-play aria-label="Play preview">▶</button>
                @endif
                <div class="pfx-reel__body">
                    <span class="pfx-reel__tag">{{ $item->services->pluck('title')->implode(' · ') }}{{ $item->year ? ' · '.$item->year : '' }}</span>
                    <h2>{{ $item->title }}</h2>
                    @if ($item->client)<span class="pfx-reel__client">{{ $item->client }}</span>@endif
                </div>
            </a>
        @endforeach
    </section>
```

After the sections, the inline script (same pattern as home strip):

```blade
    <script>
    (() => {
        const rows = [...document.querySelectorAll('[data-reel]')];
        if (!rows.length) return;
        const touch = window.matchMedia('(hover: none)').matches;
        let active = null;

        const play = (v) => { if (!v.src) v.src = v.dataset.src; v.play().catch(() => {}); };
        const stop = (v) => v.pause();

        if (!touch) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach((e) => {
                    const v = e.target.querySelector('[data-reel-video]');
                    if (!v) return;
                    if (e.isIntersecting && e.intersectionRatio >= 0.6) {
                        if (active && active !== v) stop(active);
                        active = v; play(v);
                    } else if (active === v) {
                        stop(v); active = null;
                    }
                });
            }, { threshold: [0, 0.6] });
            rows.forEach((r) => io.observe(r));
        } else {
            document.querySelectorAll('[data-reel-play]').forEach((btn) => {
                btn.addEventListener('click', (ev) => {
                    ev.preventDefault(); ev.stopPropagation();
                    const v = btn.closest('[data-reel]').querySelector('[data-reel-video]');
                    if (!v) return;
                    if (v.paused) { if (active && active !== v) stop(active); active = v; play(v); }
                    else stop(v);
                });
            });
        }

        document.querySelectorAll('[data-chip]').forEach((chip) => {
            chip.addEventListener('click', () => {
                document.querySelectorAll('[data-chip]').forEach((c) => c.classList.remove('is-on'));
                chip.classList.add('is-on');
                const key = chip.dataset.chip;
                rows.forEach((r) => {
                    const show = key === 'all' || (r.dataset.services || '').split(' ').includes(key);
                    r.classList.toggle('is-hidden', !show);
                    if (!show) { const v = r.querySelector('[data-reel-video]'); if (v) stop(v); }
                });
            });
        });
    })();
    </script>
```

Styles in the head slot (`<style>` block) — use exactly these rules (they follow the site's CSS-variable conventions):

```css
.pfx-hero { padding: 140px 40px 40px; }
.pfx-hero h1 { font-family: var(--f-display); font-size: clamp(44px, 7vw, 96px); letter-spacing: -0.03em; }
.pfx-hero__sub { max-width: 520px; color: var(--paper-dim); margin-top: 18px; }
.pfx-hero__proof { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.16em; text-transform: uppercase; margin-top: 26px; color: var(--paper-dim); }
.pfx-marquee { overflow: hidden; border-block: 1px solid var(--line); padding: 14px 0; }
.pfx-marquee__track { display: inline-flex; gap: 28px; white-space: nowrap; animation: pfx-scroll 40s linear infinite; font-family: var(--f-mono); font-size: 12px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); }
@keyframes pfx-scroll { to { transform: translateX(-50%); } }
.pfx-chips { display: flex; flex-wrap: wrap; gap: 10px; padding: 28px 40px 8px; }
.pfx-chips button { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.14em; text-transform: uppercase; padding: 8px 14px; border: 1px solid var(--line); background: transparent; color: var(--paper-dim); cursor: pointer; }
.pfx-chips button.is-on { border-color: var(--red); color: #fff; }
.pfx-stack { display: flex; flex-direction: column; gap: 22px; padding: 26px 40px 80px; }
.pfx-reel { position: relative; display: block; aspect-ratio: 21 / 9; overflow: hidden; background: var(--ink-2); }
.pfx-reel.is-hidden { display: none; }
.pfx-reel video, .pfx-reel img { width: 100%; height: 100%; object-fit: cover; }
.pfx-reel__scrim { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.72), transparent 55%); }
.pfx-reel__body { position: absolute; left: 28px; bottom: 24px; right: 28px; }
.pfx-reel__body h2 { font-family: var(--f-display); font-size: clamp(24px, 3.4vw, 44px); letter-spacing: -0.02em; }
.pfx-reel__tag, .pfx-reel__client { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.15em; text-transform: uppercase; color: var(--paper-dim); }
.pfx-reel__play { display: none; }
@media (hover: none) {
  .pfx-reel__play { display: grid; place-items: center; position: absolute; right: 16px; bottom: 16px; width: 44px; height: 44px; border-radius: 50%; border: 1px solid var(--line); background: rgba(0,0,0,0.5); color: #fff; }
}
@media (max-width: 760px) {
  .pfx-hero { padding: 110px 20px 28px; }
  .pfx-chips { padding: 20px 20px 4px; }
  .pfx-stack { padding: 18px 20px 60px; gap: 14px; }
  .pfx-reel { aspect-ratio: 16 / 9; }
}
```

Delete the old hero/featured/grid sections and their now-unused styles from this file.

- [ ] **Step 5: Run tests**

Run: `php artisan test tests/Feature/Public/PortfolioIndexTest.php tests/Feature/Public/PortfolioPageTest.php`
Expected: PASS. If other existing tests referenced removed markup (e.g. old grid strings), update them to the new markup and note each change in the report.

- [ ] **Step 6: Full checks**

Run: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: all green. Leave uncommitted.

---

### Task 4: Case page trust layer

**Files:**
- Modify: `app/Http/Controllers/Public/PortfolioController.php` (show method eager loads)
- Modify: `resources/views/portfolio/show.blade.php` (Discipline line; Result + testimonial + CTA sections after gallery)
- Test: `tests/Feature/Public/PortfolioPageTest.php` (extend)

**Interfaces:**
- Consumes: `results` cast shape `[['label','value']]`, `Testimonial::portfolio()` + `published()` scope, `SiteSetting::get('whatsapp_url')`, `Portfolio::services()`.
- Produces: nothing later tasks need.

- [ ] **Step 1: Write the failing tests**

Add to `tests/Feature/Public/PortfolioPageTest.php` (reuse the file's existing seed/beforeEach conventions; add imports for `App\Models\Testimonial`, `App\Models\Service`, and `App\Models\SiteSetting` if missing):

```php
it('shows result facts only when filled', function () {
    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();

    $this->get('/portfolio/'.$portfolio->slug)->assertOk()->assertDontSee('The result');

    $portfolio->update(['results' => [['label' => 'Deliverables', 'value' => '14 films']]]);

    $this->get('/portfolio/'.$portfolio->slug)
        ->assertOk()
        ->assertSee('The result')
        ->assertSee('Deliverables')
        ->assertSee('14 films');
});

it('shows a linked published testimonial only', function () {
    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    $t = Testimonial::published()->firstOrFail();
    $t->update(['portfolio_id' => $portfolio->id]);

    $this->get('/portfolio/'.$portfolio->slug)->assertOk()->assertSee(e($t->client_name), false);

    $t->update(['is_published' => false]);
    $this->get('/portfolio/'.$portfolio->slug)->assertOk()->assertDontSee(e($t->client_name), false);
});

it('shows the CTA band with quote trigger', function () {
    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();

    $this->get('/portfolio/'.$portfolio->slug)
        ->assertOk()
        ->assertSee('Want a film like this', false)
        ->assertSee('data-quote-trigger', false);
});

it('lists all services on the discipline line', function () {
    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    $extra = Service::where('slug', '!=', 'videography')->firstOrFail();
    $portfolio->services()->syncWithoutDetaching([$extra->id]);

    $this->get('/portfolio/'.$portfolio->slug)
        ->assertOk()
        ->assertSee($extra->title);
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test tests/Feature/Public/PortfolioPageTest.php`
Expected: new tests FAIL (sections absent; Discipline shows single service).

- [ ] **Step 3: Controller show eager loads**

```php
public function show(string $slug): View
{
    $item = Portfolio::published()->where('slug', $slug)
        ->with(['services', 'media'])
        ->firstOrFail();

    $testimonial = Testimonial::published()->where('portfolio_id', $item->id)->orderBy('order')->first();

    return view('portfolio.show', compact('item', 'testimonial'));
}
```

Add `use App\Models\Testimonial;`.

- [ ] **Step 4: View changes**

In `resources/views/portfolio/show.blade.php`:

(a) Discipline meta line — replace `{{ $item->service?->title }}` with:

```blade
{{ $item->services->pluck('title')->implode(' · ') ?: $item->service?->title }}
```

(b) After the `{{-- GALLERY --}}` section, insert:

```blade
    {{-- RESULT --}}
    @if (! empty($item->results))
        <section class="case-body case-result">
            <div><h2>The result</h2></div>
            <div>
                <dl class="case-result__facts">
                    @foreach ($item->results as $fact)
                        <div>
                            <dt>{{ $fact['label'] ?? '' }}</dt>
                            <dd>{{ $fact['value'] ?? '' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>
    @endif

    {{-- TESTIMONIAL --}}
    @if ($testimonial)
        <section class="case-quote">
            <blockquote>
                <p>&ldquo;{{ $testimonial->quote }}&rdquo;</p>
                <footer>{{ $testimonial->client_name }}@if ($testimonial->role_company) — {{ $testimonial->role_company }}@endif</footer>
            </blockquote>
        </section>
    @endif

    {{-- CTA --}}
    <section class="case-cta">
        <h2>Want a film like this?</h2>
        <div class="case-cta__actions">
            <a class="btn btn--red" href="#quote" data-quote-trigger data-cursor="LET'S TALK">Start a project <span class="arr"></span></a>
            @if ($wa = \App\Models\SiteSetting::get('whatsapp_url'))
                <a class="btn btn--ghost" href="{{ $wa }}" target="_blank" rel="noopener">WhatsApp us</a>
            @endif
        </div>
    </section>
```

(c) Styles in the page's existing `<style>` head block:

```css
.case-result__facts { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 24px; }
.case-result__facts dt { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--paper-dim); }
.case-result__facts dd { font-family: var(--f-display); font-size: 28px; margin-top: 6px; }
.case-quote { padding: 72px 40px; border-top: 1px solid var(--line); }
.case-quote p { font-family: var(--f-display); font-size: clamp(22px, 3vw, 34px); line-height: 1.35; max-width: 880px; }
.case-quote footer { margin-top: 18px; font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.15em; text-transform: uppercase; color: var(--paper-dim); }
.case-cta { padding: 80px 40px; border-top: 1px solid var(--line); text-align: center; }
.case-cta h2 { font-family: var(--f-display); font-size: clamp(30px, 4.5vw, 56px); letter-spacing: -0.02em; }
.case-cta__actions { display: flex; gap: 16px; justify-content: center; margin-top: 28px; flex-wrap: wrap; }
@media (max-width: 760px) { .case-quote, .case-cta { padding: 56px 20px; } }
```

- [ ] **Step 5: Run tests, then full checks**

Run: `php artisan test tests/Feature/Public/PortfolioPageTest.php`
Expected: PASS.
Then: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: all green. Leave uncommitted.

---

### Task 5: Pivot consumers + sweep

**Files:**
- Modify: `app/Http/Controllers/Public/ServiceController.php:15` (pivot query)
- Modify: `resources/views/components/card-portfolio.blade.php` (if it prints `service->title`, switch to services list — check first)
- Modify: `docs/deploy/s3-cloudfront-media.md` (append migration note)
- Test: `tests/Feature/Public/ServicePageTest.php` (extend)

**Interfaces:**
- Consumes: `Portfolio::services()` / `Service::portfolios()` from Tasks 1/3.

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/Public/ServicePageTest.php` (match its existing seed/beforeEach conventions):

```php
it('shows work attached via the services pivot', function () {
    $service = Service::where('slug', 'post-production')->firstOrFail();
    $portfolio = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    $portfolio->services()->syncWithoutDetaching([$service->id]);

    $this->get('/services/'.$service->slug)->assertOk()->assertSee($portfolio->title);
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test tests/Feature/Public/ServicePageTest.php`
Expected: new test FAILS (query still uses `service_id`; ins-navy is videography).

- [ ] **Step 3: Switch the query**

In `app/Http/Controllers/Public/ServiceController.php` replace the `$work` line:

```php
$work = Portfolio::published()
    ->whereHas('services', fn ($q) => $q->where('services.id', $service->id))
    ->with(['services', 'media'])
    ->latest()->take(6)->get();
```

- [ ] **Step 4: Sweep remaining single-service reads**

Run: `grep -rn "->service?->\|->service->\|'service_id'" resources/views app/Http/Controllers/Public | grep -v services`
For each front-end hit (e.g. `card-portfolio.blade.php`), render the pivot list with legacy fallback: `$item->services->pluck('title')->implode(' · ') ?: $item->service?->title`. Leave admin/Filament legacy references and the model column itself alone. List every change in the report.

- [ ] **Step 5: Runbook note**

Append to `docs/deploy/s3-cloudfront-media.md` under Deploy order:

```markdown
> Portfolio revamp (same release): `php artisan migrate` creates `portfolio_service`
> (backfilled from `service_id`), `portfolios.results`, `testimonials.portfolio_id`.
> No data entry required — trust sections stay hidden until real content is added
> in admin (Result facts on portfolios, "Attach to case" on testimonials).
```

- [ ] **Step 6: Run tests, then full checks**

Run: `php artisan test tests/Feature/Public/ServicePageTest.php`
Expected: PASS.
Then: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: all green. Leave uncommitted.
