# 005 — Replace animated padding/width on hovers and progress bars with transforms

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: HIGH
- **Category**: Performance (AUDIT §5)
- **Estimated scope**: 3 files (`resources/css/core.css`, `resources/css/pages.css`, `resources/js/chrome.js`), ~11 rules

## Problem

AUDIT §5: animate `transform` and `opacity` only — `width`, `padding` and
`margin` trigger layout + paint + composite on every frame. Eleven rules break
this, and most sit on hovers that fire dozens of times per session.

**Row hovers that re-pad themselves** (each forces a layout of the row's whole
subtree for 400-500ms, per hover):

```css
/* resources/css/pages.css:865 — current */
.why-list li:hover { padding-left: 16px; color: var(--red); transition: padding-left 0.4s var(--ease), color 0.3s var(--ease); }
```

```css
/* resources/css/pages.css:1677-1687 — current */
.timeline__row {
  display: grid;
  grid-template-columns: 80px 1fr 2fr 1fr;
  gap: 40px;
  align-items: start;
  padding: 48px 0;
  border-bottom: 1px solid var(--line);
  position: relative;
  transition: padding 0.5s var(--ease);
}
.timeline__row:hover { padding-left: 16px; }
```

```css
/* resources/css/pages.css:3862-3871 — current */
.hours__day {
  display: grid;
  grid-template-columns: 100px 1fr auto;
  gap: 18px;
  padding: 16px 0;
  border-bottom: 1px solid var(--line);
  align-items: baseline;
  transition: padding-left 0.4s var(--ease-soft);
}
.hours__day:hover { padding-left: 8px; }
```

```css
/* resources/css/pages.css:4056-4057 — current */
.pp-phase { display: grid; grid-template-columns: 64px minmax(0, 1fr) 140px; gap: 26px; align-items: start; padding: 30px 0; border-bottom: 1px solid var(--line); transition: padding-left 0.5s var(--ease-soft), background 0.4s var(--ease-soft); }
.pp-phase:hover { padding-left: 16px; background: rgba(232,15,3,0.02); }
```

Note the `.why-list` rule also declares its `transition` **inside** `:hover`, so
un-hovering has no transition at all — the row snaps back. That is a second bug
in the same line.

**Bars and rules that grow by animating `width`:**

```css
/* resources/css/pages.css:3970-3977 — current */
  width: 0;
  height: 1px;
  background: var(--red);
  transform: translateY(-50%);
  transition: width 0.7s cubic-bezier(0.22, 1, 0.36, 1);
  pointer-events: none;
}
.svc:hover::after { width: 80px; }
```

```css
/* resources/css/pages.css:762-763 — current */
.car__dot { width: 28px; height: 2px; background: var(--line); border: 0; transition: background 0.3s var(--ease), width 0.4s var(--ease); cursor: none; }
.car__dot.is-on { background: var(--red); width: 56px; }
```

```css
/* resources/css/pages.css:2512-2520 — current */
.sproc__dot {
  width: 36px; height: 2px;
  background: var(--line);
  border: 0;
  cursor: pointer;
  padding: 0;
  transition: background 0.3s var(--ease), width 0.4s var(--ease-3);
}
.sproc__dot.is-on { background: var(--red); width: 64px; }
```

```css
/* resources/css/pages.css:4614-4625 — current */
.strip__dot::before {
  content: '';
  display: block;
  height: 2px;
  background: var(--line);
  transition: background 0.4s var(--ease-soft), width 0.5s var(--ease-spring);
  width: 36px;
}
```

`.car__dot` and `.sproc__dot` are laid out in a flex row, so animating one dot's
width relays out every sibling dot, every frame.

**The quote wizard progress bar animates `width` from JS:**

```css
/* resources/css/pages.css:1938-1944 — current */
.quote__bar-fill {
  position: absolute; inset: 0;
  background: var(--red);
  width: 0%;
  transition: width 0.6s var(--ease-3);
}
```

```js
/* resources/js/chrome.js:361 — current */
        if (fill) fill.style.width = ((step-1)/total)*100 + '%';
```

```js
/* resources/js/chrome.js:365 — current */
          if (fill) fill.style.width = '100%';
```

