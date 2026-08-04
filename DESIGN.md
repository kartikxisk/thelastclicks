# Design System: TheLastClicks

Source of truth for generating new screens in Google Stitch. Every value here is
taken from the shipping site (`resources/css/core.css`, `resources/css/pages.css`,
`docs/motion-spec.md`) — not from a generic template. Screens generated against
this document should drop into the Blade front end without a reconciliation pass.

---

## 1. Visual Theme & Atmosphere

A dark-room editorial interface for a photography and videography studio. The
mood is a darkroom at night with one safelight on: near-black surfaces, warm
off-white type, and a single arterial red that appears only where the eye is
meant to land.

Everything is **hard-edged**. There are no rounded corners anywhere on this site
— every radius is `0`. Structure is communicated by 1px hairline rules and
generous negative space, never by pillowy cards. The grid is a 12-column
architect's grid and the layout leans asymmetric: content sits left-aligned
against the container edge, not centered.

Imagery is the product, so imagery is the background. Type sits over
full-bleed photography and video with a soft text shadow rather than a
darkening scrim slab.

**Calibration:** Density 3 (gallery-airy). Variance 7 (offset, asymmetric,
left-aligned). Motion 6 (fluid CSS, no scroll-jacking, no cinematic pinning).

---

## 2. Color Palette & Roles

Single palette, absolutely neutral base, one accent. No warm/cool grey drift.

- **Darkroom Black** (`#0A0A0A`) — Primary page background. The site's floor.
  Never `#000000`.
- **Raised Black** (`#111111`) — Second surface: nav bar when scrolled, modal
  panels, footer.
- **Panel Black** (`#161616`) — Third surface: inset panels, form fields,
  skeleton base.
- **Hover Black** (`#1C1C1C`) — Fourth surface: row hover states, active list items.
- **Hairline** (`#262626`) — Structural 1px borders, dividers, section rules.
  This is the primary way structure is drawn.
- **Hairline Dim** (`#1F1F1F`) — Softer 1px separators inside dense lists.
- **Warm Paper** (`#F4F3EF`) — Primary text. Warm off-white, never pure `#FFFFFF`
  for body copy.
- **Paper Dim** (`#C7C5BE`) — Outlined-type stroke color, de-emphasized headings.
- **Muted Grey** (`#8A8784`) — Secondary text, metadata, eyebrow labels.
- **Muted Deep** (`#7D7A76`) — Tertiary text. Floor for readable grey: 4.64:1 on
  Darkroom Black. Anything dimmer fails WCAG AA — do not invent a fourth grey.
- **Signal Red** (`#E80F03`) — The one accent. Primary CTA fill, active states,
  focus rings, italic emphasis inside headlines, list bullets, page-transition
  curtain, text selection.
- **Red Wash** (`rgba(232, 15, 3, 0.18)`) — Spotlight gradients and low-opacity
  red fields.

**Accent discipline:** exactly one accent, and it is used sparingly — a single
red word in a headline, one filled button per view, a 12px red square as a
list marker. A screen with red in five places is wrong.

**Shadows** are tinted, never pure black:
- Card shadow: `0 18px 40px -12px rgba(5,5,5,0.5), 0 0 0 1px rgba(255,255,255,0.04)`
- Red CTA shadow: `0 12px 32px -8px rgba(232,15,3,0.32), 0 0 0 1px rgba(232,15,3,0.18)`

---

## 3. Typography Rules

**One typeface, site-wide: `Outfit`** (Google Fonts, weights 300–900).
Fallback stack: `'Outfit', 'Helvetica Neue', Arial, sans-serif`.

There is no second sans, no serif, and no separate mono family. Where the code
refers to a "mono" role it means Outfit at small size with wide tracking — that
is the site's label voice, not a monospaced font.

- **Display / H1** — Outfit 600, `clamp(2.25rem, 5.2vw, 5.5rem)` (36–88px),
  letter-spacing `-0.05em`, line-height `0.92`, `text-wrap: balance`.
  Track-tight and dense. Hierarchy comes from weight and color, not from
  making it larger.
- **Section title / H2** — Outfit 600, `clamp(1.875rem, 3.2vw, 2.75rem)`
  (30–44px), letter-spacing `-0.03em`, line-height `1`.
- **Body** — Outfit 400, `1rem` (16px), line-height `1.6`, max 65 characters per
  line, colored Muted Grey when secondary.
- **Eyebrow / label / metadata** — Outfit 400, `0.625–0.6875rem` (10–11px),
  letter-spacing `0.18–0.22em`, `text-transform: uppercase`, Muted Grey.
  Every section is introduced by one of these, never by a smaller heading.
- **Stats and counters** — Outfit 600 with `font-variant-numeric: tabular-nums`
  and `font-feature-settings: "tnum"`. Numbers must not jitter while counting.

**Two stylistic variants, and only two:**
1. **Italic red emphasis** — one or two words inside a headline set in Outfit
   400 italic, colored Signal Red. This is the headline's only accent.
