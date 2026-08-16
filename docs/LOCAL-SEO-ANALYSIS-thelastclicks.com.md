# Local SEO Analysis — thelastclicks.com

**Analysed:** 2026-08-14 · live site (`https://thelastclicks.com`) + repo source
**Method:** live HTML fetch of `/`, `/contact`, `/about`, `/services/photography`; JSON-LD extraction; robots/sitemap; GBP share-link resolution; web search for citations and brand mentions.

---

## Local SEO Score: 46/100

| # | Dimension | Weight | Score | Rating |
|---|-----------|--------|-------|--------|
| 1 | GBP Signals | 25% | 14.0 | Partial |
| 2 | Reviews & Reputation | 20% | 5.0 | **Low** |
| 3 | Local On-Page SEO | 20% | 12.0 | Partial |
| 4 | NAP Consistency & Citations | 15% | 6.0 | **Low** |
| 5 | Local Schema Markup | 10% | 6.5 | Partial-Good |
| 6 | Local Link & Authority | 10% | 2.5 | **Low** |
| | **Total** | 100% | **46.0** | |

**Shape of the result:** the technical foundation is unusually good for a studio site — valid LocalBusiness JSON-LD, 7-decimal geo, a lazy-loaded map, three dedicated service pages, an AI-crawler-friendly robots.txt. What is missing is everything that happens *off* the website: reviews, citations, and brand mentions. Three of the six dimensions score Low for the same underlying reason — the business has almost no verifiable footprint outside its own domain.

---

## Business Type: **Hybrid**

- Physical address present and visible on `/contact`: `B-7, D-Block, Sector 26, Noida, Uttar Pradesh 201301, IN`
- Google Maps embed with pin on `/contact` (lazy-loaded)
- Service-area language throughout: "across India", "Delhi NCR", `areaServed: {"@type":"Country","name":"India"}`

Full brick-and-mortar checks apply (NAP + map verified), plus SAB service-area checks.

## Industry Vertical: **none of the six standard verticals**

Creative/professional services (photography, videography, post-production). Routes to the generic `LocalBusiness` analysis path — **but** schema.org has an exact subtype for this business, `PhotographStudio`, which is currently unused. See §5.

---

## 1. GBP Signals — 14.0/25

**Confirmed: a Google Business Profile exists.** The `share.google/QlMQkefJfn2iRnma3` link in `sameAs`/`hasMap` resolves (HTTP 200) to a Google entity panel carrying `kgmid=/g/11zc38cjvd`, query `The Last Clicks (TLC)`.

| Signal | Status |
|--------|--------|
| GBP linked from site (`sameAs` + `hasMap`) | ✅ Present |
| Maps embed on contact page | ✅ Present, `loading="lazy"` |
| Maps embed / GBP reference on homepage | ❌ Absent |
| Geo coordinates matching GBP pin | ✅ `28.5808331, 77.3328251` |
| **Business name matches GBP exactly** | ❌ **Mismatch** |
| Business hours visible to a human on the site | ❌ **Absent** |
| Review count / rating surfaced on site | ❌ Absent |
| GBP posts / photos evidence | ❌ Not detectable |

**Two findings worth acting on:**

1. **Name mismatch.** GBP renders as `The Last Clicks (TLC)`. The site's schema `name` is `TheLastClicks`, with `The Last Clicks` and `TLC` demoted to `alternateName`. Entity matching is string-sensitive; pick one canonical form and make GBP, schema, footer, and every citation agree. `app/Support/Brand.php:22` already documents the intent ("byte-identical across Organization, LocalBusiness, WebSite") — the discipline is in the code, the GBP listing just isn't in step with it.
2. **Hours are in schema but nowhere on the page.** `openingHours: "Mo-Sa 10:00-19:00"` is declared in JSON-LD, but a text scan of `/contact` finds no rendered hours at all. "Open at time of search" is Whitespark's #5 local pack factor and it is driven by GBP, but publishing hours on the contact page is a cheap consistency and conversion signal.

**Not assessable from outside:** primary/secondary category selection, GBP post cadence, photo count, Q&A. Those need GBP dashboard access.

---

## 2. Reviews & Reputation — 5.0/20 · **weakest dimension**

| Check | Result |
|-------|--------|
| `aggregateRating` in schema | ❌ Absent |
| Review count visible on site | ❌ Absent |
| Star rating visible on site | ❌ Absent |
| Recency indicators | ❌ Absent |
| Third-party review platform presence | ❌ None found |
| Owner response patterns | Not assessable |

