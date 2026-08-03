# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

TheLastClicks — a photography/videography studio site. One Laravel 11 app, two surfaces:

1. **Public site** — server-rendered Blade (`routes/web.php` → `App\Http\Controllers\Public\*`).
2. **Filament 3 admin** at `/admin` — content, leads/quote pipeline, site settings, SEO.

There is no API layer and no JS frontend. A Next.js rewrite lived in `web/` and was removed, along with its `/api/v1` backend, in 2026-08. If you find a reference to `web/`, `/api/v1`, ISR revalidation or WebGL scenes, it is a leftover — delete it rather than restoring anything to match.

## PHP version — use `./bin/php`

Composer platform is pinned to **8.4.0**. `bin/php` is a shim at `/usr/local/opt/php@8.4/bin/php`. A bare `php` on this machine may be a different version.

```bash
./bin/php artisan …
./bin/php vendor/bin/pest
export PATH="$PWD/bin:$PATH"   # or prepend for the session
```

## Commands

```bash
# Tests (Pest 3)
./bin/php vendor/bin/pest                          # full suite
./bin/php vendor/bin/pest --filter='homepage'      # one test by name
./bin/php vendor/bin/pest tests/Feature/Public     # a directory or file

# Lint / static analysis — CI runs exactly these three, in this order
./vendor/bin/pint --test                        # drop --test to fix
./vendor/bin/phpstan analyse --memory-limit=512M --no-progress   # larastan, level 6, app/ only
./vendor/bin/pest --no-coverage

# Dev
composer dev        # serve + queue:listen + pail + vite, concurrently
npm run dev         # vite only
npm run build

# Deploy (one command, aborts non-zero on first failure)
./bin/php artisan deploy [--dry-run] [--skip-composer|--skip-npm|--skip-seed|--skip-media|--skip-permissions]
./bin/php artisan app:preflight [--strict]      # APP_URL / APP_DEBUG / storage perms / media disk / queue
```

Tests use in-memory SQLite (see `phpunit.xml`); production is MySQL.

## Architecture

### Response caching

Every public GET route is wrapped in `cacheResponse` (spatie/laravel-responsecache) — one group in `routes/web.php`, with the POST mutations deliberately outside it.

Invalidation is by observer, registered in `AppServiceProvider::boot()`. Per-model observers call `ResponseCache::clear()`. `ClearsResponseCacheObserver` is additionally attached to `Client` and to Spatie's `Media` model, because a media-only upload doesn't dirty its parent and so would never fire the parent's observer — that gap is what left editors staring at an imageless cached page.

After anything that changes `public/build`, `responsecache:clear` is mandatory: cached HTML references the old hashed asset names and 404s its own CSS/JS.

### Media

Two layers stacked:

- **spatie/laravel-medialibrary** for uploads (`cover`, `hero`, logos), disk from `MEDIA_DISK` (s3 in production, CloudFront in front).
- **`MediaItem`** — a polymorphic ordered list of `image` / `video` / `youtube` rows, shared by `Work` and `Industry` via the `HasMediaItems` trait. That trait boots a `deleting` hook that deletes children through Eloquent so medialibrary's cleanup runs; a query-builder delete would orphan the files on S3.
- **`App\Support\MediaUrl`** is the single resolver: blank → null, already-a-URL → passthrough, otherwise resolved on a disk. It builds s3 URLs from `config('filesystems.disks.s3.url')` by string concatenation rather than `Storage::disk()->url()` — resolving the s3 driver instantiates `PortableVisibilityConverter`, which hard-crashes on a server with a stale `vendor/`. Do not "simplify" this back to the facade, and do not add a `visibility` key to the s3 disk.

### SEO

`SeoPage::forPath()` gives admin-managed per-URL overrides; the layout (`components/layouts/app.blade.php`) merges them field-by-field over whatever the page passed, so a row that only sets a title keeps the page's own description. `App\Support\AppUrl` centralises the "is APP_URL actually public" question that both `sitemap:generate` and `app:preflight` ask. `public/sitemap.xml` is generated, never committed.

