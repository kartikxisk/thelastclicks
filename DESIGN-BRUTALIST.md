# Design System: TheLastClicks — Tactical Telemetry Variant

An alternate design language for Google Stitch generation. This is **not** the
shipping site's system — that is `DESIGN.md`, and the two must never be mixed
inside one screen. Generate against one file or the other.

This variant reads the studio as a signals-intelligence outfit rather than an
editorial one: the work is capture, the deliverables are assets with IDs, and
the interface is the terminal you review them through.

**Archetype: Tactical Telemetry & CRT Terminal.** Dark substrate, monospace
dominance, high tabular density, simulated hardware limitation. The Swiss
Industrial Print archetype (light newsprint substrate, macro-serif, halftone
posters) is **deliberately not used here.** Do not introduce a light substrate,
and do not alternate between the two modes across screens.

---

## 1. Visual Theme & Atmosphere

A deactivated CRT that has just been powered on in a dark room. Off-black
phosphor substrate, bone-white text, one hazard red. Everything is framed,
bracketed, numbered, and stamped — as if the page were a record retrieved from
a system rather than a document composed for a reader.

Structure is drawn by **1px dividing lines produced by grid gaps**, not by
borders on elements and not by cards. Zones of information are visibly
compartmentalized. Every corner is exactly 90°.

Density is bimodal and the oscillation is the whole effect: tightly packed
monospace metadata clusters — coordinates, revisions, unit IDs, timestamps —
sitting adjacent to vast calculated emptiness framing one enormous headline.
There is no medium density. A screen that is uniformly comfortable has failed.

**Calibration:** Density 8 in the data zones, 2 in the display zones.
Variance 8 (asymmetric, viewport-bleeding). Motion 4 (restrained, mechanical,
no easing that reads as organic).

---

## 2. Color Palette & Roles

Dark substrate only. No gradients as decoration, no soft shadows, no
translucency, no glassmorphism. The only gradients permitted anywhere are the
functional `repeating-linear-gradient` scanline and the SVG halftone pattern in
section 7.

- **Deactivated CRT** (`#0A0A0A`) — Primary substrate. Never pure `#000000`.
- **Panel** (`#121212`) — Second surface: data tables, framed modules, the fill
  behind a compartment.
- **Deep Panel** (`#161616`) — Inset wells, input fields, disabled compartments.
- **Grid Line** (`#262626`) — The 1px rule. This is the primary structural
  device and it is everywhere.
- **White Phosphor** (`#EAEAEA`) — Primary text. All macro-typography, all
  active data values.
- **Phosphor Dim** (`#8A8784`) — Field labels, units, inactive metadata.
- **Phosphor Floor** (`#7D7A76`) — Dimmest permitted text. 4.64:1 on the
  substrate. Anything below this fails WCAG AA — do not invent a fourth grey.
  This floor is inherited from the shipping site, where a dimmer value was
  already caught and corrected.
- **Hazard Red** (`#E80F03`) — The single accent. The studio's mark, and within
  a hair of the archetype's specified `#E61919` — use the brand value.
  Reserved for: alert states, the active row in a table, strike-throughs,
  thick structural warning stripes, and vital data highlights.
- **Signal Green** (`#4AF626`) — Assigned to **exactly one element**: the
  live-capture status indicator (`● REC` / `○ IDLE`). It appears nowhere else
  in the system. It is not a success color, not a link color, and never body
  text. If a screen has no capture-state indicator, this color does not appear
  on that screen at all.

Two accents is the ceiling and the second one has exactly one job. A screen
with green scattered through it is wrong.

---

## 3. Typographic Architecture

Three registers with violent scale contrast between them. This is the load-
bearing decision of the whole system.

### 3.1 Macro — Structural Headers

- **Font:** `Archivo Black` (Google Fonts, single weight 400 which renders as
  black). Fallback: `'Archivo Black', 'Helvetica Neue', Arial Black, sans-serif`.