The homepage carries named testimonials (e.g. *"Vikram Singh, Brand Manager, Premium Automobile Portfolio"*, a wedding client in Udaipur) — good social proof for humans, but they are unstructured prose. No `Review` or `aggregateRating` markup, so they are invisible to rich results and hard for AI assistants to attribute.

Benchmarks this business is measured against: the magic threshold is **10 Google reviews** (Sterling Sky); 31% of consumers filter to 4.5★+ and 68% to 4★+ (BrightLocal 2026); 74% only weigh reviews from the last 3 months; and the **18-day rule** — rankings cliff after roughly 3 weeks with no new review.

> **Caution on implementation:** only mark up reviews the business genuinely collected, on the page where they are displayed. And do not pre-screen satisfaction before routing clients to a review link — review gating violates Google's fake-engagement policy and carries FTC exposure at $53,088/violation.

---

## 3. Local On-Page SEO — 12.0/20

**Strong:**
- **Three dedicated service pages** (`/services/photography`, `/videography`, `/editing`) — Whitespark's **#1 local organic factor and #2 AI visibility factor**. This is the single best thing the site already has.
- Service page title nails it: `Brand & Corporate Photography in Delhi NCR | TheLastClicks`
- Homepage meta description carries the geography: *"…agency in Noida, serving brands across Delhi NCR and India…"*
- `tel:+918770155842` click-to-call present on every page
- Lazy-loaded Google Maps embed on `/contact`
- Contact form above the fold on `/contact`

**Weak:**

| Element | Current | Problem |
|---------|---------|---------|
| Homepage `<title>` | `Photography, Videography & Editing Agency \| The Last Clicks (TLC)` | No city. Description has it; the title doesn't. |
| Homepage `<h1>` | `Capturing moments, creating memories.` | Zero local or service intent — pure tagline. |
| Service page `<h1>` | `Narrative stills. Precision capture.` | Same pattern; the good local keywords live only in the title tag. |
| Footer NAP | Name + phone only | **No address sitewide.** Address exists on `/contact` alone. |
| Location pages | None | No `/noida`, no `/delhi-ncr` page. |
| Service page depth | 484 words | Thin for a #1-factor page type. |
| Homepage body text | "Noida" and "Delhi" absent from visible copy | Only "NCR" and "India" appear. |

**Doorway-page risk: none.** There are no templated city pages, so the swap test does not apply. Worth stating explicitly because it constrains the fix: when location pages *are* added, they must clear >60–70% unique content. The cautionary case is the HVAC company that lost 80% of rankings and 63% of traffic to the March 2024 Core Update on swappable city pages.

---

## 4. NAP Consistency & Citations — 6.0/15

**NAP cross-source audit:**

| Source | Name | Address | Phone |
|--------|------|---------|-------|
| Visible HTML (`/contact`) | TheLastClicks | B-7, D-Block, Sector 26, Noida · Uttar Pradesh, India · 201301 | +91 87701 55842 |
| LocalBusiness JSON-LD | TheLastClicks | identical | +91 87701 55842 |
| Visible HTML (footer, sitewide) | TheLastClicks | **absent** | +91 87701 55842 |
| Google Business Profile | **The Last Clicks (TLC)** | not verifiable externally | not verifiable externally |

Page ↔ schema consistency is **clean** — the `Brand` constant approach is doing its job. Two defects:

1. **Name differs from GBP** (see §1).
2. **Email is Cloudflare-obfuscated sitewide.** The footer renders `[email protected]`; the raw string `info@thelastclicks.com` appears only inside JSON-LD. Crawlers and AI assistants reading rendered text cannot extract the email. Schema carries it, so this is a partial mitigation, not a fix — consider disabling Cloudflare Email Obfuscation for the footer address, or accept the tradeoff knowingly.

**Citation presence — effectively zero discoverable.**

Targeted searches for `"TheLastClicks"` / `"The Last Clicks"` + Noida/photography returned **no results for this business**. What came back instead was a differently-named competitor, *The Studio Clicks* (Sector 51, Noida, 4.2★, 555 reviews, listed on Justdial, Facebook, WedMeGood) — which doubles as a competitive benchmark and as a **brand-confusion risk**: the names are close enough that a searcher recalling "clicks" and "Noida" lands on them, not you.

> **Limitation, stated plainly:** the available web search index is US-only. India-market directories (Justdial, Sulekha, IndiaMART, WedMeGood) may well hold listings that this analysis cannot see. Treat "zero citations" as *"none discoverable from here"*, and verify manually before acting on it.

Detected off-site profiles, complete list: **Instagram, YouTube.** No Facebook, LinkedIn, Behance, Google Business Profile URL (only a short link), Justdial, or Bing Places.