**The footer arrow animates `width` *and* `margin-right`** — two layout
properties on one hover, on every footer link:

```css
/* resources/css/core.css:896-913 — current */
.foot__col > a::before {
  content: '→';
  font-family: var(--f-mono);
  color: var(--red);
  width: 0;
  margin-right: 0;
  opacity: 0;
  transform: translateX(-6px);
  transition: width 0.3s var(--ease), opacity 0.3s var(--ease),
              margin-right 0.3s var(--ease), transform 0.3s var(--ease);
}
.foot__col > a:hover { color: var(--paper); }
.foot__col > a:hover::before {
  width: 1em;
  margin-right: 9px;
  opacity: 1;
  transform: translateX(0);
}
```

**And one rule animates padding that only ever changes on touch:**

```css
/* resources/css/core.css:1245 — current */
.svc { transition: padding 0.55s var(--ease-spring), color 0.4s var(--ease), background 0.4s var(--ease); }
```

```css
/* resources/css/pages.css:2174-2175 — current, inside a max-width media query */
  .svc { grid-template-columns: 36px 1fr 36px; gap: 12px; padding: 18px 0 !important; }
  .svc:hover { padding-left: 8px !important; padding-right: 8px !important; }
```

The desktop design deliberately avoids this — `resources/css/pages.css:2169-2170`
says so: *"The red wash bleeds past the row instead of the row re-padding itself
— keeps the title from jumping sideways on hover."* The mobile override
contradicts that comment, and it is a `:hover` rule on a breakpoint that is
almost entirely touch, where AUDIT §6 says hover motion must be gated behind
`@media (hover: hover)` anyway. It should just go.

## Target

**Row hovers → `translateX`.** The visual result is identical; the cost is a
composited transform instead of a layout.

```css
/* target — resources/css/pages.css, replacing line 865 */
.why-list li { transition: transform 0.4s var(--ease-in-out), color 0.3s var(--ease-in-out); }
.why-list.reveal.is-in li:hover { transform: translateX(16px); color: var(--red); }
```

The `.reveal.is-in` prefix is required: `.why-list.reveal li` sets
`transform: translateY(12px)` at `resources/css/pages.css:867` and
`.why-list.reveal.is-in li` sets `transform: none` at `:868`. A bare
`.why-list li:hover` would lose the specificity fight after the reveal lands.

```css
/* target — resources/css/pages.css:1686-1687 */
  transition: transform 0.35s var(--ease-in-out);
}
.timeline__row:hover { transform: translateX(16px); }
```

```css
/* target — resources/css/pages.css:3869-3871 */
  transition: transform 0.3s var(--ease-in-out);
}
.hours__day:hover { transform: translateX(8px); }
```

```css
/* target — resources/css/pages.css:4056-4057 */
.pp-phase { display: grid; grid-template-columns: 64px minmax(0, 1fr) 140px; gap: 26px; align-items: start; padding: 30px 0; border-bottom: 1px solid var(--line); transition: transform 0.35s var(--ease-in-out), background 0.4s var(--ease-soft); }
.pp-phase:hover { transform: translateX(16px); background: rgba(232,15,3,0.02); }
```

**Growing bars → `scaleX` from a fixed width.** Set the final width once and
scale from `transform-origin: left`.

```css
/* target — resources/css/pages.css:3970-3977 */
  width: 80px;
  height: 1px;
  background: var(--red);
  transform: translateY(-50%) scaleX(0);
  transform-origin: left center;
  transition: transform 0.5s var(--ease-out);
  pointer-events: none;
}
.svc:hover::after { transform: translateY(-50%) scaleX(1); }
```

```css
/* target — resources/css/pages.css:762-763 */
.car__dot { width: 56px; height: 2px; background: var(--line); border: 0; transform: scaleX(0.5); transform-origin: left center; transition: background 0.3s var(--ease-in-out), transform 0.4s var(--ease-out); cursor: none; }
.car__dot.is-on { background: var(--red); transform: scaleX(1); }
```

