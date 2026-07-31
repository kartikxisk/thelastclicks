# Public API v1

The JSON contract the Next.js frontend renders from. Laravel keeps the Filament
admin and serves this API; nothing here requires authentication, because every
GET already returns data the public site displays and the POST routes are the
same public forms the Blade site exposed.

**This document mirrors `tests/Feature/Api/V1/contract.json`, which is asserted
on every test run.** When a response shape changes, all three of these move in
the same pull request: the Resource class, the recorded contract
(`UPDATE_CONTRACT=1 ./bin/php vendor/bin/pest --filter=contract`), and
`web/src/lib/types.ts`.

## Conventions

- Base path `/api/v1`. Route names are `api.v1.*`.
- Every GET returns an `seo` object (below). List endpoints also return `meta`
  and `filters`.
- Array-typed fields are always arrays, never null — the frontend iterates them
  without guarding.
- Nullable media fields are `null`, never `{}`.
- `spatie/laravel-responsecache` never wraps these routes. Next.js ISR is the
  cache of record; two caches over one dataset produce stale bugs that are very
  hard to reproduce.

### The `seo` object

Built from the admin-managed `SeoPage` row for the route, with per-model
overrides. Spreads directly into Next's `generateMetadata`.

```jsonc
{
  "title": "string|null",
  "description": "string|null",
  "canonical": "string",          // absolute; page-aware on paginated routes
  "noindex": false,
  "nofollow": false,
  "og": { "title": "string|null", "description": "string|null", "image": "string|null" },
  "json_ld": []                    // array of Schema.org objects
}
```

### The `media` object

Carries enough for both `next/image` and a WebGL texture in one payload.

```jsonc
{ "url": "string", "srcset": "string|null", "width": "int|null",
  "height": "int|null", "mime": "string|null", "alt": "string|null" }
```

### The `media item` object (gallery rows)

Rows whose file is missing or whose YouTube URL will not parse are dropped, so
the frontend never renders a hole.

```jsonc
{ "type": "image|video|youtube", "url": "string", "poster": "string|null",
  "caption": "string|null", "width": "int|null", "height": "int|null",
  "mime": "string|null" }
```

---

## Endpoints

### `GET /health`

`{ "status": "ok", "version": "v1" }`. Liveness only.

### `GET /settings`

Global chrome for the root layout. No `seo` — this is configuration, not a page.

| Field | Type | Notes |
|---|---|---|
| `contact_email` | string\|null | Falls back to `mail.from.address` |
| `contact_phone` | string\|null | |
| `whatsapp_url` | string\|null | |
| `socials` | object | Fixed keys: `instagram`, `youtube`, `facebook`, `linkedin`, `x`, `behance`, `pinterest`. Unset platforms are `null`, never absent. |
| `brand_logo_url` | string\|null | **Null means render no logo** — do not substitute a bundled file |
| `favicon_url` | string | Always resolves; falls back to the bundled favicon |
| `cta_video_url` | string | Falls back to `/videos/bg-footer.mp4` |
| `work_tile_ratio` | string | A CSS `aspect-ratio` value. An unrecognised admin value falls back to `4 / 3`. |
| `seo_defaults` | object | `{title, description, og_image}`, each nullable |

### `GET /pages/home`

`data`: `hero_slides[]`, `services[]`, `featured_works[]`, `industries[]`,
`testimonials[]`, `clients[]`. `seo.json_ld` carries `Organization` and
`WebSite`.

`featured_works` is always up to 15 items: flagged works first, topped up with
recent published work so the collage never collapses to a thin line.

### `GET /pages/about`

`data`: `testimonials[]`, `clients[]`, `stats: {works, clients}`.

### `GET /pages/contact`

`data`: `services[]`, `project_types[]`, `budget_ranges[]`.

`project_types` are `{value, label}` from `Work::CATEGORIES`. `budget_ranges`
are `{value, label}` where **value equals label** — the quote form stores the
label itself, so inventing slugs here would save values existing rows do not
use.

### `GET /pages/{slug}`

`slug` ∈ `privacy-policy | terms-of-service | cookie-policy | disclaimer | thank-you`.
A fixed route constraint, not a database lookup — an arbitrary `SeoPage` row
cannot become an endpoint. Anything else 404s.

`data.body` is rendered HTML for the four legal pages, and **`null` for
`thank-you`**, which is a designed confirmation screen the frontend owns rather
than an article.

### `GET /works`

Query: `category` (string), `page` (int ≥ 1). Twelve per page.

Returns `data[]`, `meta {current_page, last_page, per_page, total}`,
`filters.categories[]`, `seo` with a page-aware canonical.

`filters.categories` lists only categories that published work actually uses,
ordered by the `CATEGORIES` map so the filter row does not reshuffle as content
changes. An unknown category returns an empty page, not an error. A
non-integer `page` returns 422.

