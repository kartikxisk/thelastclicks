# 012 — Stop the process glyph appearing from nothing, and stop the hero tile growing after the cursor leaves

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: LOW
- **Category**: Physicality & origin (AUDIT §3), Easing & duration (AUDIT §2)
- **Estimated scope**: 1 file (`resources/css/pages.css`), 2 rules

## Problem

**A. `scale(0)` on the sticky-process glyph.**

```css
/* resources/css/pages.css:2444-2453 — current */
.sproc__glyph .f {
  opacity: 0;
  transform: scale(0);
  transform-origin: center;
  transform-box: fill-box;
  transition: opacity 0.4s var(--ease) 0.85s, transform 0.4s var(--ease) 0.85s;
}
.sproc__panel.is-on .sproc__glyph .f {
  opacity: 1;
  transform: scale(1);
}
```

AUDIT §3: *"Never `scale(0)` — nothing in the real world appears from nothing.
Target: `scale(0.9–0.97)` + `opacity: 0`."* Scaling from literally zero reads as
a magic trick rather than an object arriving. The opacity is already there doing
the concealing work; the scale only needs to supply a small amount of growth.

(The `transform-origin: center` here is correct and stays — this is an SVG fill
inside a glyph, not a trigger-anchored popover.)

**B. The hero tile keeps growing for a second after you leave it.**

```css
/* resources/css/pages.css:63-75 — current */
.hero__bg img,
.hero__bg video {
  width: 100%; height: 100%;
  object-fit: cover;
  filter: brightness(0.92) contrast(1.02) saturate(1.06);
  transform: scale(1.06);
  transition: transform 1.4s var(--ease), filter 1.4s var(--ease);
}
.hero__bg .tile:hover img,
.hero__bg .tile:hover video {
  transform: scale(1.12);
  filter: brightness(1) contrast(1.03) saturate(1.1);
}
```

1400ms is the slowest hover response on the site. Sweeping the cursor across the
hero tile grid leaves a trail of images still growing and brightening a full
second after the pointer has moved on — several tiles animating at once, none of
them tracking anything the user is doing. A slow editorial zoom is the right idea
for a photography agency; 1.4s on a grid you sweep across is not.

AUDIT §2's decision order puts hover on `ease`, and its duration table caps UI
motion at 300ms while allowing marketing motion to run longer. A hero image zoom
sits between the two — but it must at least stop before the user has forgotten
they hovered it.

## Target

**A.**

```css
/* target — resources/css/pages.css:2444-2453 */
.sproc__glyph .f {
  opacity: 0;
  transform: scale(0.9);
  transform-origin: center;
  transform-box: fill-box;
  transition: opacity 0.4s var(--ease-out) 0.85s, transform 0.4s var(--ease-out) 0.85s;
}
.sproc__panel.is-on .sproc__glyph .f {
  opacity: 1;
  transform: scale(1);
}
```

`0.9` is the bottom of AUDIT §3's band, chosen because this is a small SVG detail
where a subtler `0.97` would be invisible.

**B. Asymmetric timing** — AUDIT §4: the deliberate phase can be slower, the
release snaps back. Hovering *in* keeps an editorial pace; leaving recovers
quickly so a swept-across grid settles immediately.

```css
/* target — resources/css/pages.css:63-75 */
.hero__bg img,
.hero__bg video {
  width: 100%; height: 100%;
  object-fit: cover;
  filter: brightness(0.92) contrast(1.02) saturate(1.06);
  transform: scale(1.06);
  /* Leaving: snap back in 0.35s so a swept-across grid settles at once. */
  transition: transform 0.35s var(--ease-out), filter 0.35s var(--ease-out);
}
.hero__bg .tile:hover img,
.hero__bg .tile:hover video {
  /* Entering: keep the slow editorial push. */
  transition: transform 0.8s var(--ease-out), filter 0.8s var(--ease-out);
  transform: scale(1.12);
  filter: brightness(1) contrast(1.03) saturate(1.1);
}
@media (hover: none) and (pointer: coarse) {
  .hero__bg .tile:hover img,
  .hero__bg .tile:hover video { transform: scale(1.06); filter: brightness(0.92) contrast(1.02) saturate(1.06); }
}
```