```css
/* target — resources/css/pages.css:2512-2520 */
.sproc__dot {
  width: 64px; height: 2px;
  background: var(--line);
  border: 0;
  cursor: pointer;
  padding: 0;
  transform: scaleX(0.5625);
  transform-origin: left center;
  transition: background 0.3s var(--ease-in-out), transform 0.4s var(--ease-out);
}
.sproc__dot.is-on { background: var(--red); transform: scaleX(1); }
```

`0.5625` is `36 / 64` — the inactive dot keeps its exact current 36px width.
`.car__dot`'s `0.5` is `28 / 56`.

```css
/* target — resources/css/pages.css:4614-4625 */
.strip__dot::before {
  content: '';
  display: block;
  height: 2px;
  background: var(--line);
  width: 56px;
  transform: scaleX(0.642857);
  transform-origin: left center;
  transition: background 0.4s var(--ease-soft), transform 0.5s var(--ease-spring);
}
```

`0.642857` is `36 / 56`. Read the `.strip__dot.is-on::before` rule that follows
at `resources/css/pages.css:4626` and change its `width` (if it sets one) to
`transform: scaleX(1)`; if it only sets `background`, add `transform: scaleX(1);`
to it.

**Progress bar → `scaleX` driven from JS.**

```css
/* target — resources/css/pages.css:1938-1944 */
.quote__bar-fill {
  position: absolute; inset: 0;
  background: var(--red);
  width: 100%;
  transform: scaleX(0);
  transform-origin: left center;
  transition: transform 0.4s var(--ease-out);
}
```

```js
/* target — resources/js/chrome.js:361 */
        if (fill) fill.style.transform = `scaleX(${(step - 1) / total})`;
```

```js
/* target — resources/js/chrome.js:365 */
          if (fill) fill.style.transform = 'scaleX(1)';
```

**Footer arrow → fixed box, fade + slide.** The arrow reserves its space at all
times, so nothing reflows; only opacity and transform animate.

```css
/* target — resources/css/core.css:896-913 */
.foot__col > a::before {
  content: '→';
  font-family: var(--f-mono);
  color: var(--red);
  display: inline-block;
  width: 1em;
  margin-right: 9px;
  opacity: 0;
  transform: translateX(-6px);
  transition: opacity 0.3s var(--ease-in-out), transform 0.3s var(--ease-in-out);
}
.foot__col > a:hover { color: var(--paper); }
.foot__col > a:hover::before {
  opacity: 1;
  transform: translateX(0);
}
```

This shifts each footer link right by `1em + 9px` permanently. That is the
correct trade — the alternative is a layout animation on every footer link
hover. If the reserved gutter reads as wrong, the fallback is to give
`.foot__col > a` `padding-left: calc(1em + 9px)` and position the `::before`
absolutely at `left: 0`; do that only if the feel check below fails.

**`.svc` padding → delete the transition and the mobile hover rule.**

```css
/* target — resources/css/core.css:1245 */
.svc { transition: color 0.4s var(--ease-in-out), background 0.4s var(--ease-in-out); }
```

```css
/* target — resources/css/pages.css:2174-2175 */
  .svc { grid-template-columns: 36px 1fr 36px; gap: 12px; padding: 18px 0 !important; }
```

(The `.svc:hover` line is deleted outright.)

## Repo conventions to follow

- `--ease-out` and `--ease-in-out` come from plan `004`. **Run plan 004 first.**
  If `grep -n "\-\-ease-in-out:" resources/css/core.css` returns nothing, STOP.
- The repo already does the scaleX-from-origin pattern correctly in several
  places — copy the shape from `resources/css/pages.css:900-906`:
  ```css
  .belief::before {
    /* … */
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.6s var(--ease-3);
  }
  .belief:hover::before { transform: scaleX(1); }
  ```
  and from `resources/css/core.css:176-184` (`.preloader__bar::after` uses
  `transform: scaleX(var(--p, 0)); transform-origin: left;` — exactly the
  progress-bar pattern this plan applies to `.quote__bar-fill`).
- Single-line rules stay single-line; multi-line rules stay multi-line. Match
  what is already there per rule.

## Steps

1. `resources/css/core.css:896-913` — replace the `.foot__col > a::before` block
   and its `:hover` counterpart with the target.