- **Zero-new-font alternative:** `Outfit` at weight 900, already loaded by the
  shipping site. Geometric rather than neo-grotesque, so it reads warmer and
  less mechanical — acceptable if adding a family is not worth it, but Archivo
  Black is the correct call for this archetype.
- **Scale:** `clamp(4rem, 10vw, 15rem)` (64–240px). Genuinely enormous, and
  permitted to bleed past the viewport edge.
- **Tracking:** `-0.04em` to `-0.06em`. Glyphs must collide into a solid
  architectural block.
- **Leading:** `0.85` to `0.9`.
- **Casing:** Uppercase, always.

### 3.2 Micro — Data & Telemetry

- **Font:** `JetBrains Mono` (Google Fonts, weights 400 and 700).
  Fallback: `'JetBrains Mono', 'IBM Plex Mono', ui-monospace, monospace`.
- **Scale:** Fixed, `0.625rem` to `0.875rem` (10–14px). Does not scale
  fluidly — terminal text is a fixed matrix.
- **Tracking:** `0.05em` to `0.1em`.
- **Leading:** `1.2` to `1.4`.
- **Casing:** Uppercase, always.
- **Numerals:** `font-variant-numeric: tabular-nums` with
  `font-feature-settings: "tnum"`. Non-negotiable — columns must align and
  counters must not jitter.
- **Applies to:** every label, every nav item, all metadata, unit IDs,
  coordinates, timestamps, revisions, table content, button text, form labels,
  breadcrumbs. If it is not a macro headline, it is monospace.

### 3.3 Textural Disruption — Degraded Serif

- **Font:** `EB Garamond` or `Playfair Display`.
- **Usage:** Exceedingly sparingly — at most one instance per screen, and most
  screens have none. A single pull-quote or one archival caption.
- **Mandatory treatment:** never rendered clean. Must carry the halftone or
  1-bit dither filter from section 7. Undegraded serif is banned; the point is
  the textural collision between a degraded analog letterform and the crisp
  vector grid around it.

**Note on divergence:** `DESIGN.md` bans serif outright and mandates one
typeface site-wide. This variant deliberately breaks both. That is the largest
single difference between the two systems.

---

## 4. Layout & Spatial Engineering

- **Determinist grid lines.** Build dividing lines with
  `display: grid; gap: 1px; background: #262626;` and give every child
  `background: #0A0A0A`. The gap *becomes* the hairline. Do not draw these with
  per-element borders — that produces doubled 2px lines at every shared edge
  and the grid stops being mathematically clean.
- **Container:** max-width `1560px`, horizontal padding
  `clamp(20px, 4vw, 56px)`. Inherited from the shipping site so the two systems
  align at the container level.
- **Full-bleed rules.** Horizontal rules span the entire container width to
  segregate operational units. They are structural, not decorative.
- **Anchoring.** Elements are anchored to grid tracks and intersections.
  Nothing floats, nothing is centered by convenience, nothing overlaps.
- **Geometry:** `border-radius: 0` everywhere, without exception — inputs,
  avatars, images, modals, chips, indicators. Already true of the shipping site.
- **Full-height sections:** `min-height: 100svh` with a `100vh` fallback
  declared first. Never `height: 100vh`.
- **Responsive:** multi-column collapses to single column below `768px`. The
  monospace register does **not** shrink below 10px on mobile — instead, drop
  columns from data tables and keep the type size fixed. Macro type keeps
  scaling via its `clamp()`. No horizontal overflow, ever. Touch targets 44px
  minimum.

---

## 5. Components & Symbology

Standard consumer UI conventions are replaced by utilitarian industrial
graphics.

* **Section headers** — framed in ASCII brackets, monospace uppercase:
  `[ DELIVERY SYSTEMS ]`, `[ 004 / CAPTURE ]`. The bracket is part of the
  content, not a pseudo-element hack, so it survives copy-paste.

