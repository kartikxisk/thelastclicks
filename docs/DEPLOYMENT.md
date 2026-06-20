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
   ```

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
