# 006 — Replace `max-height` collapsibles with `grid-template-rows` so they open at their real height

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: MEDIUM
- **Category**: Performance (AUDIT §5), Easing & duration (AUDIT §2)
- **Estimated scope**: 1 file (`resources/css/pages.css`), 3 rules

## Problem

Three collapsibles animate `max-height` to a guessed ceiling.

```css
/* resources/css/pages.css:826-832 — current, the FAQ accordion */
.acc__body {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.6s var(--ease-3);
}
.acc__item.is-open .acc__body { max-height: 280px; }
```

```css
/* resources/css/pages.css:930-944 — current, the beliefs hover note */
  max-height: 0;
  opacity: 0;
  overflow: hidden;
  transition: max-height 0.6s var(--ease-3), opacity 0.4s var(--ease), margin-top 0.5s var(--ease);
}
.belief:hover .belief__title { transform: translateX(8px); }
.belief:hover .belief__note {
  max-height: 160px;
  opacity: 1;
}
```

```css
/* resources/css/pages.css:3678-3684 — current, the vertical accordion */
.vacc__body {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.7s var(--ease-soft);
}
.vacc__row.is-open .vacc__body { max-height: 540px; }
```

Three problems, all consequences of the same technique:

1. **The easing lies.** The transition interpolates 0 → 280px, but the content
   might be 150px tall. The panel finishes opening at 320ms and then spends the
   remaining 280ms animating empty space — so the visible motion stops dead
   mid-curve. The bigger the gap between the ceiling and the real height, the
   worse it reads. `.vacc__body`'s 540px ceiling makes this severe.
2. **It clips.** Any FAQ answer taller than 280px, or a beliefs note taller than
   160px, is silently cut off with `overflow: hidden`. This is a content bug
   waiting for an admin to write a long answer.
3. **It's a layout animation.** `max-height` relayouts the panel and everything
   after it on every frame (AUDIT §5).

`grid-template-rows: 0fr → 1fr` fixes all three: it animates to the content's
actual height, the curve maps onto the real motion, and nothing is clipped.

## Target

The pattern requires one wrapper element between the animating box and the
content. All three already have one, so no markup changes.

```css
/* target — resources/css/pages.css:826-832 */
.acc__body {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.4s var(--ease-out);
}
.acc__body > * { overflow: hidden; min-height: 0; }
.acc__item.is-open .acc__body { grid-template-rows: 1fr; }
```

`.acc__body`'s child is `.acc__body-inner` (`resources/css/pages.css:833`), which
already exists. `min-height: 0` is required — grid items default to
`min-height: auto`, which refuses to shrink below their content and breaks the
collapse.

```css
/* target — resources/css/pages.css:930-944 */
  display: grid;
  grid-template-rows: 0fr;
  opacity: 0;
  transition: grid-template-rows 0.4s var(--ease-out), opacity 0.3s var(--ease-out), margin-top 0.4s var(--ease-out);
}
.belief__note > * { overflow: hidden; min-height: 0; }
.belief:hover .belief__title { transform: translateX(8px); }
.belief:hover .belief__note {
  grid-template-rows: 1fr;
  opacity: 1;
}
```

Before editing, open `resources/views/` and confirm `.belief__note` wraps its
text in a child element. If its text is a direct text node with no element
wrapper, this pattern cannot apply — in that case leave `.belief__note` on
`max-height` and report it as skipped, since adding a wrapper is a markup change
this plan does not authorise.

```css
/* target — resources/css/pages.css:3678-3684 */
.vacc__body {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.45s var(--ease-soft);
}
.vacc__body > * { overflow: hidden; min-height: 0; }
.vacc__row.is-open .vacc__body { grid-template-rows: 1fr; }
```

Same precondition: `.vacc__body` must have exactly one element child. Check the
Blade view before editing.

Durations drop from 0.6s/0.7s to 0.4s/0.45s. They were long partly to disguise
the dead tail; without it, the shorter time reads as more responsive and lands
inside the AUDIT §2 budget for a dropdown-class element.

