# Industry Media + Shared Media Items Design

**Date:** 2026-07-23
**Status:** Approved

## Goal

Let admins manage industries with a title, short description, and a **media array** (uploaded images, uploaded videos, embedded YouTube) — the same capability Work has. Render industries on `/industries` as cards that open their media in the existing lightbox. Generalize the media-row system so Work and Industry share one implementation.

## Context

- `Industry` already exists (title, slug, summary, body, `hero` singleFile media, order) with a Filament `IndustryResource`, and all 7 industries are seeded with real title/summary/cover-image data.
- `/industries` currently renders **no** industry data — the page is a static clients-grid + marquee; `IndustryController` passes `$industries` and the view ignores it.
- The Work feature (just shipped) has `work_media` rows typed `image|video|youtube`, `Work::mediaPayload()`, a masonry grid component, and a JS lightbox. Work has **no production data yet**, so its storage can be refactored safely.

## Decisions (from brainstorming)

- **Generalize, don't duplicate:** convert `work_media` into one polymorphic media-items table shared by Work, Industry, and future models.
- **`/industries` renders industry cards + lightbox**, keeping the existing clients-grid and marquee sections.
- No fabricated media is seeded; industries display their existing seeded cover/summary until an admin adds media.

## Architecture

### 1. Polymorphic media rows

Rename/repoint `work_media` → **`media_items`**:
- `id`, `mediable_type` (string), `mediable_id` (bigint), `type` (`image|video|youtube`), `youtube_url` (nullable), `caption` (nullable), `order` (int), timestamps.
- Index on `(mediable_type, mediable_id, order)`.
- Migration converts existing rows by setting `mediable_type = App\Models\Work::class`, `mediable_id = work_id`, then dropping `work_id`. Safe: no production rows exist.

`App\Models\WorkMedia` becomes **`App\Models\MediaItem`** (table `media_items`), keeping its API unchanged: `file` singleFile collection, `youtubeId()`, `embedUrl()`, `thumbnailUrl()`, `resolvedUrl()`. Adds `mediable(): MorphTo`.

### 2. Shared trait

New `App\Models\Concerns\HasMediaItems` trait providing:
- `mediaItems(): MorphMany<MediaItem>` ordered by `order` then `id`.
- `mediaPayload(): list<array{type,url,caption}>` — ordered, skipping unresolvable rows (moved verbatim from `Work`).
- `coverUrl(): ?string` — explicit `cover` media → first image row's file → first YouTube thumbnail → `null`.

`Work` uses the trait (its existing methods are deleted in favour of it, so behaviour and tests are preserved). `Industry` uses the trait too; for Industry the cover chain starts at its existing `hero` collection, so the trait takes the cover collection name from a `mediaCoverCollection()` method defaulting to `'cover'`, which `Industry` overrides to `'hero'`. Industry additionally falls back to its legacy `image_url` column when no media exists, so seeded industries keep their covers.

### 3. Cascade + cache

- `MediaItem` rows are deleted through Eloquent by the parent's `deleting` observer hook so medialibrary cleanup runs (the existing `WorkObserver::deleting` pattern). `IndustryObserver` gains the same hook.
- The existing `WorkMediaObserver` becomes `MediaItemObserver`, registered against `MediaItem`, so any parent's media edit clears the response cache.

### 4. Admin

- The existing `MediaItemsRelationManager` moves to a shared location (`app/Filament/RelationManagers/MediaItemsRelationManager.php`) and is registered on **both** `WorkResource` and `IndustryResource`. Its form is unchanged: type select, one conditional upload bound to `file`, `youtube_url` required when type is `youtube`, caption, order, drag reorder.
- `IndustryResource` keeps its current fields; `summary` is the "short description".

### 5. `/industries` page

- `IndustryController@index` eager-loads `['media', 'mediaItems.media']`.
- The page gains an "Industries" section rendering each industry as a card: cover image, title, and `summary`. Cards with media are buttons carrying `data-work-media` (the shared lightbox payload attribute) and open the existing lightbox; cards without media render as non-interactive tiles.
- Reuses the existing `<x-work-grid>` markup contract. To avoid a Work-specific name for shared markup, the component is renamed **`<x-media-grid>`** and takes `:items` (any collection of models using `HasMediaItems` that expose `title` plus an optional `client`/`year` or `summary` line). `works/index.blade.php` and the homepage section are updated to the new name.
- Existing clients-grid and marquee sections stay.

## Testing

Pest (media tests set `config(['media-library.disk_name' => 's3'])` + `Storage::fake('s3')`):

1. Migration: existing work media rows survive the move to `media_items` with `mediable_type`/`mediable_id` set.
2. `Work` and `Industry` both return correct ordered `mediaPayload()` and skip unresolvable rows.
3. `Industry::coverUrl()` chain: `hero` media → first image row → YouTube thumb → legacy `image_url`.
4. Admin: media rows can be added to an Industry with each of the three types.
5. `/industries` renders each seeded industry's title and summary, marks media-bearing cards interactive, and leaves media-less ones non-interactive.
6. Existing Work tests continue to pass unchanged (regression guard for the refactor).

## Out of scope

- Industry detail pages (`/industries/{slug}` stays a 301 to the list).
- Seeding any media rows.
- Changing the clients-grid/marquee sections.
- Applying `HasMediaItems` to models beyond Work and Industry.
