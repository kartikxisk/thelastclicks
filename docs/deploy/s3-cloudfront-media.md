# S3 + CloudFront media — deployment runbook

## Prerequisites
- S3 bucket (private, Object Ownership: bucket owner enforced) with CloudFront
  distribution using Origin Access Control (OAC) pointed at the bucket.
- IAM user/role for the app with `s3:PutObject`, `s3:GetObject`, `s3:DeleteObject`,
  `s3:ListBucket` on the bucket.

## Env (production and local)
    FILESYSTEM_DISK=s3
    MEDIA_DISK=s3
    AWS_ACCESS_KEY_ID=...
    AWS_SECRET_ACCESS_KEY=...
    AWS_DEFAULT_REGION=...
    AWS_BUCKET=...
    AWS_URL=https://<cloudfront-domain>

## Deploy order
1. Deploy code; `composer install`.
2. `php artisan config:clear && php artisan db:seed` (adds home_strip / hero_videos
   settings; portfolio seeder no longer writes /videos paths).
   **Warning:** `db:seed` unconditionally overwrites `home_strip` / `hero_videos`
   via `SiteSetting::set`, clobbering any admin edits made through Filament since
   the last seed. Skip this step on subsequent deploys once an admin has edited
   those settings, or re-apply the admin's edits afterward.
3. `php artisan media:import-local` — uploads public/videos films + posters to S3
   and attaches them to portfolios; migrates any existing public-disk media rows.
   Idempotent; re-run until exit code 0.
4. Verify: homepage hero + strip and portfolio pages serve `https://<cloudfront-domain>/...`
   URLs; spot-check a video plays.
5. `php artisan responsecache:clear`.

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