There is no industry filter — `Work` carries no industry relation.

**Work object**

| Field | Type |
|---|---|
| `id` | int |
| `slug`, `title` | string |
| `summary`, `client`, `category`, `category_label`, `location`, `agency`, `year` | string\|null |
| `crafts` | string[] — labels, filtered to slugs still in the CRAFTS map |
| `credits` | `{role, name}[]` — half-filled rows dropped |
| `cover` | string\|null |
| `preview_video_url` | string\|null — uploaded video only; YouTube is excluded because an iframe per tile is too heavy for a grid |
| `media` | media item[] |
| `is_featured` | bool |

### `GET /services` · `GET /services/{slug}`

Index returns `data[]` in admin order. Detail adds `data.related_works[]` (six
most recent published works) and emits `Service` JSON-LD. Unknown slug 404s.

**Service object** — note the inner key names, which are admin-authored and not
what you would guess:

| Field | Type |
|---|---|
| `id`, `share` | int |
| `slug`, `title` | string |
| `hero_headline`, `hero_copy`, `body` | string\|null |
| `hero_meta` | `{label, value}[]` |
| `hero` | media\|null |
| `proof` | `{count, label, sectors}` |
| `pillars` | `{title, desc}[]` |
| `phases` | `{num, title, desc, time}[]` |
| `kit` | `{title, items: string[]}[]` |
| `faqs` | `{q, a}[]` |
| `cta` | `{title, copy, prefill}` |
| `tags`, `gallery` | string[] |

### `GET /industries`

Index only. Detail pages are retired — `/industries/{slug}` 301s to the index
and a card click opens a pre-filled quote, so there is deliberately no show
endpoint.

**Industry object**: `id`, `slug`, `title`, `summary`, `body`, `cover`,
`media[]`, `testimonials[]`.

**Testimonial object**: `id`, `quote`, `client_name`, `role_company`. No avatar
— the model has no media collection.

**Client object**: `id`, `name`, `logo` (resolved URL string, not a media
object), `url`.

**HeroSlide object**: `id`, `label`, `asset` (media\|null), `poster`
(media\|null), `mime`, `is_video`.

### `GET /posts` · `GET /posts/{slug}`

Query: `category`, `tag`, `page`. Nine per page, newest first.

Detail adds `data.related[]` (three most recent, excluding itself) and emits
`BlogPosting` JSON-LD. Detail `seo.title` prefers the post's own `seo_title`,
then its `title` — never the `/blog` index row, which would give every article
the same title. Unpublished and future-dated slugs 404.

**Post object**: `id`, `slug`, `title`, `excerpt`, `body`, `published_at`
(ISO 8601\|null), `reading_minutes` (int ≥ 1, at 200 wpm), `cover`,
`category` (`{value, label}`\|null — the first category only), `tags`
(`{value, label}[]`).

---

## Write endpoints

All three rate limit at **5 requests per minute per IP** and carry a `website`
honeypot field. Guard order is load bearing: rate limit before any other work,
honeypot before validation.

**A honeypot hit returns the same 201 a real submission does and queues no
mail**, so a bot cannot distinguish a silent drop from a save.

| Route | Body | Success | Errors |
|---|---|---|---|
| `POST /contact` | `name`\*, `email`\*, `message`\*, `company`, `phone`, `project_type`, `budget`, `timeline`, `source_page` | 201 `{data: {id}, message}` | 422 field errors, 429 |
| `POST /quotes` | same as `/contact` | same | same |
| `POST /newsletter` | `email`\* | 201 `{message}` | 422, 429 |

`POST /newsletter` upserts, so re-subscribing never collides with the unique
index and a previously unsubscribed address is reactivated.

---

## Cache invalidation

Nine models use the `TouchesFrontend` trait. On save or delete they queue a
`RevalidateFrontend` job that POSTs `{tags, secret}` to
`FRONTEND_REVALIDATE_URL`.

| Model | Tags |
|---|---|
| Work | `works`, `works:{slug}`, `pages:home` |
| Service | `services`, `services:{slug}`, `pages:home`, `pages:contact` |
| Industry | `industries`, `pages:home` |
| Post | `posts`, `posts:{slug}` |
| HeroSlide | `pages:home` |
| Client | `pages:home`, `pages:about` |
| Testimonial | `pages:home`, `pages:about`, `industries` |
| SiteSetting | `settings` |
| SeoPage | all of the above — metadata touches every route |

The job is failure-tolerant by design: an editor saving in Filament must never
see an error because the frontend is restarting, and a missed revalidation
self-heals at the next time-based window. It no-ops entirely when
`FRONTEND_REVALIDATE_URL` is unset.
