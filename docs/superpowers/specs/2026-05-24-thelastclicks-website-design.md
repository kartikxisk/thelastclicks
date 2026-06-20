# TheLastClicks — Company Website Design Spec

**Date:** 2026-05-24
**Status:** Approved for implementation planning
**Stack:** Laravel 11 · PHP 8.3 · MySQL 8 · Filament v3 · Blade

---

## 1. Purpose

Convert the existing static HTML/CSS design in `design/` into a production Laravel application for TheLastClicks (cinematic photography & film production studio, Bhopal). Ship a public-facing marketing site plus an admin panel for staff to manage leads, content, and site configuration. The admin panel must visually match the frontend's dark cinematic theme.

---

## 2. High-Level Architecture

```
┌────────────────────────────────────────────────────────────────┐
│  Laravel 11 monolith (PHP 8.3 + MySQL 8)                       │
│                                                                │
│  ┌─────────────────────────┐  ┌──────────────────────────────┐│
│  │ PUBLIC SITE             │  │ ADMIN PANEL (/admin)         ││
│  │ Blade + Vite + Alpine   │  │ Filament v3                  ││
│  │ - Static pages          │  │ - Resources per model        ││
│  │ - Blog (DB-driven)      │  │ - Custom dark theme          ││
│  │ - Portfolio + cases     │  │ - filament-shield (RBAC)     ││
│  │ - Contact → Quote       │  │ - Policies (ownership ABAC)  ││
│  └─────────────────────────┘  └──────────────────────────────┘│
│             │                              │                   │
│             └──────────┬───────────────────┘                   │
│                        ▼                                       │
│              MySQL 8 + storage/app/public (media)              │
└────────────────────────────────────────────────────────────────┘
```

- Single Laravel app exposes two surfaces: public Blade site and Filament admin.
- Public assets (`core.css`, `pages.css`, `core.js`, `chrome.js`) lifted verbatim from `design/assets/` into `resources/` and served via Vite to preserve the existing visual design 1:1.
- Admin uses Filament v3 with a custom theme that matches the frontend palette (`#0a0a0a` background, brand red accent, Outfit/Sora/Instrument Serif fonts).
- Mail: log driver in dev, Resend (or SMTP) in staging/prod. Quote submissions trigger admin notification + client auto-reply.
- Media v1 stored on local disk (`storage/app/public`, symlinked). Filesystem driver is swappable to S3/R2 later via env config — no code change.

---

## 3. Data Model

### 3.1 ER overview

```
users ──┬─< role_user >── roles ──< permission_role >── permissions   (spatie)
        │
        ├─< posts (author_id)
        ├─< portfolios (owner_id)
        └─< quotes (assigned_to)

posts ──< post_category (M:N) ── categories
posts ──< post_tag      (M:N) ── tags
posts.cover_media_id → media

portfolios.cover_media_id → media
portfolios ──< portfolio_media (gallery via spatie/media-library)
portfolios.industry_id → industries
portfolios.service_id  → services

services       (slug, title, hero_copy, body, hero_media_id, order)
industries     (slug, title, summary, body, hero_media_id, order)
crew           (slug, name, role, bio, headshot_media_id, social_json, order)
quotes         (name, company, email, phone, project_type, budget,
                timeline, message, source_page, ip, ua,
                status [new|contacted|qualified|won|lost],
                assigned_to → users.id, created_at)
quote_notes    (quote_id, author_id, body, created_at)
site_settings  (key, value_json)  — KV singleton table
media          (spatie/media-library tables)
activity_log   (spatie/activitylog — audit on quotes + posts)
```

### 3.2 Models and ownership fields

