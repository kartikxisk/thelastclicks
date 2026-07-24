# S3 + CloudFront media — deployment runbook

## Prerequisites
- S3 bucket (private, Object Ownership: bucket owner enforced) with CloudFront
  distribution using Origin Access Control (OAC) pointed at the bucket.
- IAM user/role for the app with `s3:PutObject`, `s3:GetObject`, `s3:DeleteObject`,
  `s3:ListBucket` on the bucket.

## Env (production and local)
    FILESYSTEM_DISK=s3
    MEDIA_DISK=s3
    FILAMENT_FILESYSTEM_DISK=s3  # RichEditor attachments
    AWS_ACCESS_KEY_ID=...
    AWS_SECRET_ACCESS_KEY=...
    AWS_DEFAULT_REGION=...
    AWS_BUCKET=...
    AWS_URL=https://<cloudfront-domain>

> **Outdated (Portfolio feature removed).** The Portfolio feature — model,
> pages, admin resource, `portfolios` / `portfolio_service` tables,
> `testimonials.portfolio_id`, the `home_strip` / `hero_videos` settings and the
> `media:import-local` command — was deleted. Steps referencing them no longer
> apply. The S3/CloudFront disk config below is still current.

## Deploy order
1. Deploy code; `composer install`.
2. `php artisan migrate && php artisan config:clear && php artisan db:seed`.
   Migrate drops the retired portfolio tables (`drop_portfolio_feature`).
3. Verify: the homepage hero reel (`public/videos/hero-reel.mp4`) and service
   pages serve their media; spot-check a video plays.
4. `php artisan responsecache:clear`.

> Portfolio revamp (same release): no data entry required — trust sections stay
> hidden until real content is added in admin (Result facts on portfolios,
> "Attach to case" on testimonials).

## Cleanup (only after step 4 verified in production)
- Delete `public/videos/` from the server and from git (`git rm -r public/videos`)
  — removes 289 MB. Old `/videos/...` paths in the DB stay harmless: media-backed
  rendering wins everywhere media exists.

## Notes
- Media URLs are immutable (`Cache-Control: max-age=31536000, immutable`);
  medialibrary paths are unique per media id, so no CloudFront invalidation needed.
- Temp uploads are pinned to the local disk via `config/livewire.php`
  (`temporary_file_upload.disk = local`), so Filament/Livewire uploads don't
  need S3 presigned uploads or CORS configuration. The server's php.ini must
  still allow large uploads: `upload_max_filesize >= 160M`,
  `post_max_size >= 165M`.
- `MEDIA_MAX_FILE_SIZE` env (bytes) overrides the 160 MB default media size
  limit set in `config/media-library.php`.
- Admin uploads (portfolio cover/gallery, service hero, post cover, industry hero)
  now land on S3 automatically.
