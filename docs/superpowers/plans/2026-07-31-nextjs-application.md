# Next.js Application Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A complete Next.js frontend rendering every route of the site from the `/api/v1` layer, running on the staging subdomain, with no WebGL yet.

**Architecture:** Next.js 15 App Router in `web/`, server components by default, fetching from `http://127.0.0.1/api/v1` over localhost. Tailwind v4 carries the existing design tokens forward. GSAP and Lenis provide motion. Every route is ISR-cached by tag, invalidated by the webhook Plan 1 Task 11 fires. The Blade site keeps serving production traffic throughout.

**Tech Stack:** Next.js **16** (App Router, Cache Components), React 19, TypeScript 5, Tailwind CSS v4, GSAP 3 (ScrollTrigger, SplitText), Lenis, Playwright.

> **Revised during execution.** The installed Next is 16.2.12, not 15. Next 16
> replaces the ISR fetch-options model this plan was written against with
> **Cache Components**: `'use cache'` + `cacheTag()` + `cacheLife()`, with
> Partial Prerendering on by default. `revalidateTag` still works in Route
> Handlers, so the contract with Laravel's `RevalidateFrontend` job is
> unchanged. Tasks 1, 2 and 10 are implemented against the new model; the
> snippets below that show `fetch(url, {next: {tags, revalidate}})` are
> superseded by `web/src/lib/api.ts` as built.
>
> `web/AGENTS.md` requires reading `node_modules/next/dist/docs/` before writing
> code in this app. Do that — the differences are not cosmetic.

**Prerequisite:** Plan 1 (`docs/superpowers/plans/2026-07-31-api-v1-layer.md`) is complete and `docs/api-v1.md` exists.

## Global Constraints

- Node **22 LTS**. Package manager is **npm** — the repo already has `package-lock.json` at the root, and adding a second manager for `web/` invites lockfile drift.
- All frontend work lives in `web/`. Never modify `resources/views/`, `resources/css/`, or `resources/js/` in this plan — the Blade site is production and must keep working.
- **Server Components by default.** A component only gets `'use client'` when it needs state, an effect, or a browser API. Every `'use client'` directive added must be justified in a comment on the same line.
- Data is fetched **only in server components or route handlers**. No client-side fetching of page content.
- Every `fetch` to the API passes `next: { tags: [...] }` using the tag vocabulary from Plan 1 Task 11: `settings`, `pages:home`, `pages:about`, `pages:contact`, `works`, `services`, `industries`, `posts`, `works:{slug}`, `services:{slug}`, `posts:{slug}`.
- Design tokens are the existing ones, verbatim: `--ink #0a0a0a`, `--ink-2 #111111`, `--ink-3 #161616`, `--ink-4 #1c1c1c`, `--line #262626`, `--line-2 #1f1f1f`, `--paper #f4f3ef`, `--paper-dim #c7c5be`, `--muted #8a8784`, `--muted-2 #7d7a76`, `--red #e80f03`, `--red-soft rgba(232,15,3,0.18)`. Typeface is **Outfit**. Easings are `--ease cubic-bezier(0.16, 1, 0.3, 1)`, `--ease-2 cubic-bezier(0.65, 0, 0.35, 1)`, `--ease-3 cubic-bezier(0.85, 0, 0.15, 1)`.
- `--muted-2` is `#7d7a76` and must not be darkened — `#6a6864` was 3.56:1 and failed WCAG AA. Any new muted color must be verified at 4.5:1 against `--ink`.
- Run type checks with `npm run typecheck` (`tsc --noEmit`), lint with `npm run lint`, and end-to-end tests with `npm run test:e2e`.
- No WebGL, no `three`, no `@react-three/*` in this plan. Those arrive in Plan 3.

---

### Task 1: Scaffold the Next.js application

Creates `web/`, wires the build, and gets a deployable process on the staging subdomain. Ends with a real page served by nginx.

**Files:**
- Create: `web/package.json`, `web/tsconfig.json`, `web/next.config.ts`, `web/.env.example`, `web/.gitignore`
- Create: `web/src/app/layout.tsx`, `web/src/app/page.tsx`
- Create: `docs/deploy/tlc-web.service`, `docs/deploy/nginx-staging.conf`
- Modify: `.gitignore`
- Test: `web/tests/e2e/smoke.spec.ts`

**Interfaces:**
- Consumes: nothing.
- Produces:
  - `web/src/lib/env.ts` — `API_BASE_URL` and `REVALIDATE_SECRET`, validated at module load.
  - npm scripts: `dev`, `build`, `start`, `typecheck`, `lint`, `test:e2e`.
  - `output: 'standalone'` in `next.config.ts`.

- [ ] **Step 1: Scaffold the app**

Run from the repo root:

```bash
npx create-next-app@latest web \
  --typescript --tailwind --app --src-dir --eslint \
  --import-alias "@/*" --no-turbopack --use-npm
```

When asked about anything not covered by a flag, accept the default.

- [ ] **Step 2: Configure the build for standalone output**

Replace `web/next.config.ts` with:

```ts
import type { NextConfig } from 'next'

const config: NextConfig = {
  // Traces the exact dependency set into .next/standalone so the deploy
  // artifact is a self-contained Node server, not a node_modules tree.
  output: 'standalone',

  images: {
    remotePatterns: [
      // Media is served from S3/CloudFront. The host comes from env so
      // staging and production can differ without a code change.
      {
        protocol: 'https',
        hostname: process.env.NEXT_PUBLIC_MEDIA_HOST ?? 'cdn.thelastclicks.com',
      },
      { protocol: 'https', hostname: 'img.youtube.com' },
    ],
  },

  // The API is same-origin in production. In dev, Next proxies to the PHP
  // server so the browser never hits a second port and CORS never enters
  // the picture.
  async rewrites() {
    return process.env.NODE_ENV === 'development'
      ? [{ source: '/api/v1/:path*', destination: `${process.env.API_BASE_URL}/api/v1/:path*` }]
      : []
  },
}

export default config
```

- [ ] **Step 3: Add the env module**

Create `web/src/lib/env.ts`:

```ts
/**
 * Environment access, validated once at module load. A missing API base URL
 * must fail the build loudly rather than produce a site that renders empty
 * sections in production.
 */
function required(name: string): string {
  const value = process.env[name]
  if (!value) {
    throw new Error(`Missing required environment variable: ${name}`)
  }
  return value
}

/** Laravel API origin. Localhost in production — nginx and Node share a box. */
export const API_BASE_URL = required('API_BASE_URL')

/** Shared secret the Laravel revalidation webhook presents. */
export const REVALIDATE_SECRET = required('REVALIDATE_SECRET')

/** Public site origin, used for canonicals and OG URLs. */
export const SITE_URL = required('NEXT_PUBLIC_SITE_URL')
```

Create `web/.env.example`:

```
# Laravel API origin. Localhost in production; the PHP dev server locally.
API_BASE_URL=http://127.0.0.1:8000

# Must match FRONTEND_REVALIDATE_SECRET in the Laravel .env
REVALIDATE_SECRET=

# Public origin, used for canonicals
NEXT_PUBLIC_SITE_URL=https://thelastclicks.com

# S3/CloudFront host serving media — must match the Laravel s3 disk url
NEXT_PUBLIC_MEDIA_HOST=cdn.thelastclicks.com
```

- [ ] **Step 4: Add the npm scripts**

In `web/package.json`, set the `scripts` block to:

```json
{
  "dev": "next dev",
  "build": "next build",
  "start": "node .next/standalone/server.js",
  "typecheck": "tsc --noEmit",
  "lint": "next lint",
  "test:e2e": "playwright test"
}
```

- [ ] **Step 5: Ignore build artifacts**

Append to the root `.gitignore`:

```
/web/node_modules
/web/.next
/web/out
/web/.env
/web/test-results
/web/playwright-report
```

- [ ] **Step 6: Write the smoke test**

Install Playwright in `web/`:

```bash
cd web && npm install -D @playwright/test && npx playwright install chromium
```

Create `web/playwright.config.ts`:

```ts
import { defineConfig, devices } from '@playwright/test'

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  use: {
    baseURL: process.env.E2E_BASE_URL ?? 'http://127.0.0.1:3000',
    trace: 'on-first-retry',
  },
  projects: [
    { name: 'desktop', use: { ...devices['Desktop Chrome'] } },
    { name: 'mobile', use: { ...devices['Pixel 7'] } },
  ],
  webServer: process.env.E2E_BASE_URL
    ? undefined
    : { command: 'npm run build && npm run start', url: 'http://127.0.0.1:3000', reuseExistingServer: !process.env.CI, timeout: 180_000 },
})
```

Create `web/tests/e2e/smoke.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('the app serves a page', async ({ page }) => {
  const response = await page.goto('/')
  expect(response?.status()).toBe(200)
})

test('the page has no console errors', async ({ page }) => {
  const errors: string[] = []
  page.on('console', (m) => m.type() === 'error' && errors.push(m.text()))
  await page.goto('/')
  expect(errors).toEqual([])
})
```

- [ ] **Step 7: Run the smoke test to verify it fails, then passes**

Run: `cd web && npm run test:e2e`
Expected: FAIL first if env vars are missing — that is `env.ts` doing its job. Copy `.env.example` to `.env`, fill `REVALIDATE_SECRET` with any value for now, and re-run.
Expected after: PASS, 4 tests (2 specs × 2 projects).

- [ ] **Step 8: Write the deploy artifacts**

Create `docs/deploy/tlc-web.service`:

