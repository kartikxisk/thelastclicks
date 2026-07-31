# TheLastClicks — Next.js + WebGL Frontend Rebuild

**Date:** 2026-07-30
**Status:** Design approved, pending implementation plan

## Problem

The current Blade frontend does not read as a top-tier agency site. The content
model, admin, and SEO work are sound; the presentation layer is not. Raw
Three.js bolted onto Blade templates caps how far the visual work can go, and
7,600 lines of hand-written CSS across 35 Blade files makes every new
interaction expensive.

## Goal

Replace the presentation layer with a Next.js application built on a persistent
WebGL canvas, while Laravel keeps the Filament admin and becomes the REST API
behind it. Match or beat current SEO and Core Web Vitals. Lose no inbound links.

Success is measured by three things:

1. Every current URL still resolves, including all 15 existing 301 redirects.
2. LCP ≤ 2.0s, INP ≤ 200ms, CLS ≤ 0.05 on throttled mid-tier mobile.
3. The five WebGL moments in "3D System" render on WebGPU with a working
   WebGL2 fallback and a documented reduced tier for low-end devices.

## Decisions Taken

| Decision | Choice | Rationale |
|---|---|---|
| 3D depth | Hybrid — SSR'd DOM content, WebGL moments | Full-canvas sites need a parallel DOM layer for SEO and a separate mobile build. Hybrid gets the visual ceiling without either cost. |
| Hosting | Node alongside Laravel on the existing VPS | Same origin means no CORS, one deploy target, no vendor lock. |
| Brand | Keep black / red `#e80f03` / Outfit / camera cursor | The identity is not the problem; execution is. Keeping it puts all effort into craft. |
| Cutover | Build on staging subdomain, one-shot nginx switch | No half-migrated state. Blade stays on disk as a 30-second rollback. |
| API shape | Hybrid — resource endpoints plus per-page bundles | Pages stay dumb; resources stay reusable for filtering and pagination. |

## Architecture

### Repository Layout

Monorepo. The API contract and its only consumer live in one repo, so a change
to a Laravel Resource breaks the TypeScript type in the same commit.

```
thelastclicks/
├── app/ routes/ database/       Laravel: API + Filament admin
├── resources/views/             Blade — kept until cutover, deleted after
├── web/                         Next.js 15, App Router
│   ├── src/app/                 routes
│   ├── src/components/          DOM components
│   ├── src/webgl/               canvas, scenes, materials, TSL shaders
│   ├── src/lib/api.ts           typed Laravel client
│   ├── src/lib/types.ts         hand-maintained mirror of API Resources
│   └── src/styles/              tokens + globals
└── docs/superpowers/specs/
```

### Deploy Topology

nginx fronts both processes on one origin:

```
thelastclicks.com
  ├─ /                       → proxy_pass 127.0.0.1:3000   Next.js standalone
  ├─ /_next/*                → proxy_pass 127.0.0.1:3000
  ├─ /api/*                  → PHP-FPM   Laravel API
  ├─ /admin/*                → PHP-FPM   Filament
  ├─ /storage/*  /livewire/* → PHP-FPM   Filament assets
  └─ /sitemap.xml /robots.txt → static from Laravel public/
```

`/sitemap.xml` and `/robots.txt` keep their existing owners.
`GenerateSitemap` writes to `public/sitemap.xml` on a weekly schedule and must
not be moved into Next.

Next builds with `output: 'standalone'` and runs under **systemd**, not PM2 —
the box already uses systemd and supervisor, and adding a third process manager
buys nothing. Deploy is `npm ci && npm run build && systemctl restart tlc-web`.

### Caching

Three layers, each owning exactly one thing:

- `spatie/laravel-responsecache` is **disabled for `/api/*`**. Next's ISR
  becomes the cache of record. Two caches over the same data produces stale
  bugs that are very hard to reproduce.
- Next ISR writes to `.next/cache` on local disk. Single instance, so no Redis
  cache handler is needed.
- On-demand revalidation: a `TouchesFrontend` trait on the content models
  observes `saved` and `deleted`, and queues a job that POSTs to
  `127.0.0.1:3000/api/revalidate` with a tag list and a shared secret from
  `REVALIDATE_SECRET`. Queued, so a Filament save never blocks on it.

## Laravel API Layer

`routes/api.php` does not exist yet and must be registered in
`bootstrap/app.php`. All routes under `/api/v1`.