| Model       | Key fields                                                                    | Ownership |
|-------------|-------------------------------------------------------------------------------|-----------|
| `User`      | name, email, password; `HasRoles` (spatie)                                    | —         |
| `Role`      | spatie                                                                        | —         |
| `Permission`| spatie + filament-shield generated                                            | —         |
| `Post`      | title, slug, excerpt, body, cover_media, status, published_at, seo_*          | `author_id` |
| `Category`  | name, slug                                                                    | —         |
| `Tag`       | name, slug                                                                    | —         |
| `Portfolio` | title, slug, client, year, service_id, industry_id, body, status, gallery     | `owner_id` |
| `Service`   | slug, title, hero_copy, body, hero_media, order                               | —         |
| `Industry`  | slug, title, summary, body, hero_media, order                                 | —         |
| `Crew`      | slug, name, role, bio, headshot, social_json, order                           | —         |
| `Quote`     | contact + brief fields, status, source_page, ip, ua                           | `assigned_to` |
| `QuoteNote` | quote_id, author_id, body                                                     | `author_id` |
| `SiteSetting` | key, value_json                                                             | —         |

### 3.3 Seeded roles

| Role          | Capabilities                                                                                  |
|---------------|-----------------------------------------------------------------------------------------------|
| Super-admin   | All permissions on all resources                                                              |
| Editor        | Posts + Portfolio: read all, write own (ownership-enforced). Services/Industries/Crew: full CRUD (global site content, no per-row ownership). |
| Sales         | Quotes (read/write only those assigned to them) + read-only Portfolio                         |
| Viewer        | Read-only on all resources                                                                    |

### 3.4 Access-control model

- **RBAC layer:** `spatie/laravel-permission` + `bezhansalleh/filament-shield`. Shield generates one permission per (resource × action): `view_quote`, `update_quote`, `delete_any_quote`, etc.
- **Ownership ABAC layer:** Laravel Policies enforce `update`/`delete` only when `auth()->id() === $resource->{owner_field}` (or the user has the `Super-admin` role).
- **Filament query scoping:** `getEloquentQuery()` overridden on `QuoteResource` so users with role `Sales` see only quotes assigned to them.

Example `QuotePolicy`:

```php
public function update(User $u, Quote $q): bool {
    if ($u->hasRole('Super-admin')) return true;
    if ($u->hasRole('Sales')) return $q->assigned_to === $u->id;
    return false;
}
```

---

## 4. Public Site

### 4.1 Route map

| URL                                  | Action                          | Source HTML                |
|--------------------------------------|---------------------------------|----------------------------|
| `/`                                  | `HomeController@index`          | `index.html`               |
| `/about`                             | `PageController@about`          | `about.html`               |
| `/our-process`                       | `PageController@process`        | `our-process.html`         |
| `/services/{slug}`                   | `ServiceController@show`        | 7 service HTML files       |
| `/industries`                        | `IndustryController@index`      | `industries.html`          |
| `/industries/{slug}`                 | `IndustryController@show`       | (new detail page)          |
| `/portfolio`                         | `PortfolioController@index`     | `portfolio.html`           |
| `/portfolio/{slug}`                  | `PortfolioController@show`      | `case-details.html`        |
| `/blog`                              | `BlogController@index`          | `blog.html`                |
| `/blog/{slug}`                       | `BlogController@show`           | `blog-details.html`        |
| `/crew`                              | `CrewController@index`          | crew section               |
| `/crew/{slug}`                       | `CrewController@show`           | `crew-details.html`        |
| `/contact`                           | `ContactController@show`        | `contact.html`             |
| `POST /contact`                      | `ContactController@store`       | → `Quote`                  |
| `/thank-you`                         | static Blade                    | `thank-you.html`           |
| `/privacy-policy` `/terms-of-service` `/cookie-policy` `/disclaimer` | `LegalController` | legal pages |
| 404                                  | view `errors.404`               | `404.html`                 |

Login/signup/forgot-password HTML pages from the design are **not** wired to routes — admin auth lives entirely under `/admin/login` (Filament). The static design files remain in `design/` for reference but do not ship as public routes.

### 4.2 Blade structure

