# 007 — Stop holding permanent compositor layers with `will-change`

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: MEDIUM
- **Category**: Performance (AUDIT §5)
- **Estimated scope**: 2 files (`resources/css/core.css`, `resources/css/pages.css`), 18 declarations

## Problem

`will-change` is a hint that promotes an element to its own compositor layer and
keeps it there for as long as the declaration applies. Declared in a static CSS
rule it never lifts — the browser holds the layer for the whole session, for
every matching element, whether or not anything is animating. The repo declares
it 18 times, all in static rules:

```
resources/css/core.css:96    .curtain__panel          will-change: transform
resources/css/core.css:701   .reveal                  will-change: transform, opacity
resources/css/core.css:717   .split-word > span       will-change: transform, opacity
resources/css/core.css:736   .clip-reveal             will-change: clip-path
resources/css/core.css:824   .foot__marquee-track     will-change: transform
resources/css/core.css:1113  [data-magnetic]          will-change: transform
resources/css/pages.css:42   .hero__slide             will-change: opacity
resources/css/pages.css:118  .hero__center            will-change: opacity, transform
resources/css/pages.css:2608 .marquee                 will-change: transform
resources/css/pages.css:2614 [data-tilt]              will-change: transform
resources/css/pages.css:3914 .odo__col-inner          will-change: transform
resources/css/pages.css:3930 .marquee__item.is-charified > span  will-change: transform
resources/css/pages.css:3940 .hover-preview           will-change: transform, filter
resources/css/pages.css:3955 .hover-preview img       will-change: transform
resources/css/pages.css:3999 .hero__bg .tile          will-change: transform
resources/css/pages.css:4006 .section__title, …       will-change: transform
resources/css/pages.css:4486 .strip__track            will-change: transform
resources/css/pages.css:4500 .strip__card             will-change: transform, filter
```

Three groups, worst first.

**Group A — layers held for features that no longer exist.** These are pure
cost with zero benefit:

```css
/* resources/css/pages.css:4002-4006 — current */
/* ---------- Magnetic scroll-jolt on big headlines ---------- */
.section__title, .pf-feat__title, .news__h, .belief__title, .hours__title,
.hero__title, .cta-strip__title, .pf-hero h1, .proc-hero h1 {
  will-change: transform;
}
```

There is no "magnetic scroll-jolt" code anywhere —
`grep -n "section__title" resources/js/*.js` returns nothing. Every section
heading on every page of the site holds a compositor layer for an animation that
was never shipped or has since been removed. This one rule is the single biggest
offender in the file.

```css
/* resources/css/pages.css:2611-2616 — current */
[data-tilt] {
  transform-style: preserve-3d;
  will-change: transform;
  transition: transform 0.5s var(--ease);
}
```

`grep -rn "data-tilt" resources/views/` returns nothing, and the JS reads
`document.querySelectorAll('[data-tilt-disabled]')`
(`resources/js/core.js:713`) — the feature is off and the attribute is unused.

```css
/* resources/css/pages.css:3908-3915 — current */
.odo__col-inner {
  display: flex;
  flex-direction: column;
  line-height: 1;
  transform: translateY(0);
  transition: transform 1.6s cubic-bezier(0.22, 1, 0.36, 1);
  will-change: transform;
}
```

The odometer is explicitly disabled — `resources/js/core.js:867-868`:
`// Odometer flipboard disabled … void odometerize; void odoIO;`.

```css
/* resources/css/pages.css:3927-3931 — current */
.marquee__item.is-charified > span {
  display: inline-block;
  transition: transform 0.55s cubic-bezier(0.34, 1.56, 0.64, 1);
  will-change: transform;
}
```

`.is-charified` is only ever applied by the loop at `resources/js/core.js:871`,
which iterates `.marquee__item--disabled` — a class that appears nowhere in
`resources/views/`. Dead.

```css
/* resources/css/pages.css:2605-2609 — current */
.marquee {
  transform: skewY(var(--skew, 0deg));
  transition: transform 0.5s var(--ease);
  will-change: transform;
}
```

`resources/js/core.js:791-792` reads
`/* Scroll-velocity skew (disabled — marquee stays steady) */` and sets `--skew`
to `0deg` once. The skew is permanently zero, so this rule holds a layer and a
containing block to apply an identity transform.

**Group B — layers held for one-shot animations.** These animate exactly once
per page load and then never again, but keep their layer forever:

- `.reveal` (`resources/css/core.css:701`) — this is the expensive one.
  `.reveal` appears **51 times across `resources/views/`**; the homepage alone
  has 8. Each one animates for 750ms on scroll-in, then holds a layer for the
  rest of the session.
