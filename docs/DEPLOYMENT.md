# Deployment

## PHP version

This project requires **PHP 8.4** (composer platform pinned to `8.4.0`). PHP 8.5+ works functionally but emits cosmetic `PDO::MYSQL_ATTR_SSL_CA` deprecation notices from the Laravel framework vendor file.

**Local dev (macOS / Homebrew):**

```bash
brew install php@8.4
# Project ships a shim at bin/php pinned to /usr/local/opt/php@8.4/bin/php
./bin/php artisan migrate
./bin/php vendor/bin/pest
# Or prepend ./bin to PATH for the session:
export PATH="$PWD/bin:$PATH"
```

**Production:** install `php@8.4` via the package manager (Ubuntu: `apt-get install php8.4-{cli,fpm,mbstring,xml,gd,zip,intl,mysql,sqlite3}`).

## Environment

Required env vars beyond Laravel defaults:
- `ADMIN_SEED_EMAIL`, `ADMIN_SEED_PASSWORD` — for initial Super-admin user
- `QUEUE_CONNECTION=database` (or `redis` for higher throughput) in production
- `MAIL_MAILER=smtp` (or `resend` once driver is configured)
- `SENTRY_LARAVEL_DSN` — error tracking (added in Task 47)
- `RESPONSE_CACHE_ENABLED=true`

## Queue worker

After deploy, start a worker:

```bash
php artisan queue:work --tries=3 --max-time=3600
```

On Forge/Ploi/Supervisor, configure as a daemon. Example supervisor config:

```ini
[program:thelastclicks-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/forge/thelastclicks.com/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2
user=forge
```

## Scheduler

Add a system cron entry:

```cron
* * * * * cd /home/forge/thelastclicks.com && php artisan schedule:run >> /dev/null 2>&1
```

This drives `sitemap:generate` (weekly) and any other scheduled commands.

## Dev environment

For local development, `.env` ships with `QUEUE_CONNECTION=sync` — Mail::queue() and Filament database notifications dispatch synchronously, no worker required. This is intentional and reverts to `database` in production.