Declaring the transition inside `:hover` as well as on the base rule is what
makes the timing asymmetric: the `:hover` transition governs entering, the base
transition governs leaving.

The `@media (hover: none) and (pointer: coarse)` block is AUDIT §6's rule — a tap
on a touch device fires a false `:hover` that then sticks until the next tap
elsewhere. Neutralising it stops a tapped hero tile from staying zoomed.

## Repo conventions to follow

- `--ease-out` comes from plan `004`. **Run plan 004 first.**
- The repo already gates hover behind pointer capability at
  `resources/css/core.css:68-70`:
  ```css
  @media (hover: none) and (pointer: coarse) {
    body, a, button, [data-cursor], [data-magnetic], label, input, select, textarea { cursor: auto; }
  }
  ```
  and at `resources/css/pages.css:5012-5015`, where the work-tile hovers are
  wrapped in `@media (hover: hover)`. Either form is acceptable; match whichever
  is nearer in the file.
- Durations are seconds with a leading zero.

## Steps

1. `resources/css/pages.css:2446` — change `transform: scale(0);` to
   `transform: scale(0.9);`, and change both `var(--ease)` occurrences on line
   2449 to `var(--ease-out)`.
2. `resources/css/pages.css:69` — change the base transition to
   `transform 0.35s var(--ease-out), filter 0.35s var(--ease-out);`.
3. `resources/css/pages.css:71-75` — add the `transition:` line inside the
   `:hover` rule, before the `transform`.
4. Add the `@media (hover: none) and (pointer: coarse)` override immediately
   after the `:hover` rule.
5. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT change the scale values `1.06` and `1.12`, or any of the `filter`
  values — the visual end states stay exactly as they are.
- Do NOT touch `.hero__slide` (`resources/css/pages.css:37-44`) or its 1.2s
  crossfade — that is a deliberate hero slideshow dissolve and it is correct.
- Do NOT touch the JS tilt on `.hero__bg .tile` (`resources/js/core.js:997-1016`)
  — it writes to the `.tile`, this plan touches the `img`/`video` inside it.
  They do not conflict.
- Do NOT change the other image-zoom durations across the site — plan `011`
  normalises those, and `resources/css/pages.css:69` is explicitly excluded there
  so this plan can own it.
- Do NOT change the `0.85s` delays on the glyph transition — they sequence the
  glyph behind the stroke draw above it (`resources/css/pages.css:2441`) and are
  deliberate.
- If `resources/css/pages.css:2446` does not read `  transform: scale(0);`, the
  file has drifted since commit 8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
  `grep -n "scale(0)" resources/css/pages.css resources/css/core.css` returns no
  matches.
- **Feel check**: run the site and
  - Scroll the sticky process section so each panel becomes active in turn. The
    glyph's fill detail must **grow into place** from slightly small, not pop out
    of nothing. At 10% playback in DevTools → Animations the difference is
    obvious: before, the shape starts as an invisible point.
  - Sweep the cursor quickly left-to-right across the hero tile grid. Each tile
    must push in while hovered and **snap back within a third of a second** once
    you leave. Before this change you can sweep across and watch four tiles still
    growing behind you.
  - Hover a single hero tile and hold: the push-in must still feel slow and
    cinematic (0.8s), not clipped.
  - Hover in and immediately out: the tile must reverse from wherever it got to,
    not jump.
  - On a real phone (or DevTools device emulation with touch), tap a hero tile.
    It must **not** stay zoomed after the tap. If it does, the `@media (hover:
    none)` override is not applying — check the selector specificity.
  - Toggle `prefers-reduced-motion: reduce` (with plan 008 applied): neither the
    glyph nor the hero tile may scale at all.
- **Done when**: no `scale(0)` remains in the stylesheets, and a fast sweep
  across the hero grid leaves no tile still animating after ~350ms.