- `.split-word > span` (`resources/css/core.css:717`) — worse per element,
  because the split runs *per word*. A split headline of 10 words is 10
  permanent layers.
- `.clip-reveal` (`resources/css/core.css:736`), `.hero__center`
  (`resources/css/pages.css:118`), `.hero__slide`
  (`resources/css/pages.css:42`), `.curtain__panel`
  (`resources/css/core.css:96`).

**Group C — legitimately continuous, keep them.** These animate for the whole
time they are on screen, which is exactly what `will-change` is for:

- `.foot__marquee-track` (`resources/css/core.css:824`) — infinite marquee.
- `.strip__track` / `.strip__card` (`resources/css/pages.css:4486`, `:4500`) —
  the film strip is on an auto-advancing 4.8s interval
  (`resources/js/core.js:1055`).
- `.hero__bg .tile` (`resources/css/pages.css:3999`) — driven by a mousemove
  rAF loop (`resources/js/core.js:997-1016`) for as long as the hero is visible.
- `[data-magnetic]` (`resources/css/core.css:1113`) — there are two or three per
  page and they are cursor-driven.
- `.hover-preview` / `.hover-preview img` (`resources/css/pages.css:3940`,
  `:3955`) — cursor-tracked at 60fps while open. Note plan `003` restricts the
  loop; the layer hint stays correct.

## Target

Delete Group A entirely (dead code, not just the hint). Delete the `will-change`
declaration from Group B and let the JS that triggers the animation apply the
hint for the animation's lifetime instead. Leave Group C alone.

**Group A deletions:**

```css
/* target — resources/css/pages.css:4002-4006 → delete the whole rule
   including its banner comment */
```

```css
/* target — resources/css/pages.css:2611-2620 → delete the whole
   "3D tilt cards" block: [data-tilt] and [data-tilt].is-tilting and
   [data-tilt] > * , plus the banner comment */
```

```css
/* target — resources/css/pages.css:3908-3922 → delete the .odo__col-inner
   rule and the sibling .odo__* rules around it. Keep going until the block
   ends; confirm with `grep -n "odo__" resources/css/pages.css` returning
   nothing afterwards. */
```

```css
/* target — resources/css/pages.css:3927-3934 → delete the
   .marquee__item.is-charified rules and the banner comment */
```

```css
/* target — resources/css/pages.css:2605-2609 */
.marquee {
  transition: transform 0.5s var(--ease);
}
```

(The `transform: skewY(…)` and `will-change` both go; the transition is harmless
and left so the rule stays valid if the skew is ever revived. If the rule is then
empty apart from the transition, deleting it entirely is also fine.)

**Group B — remove the declaration from CSS, add it in JS for the duration.**

```css
/* target — resources/css/core.css:697-703 */
.reveal {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity 0.75s var(--ease), transform 0.75s var(--ease);
}
```

```css
/* target — resources/css/core.css:712-718 */
.split-word > span {
  display: inline-block;
  transform: translateY(40%);
  opacity: 0;
  transition: transform 0.7s var(--ease), opacity 0.7s var(--ease);
}
```

```css
/* target — resources/css/core.css:733-738 */
.clip-reveal {
  clip-path: inset(0 0 100% 0);
  transition: clip-path 1.1s var(--ease-3);
}
```

```css
/* target — resources/css/core.css:93-97 */
.curtain__panel {
  background: var(--red);
  transform: translateY(100%);
}
```

```css
/* target — resources/css/pages.css:37-43 */
.hero__slide {
  position: absolute;
  inset: 0;
  opacity: 0;
  transition: opacity 1.2s var(--ease-soft);
}
```

`.hero__center`'s `will-change` (`resources/css/pages.css:118`) is deleted by
plan `002`. If plan 002 has already run, that line is gone; if not, delete it
here too.

Then hint only while the reveal is actually running, in the observer that
already exists:

```js
/* target — resources/js/core.js:91-99, replacing the IntersectionObserver */
  const io = new IntersectionObserver(entries => {
    entries.forEach(en => {
      if (en.isIntersecting) {
        // Promote for the duration of the transition, then release the layer.
        en.target.style.willChange = 'transform, opacity';
        en.target.classList.add('is-in');
        io.unobserve(en.target);
        setTimeout(() => { en.target.style.willChange = ''; }, 1200);
      }
    });
  }, { threshold: 0.05, rootMargin: '0px 0px -2% 0px' });
```