(Expanded deployment recipe added in Task 49.)

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

   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   npm install
   npm run build
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan responsecache:clear
   php artisan clients:import-legacy
   php artisan videos:import
   php artisan sitemap:generate
   php artisan app:preflight
   ```

   `sitemap:generate` runs on deploy as well as weekly on the scheduler:
   `public/sitemap.xml` is generated, not committed, so a fresh checkout has
   none until it runs. It refuses to write localhost URLs unless forced.

   `clients:import-legacy` and `videos:import` push the bundled logo and video
   assets to the media disk so CloudFront serves them instead of the app server.
   Both are idempotent — they skip anything already uploaded, so leaving them in
   the deploy script is safe. The hero reel resolves through the media disk, so
   on a fresh environment `videos:import` must run or the homepage video 404s.

   `app:preflight` is the last step on purpose — it runs after `config:cache`,
   so it validates the config the app will actually serve. It exits non-zero and
   should fail the deploy if `APP_URL` is still a local value or not https, if
   `APP_DEBUG` is on, or if the media disk is unreachable. None of those throw on
   their own: a wrong `APP_URL` silently publishes canonical tags, `og:url` and
   every `asset()` pointing at a host crawlers cannot reach.

   `responsecache:clear` is NOT optional: `npm run build` replaces the hashed
   filenames in `public/build`, and the response cache keeps serving HTML that
   references the old names — every cached page 404s its CSS/JS until the
   cache is flushed. Every deploy script (including the Forge auto-deploy
   script) must end with it.

6. Add the supervisor config for `queue:work` (see top of this file).
7. Add the system cron entry (see top).
8. Configure SSL via Let's Encrypt (Forge button).
9. Point DNS A record at the VPS IP.
10. First login: `/admin/login` with the seeded credentials. CHANGE PASSWORD.

## CI/CD

GitHub Actions runs pest + pint + phpstan on every PR. Forge auto-deploys on push to `main` via Forge webhook (configure in Forge → Site → Auto deploy).

The Forge deploy script must include, after `npm run build`:

```bash
php artisan responsecache:clear
```

Without it, stale cached pages reference wiped `public/build` asset hashes
and the site loads unstyled (404 on CSS/JS) until the cache expires.

The work-categories release (2026-07) additionally requires, before
`responsecache:clear`:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Seeders are idempotent (`updateOrCreate` keyed by slug/name). This run
retires the 6 placeholder industries, seeds the 7 real ones plus 22 work
categories, and moves homepage testimonials into the database.

## Real-content release (2026-07-16)

This release removes the crew and work-categories features, replaces all
placeholder portfolio/blog content with real work, and ships ~290MB of
compressed client films in `public/videos/`.

**BEFORE deploying — back up the tables the migrations drop permanently:**

```bash
mysqldump thelastclicks crew work_categories > ~/backup-crew-workcats-$(date +%F).sql
```

**Deploy runs the normal script** (migrate → seed → build → caches →
`responsecache:clear`). The migrations drop `crew` and `work_categories`;
the seeders write the 9 real portfolio cases and 5 real blog posts.

**Placeholder cleanup:** PortfoliosSeeder now deletes the old fictional
case slugs itself on every run (targeted list — admin-added cases are
untouched), so the normal `db:seed --force` handles portfolio cleanup.

Old faker blog posts still need a one-off delete on the server (their
slugs were random, so the seeder can't target them):

```bash
php artisan tinker --execute="
\App\Models\Post::whereNotIn('slug', ['how-to-brief-a-video-production-team','wedding-photography-timeline-planning','what-post-production-actually-includes','photo-vs-video-corporate-event-coverage','preparing-your-team-for-a-corporate-shoot'])->delete();
"
```

Skip that command if real posts have already been written in the admin —
it deletes anything not in the seeded five.

**Videos:** the 9 films live in `public/videos/` (19–45MB each, all under
GitHub's 100MB hard limit) and deploy with the repo. Longer term, move
them to R2/S3 per the storage section below and serve via `AWS_URL` — the
repo history keeps the weight either way, so migrate before the archive
grows.

## Shared media-items release (2026-07-23)

This release drops the Work-only `work_media` table and folds it into a
single polymorphic `media_items` table shared by Work and Industry
(`HasMediaItems`), then repoints medialibrary's `media.model_type` for the
affected rows from `App\Models\WorkMedia` to `App\Models\MediaItem`. The
migration is destructive — `work_media` rows are copied into `media_items`
and the table is dropped in the same `up()`.

**BEFORE deploying — back up the tables the migration rewrites:**

```bash
mysqldump thelastclicks work_media media > ~/backup-media-items-$(date +%F).sql
```

**Deploy runs the normal script:**

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan responsecache:clear
```

Seeders are idempotent. `IndustriesSeeder` retires any industry outside the
hardcoded 7 (placeholder cleanup) by hydrating and deleting through Eloquent
so `HasMediaItems`' cascade runs — its `media_items` and medialibrary rows,
and the underlying S3 files, are cleaned up rather than orphaned.

**Rollback:** `php artisan migrate:rollback` recreates `work_media` from the
`media_items` rows with `mediable_type = Work` and repoints only the
`media` rows that were actually copied back — Industry-owned rows are left
pointing at `MediaItem`, since `work_media` has no room for them.

## Monitoring

- **Errors:** Sentry (configured via `SENTRY_LARAVEL_DSN`).
- **Uptime:** Better Stack hitting `/up` (Laravel 11 built-in health endpoint).
- **Logs:** `storage/logs/laravel.log` — rotate via daily channel or ship to Logtail.

## Storage migration to S3/R2 (future)

Currently `FILESYSTEM_DISK=public` (local). To switch:

1. Add S3/R2 credentials to `.env`:

   ```env
   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=...
   AWS_SECRET_ACCESS_KEY=...
   AWS_DEFAULT_REGION=auto
   AWS_BUCKET=thelastclicks
   AWS_ENDPOINT=https://<account>.r2.cloudflarestorage.com
   AWS_URL=https://media.thelastclicks.com
   ```

2. Migrate existing media: `php artisan media-library:cloud-migrate` (or manual rsync to bucket).
3. Update `config/backup.php` `destination.disks` to `['s3']`.