```
GET  /api/v1/settings              nav, footer, socials, CTA video, work tile ratio
GET  /api/v1/pages/home            hero slides, services, featured works,
                                   industries, testimonials, clients, seo
GET  /api/v1/pages/about           body blocks + seo
GET  /api/v1/pages/contact         form config, service options, seo
GET  /api/v1/pages/{static}        privacy | terms | cookies | disclaimer | thank-you
GET  /api/v1/works                 ?category= &industry= &page=   paginated + seo
GET  /api/v1/services              list + seo
GET  /api/v1/services/{slug}       hero, pillars, phases, kit, faqs, gallery, cta, seo
GET  /api/v1/industries            list + testimonials + seo
GET  /api/v1/posts                 ?category= &tag= &page=       paginated + seo
GET  /api/v1/posts/{slug}          body + related + seo
POST /api/v1/contact               creates a Quote
POST /api/v1/newsletter            creates a Subscriber
POST /api/v1/quotes                wizard submission
```

`{static}` is a fixed enum of exactly those five slugs, validated by route
constraint — not an open lookup against `SeoPage`.

Every GET returns an `seo` object, including the paginated list endpoints. A
route that renders a page needs its metadata from the same call that gives it
data, or `generateMetadata` fires a second uncached request.

Rules:

- One `App\Http\Resources\*` class per model. These classes are the contract.
  `web/src/lib/types.ts` mirrors them by hand and is reviewed in the same PR as
  any Resource change.
- Media fields serialize to absolute S3 URLs with conversions already applied,
  shaped as `{ url, srcset, width, height, blur_hash, mime }`. That shape feeds
  both `next/image` and WebGL `VideoTexture` without a second request.
- Every GET endpoint returns an `seo` object built from the existing `SeoPage`
  model: title, meta description, canonical, OG title/description/image,
  noindex, nofollow, and JSON-LD. Next spreads it into `generateMetadata`. On
  paginated endpoints the canonical reflects the requested page number.
- Every query eager-loads explicitly. Each endpoint gets a Pest test asserting a
  **query-count ceiling**. Query-count assertions are what prevent N+1
  regressions; code review does not catch them reliably.
- The three POST routes rate limit at **5 requests per minute per IP**, matching
  the existing `ContactController` and `NewsletterController` exactly. The
  honeypot check carries over unchanged, and keeps its position ahead of
  validation — a honeypot hit must return the same success response a real
  submission does, so a bot cannot tell a silent drop from a save.
- **No authentication.** Every GET already serves public data. The POST routes
  are public by design, exactly as the current Blade forms are. Filament's auth
  is untouched.

## 3D System

### Canvas Model

One `<Canvas eventSource={document.body}>` mounts in the root layout and never
unmounts across navigation. Sections that want 3D render a drei `<View track={ref}>`,
which uses `gl.scissor` to cut the shared canvas to that element's rect and
follows it through scroll and resize.

This gives fully server-rendered HTML for every word of content, WebGL only
where it earns its place, and a single WebGL context for the entire site.
`pmndrs/react-three-next` is the reference implementation of this pattern.

### Renderer

`WebGPURenderer` from `three/webgpu`, which falls back to WebGL2 automatically.
WebGPU has been production-ready since three r171 (September 2025) and is
supported in Chrome, Edge, Firefox, and Safari 26 including iOS. Reported gains
on heavy scenes are 2–10x.

Materials are authored in **TSL** (Three.js Shading Language) so one shader
source compiles to both backends. Hand-written GLSL would mean maintaining two
shader codebases.

### The Five WebGL Moments

| # | Location | Technique | Why it fits |
|---|---|---|---|
| 1 | Home hero | `HeroSlide` videos as `VideoTexture` on a subdivided plane; vertex displacement driven by scroll velocity and pointer; `#e80f03` as rim light | The `HeroSlide` model already stores video plus poster. The agency's actual product distorting in 3D. |
| 2 | Portfolio grid | Curved cylindrical plane gallery; each `Work.preview_video_url` becomes a video texture that plays on proximity, distorts on scroll velocity, bulges on hover | The single most expensive-looking technique available, and it is purpose-built for a video portfolio. Shader recipes exist on Codrops. |
| 3 | Services | Scroll-scrubbed camera through depth layers, one per `Service`; particle-dispersion transition on display type between sections | Turns three service pages into a journey rather than a list. |
| 4 | Cursor and transitions | WebGL cursor carrying the camera-shutter glyph, with trail and magnetic snap; page transitions as a full-screen shader wipe over the persistent canvas | Keeps the existing brand cursor but makes it feel authored rather than a CSS `cursor:` value. |
| 5 | Global post-processing | Film grain, subtle chromatic aberration, vignette — all in TSL | Photography grammar. Cheap, and it is what visually separates a shader site from a CSS site. |

### Performance Contract

Enforced by a Lighthouse CI gate on every pull request.

- The canvas is dynamically imported behind `<Suspense>`. **Zero WebGL bytes in
  the initial bundle.** First paint is server-rendered HTML and CSS only.
