# Content the Next port dropped

The port followed the API. Anything the Blade templates carried themselves —
hardcoded copy, bespoke components — was silently missed, because there was no
data to notice was absent.

This is the audit. It is not complete design work; it is the list of content
that exists on the live site and does not exist on the new one.

## Fixed

- **Home → Discipline (`02 Discipline`).** Positioning copy plus three
  counters (5+ years, 20+ cities, 1000+ events). Ported.

## Outstanding

| Page | Gap |
|---|---|
| **About** | 13 hardcoded text blocks in `pages/about.blade.php`; the Next page carries one paragraph and two stats. Also uses `<x-india-outline>`, an illustrated map component never ported. |
| **Contact** | 7 hardcoded text blocks — process copy, response-time promise, supporting detail. The Next page has one line above the form. |
| **Home** | `<x-work-globe>` — the rotating 3D work globe. Never ported; the Next homepage uses a flat collage instead. |
| **Industries / Blog** | 2 hardcoded blocks each — intro copy above the listings. |
| **Work / Industry detail** | `<x-media-grid>` — the gallery layout. Partially replaced by `WorkGallery`, not matched. |
| **Blog** | `<x-card-post>` — the post card. Reimplemented rather than matched; worth diffing. |

## How to avoid repeating it

Diff against the rendered Blade page, not against the API payload. For each
route, load the Blade version and the Next version side by side and compare
what a reader sees — the API tells you what is *dynamic*, never what is *there*.

`docs/deploy/` has the SEO parity crawl, which compares titles and headings.
Extending it to compare visible text content would catch this class of gap
automatically.