**Bing Places matters more than its market share suggests** — it feeds ChatGPT, Copilot, and Alexa. ChatGPT does not read GBP; it sources local answers from the Bing index plus Yelp/TripAdvisor/BBB/Reddit. With 45% of consumers now using AI for local recommendations (up from 6%) and ChatGPT converting at 15.9% vs Google organic's 1.76%, an unclaimed Bing listing is a direct revenue gap.

---

## 5. Local Schema Markup — 6.5/10

Schema is **not** a ranking factor (John Mueller, confirmed) — it earns rich results and machine-readability.

**Present and correct** (`resources/views/contact.blade.php`): `name`, `alternateName`, `url`, `image`, `priceRange` (`₹₹₹`), `telephone`, `email`, full `PostalAddress`, `GeoCoordinates` at 7 decimals (spec asks 5+), `hasMap`, `openingHours`, `sameAs`, plus `BreadcrumbList`. Homepage carries `WebSite` + `Organization` (with `@id`, `contactPoint`, `areaServed`). All blocks parse as valid JSON.

**Gaps, in priority order:**

| Gap | Fix |
|-----|-----|
| Generic `LocalBusiness` | Use **`PhotographStudio`** — schema.org's exact subtype for this business |
| No `@id` on LocalBusiness; no link to the Organization entity | Add `@id` + `parentOrganization` pointing at `https://thelastclicks.com#organization` |
| `openingHours` as a string | `openingHoursSpecification` is the recommended form |
| No `areaServed` on LocalBusiness | Add named cities — Noida, Delhi, Gurgaon, Ghaziabad |
| Organization (homepage) has no `address` | Add the same `PostalAddress` |
| No `aggregateRating` anywhere | Add once real reviews are displayed on-page (§2) |
| LocalBusiness scoped to `/contact` only | Fine as-is, but the homepage Organization should reference it |
| `hasMap` uses a `share.google` redirect | Prefer a stable canonical Maps URL / place ID |