* **Directional runs** — `>>>`, `///`, `\\\` used as inline separators and to
  indicate flow between compartments.

* **Industrial marks** — `®`, `©`, `™` deployed as structural geometric
  elements at compartment corners and beside wordmarks, at display size rather
  than legal-fine-print size. They are shapes first, notation second.

* **Crosshairs** — a `+` glyph in Grid Line color at grid intersections,
  `0.75rem`, marking where tracks cross. Decorative registration, applied to
  the outer frame of major modules.

* **Stamp strings** — every major module carries a monospace identifier in its
  corner: `REV 2.6`, `UNIT / D-01`, `SEQ 0042`, `LAT 19.0760 / LON 72.8777`.
  These must be **real values derived from the content** (a work's ID, its
  shoot date, its location) — never randomized decoration, and never fake
  precision.

* **Buttons** — square, 1px Grid Line border, monospace uppercase at `0.75rem`
  with `0.1em` tracking, `16px 28px` padding. Primary variant fills Hazard Red
  with `#EAEAEA` text. Hover inverts fill and foreground instantly at 120ms —
  a hard swap, not a wipe or a fade. Active state pushes
  `translateY(1px)`.

* **Data tables** — the primary content component. Monospace throughout, 1px
  grid gaps, uppercase column headers in Phosphor Dim, values in White
  Phosphor, active row marked by a 2px Hazard Red left edge.

* **Warning stripes** — a `repeating-linear-gradient(45deg, #E80F03 0 12px,
  #0A0A0A 12px 24px)` band, `8px` tall, used to mark a destructive zone or a
  section boundary. At most once per screen.

* **Barcodes** — a run of `repeating-linear-gradient` vertical rules of varied
  width, used as a corner texture on a module. Purely graphic.

* **Inputs** — label above in monospace uppercase Phosphor Dim, field on Deep
  Panel with a 1px Grid Line border, square. Focus ring `2px solid #E80F03`,
  `outline-offset: 3px`. Error text below in Hazard Red, prefixed `>> `. No
  floating labels, no placeholder-as-label.

* **Loading** — a monospace character-cycle or a block-fill bar
  (`░▒▓█`), never a circular spinner and never a soft shimmer.

* **Empty states** — a framed compartment reading `[ NO RECORDS ]` plus one
  monospace line explaining how to populate it.

* **Semantic markup** — use `<data>`, `<samp>`, `<kbd>`, `<output>`, `<dl>` and
  `<time>` to carry telemetry rather than wrapping everything in `<div>`. The
  technical register should exist in the DOM, not just in the CSS.

---

## 6. Texture & Post-Processing

Three layers of simulated analog degradation. All three are **static** — none
of them animate. A drifting scanline or a shimmering grain is a vestibular
hazard and reads as a broken screen rather than an old one.

* **CRT scanlines** — a fixed, `pointer-events: none` overlay on the root:
  ```css
  repeating-linear-gradient(
    0deg,
    transparent 0 2px,
    rgba(0, 0, 0, 0.10) 2px 4px
  )
  ```
  Applied to a fixed pseudo-element, never to a scrolling container.

* **Mechanical noise** — a global low-opacity SVG `feTurbulence` grain on a
  fixed pseudo-element at the DOM root, `opacity: 0.035`, `pointer-events:
  none`. One grain layer for the whole document. Never per-component — each
  instance is a full-viewport composited layer and a handful will visibly cost
  frames.

* **Halftone / 1-bit dither** — for photography and for the degraded serif.
  An SVG radial dot pattern combined with `mix-blend-mode: multiply`, or
  pre-processed assets. Applied selectively, not to every image: the studio's
  actual portfolio work should mostly render clean, with the dithered treatment
  reserved for archival or supporting imagery. Dithering the entire portfolio
  would destroy the product being sold.

**Reduced motion:** the three texture layers are static, so they persist under
`prefers-reduced-motion`. Only the transitions in section 7 respond to it.