```
resources/views/
├── layouts/
│   └── app.blade.php              ← single shared layout (head, fonts, nav, footer, scripts)
├── components/
│   ├── nav.blade.php
│   ├── footer.blade.php
│   ├── hero.blade.php
│   ├── marquee.blade.php
│   ├── card-post.blade.php
│   ├── card-portfolio.blade.php
│   ├── quote-form.blade.php
│   └── json-ld.blade.php
├── pages/                         (about, our-process, legal)
├── services/show.blade.php
├── portfolio/{index,show}.blade.php
├── blog/{index,show}.blade.php
├── industries/{index,show}.blade.php
├── crew/{index,show}.blade.php
└── contact.blade.php
```

Per-page metadata (title, description, OG image, structured data) flows into `app.blade.php` via component slots/props. One layout, many pages — single source of truth for chrome.

### 4.3 Asset pipeline

- `design/assets/core.css` and `pages.css` copied to `resources/css/`.
- `design/assets/core.js` and `chrome.js` copied to `resources/js/`.
- Loaded via `@vite([...])` in `app.blade.php`.
- Google Fonts preconnected in `<head>` (Outfit, Sora, Inter, JetBrains Mono, Instrument Serif).
- TailwindCSS used only for the Filament admin theme, not the public site (public site keeps handwritten CSS for 1:1 design preservation).

### 4.4 Contact form → Quote

