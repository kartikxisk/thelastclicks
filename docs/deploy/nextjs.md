# Deploying the Next.js frontend

The public site is a Next.js 16 app in `web/`, served by Node on the same box
as Laravel. Laravel keeps the Filament admin and the `/api/v1` layer.

## Prerequisites

- **Node 22 LTS.** `node --version` must print `v22.x`.
- **`php artisan db:seed --class=PageSeoSeeder` run once** in the target
  environment. The frontend reads every page title and description from the
  `seo_pages` table; without those rows eleven routes ship with no title.
- The Laravel API reachable on localhost.

## Environment

`web/.env` — copy from `web/.env.example`.

| Variable | Notes |
|---|---|
| `API_BASE_URL` | Laravel origin. `http://127.0.0.1` in production; nginx and Node share a box. |
| `REVALIDATE_SECRET` | **Must equal `FRONTEND_REVALIDATE_SECRET` in the Laravel `.env`.** A mismatch makes every content edit silently fail to appear. |
| `NEXT_PUBLIC_SITE_URL` | Public origin, used for canonicals and OG URLs. |
| `NEXT_PUBLIC_MEDIA_HOST` | **Must equal the host in Laravel's `AWS_URL`** (the CloudFront host, not the bucket). `next/image` rejects any host not in `remotePatterns`, so a mismatch 400s every image on the site. |

And in the Laravel `.env`:

```
FRONTEND_REVALIDATE_URL=http://127.0.0.1:3000/api/revalidate
FRONTEND_REVALIDATE_SECRET=<same value as web/.env REVALIDATE_SECRET>
```

Then `php artisan config:clear`.

## Build

```bash
cd /home/forge/thelastclicks.com/web
npm ci
npm run build

# The standalone server does NOT copy these itself. Skipping them is the
# single most common self-hosting mistake and produces an unstyled site.
cp -r .next/static  .next/standalone/.next/static
cp -r public        .next/standalone/public

sudo systemctl restart tlc-web
curl -sI http://127.0.0.1:3000/ | head -1   # expect 200 before touching nginx
```

## Caching

The app uses Next 16 **Cache Components**: each API getter is a `use cache`
scope with its own `cacheTag` and a one-hour `cacheLife`.

- **On-demand** is the primary mechanism. Saving in Filament fires the
  `TouchesFrontend` observer, which queues `RevalidateFrontend`, which POSTs to
  `/api/revalidate`, which calls `revalidateTag`.
- **`revalidateTag` is stale-while-revalidate.** The first request after an
  edit still serves the cached page while the fresh one builds behind it. One
  request of lag is expected and is not a bug.
- **The one-hour window is only a safety net** for a webhook that never
  arrived.
- **`.next/cache` survives a rebuild.** A deploy after a content change will
  reuse cached entries until their tags are dropped or the window expires. To
  force a cold build: `rm -rf .next` before `npm run build`.

A queue worker must be running, or revalidation jobs never execute.

## Verifying a deploy

```bash
curl -sI https://thelastclicks.com/ | head -1               # 200
curl -sI https://thelastclicks.com/admin | head -1          # Filament still up
curl -sI https://thelastclicks.com/sitemap.xml | head -1    # 200, served by Laravel
curl -sI https://thelastclicks.com/our-works | head -1      # 301
```

Then edit a work title in Filament and confirm `/portfolio` reflects it within
about ten seconds — that exercises the whole revalidation loop end to end.

## Rollback

nginx keeps the previous config at
`/etc/nginx/sites-available/thelastclicks.com.blade-backup`. Restoring it and
reloading returns the Blade site immediately; see
`docs/deploy/cutover-runbook.md`.

## Known behaviour

`/portfolio` and `/blog` read `searchParams`, so their content streams into a
Suspense boundary. It is present in the HTML payload but hidden until an inline
script reveals it — crawlers that execute JavaScript (Googlebot does) see it, a
JavaScript-disabled browser sees the skeleton. Every other route is prerendered
whole and renders without JavaScript.

## Tests

```bash
cd web
npm run typecheck
npm run lint
npx playwright test          # builds and starts its own server
```

Point at a running instance instead with
`E2E_BASE_URL=https://next.thelastclicks.com npx playwright test`.

The suite asserts against real admin content rather than fixtures, so the
Laravel API must be reachable — a contract change on the Laravel side fails
here rather than surfacing as an empty section in production.
