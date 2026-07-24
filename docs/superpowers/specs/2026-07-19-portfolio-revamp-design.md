# Portfolio Revamp — Cinematic + Trust Design

**Date:** 2026-07-19
**Status:** Approved

## Goal

Replace the static portfolio with a cinematic, video-first experience that builds trust and drives inquiries. Fix the meaningless "Selected work" hero. Support multiple services per portfolio (videography, photography, editing) selected in admin.

## Decisions (from brainstorming)

- Goals: trust signals + drive inquiries + richer case stories + more alive browsing — all four.
- No trust content exists yet (no logos, no extra quotes, no outcome numbers). **Hard rule: never fabricate trust content.** Trust sections render only when real content exists; derivable facts (client names, counts) come from live DB.
- Hero: outcome-led copy (customer-result promise), not "Selected work".
- Approach B chosen: cinematic immersive index, with the trust layer on case pages.
- New requirement: portfolios can belong to multiple services (admin multi-select).

## Architecture

### 1. Data model

- **`portfolio_service` pivot** (`portfolio_id`, `service_id`, unique pair). `Portfolio::services()` belongsToMany. Migration backfills the pivot from the existing `service_id` column; the column stays (legacy read fallback, no longer written by admin). Front-end reads `services` everywhere; where old data has only `service_id` the migration backfill makes the pivot complete, so no dual-read logic is needed in views.
- **`portfolios.results`** — nullable JSON array of `{label, value}` facts. Filament repeater (two text inputs). Case page renders a Result section only when non-empty.
- **`testimonials.portfolio_id`** — nullable FK. Filament select on TestimonialResource ("Attach to case"). Case page renders the quote when a published testimonial is linked.

### 2. Portfolio index — cinema reel stack

- **Hero:** outcome-led headline: `Films that make <em>people act.</em>` with sub-line `Cinema-grade films and photography for brands that need to be remembered.` Below it a derived proof line: production count + year window from DB (reuses existing `$stats`). Copy editable later; these are the shipped defaults.
- **Client marquee:** horizontally scrolling text strip of distinct `client` values from published portfolios. Pure CSS animation, real names only.
- **Reel stack:** each published portfolio is a full-width cinematic row (aspect ~21:9 desktop, 16:9 mobile): poster image immediately, overlay of title / client / services / year. The row's `<video>` has `preload="none"` and **no `src`**; a small IntersectionObserver script assigns `src` and plays (muted, loop, playsinline) when the row is ≥60% in viewport, pauses and keeps poster otherwise. Only one row plays at a time. Click anywhere → case page.
- **Service chips:** row of chips (All + services that have portfolios). Client-side filter (hide/show rows), no URL state. Data comes from the pivot.
- **Mobile:** no autoplay; poster + tap-to-play (same script, tap handler).
- The old featured/grid sections are replaced by the reel stack.

### 3. Case page — trust layer

Existing hero + gallery + brief/approach stay. Added, in order after gallery:

- **Result section:** renders `results` facts as a stat row (label + value), only when non-empty.
- **Testimonial:** blockquote with client_name + role_company, only when a published testimonial is linked to this portfolio.
- **CTA band:** always renders — "Want a film like this?" + two actions: existing quote trigger (`data-quote-trigger`) and WhatsApp link from `SiteSetting('whatsapp_url')` (hidden if not set).
- "Discipline" meta line shows all linked services (comma-separated), not just one.

### 4. Admin (Filament)

- PortfolioResource: `service_id` select replaced by `services` multi-select (relationship). New `results` repeater (label/value). Existing media fields unchanged.
- TestimonialResource: new nullable `portfolio_id` select (published portfolios by title).

### 5. Performance & fallbacks

- No film downloads until a row activates; posters are the only eager media. Films stream from CloudFront (range requests).
- Row without gallery video media: renders poster (cover media, else legacy `cover_url`) as a static row — never breaks.
- Missing poster and video: row still renders with title overlay on dark background.
- responsecache: pages remain cacheable; the observer already clears on portfolio/testimonial/site-setting saves (TestimonialObserver existence verified in plan; add if missing).

### 6. Seeder

- PortfoliosSeeder attaches its single service to the pivot (same mapping as today) so fresh installs match production shape. No fake results/testimonial links are seeded.

## Testing

Pest feature tests:

1. Migration/backfill: existing `service_id` rows appear in pivot; `Portfolio::services()` returns them.
2. Admin: portfolio saves with multiple services; testimonial links to a portfolio; results repeater persists shape `[{label, value}]`.
3. Index: reel rows render per published portfolio with poster + data-video attributes; chips render only services having portfolios; client marquee lists distinct clients.
4. Case page: Result section absent when `results` empty, present when filled; testimonial absent unless linked + published; CTA band present; Discipline shows multiple services.

## Out of scope

- Preview-clip generation (ffmpeg) — full films with lazy activation instead.
- Logo image uploads, review platform integrations.
- URL-state filters, year filters.
- Homepage/service page changes beyond what the pivot forces (service page "featured" queries updated to pivot).