- Video textures pause via `IntersectionObserver` when offscreen. Never more
  than two decoding simultaneously.
- Reduced tier for `pointer: coarse` or `navigator.deviceMemory < 4`: poster
  images instead of video textures, no post-processing, half device pixel ratio.
- `prefers-reduced-motion` renders scenes static with no scroll scrubbing.
- Budgets: LCP ≤ 2.0s, INP ≤ 200ms, CLS ≤ 0.05, throttled mid-tier mobile.

## Design System

Tailwind v4, with the existing tokens as the theme source of truth: `--ink`
family for dark surfaces, `--paper` family for light text, `--red` `#e80f03`
for accent, Outfit as the single typeface, and the existing easing tokens
`--ease`, `--ease-2`, `--ease-3`.

DOM components are sourced from **Aceternity UI** — MIT licensed, copy-paste
rather than an npm dependency, and built around dark-cinematic effects
(spotlights, beams, glows) that match this palette directly. Copy-paste means
the source is owned locally and framer-motion can be stripped from any component
where GSAP already covers the motion. **react-bits** is the second source, used
for text-reveal effects, since its motion dependency is optional and
tree-shakeable.

Neither library touches the canvas. All WebGL is written against R3F, drei, and
TSL.

## Motion System

GSAP with ScrollTrigger and SplitText — free including all plugins since April
2025 — plus Lenis for momentum scroll. One `useGSAP` context per component so
cleanup happens automatically on unmount.

The 15 motion plans already written in `plans/` port forward as the motion
specification rather than being discarded. Specifically they define: consolidated
easing and duration tokens, ease-out entrance curves, the reduced-motion policy
(gentler, not zero), `will-change` release discipline, transform-only hover
states, and grid-rows collapsibles instead of `max-height`.

## SEO Parity

Non-negotiable, verified before cutover:

- All 15 existing 301 redirects in `routes/web.php` port to `next.config.js`
  `redirects()`. Both lists are asserted equal by a test that parses the Laravel
  route file, so a redirect cannot be dropped silently.
- `generateMetadata` per route, populated entirely from the `seo` object the API
  returns. No metadata is hardcoded in the Next app.
- JSON-LD emitted server-side: `Organization` and `WebSite` sitewide,
  `Service` on service pages, `BlogPosting` on posts, `BreadcrumbList` throughout.
- `sitemap.xml` and `robots.txt` stay owned by Laravel and served by nginx from
  `public/`. The weekly `sitemap:generate` schedule is unchanged.
- A pre-cutover crawl diffs every URL's title, meta description, canonical, and
  H1 between the Blade site and the staging Next site. Any diff blocks cutover.

## Testing

- **Laravel:** Pest feature test per endpoint asserting response shape, status,
  and query-count ceiling. Existing test suite must stay green.
- **Next:** Playwright end-to-end per route covering render, navigation, form
  submission, and the redirect map. A visual-regression baseline is captured
  once the design is approved.
- **WebGL:** smoke test asserting the canvas mounts, the WebGL2 fallback path is
  reachable, and the reduced tier activates under a forced `deviceMemory` value.
  Shader output itself is not asserted — it is reviewed visually.
- **Performance:** Lighthouse CI on every PR with the budgets above as hard
  failures.

## Rollout

| Phase | Work | Site state |
|---|---|---|
| 1 | Laravel `/api/v1` built, tested, documented | Blade live and untouched |
| 2 | Next scaffold, design tokens, API client, layout shell | Blade live |
| 3 | Pages built against the live API at `next.thelastclicks.com` (nginx server block, `noindex` header, basic auth) | Blade live |
| 4 | WebGL moments layered in, performance budgets enforced | Blade live |
| 5 | SEO parity crawl, redirect assertion, Lighthouse gate | Blade live |
| 6 | nginx `location /` switched to Node | Next live, Blade on disk |
| 7 | Blade views, `resources/css`, `resources/js` deleted | Next only |

Rollback at any point through phase 6 is a single nginx `location` swap and a
reload.

This spec is too large for one implementation plan. It decomposes along the
phase boundaries above into three plans, each independently shippable and
reviewable:

1. **API layer** — phase 1. Laravel only. Ends with every endpoint tested and
   the Blade site still serving traffic unchanged.
2. **Next application** — phases 2 and 3. All routes rendering real content from
   the API on the staging subdomain, no WebGL yet.
3. **WebGL and cutover** — phases 4 through 7. The five 3D moments, performance
   gates, SEO parity crawl, nginx switch, Blade deletion.

## Out of Scope

- Any change to the Filament admin, its resources, or its permissions.
- Any change to the database schema or the content model.
- Brand redesign — colors, typeface, and cursor are fixed.
- Authentication on the public API.
- Multi-instance deployment, Redis cache handler, or containerization.