2. **Outlined type** — `-webkit-text-stroke: 1.5px #C7C5BE` with
   `color: transparent`. Used for a secondary word in a display headline or a
   repeated word in the marquee. Never for body copy.

**Banned:** `Inter`. Any serif (`Times New Roman`, `Georgia`, `Garamond`,
`Playfair`) — this site's italic accent is Outfit italic, not a serif swap.
Any second typeface at all. Gradient-filled headline text.

---

## 4. Component Stylings

**Every component has `border-radius: 0`. No exceptions, including avatars,
inputs, modals, chips, and image frames.**

* **Primary button (`.btn--red`)** — Signal Red fill, white text, 1px Signal
  Red border, `16px 28px` padding, `0.875rem` (14px) text, letter-spacing
  `0.02em`, square corners. Carries the tinted red shadow. On hover a white
  panel wipes up from the bottom (`translateY(101%) → 0`, 500ms) and the label
  flips to Darkroom Black.

* **Secondary button (`.btn`)** — transparent fill, 1px Hairline border, Warm
  Paper text, same padding and square corners. On hover a Warm Paper panel
  wipes up from the bottom and the label inverts to Darkroom Black. Both button
  variants carry a `→` glyph that slides out to the top-right while a duplicate
  slides in from the bottom-left on hover.

* **Press feedback** — every interactive element gets a physical push on
  `:active`: `transform: translateY(1px) scale(0.985)`. No exceptions.

* **Cards** — used sparingly, and when used they are square-cornered panels on
  Panel Black with a 1px Hairline border. Prefer a `border-top` hairline and
  negative space over a card wherever the card is not communicating elevation.
  Optional cursor-tracking spotlight: a `600px` radial of Red Wash following
  the pointer, fading in over 500ms.

* **Work tiles / imagery** — full-bleed, square corners, `object-fit: cover`.
  Hover scales the *image inside* the frame, never the frame itself — the tile's
  bounding box must not change size on hover.

* **Inputs** — label above in the uppercase eyebrow style, field on Panel Black
  with a 1px Hairline border and square corners, helper text optional, error
  text below in Signal Red. No floating labels, no placeholder-as-label.

* **Focus ring** — `2px solid #E80F03` with `outline-offset: 3px` (`4px` on
  links and buttons), square. Always visible, never removed.

* **Loading** — skeleton blocks matching the real layout's dimensions, shimmering
  on a `#161616 → #111111 → #161616` gradient sweeping over 1.6s. No circular
  spinners.

* **Empty states** — a composed block explaining how to populate the view, set
  in the eyebrow + body pairing. Never a bare "No data".

* **Marquee** — full-width band with a 1px Hairline rule top and bottom, `28px`
  vertical padding, words at `clamp(1.5rem, 3vw, 2.75rem)` separated by 12px
  solid red squares, alternating filled and outlined words, scrolling
  continuously.

---

## 5. Layout Principles

- **Container:** max-width `1560px`, centered, horizontal padding
  `clamp(20px, 4vw, 56px)`.
- **Vertical rhythm:** one section spacing value site-wide —
  `clamp(64px, 9vh, 120px)`. Do not invent per-section spacing.
- **Grid:** CSS Grid, 12 columns with a `24px` gutter, collapsing to 6 columns
  with a `16px` gutter below `760px`. Never `calc()` percentage math, never
  flexbox for page-level structure.
- **Hero:** full-bleed media background with content pinned left, headline over
  the image, and a metadata row along the bottom separated by a 1px Hairline
  rule. Height is `min-height: 100svh` with a `100vh` fallback declared first —
  never `height: 100vh`, which jumps under the iOS Safari URL bar.
- **Alignment:** left-aligned and asymmetric by default. Centered hero layouts
  are banned. Content sits against the container's left edge with the imagery
  or metadata carrying the right side.
- **Feature rows:** the generic three-equal-cards row is banned. Use a
  two-column zig-zag, a numbered list with hairline dividers, or an asymmetric
  8/4 split.
- **No overlapping elements.** Text never sits on top of other text. Type over
  imagery is the one intentional layering, and it is handled with
  `text-shadow: 0 2px 24px rgba(20,14,12,0.35)` rather than by stacking
  absolutely-positioned blocks.
- **Structure is drawn with hairlines**, not with boxes. A 1px `#262626` rule
  above a group does the work a card would do elsewhere.

---

## 6. Responsive Rules

- **Below 768px** every multi-column layout collapses to a single column.
- **No horizontal overflow, ever.** `body` carries `overflow-x: hidden` as a
  backstop, but a screen that needs it has a layout bug.
- **Typography** scales via `clamp()` only. Body text never below `1rem` (16px);
  eyebrow labels never below `0.625rem` (10px) and only in uppercase with wide
  tracking.
- **Touch targets** minimum `44px`. The custom cursor is disabled entirely under
  `@media (hover: none) and (pointer: coarse)` — coarse pointers get the native
  cursor.
- **Section spacing** reduces automatically through the `clamp()` on
  `--section-y`; do not add mobile-specific overrides.
- **Navigation** — desktop horizontal nav collapses to a full-screen overlay
  menu with list items that slide `8px` right and turn Signal Red on hover.