```ini
[Unit]
Description=TheLastClicks Next.js frontend
After=network.target

[Service]
Type=simple
User=forge
WorkingDirectory=/home/forge/thelastclicks.com/web
Environment=NODE_ENV=production
Environment=PORT=3000
EnvironmentFile=/home/forge/thelastclicks.com/web/.env
ExecStart=/usr/bin/node .next/standalone/server.js
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Create `docs/deploy/nginx-staging.conf`:

```nginx
# Staging: the Next app under its own subdomain while Blade keeps serving
# production. noindex + basic auth so a crawler never sees a duplicate site.
server {
    listen 443 ssl http2;
    server_name next.thelastclicks.com;

    auth_basic "staging";
    auth_basic_user_file /etc/nginx/.htpasswd-next;

    add_header X-Robots-Tag "noindex, nofollow" always;

    # The API and admin still come from PHP on the same box.
    location ~ ^/(api|admin|storage|livewire)/ {
        proxy_pass http://127.0.0.1:80;
        proxy_set_header Host thelastclicks.com;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

- [ ] **Step 9: Commit**

```bash
git add web docs/deploy/tlc-web.service docs/deploy/nginx-staging.conf .gitignore
git commit -m "feat(web): scaffold Next.js app with standalone output and staging deploy config"
```

---

### Task 2: API client and types

The typed boundary between Laravel and Next. Everything downstream imports from here.

**Files:**
- Create: `web/src/lib/types.ts`
- Create: `web/src/lib/api.ts`
- Test: `web/tests/e2e/api-contract.spec.ts`

**Interfaces:**
- Consumes: `docs/api-v1.md` (Plan 1 Task 12), `web/src/lib/env.ts` (Task 1).
- Produces:
  - Types: `Media`, `MediaItem`, `Seo`, `Work`, `Service`, `Industry`, `Post`, `Testimonial`, `Client`, `HeroSlide`, `Settings`, `Paginated<T>`, `FilterOption`.
  - `api<T>(path: string, opts?: {tags?: string[]; revalidate?: number; searchParams?: Record<string, string|number|undefined>}): Promise<T>`
  - `getSettings()`, `getHome()`, `getAbout()`, `getContact()`, `getStaticPage(slug)`, `getWorks(params)`, `getServices()`, `getService(slug)`, `getIndustries()`, `getPosts(params)`, `getPost(slug)`.
  - `submitContact(body)`, `submitNewsletter(body)` — these POST and are the only functions safe to call from a client component.
  - `ApiError` class with a `status` field. `api()` throws it on non-2xx; `notFound()` is the caller's job.

- [ ] **Step 1: Read the contract**

Read `docs/api-v1.md` in full, and read the snapshot files under `tests/Feature/Api/V1/__snapshots__/`. The types in this task must match those shapes exactly — every key, every nullability. Do not infer a shape you have not read.

- [ ] **Step 2: Write the failing contract test**

Create `web/tests/e2e/api-contract.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

// These run against the real API, so a Laravel-side contract change fails the
// frontend build rather than surfacing as an empty section in production.
const API = process.env.API_BASE_URL ?? 'http://127.0.0.1:8000'

test('settings returns the documented shape', async ({ request }) => {
  const res = await request.get(`${API}/api/v1/settings`)
  expect(res.ok()).toBeTruthy()
  const { data } = await res.json()
  expect(Object.keys(data).sort()).toEqual(
    [
      'brand_logo_url', 'contact_email', 'contact_phone', 'cta_video_url',
      'favicon_url', 'seo_defaults', 'socials', 'whatsapp_url', 'work_tile_ratio',
    ].sort()
  )
})

test('home bundle returns every section', async ({ request }) => {
  const res = await request.get(`${API}/api/v1/pages/home`)
  expect(res.ok()).toBeTruthy()
  const body = await res.json()
  expect(Object.keys(body.data).sort()).toEqual(
    ['clients', 'featured_works', 'hero_slides', 'industries', 'services', 'testimonials'].sort()
  )
  expect(Object.keys(body.seo).sort()).toEqual(
    ['canonical', 'description', 'json_ld', 'nofollow', 'noindex', 'og', 'title'].sort()
  )
})

test('works returns pagination meta and filters', async ({ request }) => {
  const res = await request.get(`${API}/api/v1/works`)
  const body = await res.json()
  expect(body.meta.per_page).toBe(12)
  expect(body.filters).toHaveProperty('categories')
})

test('posts returns pagination meta', async ({ request }) => {
  const body = await (await request.get(`${API}/api/v1/posts`)).json()
  expect(body.meta.per_page).toBe(9)
})

test('an unknown service slug 404s', async ({ request }) => {
  expect((await request.get(`${API}/api/v1/services/nope`)).status()).toBe(404)
})
```

- [ ] **Step 3: Run the contract test**

Start the Laravel dev server in another terminal: `./bin/php artisan serve`

Run: `cd web && npm run test:e2e -- api-contract`
Expected: PASS. If any assertion fails, the mismatch is real — fix `docs/api-v1.md` and this test to match what Laravel actually returns before writing any types.

- [ ] **Step 4: Write the types**

Create `web/src/lib/types.ts`, transcribing from `docs/api-v1.md`:

```ts
/**
 * Mirrors app/Http/Resources/Api/V1/*.php. Any change to a Laravel Resource
 * requires the matching change here in the same pull request — see
 * docs/api-v1.md.
 */

export interface Media {
  url: string
  srcset: string | null
  width: number | null
  height: number | null
  mime: string | null
  alt: string | null
}

export interface MediaItem {
  type: 'image' | 'video' | 'youtube'
  url: string
  poster: string | null
  caption: string | null
  width: number | null
  height: number | null
  mime: string | null
}

export interface Seo {
  title: string | null
  description: string | null
  canonical: string
  noindex: boolean
  nofollow: boolean
  og: { title: string | null; description: string | null; image: string | null }
  json_ld: Record<string, unknown>[]
}

export interface FilterOption {
  value: string
  label: string
}

export interface Paginated<T> {
  data: T[]
  meta: { current_page: number; last_page: number; per_page: number; total: number }
  seo: Seo
}

export interface Work {
  id: number
  slug: string
  title: string
  summary: string | null
  client: string | null
  category: string | null
  category_label: string | null
  crafts: string[]
  credits: { role: string; name: string }[]
  location: string | null
  agency: string | null
  year: string | null
  cover: string | null
  preview_video_url: string | null
  media: MediaItem[]
  is_featured: boolean
}

/**
 * Inner key names are admin-authored and are NOT what you would guess —
 * transcribed from tests/Feature/Api/V1/contract.json, which is the authority.
 */
export interface Service {
  id: number
  slug: string
  title: string
  hero_headline: string | null
  hero_copy: string | null
  hero_meta: { label: string; value: string }[]
  hero: Media | null
  proof: { count: string; label: string; sectors: string } | Record<string, never>
  pillars: { title: string; desc: string }[]
  phases: { num: string; title: string; desc: string; time: string }[]
  kit: { title: string; items: string[] }[]
  faqs: { q: string; a: string }[]
  cta: { title: string; copy: string; prefill: string } | Record<string, never>
  tags: string[]
  gallery: string[]
  body: string | null
  share: number | null
}

/** No avatar — Testimonial has no media collection. */
export interface Testimonial {
  id: number
  quote: string
  client_name: string | null
  role_company: string | null
}

export interface Industry {
  id: number
  slug: string
  title: string
  summary: string | null
  body: string | null
  cover: string | null
  media: MediaItem[]
  testimonials: Testimonial[]
}

export interface Post {
  id: number
  slug: string
  title: string
  excerpt: string | null
  body: string | null
  published_at: string | null
  reading_minutes: number
  cover: string | null
  category: FilterOption | null
  tags: FilterOption[]
}

export interface Client {
  id: number
  name: string
  /** Resolved URL string, not a Media object — logoUrl() falls back to an
   *  admin-set path that has no Media record behind it. */
  logo: string | null
  url: string | null
}

export interface HeroSlide {
  id: number
  label: string | null
  asset: Media | null
  poster: Media | null
  mime: string | null
  is_video: boolean
}

export interface Settings {
  contact_email: string | null
  contact_phone: string | null
  whatsapp_url: string | null
  /** Fixed key set — unset platforms are null, never absent. */
  socials: {
    instagram: string | null
    youtube: string | null
    facebook: string | null
    linkedin: string | null
    x: string | null
    behance: string | null
    pinterest: string | null
  }
  /** Null when nothing is uploaded. Render no logo rather than a fallback. */
  brand_logo_url: string | null
  favicon_url: string
  cta_video_url: string
  work_tile_ratio: string
  seo_defaults: {
    title: string | null
    description: string | null
    og_image: string | null
  }
}

export interface HomePage {
  data: {
    hero_slides: HeroSlide[]
    services: Service[]
    featured_works: Work[]
    industries: Industry[]
    testimonials: Testimonial[]
    clients: Client[]
  }
  seo: Seo
}
```

Correct any type above that disagrees with `docs/api-v1.md`. The document wins.

- [ ] **Step 5: Write the client**

Create `web/src/lib/api.ts`:

```ts
import { API_BASE_URL } from './env'
import type {
  Client, FilterOption, HomePage, Industry, Paginated, Post, Seo, Service, Settings, Testimonial, Work,
} from './types'

export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly path: string,
    message?: string
  ) {
    super(message ?? `API ${status} on ${path}`)
    this.name = 'ApiError'
  }
}

interface ApiOptions {
  /** ISR cache tags. Laravel's revalidation webhook drops these by name. */
  tags?: string[]
  /** Seconds before a background refresh. Defaults to one hour. */
  revalidate?: number
  searchParams?: Record<string, string | number | undefined>
}

/**
 * Single entry point for every read. Every caller passes tags — a fetch
 * without them is uncacheable by the webhook and will silently serve stale
 * content until the time-based window expires.
 */
export async function api<T>(path: string, opts: ApiOptions = {}): Promise<T> {
  const url = new URL(`/api/v1${path}`, API_BASE_URL)

  for (const [key, value] of Object.entries(opts.searchParams ?? {})) {
    if (value !== undefined && value !== '') url.searchParams.set(key, String(value))
  }

  const response = await fetch(url, {
    headers: { Accept: 'application/json' },
    next: { tags: opts.tags ?? [], revalidate: opts.revalidate ?? 3600 },
  })

  if (!response.ok) {
    throw new ApiError(response.status, path)
  }

  return response.json() as Promise<T>
}

export const getSettings = () =>
  api<{ data: Settings }>('/settings', { tags: ['settings'] }).then((r) => r.data)

export const getHome = () => api<HomePage>('/pages/home', { tags: ['pages:home'] })

export const getAbout = () =>
  api<{ data: { testimonials: Testimonial[]; clients: Client[]; stats: Record<string, number> }; seo: Seo }>(
    '/pages/about',
    { tags: ['pages:about'] }
  )

export const getContact = () =>
  api<{ data: { services: Service[]; project_types: FilterOption[]; budget_ranges: FilterOption[] }; seo: Seo }>(
    '/pages/contact',
    { tags: ['pages:contact'] }
  )

/**
 * Legal pages. Tagged `settings` as well as their own slug because the copy
 * lives in SiteSetting once Plan 3 moves it out of Blade — without the
 * `settings` tag an edit would never invalidate these routes.
 */
export const getStaticPage = (slug: string) =>
  api<{ data: { body: string }; seo: Seo }>(`/pages/${slug}`, {
    tags: [`pages:${slug}`, 'settings'],
  })

export const getWorks = (params: { category?: string; industry?: string; page?: number } = {}) =>
  api<Paginated<Work> & { filters: { categories: FilterOption[]; industries: FilterOption[] } }>('/works', {
    tags: ['works'],
    searchParams: params,
  })

export const getServices = () =>
  api<{ data: Service[]; seo: Seo }>('/services', { tags: ['services'] })

export const getService = (slug: string) =>
  api<{ data: Service & { related_works: Work[] }; seo: Seo }>(`/services/${slug}`, {
    tags: ['services', `services:${slug}`],
  })

export const getIndustries = () =>
  api<{ data: Industry[]; seo: Seo }>('/industries', { tags: ['industries'] })

export const getPosts = (params: { category?: string; tag?: string; page?: number } = {}) =>
  api<Paginated<Post> & { filters: { categories: FilterOption[]; tags: FilterOption[] } }>('/posts', {
    tags: ['posts'],
    searchParams: params,
  })

export const getPost = (slug: string) =>
  api<{ data: Post & { related: Post[] }; seo: Seo }>(`/posts/${slug}`, {
    tags: ['posts', `posts:${slug}`],
  })

/** Write endpoints. Safe to call from a client component; never cached. */
export async function submitContact(body: Record<string, string>) {
  const response = await fetch(`${API_BASE_URL}/api/v1/contact`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify(body),
    cache: 'no-store',
  })
  return { ok: response.ok, status: response.status, body: await response.json() }
}

export async function submitNewsletter(body: { email: string; website?: string }) {
  const response = await fetch(`${API_BASE_URL}/api/v1/newsletter`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify(body),
    cache: 'no-store',
  })
  return { ok: response.ok, status: response.status, body: await response.json() }
}
```

- [ ] **Step 6: Verify types compile**

Run: `cd web && npm run typecheck`
Expected: no errors.

- [ ] **Step 7: Commit**

```bash
git add web/src/lib web/tests/e2e/api-contract.spec.ts web/playwright.config.ts
git commit -m "feat(web): add typed API client mirroring the v1 contract"
```

---

### Task 3: Design system — tokens, vendored components, and metadata

Ports the existing visual language into Tailwind v4, vendors the third-party component sources, and centralizes `generateMetadata` so no route hardcodes metadata.

**Files:**
- Create: `web/src/styles/tokens.css`
- Modify: `web/src/app/globals.css`
- Create: `web/src/lib/metadata.ts`
- Create: `web/src/components/JsonLd.tsx`
- Create: `web/src/components/ui/` (vendored components)
- Create: `web/src/components/ui/README.md`
- Test: `web/tests/e2e/tokens.spec.ts`

**Interfaces:**
- Consumes: `Seo` type (Task 2), `SITE_URL` (Task 1).
- Produces:
  - CSS custom properties and Tailwind v4 `@theme` entries for every token in Global Constraints.
  - `toMetadata(seo: Seo): Metadata` — maps an API `seo` object to a Next `Metadata` object.
  - `<JsonLd data={seo.json_ld} />` — renders each entry as its own `application/ld+json` script tag.
  - `web/src/components/ui/` — vendored, retokenized components that Tasks 4–9 compose. Each file names its upstream source at the top.

- [ ] **Step 1: Read the source of truth**

Read `resources/css/core.css:1-40`. Those are the tokens. Copy the values exactly, including the comment explaining that `--ink*` are dark surfaces and `--paper*` are light text — that naming is historical and confusing without it.

- [ ] **Step 2: Write the failing test**

Create `web/tests/e2e/tokens.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('brand tokens resolve on the document root', async ({ page }) => {
  await page.goto('/')

  const tokens = await page.evaluate(() => {
    const s = getComputedStyle(document.documentElement)
    return {
      ink: s.getPropertyValue('--ink').trim(),
      paper: s.getPropertyValue('--paper').trim(),
      red: s.getPropertyValue('--red').trim(),
      muted2: s.getPropertyValue('--muted-2').trim(),
      ease: s.getPropertyValue('--ease').trim(),
    }
  })

  expect(tokens.ink).toBe('#0a0a0a')
  expect(tokens.paper).toBe('#f4f3ef')
  expect(tokens.red).toBe('#e80f03')
  expect(tokens.muted2).toBe('#7d7a76')
  expect(tokens.ease).toBe('cubic-bezier(0.16, 1, 0.3, 1)')
})

test('the page renders dark by default', async ({ page }) => {
  await page.goto('/')
  const bg = await page.evaluate(() => getComputedStyle(document.body).backgroundColor)
  expect(bg).toBe('rgb(10, 10, 10)')
})

test('muted-2 meets WCAG AA against the base surface', async ({ page }) => {
  await page.goto('/')

  // #7d7a76 on #0a0a0a — the previous #6a6864 was 3.56:1 and failed.
  const ratio = await page.evaluate(() => {
    const lum = (hex: string) => {
      const c = [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16) / 255)
        .map((v) => (v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4))
      return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2]
    }
    const s = getComputedStyle(document.documentElement)
    const a = lum(s.getPropertyValue('--muted-2').trim())
    const b = lum(s.getPropertyValue('--ink').trim())
    return (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05)
  })

  expect(ratio).toBeGreaterThanOrEqual(4.5)
})
```

- [ ] **Step 3: Run test to verify it fails**

Run: `cd web && npm run test:e2e -- tokens`
Expected: FAIL — tokens are empty strings.

- [ ] **Step 4: Write the tokens**

Create `web/src/styles/tokens.css`:

```css
/* ============================================================
   TheLastClicks — design tokens
   Premium agency · dark surfaces with light text · 60fps motion

   Note: names are historical — --ink* are dark SURFACES,
   --paper* are light TEXT, --red* is the brand accent.
   Ported verbatim from resources/css/core.css.
   ============================================================ */

:root {
  --ink: #0a0a0a;
  --ink-2: #111111;
  --ink-3: #161616;
  --ink-4: #1c1c1c;
  --line: #262626;
  --line-2: #1f1f1f;
  --paper: #f4f3ef;
  --paper-dim: #c7c5be;
  --muted: #8a8784;
  /* 4.64:1 on --ink. The previous #6a6864 was 3.56:1 and failed WCAG AA.
     Do not darken this without re-measuring. */
  --muted-2: #7d7a76;
  --red: #e80f03;
  --red-soft: rgba(232, 15, 3, 0.18);

  --ease: cubic-bezier(0.16, 1, 0.3, 1);
  --ease-2: cubic-bezier(0.65, 0, 0.35, 1);
  --ease-3: cubic-bezier(0.85, 0, 0.15, 1);

  --maxw: 1560px;
  --pad-x: clamp(20px, 4vw, 56px);
  --section-y: clamp(64px, 9vh, 120px);
}

@theme inline {
  --color-ink: var(--ink);
  --color-ink-2: var(--ink-2);
  --color-ink-3: var(--ink-3);
  --color-ink-4: var(--ink-4);
  --color-line: var(--line);
  --color-line-2: var(--line-2);
  --color-paper: var(--paper);
  --color-paper-dim: var(--paper-dim);
  --color-muted: var(--muted);
  --color-muted-2: var(--muted-2);
  --color-red: var(--red);

  --ease-brand: var(--ease);
  --ease-brand-2: var(--ease-2);
  --ease-brand-3: var(--ease-3);

  --font-display: var(--font-outfit), 'Helvetica Neue', Arial, sans-serif;
  --font-sans: var(--font-outfit), 'Helvetica Neue', Arial, sans-serif;
}
```

Replace `web/src/app/globals.css` with:

```css
@import 'tailwindcss';
@import '../styles/tokens.css';

html,
body {
  background: var(--ink);
  color: var(--paper);
  font-family: var(--font-sans);
  font-size: 16px;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-rendering: optimizeLegibility;
  overflow-x: hidden;
}

::selection {
  background: var(--red);
  color: #fff;
}

a {
  color: inherit;
  text-decoration: none;
}

img,
video {
  max-width: 100%;
  display: block;
}

/* Reduced motion is gentler, not zero — see plans/008. Removing motion
   entirely loses the spatial cues that make navigation legible. */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.15s !important;
    scroll-behavior: auto !important;
  }
}
```

- [ ] **Step 5: Load the typeface**

In `web/src/app/layout.tsx`, load Outfit through `next/font` so it self-hosts and emits no render-blocking request:

```tsx
import { Outfit } from 'next/font/google'

const outfit = Outfit({
  subsets: ['latin'],
  display: 'swap',
  variable: '--font-outfit',
})
```

Apply `className={outfit.variable}` to the `<html>` element.

- [ ] **Step 6: Run test to verify it passes**

Run: `cd web && npm run test:e2e -- tokens`
Expected: PASS, 6 tests.

- [ ] **Step 7: Write the metadata helper**

Create `web/src/lib/metadata.ts`:

```ts
import type { Metadata } from 'next'
import { SITE_URL } from './env'
import type { Seo } from './types'

/**
 * Maps an API `seo` object onto Next's Metadata. Every route uses this — no
 * route hardcodes a title, because the admin owns them through SeoPage.
 */
export function toMetadata(seo: Seo): Metadata {
  return {
    metadataBase: new URL(SITE_URL),
    title: seo.title ?? undefined,
    description: seo.description ?? undefined,
    alternates: { canonical: seo.canonical },
    robots: {
      index: !seo.noindex,
      follow: !seo.nofollow,
    },
    openGraph: {
      title: seo.og.title ?? seo.title ?? undefined,
      description: seo.og.description ?? seo.description ?? undefined,
      url: seo.canonical,
      images: seo.og.image ? [{ url: seo.og.image }] : undefined,
      type: 'website',
    },
    twitter: {
      card: 'summary_large_image',
      title: seo.og.title ?? seo.title ?? undefined,
      description: seo.og.description ?? seo.description ?? undefined,
      images: seo.og.image ? [seo.og.image] : undefined,
    },
  }
}
```

Create `web/src/components/JsonLd.tsx`:

```tsx
/**
 * Renders each structured-data entry as its own script tag. One combined
 * @graph would also be valid, but separate tags are what the Blade site
 * emitted and keeps the before/after SEO diff clean at cutover.
 */
export function JsonLd({ data }: { data: Record<string, unknown>[] }) {
  if (data.length === 0) return null

  return (
    <>
      {data.map((entry, i) => (
        <script
          key={i}
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(entry) }}
        />
      ))}
    </>
  )
}
```

- [ ] **Step 8: Vendor the third-party components**

Both sources are MIT licensed and distribute by **copy-paste, not npm** — the code lands in the repo and is owned locally from that point. That is the point: each component gets retokenized to the brand palette rather than themed around.

Pull only what a later task actually composes. Vendoring a component nothing renders is dead code that still has to be maintained.

From **Aceternity UI** (dark-cinematic: spotlights, beams, glows — built for exactly this palette):

```bash
cd web
npx shadcn@latest add "https://ui.aceternity.com/registry/spotlight.json"
npx shadcn@latest add "https://ui.aceternity.com/registry/text-generate-effect.json"
```

From **react-bits** (its motion dependency is optional and tree-shakeable, so text effects cost less here than the Aceternity equivalents):

```bash
npx jsrepo add github/DavidHDev/react-bits/TextAnimations/SplitText
npx jsrepo add github/DavidHDev/react-bits/Animations/Magnet
```

If a registry URL has moved, fetch the component's source from its documentation page and paste it into `web/src/components/ui/` by hand. Do not add either library as an npm dependency — neither publishes one, and a package that appears to work is not the real thing.

- [ ] **Step 9: Retokenize every vendored component**

Each vendored file arrives with hardcoded Tailwind colors (`bg-black`, `text-white`, `bg-neutral-900`) and its own motion values. Before any of it renders:

1. Replace every color utility with a brand token — `bg-ink`, `text-paper`, `border-line`, `text-red`. A vendored component must not introduce a color that is not in `tokens.css`.
2. Replace every easing with `--ease`, `--ease-2`, or `--ease-3`.
3. Strip `framer-motion` from any component whose animation GSAP already covers. Aceternity components require it at roughly 125KB; react-bits treat it as optional. Every one you strip is bytes that never ship.
4. Add `'use client'` only where the component genuinely needs it, and delete any `'use client'` on a component that turns out to be static after retokenizing.
5. Add a header comment naming the upstream source and listing what was changed.

Create `web/src/components/ui/README.md` recording, per component: where it came from, what was modified, and why it is here. Vendored code with no provenance becomes code nobody dares touch.

- [ ] **Step 10: Verify no color escaped the token system**

```bash
cd web && grep -rnE '(bg|text|border)-(white|black|gray|zinc|neutral|slate|stone)-?[0-9]*' src/components/ui/
```

Expected: no output. Any hit is a hardcoded color that will drift from the brand.

- [ ] **Step 11: Typecheck and commit**

Run: `cd web && npm run typecheck && npm run lint`
Expected: clean.

```bash
git add web/src/styles web/src/app/globals.css web/src/app/layout.tsx web/src/lib/metadata.ts web/src/components/JsonLd.tsx web/src/components/ui web/tests/e2e/tokens.spec.ts
git commit -m "feat(web): port design tokens, vendor UI components, add metadata helpers"
```

---

### Task 4: Root layout — navigation, footer, cursor, smooth scroll

The chrome every route sits inside.

**Files:**
- Modify: `web/src/app/layout.tsx`
- Create: `web/src/components/chrome/Nav.tsx`
- Create: `web/src/components/chrome/Footer.tsx`
- Create: `web/src/components/chrome/Cursor.tsx`
- Create: `web/src/components/chrome/SmoothScroll.tsx`
- Create: `web/src/components/chrome/NewsletterForm.tsx`
- Test: `web/tests/e2e/chrome.spec.ts`

**Interfaces:**
- Consumes: `getSettings()` (Task 2), `toMetadata` (Task 3).
- Produces:
  - `<Nav settings={settings} />` — server component; its mobile menu toggle is a small client child.
  - `<Footer settings={settings} />` — server component containing `<NewsletterForm />`.
  - `<SmoothScroll>{children}</SmoothScroll>` — client, wraps everything, owns the Lenis instance.
  - `<Cursor />` — client, camera-glyph cursor with magnetic snap on `[data-magnetic]`.

- [ ] **Step 1: Read the Blade chrome**

Read `resources/js/chrome.js` (600 lines) and `resources/views/components/layouts/`. Note the nav scroll-state behavior, the magnetic-cursor targets, and the mobile menu markup. Read `plans/009-nav-scroll-state-listener.md` — it documents the scroll-listener approach that was chosen and why.

- [ ] **Step 2: Write the failing test**

Create `web/tests/e2e/chrome.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('nav links to every primary route', async ({ page }) => {
  await page.goto('/')
  const nav = page.getByRole('navigation').first()

  for (const path of ['/portfolio', '/industries', '/blog', '/contact']) {
    await expect(nav.getByRole('link', { name: new RegExp(path.slice(1), 'i') }).first()).toBeVisible()
  }
})

test('nav gains a scrolled state after scrolling', async ({ page }) => {
  await page.goto('/')
  const header = page.locator('header').first()

  await expect(header).not.toHaveAttribute('data-scrolled', 'true')
  await page.evaluate(() => window.scrollTo(0, 600))
  await expect(header).toHaveAttribute('data-scrolled', 'true')
})

test('footer shows the admin-managed contact email', async ({ page, request }) => {
  const { data } = await (await request.get(`${process.env.API_BASE_URL}/api/v1/settings`)).json()
  await page.goto('/')
  await expect(page.locator('footer')).toContainText(data.contact_email)
})

test('the newsletter form accepts a submission', async ({ page }) => {
  await page.goto('/')
  await page.locator('footer input[type="email"]').fill('e2e@example.com')
  await page.locator('footer form').getByRole('button').click()
  await expect(page.locator('footer')).toContainText(/on the list/i)
})

test('mobile menu opens and traps focus', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 })
  await page.goto('/')
  await page.getByRole('button', { name: /menu/i }).click()
  await expect(page.getByRole('dialog')).toBeVisible()
  await page.keyboard.press('Escape')
  await expect(page.getByRole('dialog')).not.toBeVisible()
})

test('no custom cursor on coarse pointers', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 })
  await page.goto('/')
  await expect(page.locator('[data-cursor-root]')).toHaveCount(0)
})
```

- [ ] **Step 3: Run test to verify it fails**

Run: `cd web && npm run test:e2e -- chrome`
Expected: FAIL — no nav, no footer.

- [ ] **Step 4: Install motion dependencies**

```bash
cd web && npm install gsap @gsap/react lenis
```

GSAP is free including every plugin since April 2025. No license key or private registry is needed.

- [ ] **Step 5: Implement SmoothScroll**

Create `web/src/components/chrome/SmoothScroll.tsx`:

```tsx
'use client' // owns a Lenis instance and a rAF loop

import { ReactLenis } from 'lenis/react'
import type { ReactNode } from 'react'

/**
 * Momentum scroll for the whole document. Lenis drives native scroll rather
 * than transform-virtualizing the page, so position:sticky, anchor links, and
 * the browser's own scroll restoration all keep working.
 *
 * Users who ask for reduced motion get native scroll — momentum is exactly the
 * kind of motion that request is about.
 */
export function SmoothScroll({ children }: { children: ReactNode }) {
  // Momentum scroll is exactly the kind of motion this request is about, so
  // opt out entirely rather than shortening the duration.
  const reduced =
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches

  if (reduced) return <>{children}</>

  return (
    <ReactLenis
      root
      options={{
        duration: 1.1,
        easing: (t: number) => 1 - Math.pow(1 - t, 3),
        smoothWheel: true,
      }}
    >
      {children}
    </ReactLenis>
  )
}
```

- [ ] **Step 6: Implement Nav, Footer, NewsletterForm, and Cursor**

Build each against what Step 1 showed you. Requirements the tests encode:

- `Nav` renders a `<header>` carrying `data-scrolled="true"` once `window.scrollY > 100`. Use a single passive scroll listener in a small client child; do not attach a listener per link.
- The mobile menu is a `role="dialog"` that closes on Escape and traps focus while open.
- `Footer` renders the contact email, phone, address, and social links from `Settings`, plus `<NewsletterForm />`.
- `NewsletterForm` is a client component. It posts via `submitNewsletter()`, includes a visually hidden `website` honeypot input, and renders the success message from the response body. On a 429 it renders "Too many requests — try again in a minute."
- `Cursor` returns `null` when `matchMedia('(hover: none) and (pointer: coarse)')` matches, so coarse-pointer devices never mount it. It renders a `[data-cursor-root]` element carrying the camera glyph from `resources/css/core.css:52`, and snaps to elements with `[data-magnetic]`.
- Every animated property is `transform` or `opacity` — see `plans/005-hover-layout-properties-to-transform.md`.

- [ ] **Step 7: Wire the layout**

`web/src/app/layout.tsx` fetches settings once and renders the chrome:

```tsx
import type { ReactNode } from 'react'
import { Outfit } from 'next/font/google'
import { getSettings } from '@/lib/api'
import { Cursor } from '@/components/chrome/Cursor'
import { Footer } from '@/components/chrome/Footer'
import { Nav } from '@/components/chrome/Nav'
import { SmoothScroll } from '@/components/chrome/SmoothScroll'
import './globals.css'

const outfit = Outfit({ subsets: ['latin'], display: 'swap', variable: '--font-outfit' })

export default async function RootLayout({ children }: { children: ReactNode }) {
  const settings = await getSettings()

  return (
    <html lang="en" className={outfit.variable}>
      <body>
        <SmoothScroll>
          <Cursor />
          <Nav settings={settings} />
          <main id="main">{children}</main>
          <Footer settings={settings} />
        </SmoothScroll>
      </body>
    </html>
  )
}
```

- [ ] **Step 8: Run test to verify it passes**

Run: `cd web && npm run test:e2e -- chrome`
Expected: PASS, 12 tests (6 specs × 2 projects).

- [ ] **Step 9: Commit**

```bash
git add web/src/components/chrome web/src/app/layout.tsx web/tests/e2e/chrome.spec.ts web/package.json web/package-lock.json
git commit -m "feat(web): add root layout with nav, footer, cursor and smooth scroll"
```

---

### Task 5: Home page

**Files:**
- Modify: `web/src/app/page.tsx`
- Create: `web/src/components/home/Hero.tsx`
- Create: `web/src/components/home/ServicesSection.tsx`
- Create: `web/src/components/home/WorkCollage.tsx`
- Create: `web/src/components/home/IndustriesDeck.tsx`
- Create: `web/src/components/home/TestimonialsSection.tsx`
- Create: `web/src/components/home/ClientLogos.tsx`
- Create: `web/src/components/home/CtaBand.tsx`
- Test: `web/tests/e2e/home.spec.ts`

**Interfaces:**
- Consumes: `getHome()` (Task 2), `toMetadata`, `JsonLd` (Task 3), `Settings` from layout.
- Produces: components that Plan 3 wraps in `<View>` — each section root element carries a stable `data-section` attribute (`hero`, `work-collage`, `industries`) so Plan 3 can attach a WebGL viewport without restructuring the DOM.

- [ ] **Step 1: Write the failing test**

Create `web/tests/e2e/home.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('renders every homepage section', async ({ page }) => {
  await page.goto('/')

  for (const section of ['hero', 'services', 'work-collage', 'industries', 'testimonials', 'clients', 'cta']) {
    await expect(page.locator(`[data-section="${section}"]`)).toBeVisible()
  }
})

test('the hero renders admin content, not placeholder copy', async ({ page, request }) => {
  const body = await (await request.get(`${process.env.API_BASE_URL}/api/v1/pages/home`)).json()
  const label = body.data.hero_slides[0]?.label

  test.skip(!label, 'no hero slides seeded')
  await page.goto('/')
  await expect(page.locator('[data-section="hero"]')).toContainText(label)
})

test('work tiles link to nothing — detail pages are retired', async ({ page }) => {
  await page.goto('/')
  const links = page.locator('[data-section="work-collage"] a[href^="/portfolio/"]')
  await expect(links).toHaveCount(0)
})

test('emits Organization and WebSite json-ld', async ({ page }) => {
  await page.goto('/')
  const scripts = await page.locator('script[type="application/ld+json"]').allTextContents()
  const types = scripts.map((s) => JSON.parse(s)['@type'])
  expect(types).toContain('Organization')
  expect(types).toContain('WebSite')
})

test('title comes from the admin SeoPage row', async ({ page, request }) => {
  const body = await (await request.get(`${process.env.API_BASE_URL}/api/v1/pages/home`)).json()
  test.skip(!body.seo.title, 'no seo row for /')
  await page.goto('/')
  await expect(page).toHaveTitle(body.seo.title)
})

test('has exactly one h1', async ({ page }) => {
  await page.goto('/')
  await expect(page.locator('h1')).toHaveCount(1)
})

test('every image has an alt attribute', async ({ page }) => {
  await page.goto('/')
  const missing = await page.locator('img:not([alt])').count()
  expect(missing).toBe(0)
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd web && npm run test:e2e -- home`
Expected: FAIL — no sections.

- [ ] **Step 3: Read the Blade homepage**

Read `resources/views/home.blade.php` and the partials it includes. Note the section order, the collage layout rules, and which pieces of copy come from the database versus the template. The Next version renders the same sections in the same order with the same content sources.

- [ ] **Step 4: Implement the page**

`web/src/app/page.tsx`:

```tsx
import type { Metadata } from 'next'
import { getHome } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { JsonLd } from '@/components/JsonLd'
import { Hero } from '@/components/home/Hero'
import { ServicesSection } from '@/components/home/ServicesSection'
import { WorkCollage } from '@/components/home/WorkCollage'
import { IndustriesDeck } from '@/components/home/IndustriesDeck'
import { TestimonialsSection } from '@/components/home/TestimonialsSection'
import { ClientLogos } from '@/components/home/ClientLogos'
import { CtaBand } from '@/components/home/CtaBand'

export async function generateMetadata(): Promise<Metadata> {
  const { seo } = await getHome()
  return toMetadata(seo)
}

export default async function HomePage() {
  const { data, seo } = await getHome()

  return (
    <>
      <JsonLd data={seo.json_ld} />
      <Hero slides={data.hero_slides} />
      <ServicesSection services={data.services} />
      <WorkCollage works={data.featured_works} />
      <IndustriesDeck industries={data.industries} />
      <TestimonialsSection testimonials={data.testimonials} />
      <ClientLogos clients={data.clients} />
      <CtaBand />
    </>
  )
}
```

- [ ] **Step 5: Implement each section component**

Rules that apply to all of them:

- Each root element carries `data-section="<name>"` matching the test's list.
- Server components unless they need state. `Hero`'s slide advance and `WorkCollage`'s hover need `'use client'`; the rest do not.
- Use `next/image` with explicit `width`/`height` from the `Media` shape, or `fill` inside a container with a fixed aspect ratio. Never render an image without reserved space — see `plans/012` on layout stability.
- The first hero image gets `priority`. Nothing else does.
- Entrance animations use GSAP ScrollTrigger with ease-out curves, per `plans/004-ease-out-on-entrances.md`. Release `will-change` after the animation completes, per `plans/007-release-will-change.md`.
- `WorkCollage` renders tiles at the `work_tile_ratio` from settings, using it as a CSS custom property. Work detail pages are retired — tiles do not link to `/portfolio/{slug}`; they open a lightbox.
- `CtaBand` uses `settings.cta_video_url` as a background video, `muted playsInline loop`, with `preload="none"` and a poster.

- [ ] **Step 6: Run test to verify it passes**

Run: `cd web && npm run test:e2e -- home`
Expected: PASS, 14 tests.

- [ ] **Step 7: Commit**

```bash
git add web/src/app/page.tsx web/src/components/home web/tests/e2e/home.spec.ts
git commit -m "feat(web): build the home page from the api bundle"
```

---

### Task 6: Portfolio page with filtering

**Files:**
- Create: `web/src/app/portfolio/page.tsx`
- Create: `web/src/components/work/WorkGrid.tsx`
- Create: `web/src/components/work/WorkFilters.tsx`
- Create: `web/src/components/work/WorkLightbox.tsx`
- Test: `web/tests/e2e/portfolio.spec.ts`

**Interfaces:**
- Consumes: `getWorks()` (Task 2).
- Produces: `WorkGrid` root carries `data-section="work-grid"` for Plan 3's WebGL gallery to attach to.
- Filter state lives in the URL (`?category=`), not React state, so a filtered view is linkable and server-rendered.

- [ ] **Step 1: Write the failing test**

Create `web/tests/e2e/portfolio.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('renders a grid of works', async ({ page }) => {
  await page.goto('/portfolio')
  await expect(page.locator('[data-work-tile]').first()).toBeVisible()
})

test('filtering by category updates the url and the grid', async ({ page, request }) => {
  const body = await (await request.get(`${process.env.API_BASE_URL}/api/v1/works`)).json()
  const category = body.filters.categories[0]
  test.skip(!category, 'no categories seeded')

  await page.goto('/portfolio')
  await page.getByRole('link', { name: category.label, exact: true }).click()

  await expect(page).toHaveURL(new RegExp(`category=${category.value}`))
  const shown = await page.locator('[data-work-tile]').count()
  expect(shown).toBeGreaterThan(0)
})

test('a filtered url renders server-side without JavaScript', async ({ browser, request }) => {
  const body = await (await request.get(`${process.env.API_BASE_URL}/api/v1/works`)).json()
  const category = body.filters.categories[0]
  test.skip(!category, 'no categories seeded')

  const context = await browser.newContext({ javaScriptEnabled: false })
  const page = await context.newPage()
  await page.goto(`/portfolio?category=${category.value}`)

  await expect(page.locator('[data-work-tile]').first()).toBeVisible()
  await context.close()
})

test('pagination advances to page two', async ({ page, request }) => {
  const body = await (await request.get(`${process.env.API_BASE_URL}/api/v1/works`)).json()
  test.skip(body.meta.last_page < 2, 'only one page of works')

  await page.goto('/portfolio')
  await page.getByRole('link', { name: /next/i }).click()
  await expect(page).toHaveURL(/page=2/)
})

test('clicking a tile opens the lightbox and Escape closes it', async ({ page }) => {
  await page.goto('/portfolio')
  await page.locator('[data-work-tile]').first().click()
  await expect(page.getByRole('dialog')).toBeVisible()
  await page.keyboard.press('Escape')
  await expect(page.getByRole('dialog')).not.toBeVisible()
})

test('canonical is page-aware', async ({ page }) => {
  await page.goto('/portfolio?page=2')
  const canonical = await page.locator('link[rel="canonical"]').getAttribute('href')
  expect(canonical).toContain('page=2')
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd web && npm run test:e2e -- portfolio`
Expected: FAIL — 404 on `/portfolio`.

- [ ] **Step 3: Implement the page**

`web/src/app/portfolio/page.tsx`:

```tsx
import type { Metadata } from 'next'
import { getWorks } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { WorkFilters } from '@/components/work/WorkFilters'
import { WorkGrid } from '@/components/work/WorkGrid'

type SearchParams = Promise<{ category?: string; industry?: string; page?: string }>

export async function generateMetadata({ searchParams }: { searchParams: SearchParams }): Promise<Metadata> {
  const params = await searchParams
  const { seo } = await getWorks({ ...params, page: Number(params.page) || undefined })
  return toMetadata(seo)
}

export default async function PortfolioPage({ searchParams }: { searchParams: SearchParams }) {
  const params = await searchParams
  const { data, meta, filters } = await getWorks({
    category: params.category,
    industry: params.industry,
    page: Number(params.page) || undefined,
  })

  return (
    <>
      <WorkFilters filters={filters} active={params} />
      <WorkGrid works={data} meta={meta} params={params} />
    </>
  )
}
```

- [ ] **Step 4: Implement the components**

- `WorkFilters` is a **server component** rendering `<Link>` elements that set `?category=`. Filter state in the URL means the filtered view is server-rendered, linkable, and crawlable. No client state.
- `WorkGrid` carries `data-section="work-grid"`, renders each tile with `data-work-tile`, and includes prev/next pagination links built from `meta`. Grid filtering animates per `plans/013-animate-work-grid-filtering.md` — items that leave animate out rather than disappearing.
- `WorkLightbox` is `'use client'`. It opens on tile click, renders `work.media` (images, videos, YouTube embeds), traps focus, closes on Escape and on backdrop click, and restores focus to the tile that opened it. Open motion follows `plans/014-lightbox-open-motion.md`.

- [ ] **Step 5: Run test to verify it passes**

Run: `cd web && npm run test:e2e -- portfolio`
Expected: PASS, 12 tests.

- [ ] **Step 6: Commit**

```bash
git add web/src/app/portfolio web/src/components/work web/tests/e2e/portfolio.spec.ts
git commit -m "feat(web): build the portfolio page with url-driven filtering"
```

---

### Task 7: Service detail and industries pages

**Files:**
- Create: `web/src/app/services/[slug]/page.tsx`
- Create: `web/src/app/industries/page.tsx`
- Create: `web/src/components/service/` (Hero, Pillars, Phases, Kit, Faqs, Cta)
- Create: `web/src/components/industry/IndustryDeck.tsx`
- Test: `web/tests/e2e/services.spec.ts`
- Test: `web/tests/e2e/industries.spec.ts`

**Interfaces:**
- Consumes: `getService()`, `getServices()`, `getIndustries()` (Task 2).
- Produces: `generateStaticParams()` on the service route, pre-rendering every service slug at build.

- [ ] **Step 1: Write the failing tests**

Create `web/tests/e2e/services.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

const API = process.env.API_BASE_URL

test('renders every section of a service page', async ({ page, request }) => {
  const { data } = await (await request.get(`${API}/api/v1/services`)).json()
  const slug = data[0].slug

  await page.goto(`/services/${slug}`)

  for (const section of ['service-hero', 'pillars', 'phases', 'kit', 'faqs', 'service-cta']) {
    await expect(page.locator(`[data-section="${section}"]`)).toBeVisible()
  }
})

test('faqs expand and collapse', async ({ page, request }) => {
  const { data } = await (await request.get(`${API}/api/v1/services`)).json()
  await page.goto(`/services/${data[0].slug}`)

  const first = page.locator('[data-section="faqs"] details').first()
  test.skip((await first.count()) === 0, 'no faqs seeded')

  await expect(first).not.toHaveAttribute('open', '')
  await first.locator('summary').click()
  await expect(first).toHaveAttribute('open', '')
})

test('emits Service json-ld', async ({ page, request }) => {
  const { data } = await (await request.get(`${API}/api/v1/services`)).json()
  await page.goto(`/services/${data[0].slug}`)

  const types = (await page.locator('script[type="application/ld+json"]').allTextContents())
    .map((s) => JSON.parse(s)['@type'])
  expect(types).toContain('Service')
})

test('an unknown slug renders the 404 page', async ({ page }) => {
  const response = await page.goto('/services/not-a-real-service')
  expect(response?.status()).toBe(404)
})
```

Create `web/tests/e2e/industries.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('lists every industry', async ({ page, request }) => {
  const { data } = await (await request.get(`${process.env.API_BASE_URL}/api/v1/industries`)).json()

  await page.goto('/industries')
  await expect(page.locator('[data-industry-card]')).toHaveCount(data.length)
})

test('an industry card opens a pre-filled quote rather than a detail page', async ({ page }) => {
  await page.goto('/industries')
  const first = page.locator('[data-industry-card]').first()

  // Detail pages are retired — the card must not link to /industries/{slug}.
  await expect(first.locator('a[href^="/industries/"]')).toHaveCount(0)
  await first.getByRole('link').first().click()
  await expect(page).toHaveURL(/\/contact\?/)
})
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `cd web && npm run test:e2e -- services industries`
Expected: FAIL — 404 on both routes.

- [ ] **Step 3: Read the Blade sources**

Read `resources/views/services/show.blade.php` and `resources/views/industries/index.blade.php`. Note that the service hero meta strip was deliberately retired (commit `517ac53`) — do not reintroduce it. Note that industry detail pages 301 to the index and clicking through opens a pre-filled quote.

- [ ] **Step 4: Implement the service route**

```tsx
import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import { ApiError, getService, getServices } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { JsonLd } from '@/components/JsonLd'

type Params = Promise<{ slug: string }>

/** Pre-render every service at build; there are three and they change rarely. */
export async function generateStaticParams() {
  const { data } = await getServices()
  return data.map((service) => ({ slug: service.slug }))
}

export async function generateMetadata({ params }: { params: Params }): Promise<Metadata> {
  const { slug } = await params
  try {
    const { seo } = await getService(slug)
    return toMetadata(seo)
  } catch {
    return {}
  }
}

export default async function ServicePage({ params }: { params: Params }) {
  const { slug } = await params

  let payload
  try {
    payload = await getService(slug)
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) notFound()
    throw error
  }

  const { data, seo } = payload

  return (
    <>
      <JsonLd data={seo.json_ld} />
      {/* section components here */}
    </>
  )
}
```

Build the section components against Step 3's reading. FAQs use native `<details>`/`<summary>` so they work without JavaScript and are accessible by default; style the disclosure, do not reimplement it. Collapsible height animates with `grid-template-rows`, not `max-height` — see `plans/006-max-height-collapsibles-to-grid-rows.md`.

- [ ] **Step 5: Implement the industries route**

Cards carry `data-industry-card`. Each links to `/contact?industry={slug}` rather than a detail page.

- [ ] **Step 6: Run tests to verify they pass**

Run: `cd web && npm run test:e2e -- services industries`
Expected: PASS, 12 tests.

- [ ] **Step 7: Commit**

```bash
git add web/src/app/services web/src/app/industries web/src/components/service web/src/components/industry web/tests/e2e/services.spec.ts web/tests/e2e/industries.spec.ts
git commit -m "feat(web): build service detail and industries pages"
```

---

### Task 8: Blog index and detail

**Files:**
- Create: `web/src/app/blog/page.tsx`
- Create: `web/src/app/blog/[slug]/page.tsx`
- Create: `web/src/components/blog/PostCard.tsx`
- Create: `web/src/components/blog/PostBody.tsx`
- Test: `web/tests/e2e/blog.spec.ts`

**Interfaces:**
- Consumes: `getPosts()`, `getPost()` (Task 2).
- Produces: `generateStaticParams()` on the post route.

- [ ] **Step 1: Write the failing test**

Create `web/tests/e2e/blog.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

const API = process.env.API_BASE_URL

test('lists posts newest first', async ({ page, request }) => {
  const body = await (await request.get(`${API}/api/v1/posts`)).json()

  await page.goto('/blog')
  await expect(page.locator('[data-post-card]')).toHaveCount(body.data.length)

  const first = await page.locator('[data-post-card] h2, [data-post-card] h3').first().textContent()
  expect(first?.trim()).toBe(body.data[0].title)
})

test('a post renders its body and reading time', async ({ page, request }) => {
  const body = await (await request.get(`${API}/api/v1/posts`)).json()
  const post = body.data[0]

  await page.goto(`/blog/${post.slug}`)
  await expect(page.locator('h1')).toContainText(post.title)
  await expect(page.locator('[data-reading-time]')).toContainText(String(post.reading_minutes))
})

test('emits BlogPosting json-ld', async ({ page, request }) => {
  const body = await (await request.get(`${API}/api/v1/posts`)).json()

  await page.goto(`/blog/${body.data[0].slug}`)
  const types = (await page.locator('script[type="application/ld+json"]').allTextContents())
    .map((s) => JSON.parse(s)['@type'])
  expect(types).toContain('BlogPosting')
})

test('shows related posts excluding the current one', async ({ page, request }) => {
  const body = await (await request.get(`${API}/api/v1/posts`)).json()
  const slug = body.data[0].slug

  await page.goto(`/blog/${slug}`)
  await expect(page.locator(`[data-related] a[href="/blog/${slug}"]`)).toHaveCount(0)
})

test('an unknown post slug 404s', async ({ page }) => {
  const response = await page.goto('/blog/not-a-real-post')
  expect(response?.status()).toBe(404)
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd web && npm run test:e2e -- blog`
Expected: FAIL — 404 on `/blog`.

- [ ] **Step 3: Implement both routes**

Follow the same structure as Task 7's service route: `generateStaticParams`, `generateMetadata` from the API `seo`, `notFound()` on a 404 from `ApiError`.

`PostBody` renders `post.body` as HTML. The body comes from the Filament rich editor, which already sanitizes on write — render it with `dangerouslySetInnerHTML` and a comment saying exactly that. Do not add a client-side sanitizer; it would strip the editor's own markup.

Cards carry `data-post-card`. Reading time carries `data-reading-time`. Related posts sit inside `[data-related]`.

- [ ] **Step 4: Run test to verify it passes**

Run: `cd web && npm run test:e2e -- blog`
Expected: PASS, 10 tests.

- [ ] **Step 5: Commit**

```bash
git add web/src/app/blog web/src/components/blog web/tests/e2e/blog.spec.ts
git commit -m "feat(web): build blog index and post detail pages"
```

---

### Task 9: Contact page, quote wizard, and static pages

**Files:**
- Create: `web/src/app/contact/page.tsx`
- Create: `web/src/app/about/page.tsx`
- Create: `web/src/app/(legal)/privacy-policy/page.tsx`
- Create: `web/src/app/(legal)/terms-of-service/page.tsx`
- Create: `web/src/app/(legal)/cookie-policy/page.tsx`
- Create: `web/src/app/(legal)/disclaimer/page.tsx`
- Create: `web/src/app/thank-you/page.tsx`
- Create: `web/src/components/contact/QuoteWizard.tsx`
- Test: `web/tests/e2e/contact.spec.ts`
- Test: `web/tests/e2e/static-pages.spec.ts`

**Interfaces:**
- Consumes: `getContact()`, `getAbout()`, `getStaticPage()`, `submitContact()` (Task 2).
- Produces: `QuoteWizard` — client component, multi-step, reads `?industry=` and `?service=` to pre-fill.

- [ ] **Step 1: Write the failing tests**

Create `web/tests/e2e/contact.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('submits a complete quote and lands on thank-you', async ({ page }) => {
  await page.goto('/contact')

  await page.getByLabel(/name/i).fill('Ada Lovelace')
  await page.getByLabel(/email/i).fill(`e2e-${Date.now()}@example.com`)
  await page.getByLabel(/message|brief|project/i).first().fill('We need a brand film for our spring launch.')

  await page.getByRole('button', { name: /send|submit/i }).click()
  await expect(page).toHaveURL(/\/thank-you/)
})

test('shows field errors from the api rather than clearing the form', async ({ page }) => {
  await page.goto('/contact')

  await page.getByLabel(/name/i).fill('Ada Lovelace')
  await page.getByLabel(/email/i).fill('not-an-email')
  await page.getByRole('button', { name: /send|submit/i }).click()

  await expect(page.locator('[data-field-error]').first()).toBeVisible()
  await expect(page.getByLabel(/name/i)).toHaveValue('Ada Lovelace')
})

test('pre-fills the industry from the query string', async ({ page, request }) => {
  const { data } = await (await request.get(`${process.env.API_BASE_URL}/api/v1/industries`)).json()
  test.skip(data.length === 0, 'no industries seeded')

  await page.goto(`/contact?industry=${data[0].slug}`)
  await expect(page.locator('[data-prefilled-industry]')).toContainText(data[0].title)
})

test('the honeypot field is present and hidden', async ({ page }) => {
  await page.goto('/contact')
  const honeypot = page.locator('input[name="website"]')
  await expect(honeypot).toHaveCount(1)
  await expect(honeypot).not.toBeVisible()
})
```

Create `web/tests/e2e/static-pages.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

const pages = ['/about', '/privacy-policy', '/terms-of-service', '/cookie-policy', '/disclaimer', '/thank-you']

test('every static page renders with a title and an h1', async ({ page }) => {
  for (const path of pages) {
    const response = await page.goto(path)
    expect(response?.status(), `${path} status`).toBe(200)
    await expect(page.locator('h1'), `${path} h1`).toHaveCount(1)
    expect(await page.title(), `${path} title`).not.toBe('')
  }
})

test('every static page sets a canonical', async ({ page }) => {
  for (const path of pages) {
    await page.goto(path)
    const canonical = await page.locator('link[rel="canonical"]').getAttribute('href')
    expect(canonical, `${path} canonical`).toContain(path)
  }
})
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `cd web && npm run test:e2e -- contact static-pages`
Expected: FAIL — 404s.

- [ ] **Step 3: Implement the legal pages**

Each legal route fetches `getStaticPage(slug)` with the matching slug and renders `data.body` via `dangerouslySetInnerHTML`. The API returns rendered Blade HTML during this plan — Plan 3 moves that copy into the admin and the shape does not change.

There is no route-to-slug mapping: the API slug **is** the URL slug
(`/privacy-policy` → `privacy-policy`). `/thank-you` returns `body: null` on
purpose — it is a designed confirmation screen, so build its markup in the Next
route and use the endpoint only for metadata.

- [ ] **Step 4: Implement the quote wizard**

`QuoteWizard` is `'use client'`. Requirements the tests encode:

- Multi-step, with step continuity motion per `plans/015-quote-wizard-step-continuity.md`.
- Reads `?industry=` and `?service=` via `useSearchParams()` and pre-fills, showing the choice in a `[data-prefilled-industry]` element.
- Includes a visually hidden `website` honeypot input — hidden with `position:absolute; left:-9999px` and `tabIndex={-1}`, not `display:none`, because some bots skip `display:none` fields.
- On submit, calls `submitContact()`. On 422 it renders each field's error in a `[data-field-error]` element **without clearing the form**. On 429 it renders "Too many requests — try again in a minute." On 201 it navigates to `/thank-you`.
- Submit button disables while the request is in flight.

- [ ] **Step 5: Run tests to verify they pass**

Run: `cd web && npm run test:e2e -- contact static-pages`
Expected: PASS, 12 tests.

- [ ] **Step 6: Commit**

```bash
git add web/src/app/contact web/src/app/about web/src/app/\(legal\) web/src/app/thank-you web/src/components/contact web/tests/e2e/contact.spec.ts web/tests/e2e/static-pages.spec.ts
git commit -m "feat(web): build contact wizard, about, and static pages"
```

---

### Task 10: Redirects and revalidation

The two integration points that make the Next app a drop-in replacement: every legacy URL still resolves, and content edits invalidate the right cache.

**Files:**
- Create: `web/src/redirects.ts`
- Modify: `web/next.config.ts`
- Create: `web/src/app/api/revalidate/route.ts`
- Create: `web/src/app/not-found.tsx`
- Test: `tests/Feature/Api/V1/RedirectParityTest.php`
- Test: `web/tests/e2e/redirects.spec.ts`

**Interfaces:**
- Consumes: `REVALIDATE_SECRET` (Task 1), the tag vocabulary from Plan 1 Task 11.
- Produces:
  - `web/src/redirects.ts` exporting `REDIRECTS: {source: string; destination: string; permanent: true}[]`.
  - `POST /api/revalidate` — validates the shared secret, calls `revalidateTag()` per tag, returns `{revalidated: string[]}`.

- [ ] **Step 1: Write the failing parity test**

This test lives on the **Laravel** side, because `routes/web.php` is the source of truth. Create `tests/Feature/Api/V1/RedirectParityTest.php`:

```php
<?php

/**
 * Every 301 in routes/web.php must exist in the Next app's redirect list.
 * A redirect dropped during the migration silently 404s an indexed URL, and
 * nothing else in either test suite would catch it.
 */
it('mirrors every laravel 301 in the next redirect list', function () {
    $laravel = collect(app('router')->getRoutes()->getRoutes())
        ->filter(fn ($route) => str_contains((string) ($route->getAction('uses') ?? ''), 'RedirectController')
            || $route->getAction('redirect') !== null)
        ->map(fn ($route) => '/'.ltrim($route->uri(), '/'))
        ->values();

    $tsPath = base_path('web/src/redirects.ts');
    expect(file_exists($tsPath))->toBeTrue('web/src/redirects.ts is missing');

    $ts = file_get_contents($tsPath);

    foreach ($laravel as $source) {
        // Laravel wildcards are {slug}; Next uses :slug.
        $next = preg_replace('/\{(\w+)\}/', ':$1', $source);
        expect($ts)->toContain("'{$next}'", "Missing redirect for {$source}");
    }
})->skip(fn () => ! file_exists(base_path('web/src/redirects.ts')), 'run after web/src/redirects.ts exists');
```

- [ ] **Step 2: Run the parity test to see it skip**

Run: `./bin/php vendor/bin/pest tests/Feature/Api/V1/RedirectParityTest.php`
Expected: SKIPPED — the file does not exist yet. That skip is the failing state.

- [ ] **Step 3: Transcribe the redirects**

Read `routes/web.php:16-53`. Every `Route::redirect(...)` becomes an entry. Create `web/src/redirects.ts`:

```ts
/**
 * Permanent redirects ported from routes/web.php. Every entry here preserves
 * an indexed URL or an inbound link — dropping one silently 404s real traffic.
 *
 * tests/Feature/Api/V1/RedirectParityTest.php asserts this list stays in sync
 * with the Laravel route file. Add to both or neither.
 */
export const REDIRECTS = [
  { source: '/our-process', destination: '/about', permanent: true },
  { source: '/services/weddings', destination: '/services/videography', permanent: true },
  { source: '/services/post-production', destination: '/services/editing', permanent: true },
  { source: '/services/social-content', destination: '/services/editing', permanent: true },
  { source: '/services/creative-direction', destination: '/services/editing', permanent: true },
  { source: '/industries/:slug', destination: '/industries', permanent: true },
  { source: '/our-works', destination: '/portfolio', permanent: true },
  { source: '/portfolio/:slug', destination: '/', permanent: true },
  {
    source: '/blog/how-to-brief-a-video-production-team-so-the-film-you-get-is-the-film-you-imagined',
    destination: '/blog/how-to-brief-a-video-production-team',
    permanent: true,
  },
  {
    source: '/blog/planning-your-wedding-photography-timeline-a-working-template',
    destination: '/blog/wedding-photography-timeline-planning',
    permanent: true,
  },
  {
    source: '/blog/what-post-production-actually-includes-and-why-it-is-half-the-film',
    destination: '/blog/what-post-production-actually-includes',
    permanent: true,
  },
  {
    source: '/blog/photo-video-or-both-choosing-coverage-for-your-corporate-event',
    destination: '/blog/photo-vs-video-corporate-event-coverage',
    permanent: true,
  },
  {
    source: '/blog/how-to-prepare-your-team-for-a-corporate-shoot',
    destination: '/blog/preparing-your-team-for-a-corporate-shoot',
    permanent: true,
  },
  { source: '/crew', destination: '/about', permanent: true },
  { source: '/crew/:slug', destination: '/about', permanent: true },
] as const
```

Verify against `routes/web.php` line by line. The `/portfolio/:slug` entry must come **after** the exact `/portfolio` route resolves — Next matches static segments before dynamic ones, so this works, but confirm it in Step 6's test.

- [ ] **Step 4: Wire the redirects into the build**

In `web/next.config.ts`, add:

```ts
import { REDIRECTS } from './src/redirects'
```

```ts
  async redirects() {
    return [...REDIRECTS]
  },
```

- [ ] **Step 5: Implement the revalidation route**

Create `web/src/app/api/revalidate/route.ts`:

```ts
import { revalidateTag } from 'next/cache'
import { NextResponse } from 'next/server'
import { REVALIDATE_SECRET } from '@/lib/env'

/**
 * Called by Laravel's RevalidateFrontend job when admin content changes.
 * Only reachable from localhost in production — nginx does not proxy
 * /api/revalidate from the public internet — but the shared secret is checked
 * anyway, because "unreachable" is a config file away from being wrong.
 */
export async function POST(request: Request) {
  let body: { tags?: string[]; secret?: string }

  try {
    body = await request.json()
  } catch {
    return NextResponse.json({ message: 'Invalid JSON' }, { status: 400 })
  }

  if (body.secret !== REVALIDATE_SECRET) {
    return NextResponse.json({ message: 'Invalid secret' }, { status: 401 })
  }

  const tags = Array.isArray(body.tags) ? body.tags.filter((t) => typeof t === 'string') : []

  for (const tag of tags) {
    revalidateTag(tag)
  }

  return NextResponse.json({ revalidated: tags })
}
```

- [ ] **Step 6: Write the redirect and revalidation e2e test**

Create `web/tests/e2e/redirects.spec.ts`:

```ts
import { expect, test } from '@playwright/test'
import { REDIRECTS } from '../../src/redirects'

for (const redirect of REDIRECTS) {
  // Dynamic sources need a concrete value to request.
  const source = redirect.source.replace(/:(\w+)/, 'sample-slug')
  const destination = redirect.destination.replace(/:(\w+)/, 'sample-slug')

  test(`${source} redirects to ${destination}`, async ({ page }) => {
    const response = await page.goto(source)
    expect(new URL(page.url()).pathname).toBe(destination)
    expect(response?.status()).toBe(200)
  })
}

test('/portfolio itself is not caught by the /portfolio/:slug redirect', async ({ page }) => {
  await page.goto('/portfolio')
  expect(new URL(page.url()).pathname).toBe('/portfolio')
})

test('revalidate rejects a wrong secret', async ({ request }) => {
  const response = await request.post('/api/revalidate', {
    data: { tags: ['works'], secret: 'wrong' },
  })
  expect(response.status()).toBe(401)
})

test('revalidate accepts the right secret and reports the tags', async ({ request }) => {
  const response = await request.post('/api/revalidate', {
    data: { tags: ['works', 'pages:home'], secret: process.env.REVALIDATE_SECRET },
  })
  expect(response.status()).toBe(200)
  expect((await response.json()).revalidated).toEqual(['works', 'pages:home'])
})

test('an unknown route renders the 404 page', async ({ page }) => {
  const response = await page.goto('/definitely-not-a-page')
  expect(response?.status()).toBe(404)
  await expect(page.locator('h1')).toBeVisible()
})
```

- [ ] **Step 7: Implement the 404 page**

Create `web/src/app/not-found.tsx` with an `<h1>`, a short line of copy, and links back to `/` and `/portfolio`. Read `resources/views/errors/404.blade.php` and match its tone.

- [ ] **Step 8: Run both test suites**

Run: `cd web && npm run test:e2e -- redirects`
Expected: PASS.

Run: `./bin/php vendor/bin/pest tests/Feature/Api/V1/RedirectParityTest.php`
Expected: PASS — no longer skipped.

- [ ] **Step 9: Commit**

```bash
git add web/src/redirects.ts web/next.config.ts web/src/app/api/revalidate web/src/app/not-found.tsx web/tests/e2e/redirects.spec.ts tests/Feature/Api/V1/RedirectParityTest.php
git commit -m "feat(web): port 301 redirects and add ISR revalidation endpoint"
```

---

### Task 11: Deploy to staging and verify against production

Puts the app on `next.thelastclicks.com` and proves it renders real content.

**Files:**
- Create: `docs/deploy/nextjs.md`
- Modify: `docs/DEPLOYMENT.md`
- Test: manual verification checklist below

**Interfaces:**
- Consumes: `docs/deploy/tlc-web.service`, `docs/deploy/nginx-staging.conf` (Task 1).
- Produces: a running staging site and a written deploy procedure.

- [ ] **Step 1: Install Node and the service on the VPS**

```bash
# On the server
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt-get install -y nodejs
node --version   # must print v22.x
```

- [ ] **Step 2: Build and start**

```bash
cd /home/forge/thelastclicks.com/web
cp .env.example .env    # then fill in real values
npm ci
npm run build

# The standalone build needs static assets copied alongside it.
cp -r .next/static .next/standalone/.next/static
cp -r public .next/standalone/public

sudo cp ../docs/deploy/tlc-web.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now tlc-web
sudo systemctl status tlc-web
```

- [ ] **Step 3: Configure staging nginx**

```bash
sudo htpasswd -c /etc/nginx/.htpasswd-next staging
sudo cp /home/forge/thelastclicks.com/docs/deploy/nginx-staging.conf /etc/nginx/sites-available/next.thelastclicks.com
sudo ln -s /etc/nginx/sites-available/next.thelastclicks.com /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

Issue a certificate for the subdomain before reloading, or nginx will fail its config test on the missing `ssl_certificate`.

- [ ] **Step 4: Point Laravel's revalidation at the running app**

In the production `.env`:

```
FRONTEND_REVALIDATE_URL=http://127.0.0.1:3000/api/revalidate
FRONTEND_REVALIDATE_SECRET=<same value as web/.env REVALIDATE_SECRET>
```

Then `./bin/php artisan config:clear`.

- [ ] **Step 5: Verify end to end**

Work through this list on `https://next.thelastclicks.com` and record the result of each:

- [ ] Every route loads: `/`, `/about`, `/portfolio`, `/services/photography`, `/services/videography`, `/services/editing`, `/industries`, `/blog`, a blog post, `/contact`, all four legal pages, `/thank-you`.
- [ ] Each page's `<title>` matches its `SeoPage` row in the admin.
- [ ] Editing a work title in Filament changes `/portfolio` within 10 seconds without a manual rebuild. This proves the revalidation loop end to end.
- [ ] A contact submission creates a Quote visible in the admin, and the admin notification email is queued.
- [ ] `curl -I https://next.thelastclicks.com` returns `X-Robots-Tag: noindex, nofollow`.
- [ ] `systemctl restart tlc-web` brings the site back within 10 seconds.

- [ ] **Step 6: Run the full e2e suite against staging**

```bash
cd web && E2E_BASE_URL=https://next.thelastclicks.com npm run test:e2e
```

Basic auth will block Playwright — add `httpCredentials` to the config's `use` block, reading from env, or temporarily disable `auth_basic` for the run.

Expected: all specs pass against the real server.

- [ ] **Step 7: Write the deploy documentation**

Create `docs/deploy/nextjs.md` covering: Node version, the build sequence including the two `cp` commands for static assets (a missing `cp` produces an unstyled site, which is the single most common self-hosting mistake), the systemd unit, the nginx blocks for staging and production, the env vars and which must match between Laravel and Next, and the rollback procedure.

Add a "Frontend" section to `docs/DEPLOYMENT.md` linking to it.

- [ ] **Step 8: Commit**

```bash
git add docs/deploy/nextjs.md docs/DEPLOYMENT.md web/playwright.config.ts
git commit -m "docs(deploy): document Next.js staging deployment"
```

---

## Definition of Done

- [ ] Every route in `routes/web.php` has a matching Next route or redirect.
- [ ] `cd web && npm run typecheck && npm run lint && npm run test:e2e` is green.
- [ ] `./bin/php vendor/bin/pest` is green, including `RedirectParityTest`.
- [ ] `https://next.thelastclicks.com` renders every page with real admin content.
- [ ] Editing content in Filament updates the staging site without a rebuild.
- [ ] The staging site returns `X-Robots-Tag: noindex, nofollow` on every response.
- [ ] Production `https://thelastclicks.com` is unchanged and still served by Blade.
- [ ] No `three` or `@react-three/*` package appears in `web/package.json`.
- [ ] Every file in `web/src/components/ui/` names its upstream source and uses only brand tokens.
