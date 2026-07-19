# S3 + CloudFront Media Design

**Date:** 2026-07-19
**Status:** Approved

## Goal

Serve all site media (admin uploads and the currently static hero/strip videos) from S3 behind CloudFront, in every environment. Static videos in `public/videos` (289 MB) become admin-managed media attached to Portfolio models via spatie/laravel-medialibrary.

## Decisions (from brainstorming)

- **Scope:** everything — static videos/posters and all Filament media library uploads.
- **Infra:** S3 bucket and CloudFront distribution already exist. Private bucket with CloudFront Origin Access Control (OAC). App must not send ACLs.
- **Environments:** S3 + CloudFront everywhere, including local dev. No local-disk fallback.
- **Approach:** full medialibrary conversion (Approach B). Static videos become media on Portfolio models; hero/strip composition becomes SiteSetting-driven.

## Constraint discovered during design

`SiteSetting` uses a **string primary key**; the `media` table's `model_id` is a bigint morph, so media cannot attach to `SiteSetting` rows. Therefore media attaches to **Portfolio** (each static video already corresponds 1:1 to a seeded portfolio slug), and SiteSettings stores only ordering and copy (tags, titles, meta).

## Architecture

### 1. Storage & config

- `.env`: `FILESYSTEM_DISK=s3`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_URL=https://<cloudfront-domain>`.
- Publish `config/media-library.php`:
  - `disk_name => env('MEDIA_DISK', 's3')`
  - `remote.extra_headers` includes `CacheControl: max-age=31536000, immutable` so CloudFront and browsers cache aggressively.
- The `s3` disk in `config/filesystems.php` keeps no `visibility` key — with OAC and Object Ownership enforced, ACL writes fail. `Storage::url()` resolves through `AWS_URL`, so every generated media URL is a CloudFront URL.

### 2. Media model

- Portfolio keeps its two collections:
  - `cover` — singleFile image (the poster jpg).
  - `gallery` — video files (mp4).
- A gallery video's poster is the portfolio's `cover`. This removes the path-rewriting hack in `resources/views/portfolio/show.blade.php` (`str_replace('/videos/', '/videos/posters/', $src)`).
- Other models (Service `hero`/`gallery`, Post `cover`, Industry `hero`) are unchanged; they inherit the s3 disk via config.

### 3. Hero & strip become data-driven

- `SiteSetting('home_strip')`: ordered array of `{portfolio_slug, tag, title, meta}` — replaces the hardcoded `$stripCards` array in `resources/views/home.blade.php`.
- `SiteSetting('hero_videos')`: ordered array of portfolio slugs (currently 3) — replaces hardcoded `<video>` sources in `resources/views/components/hero.blade.php`.
- Both editable on the existing Filament `SiteSettingsPage` via repeater fields (slug select + text fields).
- Blade resolves each slug to a Portfolio and renders `getFirstMediaUrl('gallery')` for the video and `getFirstMediaUrl('cover')` for the poster. Missing slug or missing media: card is skipped (render nothing for that entry).

### 4. Import & migration

- New artisan command `media:import-local`:
  - For each portfolio in the seeder's video mapping, attach `public/videos/{video}.mp4` to `gallery` and `public/videos/posters/{video}.jpg` to `cover` via medialibrary (which uploads to S3). Idempotent — skips portfolios that already have media in the collection.
  - Migrates any existing media rows on the `public` disk: copies the file to s3, updates `disk` and `conversions_disk` columns.
- `PortfoliosSeeder` stops writing `/videos/...` paths into `cover_url` / `gallery_urls`; those columns remain only as a legacy fallback in views (existing `?:` chains).
- Seeds `home_strip` and `hero_videos` SiteSettings with the current hardcoded values.
- After import is verified in production, `public/videos` can be deleted (removes 289 MB from repo/server).

### 5. Filament admin

- Existing `SpatieMediaLibraryFileUpload` fields automatically store to s3 once `disk_name` changes. Livewire temporary uploads remain on the local disk (unchanged, correct).

### 6. Error handling

- Missing media on a portfolio → portfolio/blog/service views fall back to `cover_url` legacy columns via existing `?:` patterns; hero/strip entries are skipped entirely (no legacy path fallback there, since legacy paths point at deleted `public/videos`).
- Import command reports per-file success/failure and exits non-zero if any upload fails; safe to re-run.
- S3 disk keeps `'throw' => false` (matches current config) so a storage outage degrades to empty URLs rather than 500s.

## Testing

Pest feature tests with `Storage::fake('s3')`:

1. `media:import-local` attaches cover + gallery media to mapped portfolios and is idempotent.
2. Home page renders strip cards from `home_strip` SiteSetting with media URLs (and skips entries whose portfolio lacks media).
3. Hero renders videos from `hero_videos` SiteSetting.
4. Media URL generation uses the configured `AWS_URL` (CloudFront domain).

## Out of scope

- Video transcoding / poster generation (ffmpeg) — posters remain pre-made jpgs.
- Image conversions/responsive images.
- CloudFront invalidation automation (immutable cache headers + unique medialibrary paths make it unnecessary).
- Provisioning IaC for bucket/distribution (already exist).