---

## 7. Motion & Interaction

Motion is CSS-transition based, not a spring-physics runtime. `docs/motion-spec.md`
is the binding contract; these are its rules restated for generation.

**Easing tokens — never inline a curve:**

| Token | Value | Use |
|---|---|---|
| `--ease` | `cubic-bezier(0.16, 1, 0.3, 1)` | Default. Fast departure, long settle. |
| `--ease-2` | `cubic-bezier(0.65, 0, 0.35, 1)` | Symmetric — things that leave and return. |
| `--ease-3` | `cubic-bezier(0.85, 0, 0.15, 1)` | Sharp both ends — wipes and curtains. |
| `--ease-soft` | `cubic-bezier(0.32, 0.72, 0, 1)` | Long crossfades, shadow transitions. |
| `--ease-spring` | `cubic-bezier(0.34, 1.56, 0.64, 1)` | Overshoot, used sparingly. |

**Duration vocabulary:** `180ms` hovers and focus rings, `420ms` entrances and
disclosures, `800ms` hero crossfades and image scale.

**Rules:**
- **Transform and opacity only.** Never animate `width`, `height`, `top`,
  `left`, `padding` or `margin` — a hover that reflows the grid recalculates
  every sibling.
- **Entrances ease out, never ease-in-out.** An entrance that starts slowly
  reads as hesitant.
- **Nothing scales from zero.** Entrances start at `scale(0.96)` with an 8px
  rise.
- **Collapsibles animate `grid-template-rows: 0fr → 1fr`**, never `max-height`.
- **`will-change` is set on start and cleared on finish.** Held permanently it
  exhausts GPU memory on a long page.
- **Reduced motion is gentler, not zero.** Durations collapse to ~150ms.
  Only momentum scroll and the route wipe opt out entirely.
- **Nothing polls.** Scroll state comes from a passive listener coalesced into
  a frame; rAF loops run only while something is actually animating.
- **Scroll is never locked.** No pinning, no scroll-jacking, no swallowed
  keypresses. Scroll-linked effects read progress from an element's own rect.
- **Page transitions** are a six-panel Signal Red curtain wiping up then out,
  600–700ms end to end, `pointer-events: none` throughout. It must never
  outlast the navigation.
- **Staggered reveals** cascade at `50ms` per item. Lists never mount all at
  once.

---

## 8. Anti-Patterns (Banned)

- **Rounded corners.** Every radius on this site is `0`.
- **Emojis.** Anywhere — copy, buttons, empty states, icons.
- **`Inter`**, or any second typeface. One family: Outfit.
- **Serif fonts** of any kind, including as an "editorial accent". The accent is
  Outfit italic in Signal Red.
- **Pure black `#000000`** as a surface, and pure white `#FFFFFF` as body text.
- **A second accent color.** One red, used sparingly.
- **Purple / blue neon glows, gradient buttons, glassmorphism.**
- **Gradient-filled headline text.**
- **Centered hero layouts.**
- **Three equal cards in a row.**
- **Animating layout properties.** Any `transition` touching `width`, `height`,
  `padding`, `margin`, `top`, or `left`.
- **Scroll-jacking, pinned sections, hijacked keyboard scrolling.**
- **Circular loading spinners.** Skeletons only.
- **Generic placeholder names** ("John Doe", "Acme", "Nexus", "Lorem Studio").
- **Fake round metrics** (`99.99%`, `500+ happy clients`, `10x`).
- **AI copywriting clichés**: "Elevate", "Seamless", "Unleash", "Next-Gen",
  "Transform your", "Take it to the next level".
- **Broken image links.** Use `picsum.photos` or inline SVG for placeholders,
  never Unsplash source URLs.
- **`height: 100vh`** for full-height sections — `min-height: 100svh` with a
  `100vh` fallback declared first.
- **Floating labels** and placeholder-as-label in forms.
- **Removed focus outlines.**

---

## 9. Deliberate Deviations From The Generic Taste Baseline

These three are house rules that a generic premium-UI checklist would flag.
They are intentional and should be preserved in generated screens.

1. **A custom cursor exists.** A camera glyph — white body, black outline, red
   shutter dot — replaces the pointer on desktop. It is the studio's signature
   and is disabled entirely on coarse pointers. Generic advice bans custom
   cursors; this one stays.
2. **The accent is fully saturated.** Signal Red `#E80F03` sits near 98%
   saturation, above the usual sub-80% guidance. It is the brand mark's red and
   is controlled by frequency — appearing rarely — rather than by desaturation.
3. **The red CTA carries a tinted shadow.** `rgba(232,15,3,0.32)` under the
   primary button reads close to a colored glow. It is diffused and offset
   downward (a shadow, not a halo) and is the only place on the site where a
   colored shadow appears. Do not extend it to other elements, and never render
   it as a symmetrical outer glow.

A fourth is a known defect rather than a house rule: `.svc` currently transitions
`padding`, which contradicts the layout-property rule in section 7. Do not
reproduce it in new screens.