**Ready-to-use replacement** for the schema block in [contact.blade.php:8-36](resources/views/contact.blade.php#L8-L36):

```php
'@type'       => 'PhotographStudio',
'@id'         => url('/#localbusiness'),
'parentOrganization' => ['@id' => url('/') . '#organization'],
'name'        => \App\Support\Brand::NAME,
'alternateName' => \App\Support\Brand::ALTERNATE_NAMES,
'url'         => url('/'),
'image'       => \App\Models\SiteSetting::brandLogoUrl(),
'priceRange'  => '₹₹₹',
'telephone'   => \App\Models\SiteSetting::get('contact_phone', '+91 87701 55842'),
'email'       => \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com'),
'address'     => [
  '@type'           => 'PostalAddress',
  'streetAddress'   => 'B-7, D-Block, Sector 26',
  'addressLocality' => 'Noida',
  'addressRegion'   => 'Uttar Pradesh',
  'postalCode'      => '201301',
  'addressCountry'  => 'IN',
],
'geo' => [
  '@type'     => 'GeoCoordinates',
  'latitude'  => 28.5808331,
  'longitude' => 77.3328251,
],
// Named cities, not just Country — this is what a local query matches against.
'areaServed' => [
  ['@type' => 'City', 'name' => 'Noida'],
  ['@type' => 'City', 'name' => 'Delhi'],
  ['@type' => 'City', 'name' => 'Gurgaon'],
  ['@type' => 'City', 'name' => 'Ghaziabad'],
],
'hasMap' => 'https://share.google/QlMQkefJfn2iRnma3',
// Structured form: parseable per-day, unlike the "Mo-Sa 10:00-19:00" string.
'openingHoursSpecification' => [[
  '@type'     => 'OpeningHoursSpecification',
  'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
  'opens'     => '10:00',
  'closes'    => '19:00',
]],
```

Validate after deploying: Google Rich Results Test + schema.org validator.

---

## 6. Local Link & Authority Signals — 2.5/10

| Signal | Found |
|--------|-------|
| Chamber of Commerce | ❌ |
| Industry association / accreditation badges | ❌ |
| Local news or press mentions | ❌ none discoverable |
| "Best of" list placements | ❌ none discoverable |
| Community involvement (sponsorships, events) | ❌ |
| Brand mentions off-domain | ❌ none discoverable beyond own social |

Two data points that make this the highest-leverage long-term dimension:

- **"Best of" list placement is the #1 AI visibility citation factor** (Whitespark 2026). "Best wedding photographers in Noida" / "top brand film studios Delhi NCR" listicles are exactly the sources AI assistants quote.
- **Brand mentions correlate ~3× more strongly with AI visibility than backlinks** (Ahrefs: 0.664 vs 0.218). An unlinked mention in a credible publication now outperforms a link.

Benchmark cadence for a business this size: 5–10 quality local links/month.

*(BBB and Yelp are US-centric and largely irrelevant for Noida; the India equivalents are Justdial, Sulekha, WedMeGood and IndiaMART.)*

---

## Location Page Quality

**Not applicable — single location, no location pages.** No store locator, no `/locations/` tree, no doorway-page risk today.

If city pages are added later: subdirectory structure (`domain.com/locations/city-name/`), server-rendered crawlable URLs, unique `LocalBusiness` schema per page with distinct `@id`, and >60–70% unique content — local photos, area-specific testimonials, local FAQs. The site is Blade-rendered server-side, so the SSR requirement is already satisfied by architecture.

---

## Top 10 Prioritized Actions

| # | Priority | Action | Effort | Why |
|---|----------|--------|--------|-----|
| 1 | **Critical** | Launch a review generation programme; clear 10 Google reviews, then hold an **18-day** maximum gap between new ones | Ongoing | Weakest dimension (5/20). Reviews ≈20% of local pack weight; rankings cliff at ~3 weeks of silence |
| 2 | **Critical** | Reconcile the business name across GBP, schema, footer and every citation — pick one canonical form | 1h | GBP says `The Last Clicks (TLC)`, site says `TheLastClicks`; entity matching is string-sensitive |
| 3 | **Critical** | Claim **Bing Places** | 1h | Feeds ChatGPT/Copilot/Alexa; 45% of consumers now use AI for local, converting at 15.9% |
| 4 | **High** | Add full NAP + hours to the sitewide footer | 2h | Address currently exists on one page; hours exist only in JSON-LD, never rendered |
| 5 | **High** | Claim Apple Business / Apple Maps *(verify current Apple platform naming against Apple's own docs before relying on any rename claim)* | 1h | Second map ecosystem, entirely absent |
| 6 | **High** | Ship the §5 schema block: `PhotographStudio`, `@id` + `parentOrganization`, `openingHoursSpecification`, named-city `areaServed` | 1h | Exact-subtype and structured hours; rich-result eligibility ≈43% CTR lift (Webstix) |
| 7 | **High** | Claim Justdial + Sulekha + WedMeGood; add Facebook and LinkedIn company pages to `sameAs` | 4h | Only Instagram + YouTube exist today; 3 of top 5 AI visibility factors are citation-related |
| 8 | **Medium** | Put city + service into homepage `<title>` and give H1s local intent (keep the taglines as H2/eyebrow) | 2h | Description carries Noida; title and H1 don't |
| 9 | **Medium** | Mark up the existing homepage testimonials as `Review`, and add `aggregateRating` once real counts are displayed | 3h | Named testimonials already exist as prose — currently invisible to machines |
| 10 | **Medium** | Digital PR aimed at "best photographers/film studios in Noida / Delhi NCR" listicles | Ongoing | #1 AI visibility citation factor; brand mentions beat backlinks ~3:1 for AI |

**Runner-up (deliberately kept off the list):** building out `/noida`, `/delhi-ncr` location pages. Correct eventually, but with one location and near-zero citations it is premature — and done carelessly it is the exact doorway-page pattern that cost the HVAC case study 80% of its rankings. Earn the reviews and citations first.

---

## Limitations

This analysis is built from publicly fetchable page data plus one US-indexed web search pass. It could **not** assess:

- **GBP dashboard internals** — primary/secondary categories (the #1 local pack factor *and* the #1 negative factor), post cadence, photo count, Q&A, Insights, verification status. Needs owner access.
- **Actual review count, rating and velocity** — no review data is exposed on-site, and the GBP panel was not retrievable.
- **India-market citation presence** — the search index available here is US-only. Justdial/Sulekha/IndiaMART/WedMeGood listings may exist unseen. **Verify manually before acting on finding §4.**
- **Geo-grid ranking / Share of Local Voice** — no rank-tracking data. Needs Local Falcon, BrightLocal, or DataForSEO.
- **Domain Authority and the backlink profile** — needs Ahrefs/Moz/Semrush. Run `/seo backlinks`.
- **Real-time local pack position** — needs live SERP data. DataForSEO's `serp_organic_live_advanced` covers it.
- **AI assistant visibility** — whether ChatGPT/Perplexity/AI Overviews currently cite this studio. Run `/seo geo https://thelastclicks.com` for citability scoring and brand-mention auditing.

**Tools that would close these gaps:** DataForSEO (`business_data_business_listings_search` for live GBP + citation audit), BrightLocal or Local Falcon (geo-grid), Ahrefs or Moz (authority), and GBP owner access for everything in dimension 1.