Retired URLs live on as `Route::redirect(…, 301)` entries in `routes/web.php` — that file is as much a redirect map as a route table. Keep new redirects there, and mind the ordering comments (exact routes are registered before the wildcards that would otherwise swallow them).

### Frontend assets

The public site's CSS is **hand-written**, not Tailwind: `resources/css/core.css` (tokens, chrome, motion) and `pages.css` (~6k lines of page styles), zero `@tailwind`/`@apply`. Tailwind exists only for the Filament admin theme (`resources/css/filament/admin/theme.css`). Design tokens are CSS custom properties on `:root` in `core.css` — note the names are historical and inverted: `--ink*` are dark *surfaces*, `--paper*` are light *text*.

JS load order matters: **`chrome.js` must load before `core.js`.** `chrome.js` injects the shared nav/footer/preloader/quote-modal/cursor HTML; `core.js` then wires behaviour onto it. Blade passes admin-managed values to `chrome.js` through `<meta>` tags (`brand-logo`, `cta-video`) — an absent meta means render nothing, never a bundled fallback.

`docs/motion-spec.md` is the binding motion contract (transform+opacity only, `grid-template-rows` not `max-height`, `will-change` cleared on finish, reduced-motion is gentler not zero, nothing polls). It also records what is still outstanding — the duration tokens are named there but not yet declared in `core.css`.

### Admin

Filament panel in `AdminPanelProvider` — resources/pages/widgets auto-discovered, nav groups ordered Leads → Content → Site → Access. Authorization is filament-shield + Laravel policies (`app/Policies/`), so a new resource needs a matching policy and a shield/`PermissionsSeeder` run before anyone can see it. `SiteSetting` is a `key`/`value_json` store with typed static accessors (`workTileRatio()`, `ctaVideoUrl()`, `brandLogoUrl()`); values that reach CSS are allowlisted (`WORK_TILE_RATIOS`) rather than trusted.

## Testing conventions

- Pest, `tests/Feature` bound to `Tests\TestCase`. `RefreshDatabase` is opted into per file (`uses(RefreshDatabase::class)`), not globally, and most feature tests start with `beforeEach(fn () => $this->seed())` — seeders are idempotent and are the fixture layer.
- `tests/Pest.php` exposes **`assertQueryCount($max, fn () => …)`**, which fails a test that issues more queries than expected. It was the N+1 guard on the deleted API endpoints; it is still available and worth reaching for on any page that renders a collection.
- Tests assert on *structure*, not marketing copy — e.g. the homepage test checks that `.hero__title` exists and is non-empty rather than pinning the tagline, because the copy gets rewritten and pinning it just breaks the suite.

## Deploy landmines

Full detail in `docs/DEPLOYMENT.md`; the ones that have actually broken production:

- **Never skip `composer install` on deploy.** A stale `vendor/` 500s every page showing s3 media (`PortableVisibilityConverter not found`) and breaks `view:cache`.
- **Never `chmod -R 775 storage bootstrap/cache`** — it sets +x on the tracked `.gitignore` placeholders and blocks the next `git pull`. Split dirs (`2775`) from files (`664`).
- `php artisan deploy` runs each step as its own subprocess on purpose (step one swaps `vendor/` under the running process).
- `.npmrc` pins the public registry so a server whose global npmrc points at GitHub Packages can't 404 `npm ci`.
- `PageSeoSeeder` and `HeroSlidesSeeder` are not part of `db:seed`; run each once per environment.

## Repo conventions

- Comments here explain *why*, usually naming the failure the code prevents. Match that when editing — a comment that only restates the line is noise, but silently dropping one of these removes the only record of a bug.
- Commits are Conventional Commits with a scope (`fix(web):`, `feat(admin):`, `docs:`).
- Planning docs live in `docs/superpowers/{specs,plans}/` dated `YYYY-MM-DD`; superseded ones move to `docs/archive/`.