2. `resources/css/core.css:1245` — drop `padding 0.55s var(--ease-spring),` from
   the `.svc` transition and repoint the remaining two to `--ease-in-out`.
3. `resources/css/pages.css:762-763` — `.car__dot` / `.car__dot.is-on`.
4. `resources/css/pages.css:865` — replace with the two-rule target. Verify
   lines 867-868 (`.why-list.reveal li` and `.why-list.reveal.is-in li`) are
   still intact and unchanged.
5. `resources/css/pages.css:1686-1687` — `.timeline__row`.
6. `resources/css/pages.css:1938-1944` — `.quote__bar-fill`.
7. `resources/css/pages.css:2175` — delete the `.svc:hover` line.
8. `resources/css/pages.css:2512-2520` — `.sproc__dot` / `.sproc__dot.is-on`.
9. `resources/css/pages.css:3970-3977` — `.svc::after` / `.svc:hover::after`.
10. `resources/css/pages.css:3869-3871` — `.hours__day`.
11. `resources/css/pages.css:4056-4057` — `.pp-phase`.
12. `resources/css/pages.css:4614-4626` — `.strip__dot::before` and the
    `.strip__dot.is-on::before` rule that follows it.
13. `resources/js/chrome.js:361` and `:365` — the two `fill.style.width` writes.
14. Run `npm run build` and confirm it exits 0.

Line numbers shift as you edit. Match on selector text, not line number.

## Boundaries

- Do NOT touch `.nav`'s `padding` transition at `resources/css/core.css:259`. The
  header shrinking on scroll is deliberate design, it fires once per scroll
  threshold crossing rather than per hover, and it is scoped to a
  `position: fixed` subtree. Plan 009 addresses the nav; this plan leaves it.
- Do NOT touch the `max-height` collapsibles (`.acc__body`, `.belief__note`,
  `.vacc__body`) — plan `006` owns those.
- Do NOT change any markup in `resources/views/`.
- Do NOT change the visual end state of any of these effects — the travel
  distances (16px, 8px), the final bar widths (80px, 56px, 64px) and the colours
  must all match what is there now.
- Do NOT add `will-change` to any of these rules.
- If `resources/css/pages.css:865` does not begin `.why-list li:hover {`, the
  file has drifted since commit 8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
  `grep -n "transition:.*padding" resources/css/core.css resources/css/pages.css`
  returns only `resources/css/core.css:259` (the nav, deliberately out of scope).
  `grep -n "transition:.*[^-]width" resources/css/core.css resources/css/pages.css`
  returns no matches.
  `grep -n "fill.style.width" resources/js/chrome.js` returns no matches.
- **Feel check**: run the site and
  - **Layout cost.** DevTools → Performance, start recording, sweep the cursor
    slowly down the "why us" list and the timeline rows on the about page, stop.
    Before the change each hover shows a "Layout" block per frame. After, the
    rows must animate with **no Layout entries at all** — only "Composite
    Layers". This is the primary check.
  - Hover a `.why-list` row: it slides right by 16px and turns red, and — new —
    it must also slide *back* smoothly when the cursor leaves. Before this change
    it snapped back.
  - Scroll the "why us" list into view first, then hover a row. The row must
    still slide right (this proves the `.reveal.is-in` specificity fix works). If
    it does not move, the specificity is wrong.
  - Hover a service row on the homepage: the red hairline (`.svc::after`) must
    grow from its left edge to 80px, not appear at full width.
  - On mobile width (<880px), tap a service row: it must **not** shift sideways.
  - Step through the quote modal: the red progress bar under the form must grow
    left-to-right, one quarter per step, ending full on the success step.
  - Click through the testimonial carousel dots and the sticky-process dots: the
    active dot must lengthen from its left edge, and its neighbours must not
    shuffle.
  - Hover a footer link: the red arrow fades and slides in without the text
    jumping. If the permanent gutter looks wrong, apply the absolute-positioned
    fallback described in Target.
  - In DevTools → Animations at 10% playback, confirm each bar scales from its
    left edge, never from the centre.
- **Done when**: a Performance recording of hovering every affected element shows
  zero "Layout" events attributable to those hovers, and all eleven effects look
  identical to before at 100% playback.