Also note `resources/css/pages.css:951` sets `.belief__note { max-height: 160px;
opacity: 1; }` inside a `@media (max-width: 880px)` block, which force-opens the
note on mobile. Change that to `grid-template-rows: 1fr;` to match.

## Repo conventions to follow

- `--ease-out` comes from plan `004`. **Run plan 004 first.**
- `--ease-soft` (`cubic-bezier(0.32, 0.72, 0, 1)`, `resources/css/core.css:1128`)
  stays on `.vacc__body` — it is the drawer curve and it is the right choice for
  a large panel sliding open.
- State classes in this repo are `.is-open` / `.is-on`; the JS that toggles them
  is at `resources/js/core.js:390-399` (accordion) and does not change.
- Two-selector collapsibles (`.x__body` + `.x__body-inner`) are the existing
  shape — keep it.

## Steps

1. Open the Blade views and confirm each of `.acc__body`, `.belief__note` and
   `.vacc__body` wraps its content in exactly one element child:
   `grep -rn "acc__body\|belief__note\|vacc__body" resources/views/`.
   Record which ones qualify. Any that do not, skip and report.
2. `resources/css/pages.css:826-832` — replace `.acc__body` and
   `.acc__item.is-open .acc__body` with the target.
3. `resources/css/pages.css:930-944` — replace the `.belief__note` declarations
   and the `.belief:hover .belief__note` rule with the target.
4. `resources/css/pages.css:951` — inside the `@media (max-width: 880px)` block,
   change `.belief__note { max-height: 160px; opacity: 1; margin-top: 12px; }` to
   use `grid-template-rows: 1fr;` in place of `max-height: 160px;`.
5. `resources/css/pages.css:3678-3684` — replace `.vacc__body` and
   `.vacc__row.is-open .vacc__body` with the target.
6. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT add, remove or rename any element in `resources/views/`. If a
  collapsible lacks a wrapper child, skip it and say so.
- Do NOT touch the JS that toggles `.is-open` — `resources/js/core.js:390-399`
  and whatever drives `.vacc__row`.
- Do NOT touch `.acc__plus` (`resources/css/pages.css:2436-2446`) — the plus/minus
  glyph rotation is a transform and is already correct.
- Do NOT change the padding inside `.acc__body-inner` — the grid wrapper animates
  around it untouched.
- Do NOT introduce a JS height measurement (`scrollHeight`) as an alternative.
  `grid-template-rows` needs no measurement.
- If `resources/css/pages.css:831` does not read
  `.acc__item.is-open .acc__body { max-height: 280px; }`, the file has drifted
  since commit 8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
  `grep -n "transition:.*max-height" resources/css/pages.css` returns no matches
  (or only the collapsibles you explicitly skipped, which you must name in your
  report).
- **Feel check**: run the site and
  - Open an FAQ item with a *short* answer. Before the change the panel stops
    moving partway through the 600ms and the rest is dead time. After, the motion
    must run continuously from start to finish and stop exactly when the panel is
    full.
  - Temporarily paste 15 lines of text into one FAQ answer (or find the longest
    one in the CMS). It must open to its full height with **nothing clipped**.
    Revert the test content afterwards.
  - Open and close the same item three times quickly. Because these are
    transitions rather than keyframes, each toggle must retarget from wherever
    the panel currently is — never jump to 0 and restart.
  - Hover a belief row: the note expands to its real height and fades in
    together, finishing at the same moment.
  - At <880px, confirm belief notes are open by default and not clipped.
  - Open a `.vacc__row`: the drawer opens over ~450ms with the soft curve and no
    dead tail.
  - DevTools → Performance while opening an accordion: `grid-template-rows` still
    costs layout (it is a layout animation by nature), but it now runs for 400ms
    instead of 600ms and animates the true distance. Confirm the panel's final
    height matches its content height exactly (Elements → box model).
  - In the Animations panel at 10% playback, confirm the panel's *content* moves
    for the entire duration.
- **Done when**: every collapsible opens to its content's real height, nothing is
  clipped at any content length, and no `max-height` transition remains except
  ones you explicitly reported as skipped.