1200ms covers the longest reveal in the set (`.clip-reveal` at 1.1s) plus the
0.40s worst-case stagger delay on `.split` words
(`resources/css/core.css:731`) — 1.1s + 0.4s exceeds 1200ms, so use **1600ms**
instead of 1200ms to be safe. Use 1600.

`forceRevealVisible()` (`resources/js/core.js:102-111`) also adds `.is-in`; it
does not need the hint — elements it catches are already on screen and the extra
promotion buys nothing.

## Repo conventions to follow

- JS state changes go through classes; inline style is used only for values that
  cannot be expressed as a class — `will-change` timing is one of those. The
  existing inline-style precedent is `resources/js/core.js:850-852`
  (`inner.style.transitionDelay = …`).
- Deleting dead CSS is in scope for this plan **only** for the blocks named in
  Group A. Do not go hunting for other dead rules.
- Keep the banner comments (`/* ---------- Name ---------- */`) for blocks that
  survive; delete them along with blocks that do not.

## Steps

1. `resources/css/pages.css` — delete the "Magnetic scroll-jolt on big headlines"
   block (lines 4002-4006) including its banner comment.
2. `resources/css/pages.css` — delete the "3D tilt cards" block (from the banner
   at line 2610 through the end of the `[data-tilt] > *` rule).
3. `resources/css/pages.css` — delete every `.odo__*` rule. Verify with
   `grep -n "odo__" resources/css/pages.css`.
4. `resources/css/pages.css` — delete the "Marquee per-character wave (on hover)"
   block including its banner comment.
5. `resources/css/pages.css:2605-2609` — drop `transform: skewY(var(--skew,
   0deg));` and `will-change: transform;` from `.marquee`.
6. Delete the `will-change` line only (leave the rest of each rule intact) from:
   `resources/css/core.css:96`, `:701`, `:717`, `:736`, and
   `resources/css/pages.css:42`. Also `resources/css/pages.css:118` if plan 002
   has not already removed it.
7. `resources/js/core.js:91-99` — replace the IntersectionObserver with the
   target version, using **1600** as the timeout.
8. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT remove `will-change` from `resources/css/core.css:824`
  (`.foot__marquee-track`), `resources/css/core.css:1113` (`[data-magnetic]`), or
  `resources/css/pages.css:3940`, `:3955`, `:3999`, `:4486`, `:4500`. Those are
  Group C and are correct.
- Do NOT delete `resources/js/core.js:712-736` (the disabled tilt loop) or
  `:809-868` (the disabled odometer) or `:870-887` (the disabled charify). This
  plan removes their **CSS** only; removing the JS is a separate cleanup and is
  out of scope.
- Do NOT change any transition durations, curves or transforms in the surviving
  rules.
- Do NOT add `will-change` anywhere new in CSS.
- If `resources/css/pages.css:4006` does not read `  will-change: transform;`
  under a `.section__title, …` selector list, the file has drifted since commit
  8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
  `grep -c "will-change" resources/css/core.css` returns `2`.
  `grep -c "will-change" resources/css/pages.css` returns `5`.
  `grep -n "odo__\|data-tilt\|is-charified" resources/css/pages.css` returns no
  matches.
- **Feel check**: run the site and
  - **Layer count.** DevTools → More tools → Layers (or Rendering → "Layer
    borders"), load the homepage and scroll to the bottom. Count the composited
    layers. Before this change every `.reveal`, every split word and every
    section heading has its own; after, the layer list should be a short one —
    the marquees, the magnetic buttons, the hero tiles, the film strip. This is
    the primary check.
  - Scroll the homepage top to bottom at normal speed. Every `.reveal` must
    still fade and rise exactly as before, with no flicker or jump at the moment
    the hint is released (1600ms after it triggers).
  - Watch a `.split` headline animate word by word — the stagger must still run
    to the last word cleanly.
  - Scroll a `.clip-reveal` element into view: the clip wipe must complete
    without a visible seam at the end.
  - Click a nav link and watch the curtain: the red panels must still slide
    smoothly with no tearing (this checks that dropping `will-change` from
    `.curtain__panel` did not cost frames — if it did, add it back and report).
  - Check the homepage hero slide crossfade still runs smoothly with two or more
    slides configured.
  - Record a Performance trace while scrolling the homepage: frame rate must be
    the same or better than before, and "Composite Layers" work should drop.
- **Done when**: the Layers panel on the homepage shows no layer for
  `.section__title`, `.reveal` or `.split-word > span` while idle, and every
  reveal animation still looks identical at 100% and 10% playback.