- `POST /contact` validated server-side:
  - Required: `name`, `email`, `message`.
  - Enum-validated: `project_type`, `budget`, `timeline` (match the design's select options).
  - Honeypot field (`website` or similar) — must be empty.
  - Rate limit: 5 submissions per minute per IP via `RateLimiter`.
- Creates a `Quote` row with `source_page`, `ip`, `ua` captured automatically.
- Dispatches `NewQuoteAdminNotification` (email + Filament bell notification) and `QuoteAutoReply` (to client).
- Redirects to `/thank-you`.
- v1 has no captcha. If spam appears, add reCAPTCHA v3 or Cloudflare Turnstile.

### 4.5 SEO and performance

- Per-page `<title>`, meta description, OG/Twitter cards via layout slots.
- `<x-json-ld>` component emits Organization JSON-LD on homepage, Article on blog detail, Service on service pages.
- `sitemap.xml` generated by `spatie/laravel-sitemap` artisan command, scheduled weekly.
- `robots.txt` is a static file.
- Public GET responses cached via `spatie/laravel-responsecache`. Cache invalidated on model save through observers. Contact POST and admin routes are excluded.

---

## 5. Admin Panel (Filament v3)

### 5.1 Panel mount

- Mounted at `/admin` via `App\Providers\Filament\AdminPanelProvider`.
- Login at `/admin/login`. No registration. Initial Super-admin user seeded via `AdminUserSeeder` (email/password from `.env`).
- Dark mode is the only mode, matching the frontend aesthetic.

### 5.2 Resources

| Resource              | Form                                                                                                   | Table (cols)                                       | Filters                                  |
|-----------------------|--------------------------------------------------------------------------------------------------------|----------------------------------------------------|------------------------------------------|
| `QuoteResource`       | name, email, phone, company, project_type, budget, timeline, message, status, assigned_to, source_page | status badge, name, project_type, budget, assigned, created_at | status, assigned_to, project_type, date range |
| `PostResource`        | title, slug (auto), cover, excerpt, body (rich editor), categories[], tags[], status, published_at, seo_* | title, author, status, published_at         | status, category, author                 |
| `CategoryResource`    | name, slug                                                                                             | name, post_count                                   | —                                        |
| `TagResource`         | name, slug                                                                                             | name, post_count                                   | —                                        |
| `PortfolioResource`   | title, slug, client, year, service, industry, hero, gallery (repeater), body, status                   | thumb, title, client, year, status                 | service, industry, year, status          |
| `ServiceResource`     | slug, title, hero_copy, body, hero_media, order                                                        | order, title, slug                                 | —                                        |
| `IndustryResource`    | slug, title, summary, body, hero_media, order                                                          | order, title, slug                                 | —                                        |
| `CrewResource`        | slug, name, role, bio, headshot, social_json, order                                                    | order, headshot, name, role                        | —                                        |
| `UserResource`        | name, email, password, roles[]                                                                         | name, email, roles                                 | role                                     |
| `RoleResource`        | name, permissions[] (filament-shield)                                                                  | name, permission_count                             | —                                        |
| `SiteSettingsPage`    | Custom Filament Page (tabbed: Contact, Socials, SEO defaults, Homepage hero)                           | n/a                                                | n/a                                      |

### 5.3 Quote workflow (priority feature)

Quote lifecycle: `new → contacted → qualified → won|lost`.

`QuoteResource` features:
- **List view:** table with status badge column; status filter chips. Optional kanban view as a secondary page.
- **Detail view tabs:** Brief · Notes · Activity log · Reply.
  - Header has contact info, status pills, and an "Assign to me" action.
  - Notes tab: threaded `QuoteNote` entries with an inline "Add note" form.
  - Reply tab: send an email from the admin (sender name = staff user). Logged as an activity entry.
  - Activity tab: `spatie/activitylog` feed (status changes, assignments, notes).
- **Bulk actions:** assign, change status, export CSV.
- **Notifications:** Filament bell + email on new quote arrival or on assignment to the logged-in user.

### 5.4 Navigation groups

```
DASHBOARD
└─ Stats widget (quote pipeline by status, recent posts, last 7d leads)

LEADS
└─ Quotes

CONTENT
├─ Blog Posts
├─ Categories
├─ Tags
└─ Portfolio

SITE
├─ Services
├─ Industries
├─ Crew
└─ Settings

ACCESS
├─ Users
└─ Roles
```

### 5.5 Theme matching the frontend

- Custom theme via `php artisan make:filament-theme`.
- Tailwind config injects the frontend palette: `#0a0a0a` background, off-white text, brand red accent.
- Filament color tokens: `'primary' => Color::hex('#ee2b35')` (exact hex confirmed against `design/assets/core.css` during implementation).
- Fonts: Outfit (UI), Sora (display), Instrument Serif (italic accents on login/empty states) — loaded from Google Fonts in the panel's `<head>`.
- Login page customized with the brand logo and hero italic treatment.

---

## 6. Packages

### 6.1 Runtime

| Package                              | Purpose                              |
|--------------------------------------|--------------------------------------|
| `laravel/framework` ^11              | Core                                 |
| `filament/filament` ^3               | Admin                                |
| `bezhansalleh/filament-shield`       | RBAC UI + permission generator       |
| `spatie/laravel-permission`          | Roles/perms (Shield dependency)      |
| `spatie/laravel-medialibrary`        | Media attached to models             |
| `spatie/laravel-activitylog`         | Audit log on Quote + Post            |
| `spatie/laravel-sitemap`             | sitemap.xml generation               |
| `spatie/laravel-responsecache`       | Public page response cache           |
| `spatie/laravel-sluggable`           | Auto-slug for models with slug field |
| `spatie/laravel-backup`              | DB + media backups                   |
| `laravel/horizon`                    | Queue UI for mail and cache jobs     |
| `predis/predis` (or `phpredis` ext)  | Redis driver for cache + queue       |

### 6.2 Dev

| Package                          | Purpose                |
|----------------------------------|------------------------|
| `pestphp/pest`                   | Test runner            |
| `pestphp/pest-plugin-laravel`    | Laravel test helpers   |
| `larastan/larastan`              | Static analysis (L8)   |
| `laravel/pint`                   | Code style             |
| `barryvdh/laravel-debugbar`      | Local debug            |

---

## 7. Testing Strategy

Pest test layout:

```
tests/
├── Feature/
│   ├── Public/
│   │   ├── HomePageTest.php          (200, key copy renders)
│   │   ├── BlogPageTest.php          (index lists published only, slug 404 on unpublished)
│   │   ├── PortfolioPageTest.php
│   │   └── ContactFormTest.php       (validation, honeypot, rate-limit, Quote created, mails sent)
│   └── Admin/
│       ├── QuoteResourceTest.php     (CRUD, status change, assignment)
│       ├── PolicyOwnershipTest.php   (Sales sees only assigned quotes)
│       ├── ShieldRolesTest.php       (Editor cannot delete Quote; Viewer cannot create Post)
│       └── PostResourceTest.php
├── Unit/
│   ├── QuotePipelineTest.php
│   └── SluggerTest.php
```

Coverage target: a feature test per public route, a feature test per Filament resource × policy path. Unit tests for non-trivial domain logic. CI fails the build if Pest, Pint, or PHPStan fail.

---

## 8. Environments and Deployment

| Env     | DB              | Mail              | Cache/Queue | Storage                       |
|---------|-----------------|-------------------|-------------|-------------------------------|
| local   | MySQL in Sail   | log driver        | sync        | local (`storage/app/public`)  |
| staging | managed MySQL   | Resend sandbox    | redis       | local (S3-ready)              |
| prod    | managed MySQL   | Resend live       | redis       | local v1; swap to S3/R2 later |

**Hosting (assumption to confirm):** Laravel Forge or Ploi on a VPS (DigitalOcean or Hetzner). Nginx + PHP-FPM 8.3 + Redis + MySQL 8.

**CI:** GitHub Actions runs `pest`, `pint --test`, `phpstan` on every PR. Merge to `main` triggers Forge deploy webhook.

**Backups:** `spatie/laravel-backup` nightly cron → S3-compatible bucket (Cloudflare R2).

**Monitoring:** Sentry for errors. Uptime via Better Stack hitting `/up` health endpoint (Laravel 11 ships this by default).

---

## 9. Project Structure

```
app/
├── Filament/
│   ├── Resources/
│   │   ├── QuoteResource.php (+ Pages/, RelationManagers/)
│   │   ├── PostResource.php
│   │   └── ...
│   ├── Pages/SiteSettingsPage.php
│   └── Widgets/QuotePipelineWidget.php
├── Http/Controllers/Public/
│   ├── HomeController.php
│   ├── BlogController.php
│   └── ...
├── Mail/
│   ├── NewQuoteAdminNotification.php
│   └── QuoteAutoReply.php
├── Models/
│   ├── Quote.php Post.php Portfolio.php Service.php Industry.php Crew.php User.php
│   └── ...
├── Policies/
│   ├── QuotePolicy.php PostPolicy.php PortfolioPolicy.php
│   └── ...
├── Providers/Filament/AdminPanelProvider.php
├── Settings/                     (key/value getters wrapping SiteSetting)
└── View/Components/              (Blade x-components)

database/
├── migrations/                   (per model + spatie packages)
├── seeders/
│   ├── RolesSeeder.php
│   ├── AdminUserSeeder.php
│   ├── ServicesSeeder.php       (7 services from design)
│   └── SiteSettingsSeeder.php
└── factories/

resources/
├── css/{core.css, pages.css, admin-theme.css}
├── js/{core.js, chrome.js}
└── views/                        (see §4.2)

routes/
└── web.php                       (public routes; Filament registers its own)

tests/                            (Pest)

design/                           (kept as-is for reference; not shipped)
docs/superpowers/specs/           (this spec lives here)
```

---

## 10. Out of Scope (v1)

- Public user accounts / client portal. Login/signup/forgot-password design files are not wired.
- Online payments or invoicing.
- Booking/calendar system beyond a free-text "Timeline" field.
- Multi-language / i18n.
- E-commerce or print sales.
- Native mobile apps.
- Real-time chat. WhatsApp deep-link already in the design footer is enough.
- Full ABAC rule engine. Ownership-aware policies cover the use cases.
- S3/R2 from day one. Local disk now; filesystem swap deferred until traffic warrants.

---

## 11. Open Items to Confirm During Implementation

- Exact brand red hex from `design/assets/core.css` (used in `--brand` or `.btn--red`) → injected into Filament `primary` color.
- Final services slug list and ordering for `ServicesSeeder` (current best guess: `videography, photography, weddings, post-production, social-content, creative-direction, talent`).
- Hosting target (Forge vs Ploi vs raw VPS) and DB host.
- Mail provider choice (Resend vs SMTP) and from-address.
- Whether industries get their own detail pages or remain list-only.