---

## 7. Motion

Motion is mechanical and short. `docs/motion-spec.md` remains binding — this
variant changes the aesthetic, not the engineering rules.

- **Transform and opacity only.** Never `width`, `height`, `padding`, `margin`,
  `top`, or `left`.
- **Durations:** `120ms` state swaps, `200ms` entrances. Substantially faster
  than the editorial system's 420/800ms — machinery actuates, it does not glide.
- **Easing:** `cubic-bezier(0.85, 0, 0.15, 1)` (`--ease-3`, sharp at both ends)
  as the default. The organic long-settle curve is wrong for this archetype.
  Never `linear`, never a spring with overshoot.
- **Entrances:** hard cut or a `4px` translate. Nothing scales from zero,
  nothing fades in over half a second.
- **Reveals:** a compartment appears at full opacity with a 1-frame clip, or
  its content types in character-by-character in monospace. No cascade of
  gentle fades.
- **Collapsibles:** `grid-template-rows: 0fr → 1fr`, never `max-height`.
- **`will-change`** set on start, cleared on finish.
- **Reduced motion:** durations collapse to ~100ms, not zero.
- **Nothing polls**, nothing pins, scroll is never locked.

---

## 8. Anti-Patterns (Banned)

- **Mixing archetypes.** No light substrate, no Swiss Industrial Print elements
  in this system.
- **`border-radius`** of any value.
- **Decorative gradients**, soft drop shadows, blur, translucency,
  glassmorphism. The scanline, warning stripe, barcode, and halftone are the
  only permitted gradients and all four are functional.
- **Emojis.** Anywhere. ASCII and industrial marks only.
- **Sentence case or title case** in the micro register — monospace is always
  uppercase.
- **Proportional numerals** in any table, counter, or stat.
- **Undegraded serif.**
- **Terminal green as a general text or success color.** One element, one job.
- **A third accent.**
- **Circular spinners**, soft shimmer loaders.
- **Animated scanlines or grain.**
- **Per-component grain layers.**
- **Randomized fake telemetry.** Stamp strings must map to real data. Invented
  coordinates, fake revision numbers, and decorative unit IDs are the tell that
  separates this from a real instrument.
- **Fake round metrics** (`99.99%`, `500+ clients`, `10x`).
- **AI copywriting clichés**: "Elevate", "Seamless", "Unleash", "Next-Gen".
- **Broken image links** — `picsum.photos` or inline SVG for placeholders.
- **`height: 100vh`.**
- **Removed focus outlines.**
- **Centered hero layouts.**
- **Three equal cards in a row.**

---

## 9. Divergences From `DESIGN.md`

Both systems share the substrate, the red, the zero radius, the 1560px
container, the grey floor, and the motion engineering rules. They differ on:

| | `DESIGN.md` (shipping) | This variant |
|---|---|---|
| Typefaces | One — Outfit, all roles | Three — Archivo Black / JetBrains Mono / degraded serif |
| Serif | Banned outright | Required, sparingly, dithered only |
| Micro register | Outfit at small size, wide tracking | True monospace, uppercase, fixed scale |
| Structure drawn by | Hairline `border-top` rules | Grid `gap: 1px` compartments |
| Accent count | 1 | 2 (green restricted to one element) |
| Texture | None | Scanlines + grain + selective halftone |
| Symbology | None | ASCII framing, crosshairs, ®/©/™, stamps |
| Hover | 500ms panel wipe | 120ms hard inversion |
| Entrance timing | 420ms, long settle | 200ms, sharp both ends |
| Density | Uniform, airy | Bimodal — extreme clusters vs. voids |

**What adopting this variant would cost the live site:** two new Google Fonts
families, reversing the documented one-typeface decision at
[`core.css:23-27`](resources/css/core.css#L23-L27), and adding two full-viewport
fixed composited layers. None of that is done — this file is a specification
only, and no site CSS has been changed.
