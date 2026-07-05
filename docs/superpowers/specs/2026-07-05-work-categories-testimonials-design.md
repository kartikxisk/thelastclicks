# Work Categories & Testimonials Restructure — Design

**Date:** 2026-07-05
**Status:** Approved by owner

## Goal

Make the site reflect the studio's real body of work: 22 shoot categories
(from the owner's archive folders), grouped into 7 industry pages, with
admin-managed client testimonials shown on the homepage and industry pages.

## Background

- Current site seeds 6 placeholder industries that do not match real work.
- `Category`/`Tag` models are blog-only (`post_category` pivot) — not reusable.
- Testimonials are 4 hardcoded quotes in `resources/views/home.blade.php`.
- Owner will upload real media per category through Filament afterwards;
  this work ships structure with placeholder imagery only.

## Decisions (owner-approved)

1. **Group 22 folders into 7 industries**; each folder becomes a filterable
   `WorkCategory` under its industry.
2. **No bulk media import** — placeholder images now, admin uploads later.
3. **Testimonials become a model** managed in Filament, rendered on the
   homepage carousel and on industry pages.

## Industry → category mapping

| Industry | Work categories |
|---|---|
| Weddings & Celebrations | Wedding, Prewedding, Anniversary, Birthday |
| Corporate & Events | Corporate, INS Navy, Anchor, Podcast |
| Brands & Products | Brands, Ecommerce, Product Shoots, Liquor Industry, Store & Brand Launch |
| Fashion & Creators | Fashion Show, Designer, Influencer |
| Nightlife & Entertainment | Clubbing, Concert & Artist, Festival |
| Spaces & Interiors | Interior Shoots, Decor Shoots |
| Motion & Post-Production | Motion Graphics |

Owner flagged two judgment calls as acceptable: Store & Brand Launch under
Brands & Products; Anchor under Corporate & Events.

## Data model

### `work_categories` (new)

| column | type | notes |
|---|---|---|
| id | pk | |
| industry_id | FK → industries, cascadeOnDelete | |
| title | string | |
| slug | string unique | Spatie sluggable from title |
| order | unsignedInteger default 0 | sort within industry |

Model: `WorkCategory` — `belongsTo(Industry)`, `hasMany(Portfolio)`.
`Industry` gains `hasMany(WorkCategory)` (ordered).

### `portfolios.work_category_id` (new column)

Nullable FK → work_categories, `nullOnDelete`. Existing `industry_id`
stays; Filament form filters the category select by chosen industry.

### `testimonials` (new)

| column | type | notes |
|---|---|---|
| id | pk | |
| industry_id | nullable FK → industries, nullOnDelete | ties quote to an industry page |
| quote | text | |
| client_name | string | |
| role_company | string nullable | e.g. "Marketing Head, Fortune 500 FMCG" |
| order | unsignedInteger default 0 | |
| is_published | boolean default true | |

Model: `Testimonial` — `belongsTo(Industry)`, `scopePublished`.

## Admin (Filament)

- **TestimonialResource**: quote textarea, client name, role/company,
  industry select (nullable), order, published toggle.
- **WorkCategoryResource**: title, industry select, order. Simple table.
- **PortfolioResource**: add dependent selects — industry first, work
  category options constrained to that industry.
- Filament Shield permissions follow the existing pattern for new resources.

## Frontend

- **Seeders**: replace the 6 placeholder industries with the 7 real ones
  (updateOrCreate by slug); seed 22 work categories; seed the 4 existing
  homepage quotes as `Testimonial` rows (placeholders the owner replaces).
- **Homepage**: testimonial carousel loops DB rows (`published`, ordered)
  instead of hardcoded slides. Section hidden if no rows.
- **Industry show page**: lists its work categories and its testimonials
  (when any), plus portfolio items for that industry.
- **Portfolio index**: filter chips by industry/work category via
  `?category={slug}` query param (response-cache friendly GET).
- **Redirects** (301) for retired industry slugs:
  - corporate-conferences → corporate-events
  - brand-launches → brands-products
  - automobile-showcases → brands-products
  - lifestyle-beverage → brands-products
  - destination-weddings → weddings-celebrations
  - commercial-productions → motion-post-production

## Out of scope

Bulk media import, service restructure, blog changes, homepage sections
other than testimonials.

## Testing

Pest feature tests: portfolio index filters by category slug, industry
page renders categories + testimonials, homepage renders DB testimonials,
old industry slugs 301 to new ones. Deploy note: run migrations + seeders,
then `responsecache:clear` (already mandated by deploy docs).
