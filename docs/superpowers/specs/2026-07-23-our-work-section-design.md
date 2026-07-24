# "Our Work" Section Design

**Date:** 2026-07-23
**Status:** Approved

## Goal

An admin-managed "Our Work" showcase supporting uploaded images, uploaded videos, and embedded YouTube videos. Appears as a small featured section on the homepage and as a full `/our-works` masonry page. Items open in a lightbox carousel — no per-item detail page.

## Context

The previous Portfolio feature was removed (model, Filament resource, views, tests, and a `2026_07_23_000000_drop_portfolio_feature.php` migration; `/portfolio` and `/portfolio/{slug}` now 301 to `/`). This is a fresh build, not a retrofit. Media continues to use spatie/laravel-medialibrary on the S3 disk behind CloudFront.

## Decisions (from brainstorming)

- Placement: a **small** homepage section (featured items + "view all" link) **and** a dedicated `/our-works` page listing all published items.
- Media: **multiple media per item**, mixing images, uploaded videos, and YouTube embeds in one sortable order.
- Viewing: **lightbox on the grid**. No per-item detail route.
- Layout: **masonry grid** (variable tile heights) with scroll-reveal animation and hover effects.

## Architecture

### 1. Data model

**`works`**
- `id`, `title`, `slug` (unique, from title via HasSlug), `summary` (nullable text), `client` (nullable), `year` (nullable), `order` (int, default 0), `is_published` (bool, default true), `is_featured` (bool, default false), timestamps.
- Implements `HasMedia`; collection `cover` (`singleFile`) — the grid thumbnail.
- `Work::published()` scope; ordering by `order` then `id`.

**`work_media`**
- `id`, `work_id` (FK, cascade delete), `type` (enum-ish string: `image` | `video` | `youtube`), `youtube_url` (nullable), `caption` (nullable), `order` (int, default 0), timestamps.
- Implements `HasMedia`; collection `file` (`singleFile`) — holds the uploaded image or video for `image`/`video` rows. `youtube` rows carry no upload.
- `Work::mediaItems()` hasMany, ordered by `order`.

A single child table (rather than separate image/video/YouTube stores) is what lets the three kinds interleave in one drag-sortable sequence.

### 2. YouTube handling

- Admin pastes any common YouTube URL form (`watch?v=`, `youtu.be/`, `/embed/`, `/shorts/`).
- A `WorkMedia::youtubeId(): ?string` accessor extracts the 11-character ID via regex; returns null if unparseable.
- Embed URL: `https://www.youtube-nocookie.com/embed/{id}` (privacy-friendly), built only at render time.
- Tile thumbnail for a YouTube item: `https://img.youtube.com/vi/{id}/hqdefault.jpg`.
- Validation: the `youtube_url` field is required when `type = youtube` and must parse to an ID.

### 3. Admin (Filament)

- **`WorkResource`** — form: title (live slug), slug, summary, client, year, order, `is_published`, `is_featured` toggles, and a `SpatieMediaLibraryFileUpload` for `cover` (images only). Table: cover thumbnail, title, client, year, published/featured icons, reorderable by `order`.
- **`WorkMediaRelationManager`** on WorkResource — list/create/edit/delete/reorder media rows. Per row: `type` select (live); `SpatieMediaLibraryFileUpload` for `file` shown when type is `image` (accepts jpeg/png/webp) or `video` (accepts mp4, max 150 MB); `youtube_url` text shown when type is `youtube`; `caption`; drag reorder on `order`.
- Uploads land on the configured media disk (S3) like all other media — no ACLs, URLs resolve through CloudFront.

### 4. Frontend

- **Shared component `<x-work-grid :works="..." />`** renders a **masonry** tile grid (CSS multi-column so tiles keep their natural aspect ratios and pack with variable heights; `break-inside: avoid` on tiles). Each tile: cover image (fallbacks below), title, client/year meta, and a `data-work-media` attribute carrying that item's media as JSON (`[{type, url, caption}]`, where `url` is the media URL or the YouTube embed URL).
- **Cover fallback chain:** `cover` media → first `image` media item → YouTube thumbnail of the first `youtube` item → dark tile with title only. Never breaks.
- **Animation:** tiles carry the site's existing `reveal` class so the shared IntersectionObserver in `core.js` fades/lifts them in on scroll, staggered via `data-delay`. Respects `prefers-reduced-motion` (the existing observer already does).
- **Hover:** cover image scales slightly, a dark-to-transparent scrim deepens, the title/meta lift, and a small "view" affordance appears — transitions use the site's `--ease` tokens. Hover effects are suppressed under `(hover: none)`.
- **Homepage:** a small "Our Work" section rendering featured published works (falls back to the most recent published works when none are flagged featured), capped at 6 tiles, with a "View all work" link to `/our-works`. Section is skipped entirely when there are no published works.
- **`/our-works` page:** new route + controller listing all published works in the same masonry grid, plus a page header consistent with other inner pages.
- **Lightbox:** one vanilla-JS module (matching the site's existing inline-script pattern) — opens on tile click, renders the item's media as `<img>`, `<video controls>`, or a YouTube `<iframe>`, with prev/next, keyboard (Esc/←/→), and focus handling. Pauses/unloads video and clears the iframe `src` on close so nothing keeps playing.
- **Nav/footer:** add an "Our Works" link (`/our-works`) to `NAV_LINKS` in `resources/js/chrome.js` and to the footer's Studio column.

### 5. Error handling / edge cases

- Work with zero media items: tile renders but is not clickable (no lightbox).
- `youtube_url` that fails to parse: accessor returns null; that media row is skipped at render and flagged in admin validation.
- Unpublished works never appear on either surface.
- Missing uploaded file on an `image`/`video` row: row is skipped at render.

### 6. Caching

`WorkObserver` clears the response cache on save/delete, mirroring the existing observers (`ServiceObserver`, `TestimonialObserver`, etc.), registered in `AppServiceProvider`.

## Testing

Pest feature tests (media tests use `config(['media-library.disk_name' => 's3'])` + `Storage::fake('s3')`):

1. Admin: create a work; add image, video, and YouTube media rows; they persist with correct `type` and order.
2. `youtubeId()` extracts the ID from `watch?v=`, `youtu.be/`, `/embed/`, `/shorts/` forms and returns null for junk.
3. `/our-works` renders published works and excludes unpublished ones.
4. Homepage renders featured works (max 6) with a link to /our-works; section absent when no published works exist.
5. Tile cover falls back correctly (cover → first image → YouTube thumb).
6. Lightbox data: a tile's `data-work-media` JSON contains its media in order, and rows with missing files/unparseable URLs are excluded.

## Out of scope

- Per-item detail pages — lightbox only, by decision.
- Vimeo or other providers (YouTube only).
- Client-side filtering/categories on the work grid.
- Video transcoding or automatic poster generation for uploaded videos.
