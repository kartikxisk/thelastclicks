# 010 — Bring the quote modal's entrance inside the duration budget and onto an ease-out curve

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: MEDIUM
- **Category**: Easing & duration (AUDIT §2), Physicality (AUDIT §3)
- **Estimated scope**: 1 file (`resources/css/pages.css`), 2 rules

## Problem

```css
/* resources/css/pages.css:1714-1718 — current, the wrapper */
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.4s var(--ease);
```

```css
/* resources/css/pages.css:1731-1745 — current, the panel */
.quote__panel {
  position: relative;
  width: min(1180px, 94vw);
  height: min(720px, 90vh);
  background: #0c0c0c;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 18px;
  display: grid;
  grid-template-columns: 380px 1fr;
  overflow: hidden;
  box-shadow: 0 40px 120px -20px rgba(0,0,0,0.7), 0 0 0 1px rgba(232,15,3,0.05);
  transform: translateY(40px) scale(0.97);
  opacity: 0;
  transition: transform 0.6s var(--ease-3), opacity 0.4s var(--ease) 0.05s;
}
.quote.is-open .quote__panel { transform: translateY(0) scale(1); opacity: 1; }
```

The physicality is right — `scale(0.97)` is inside AUDIT §3's 0.9–0.97 band,
`translateY(40px)` gives it somewhere to come from, and `transform-origin:
center` is correct for a centred modal (AUDIT §3 explicitly exempts modals). The
timing is not:

1. **600ms exceeds the modal budget.** AUDIT §2 puts modals and drawers at
   200–500ms. This is a dialog opened from a button click; 600ms of travel makes
   the click feel unacknowledged.
2. **`--ease-3` is `cubic-bezier(0.85, 0, 0.15, 1)`, an ease-in-out.** An
   entering element must be ease-out (AUDIT §2). Combined with the 600ms, the
   panel spends its first ~200ms almost stationary — the user clicks, nothing
   visibly happens, then the panel arrives.
3. **The opacity is delayed 50ms behind the transform.** The panel starts moving
   while still invisible, so the first 50ms of a 600ms animation is wasted on
   something nobody can see.
4. **The three durations disagree.** The wrapper fades over 400ms, the panel
   moves over 600ms and fades over 400ms starting at 50ms. The overlay finishes,
   the panel is still travelling for another 250ms.

There is also a smaller issue on the close button:

```css
/* resources/css/pages.css:1758-1760 — current */
  transition: background 0.3s var(--ease), transform 0.3s var(--ease), border-color 0.3s var(--ease);
}
.quote__close:hover { background: var(--red); border-color: var(--red); transform: rotate(90deg); }
```

A 300ms 90° rotation on a hover in the corner of a modal is a lot of motion for
a state that carries no information — but it is a deliberate, contained flourish
on a rarely-hovered control, so this plan leaves it and only tightens the
duration to match the rest of the modal.

## Target

One coherent 300ms entrance, all three transitions in step, on the ease-out curve.

```css
/* target — resources/css/pages.css:1714-1718 */
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.2s var(--ease-out);
```

```css
/* target — resources/css/pages.css:1743-1745 */
  transform: translateY(24px) scale(0.97);
  opacity: 0;
  transition: transform 0.3s var(--ease-out), opacity 0.2s var(--ease-out);
}
.quote.is-open .quote__panel { transform: translateY(0) scale(1); opacity: 1; }
```

Changes and why each one:

- `0.6s` → `0.3s` — inside AUDIT §2's 200–500ms modal band, at the responsive end
  because this is a click-triggered dialog rather than a dragged sheet.
- `var(--ease-3)` → `var(--ease-out)` — entering element.
- `translateY(40px)` → `translateY(24px)` — 40px over 300ms is a faster apparent
  velocity than 40px over 600ms; reducing the travel keeps the *feel* of the
  original at the shorter duration. `scale(0.97)` is unchanged.
- The `0.05s` opacity delay is deleted — the panel must be visible for its whole
  travel.
- Wrapper opacity `0.4s` → `0.2s` so the backdrop and the panel resolve together.

```css
/* target — resources/css/pages.css:1758 */
  transition: background 0.2s var(--ease-in-out), transform 0.2s var(--ease-in-out), border-color 0.2s var(--ease-in-out);
```

## Repo conventions to follow

- `--ease-out` and `--ease-in-out` come from plan `004`. **Run plan 004 first.**
  If `grep -n "\-\-ease-out" resources/css/core.css` returns nothing, STOP.
- Modal open/close state is a class on the wrapper (`.quote.is-open`) applied by
  `resources/js/chrome.js:298` / `:306`. The CSS reads that class; no JS changes.
- The correctly-timed exemplar already in the repo is the work lightbox's sibling
  overlay pattern — but a better one for timing is
  `resources/css/pages.css:4660`, the cookie banner:
  `transition: transform 0.7s var(--ease-soft), opacity 0.5s var(--ease-soft);`
  — note that one is a *sheet sliding from an edge*, which earns a longer
  duration and the drawer curve. A centred dialog does not.

## Steps

1. `resources/css/pages.css:1717` — change the `.quote` wrapper transition to
   `opacity 0.2s var(--ease-out);`.
2. `resources/css/pages.css:1743-1744` — change `transform: translateY(40px)
   scale(0.97);` to `translateY(24px) scale(0.97)` and replace the transition
   line with the target.
3. `resources/css/pages.css:1758` — change the three `.quote__close` durations
   from `0.3s` to `0.2s` and the curve to `var(--ease-in-out)`.
4. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT change `scale(0.97)`, the panel's dimensions, its border-radius, its
  box-shadow or its grid.
- Do NOT add a `transform-origin` — a centred modal must scale from its centre
  (AUDIT §3 exempts modals from the trigger-origin rule). The default is correct.
- Do NOT change `.quote__panel-step` / `@keyframes quoteIn`
  (`resources/css/pages.css:1840-1842`) — plan 004 owns that, and plan 015
  reworks the step transition.
- Do NOT change `.quote__bar-fill` (`resources/css/pages.css:1938-1944`) — plan
  005 owns it.
- Do NOT touch `resources/js/chrome.js`.
- Do NOT add a separate exit animation. The same transitions run in reverse on
  close, which is correct for a transition-based (interruptible) modal.
- If `resources/css/pages.css:1745` does not read
  `  transition: transform 0.6s var(--ease-3), opacity 0.4s var(--ease) 0.05s;`,
  the file has drifted since commit 8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
  `grep -n "0.6s var(--ease-3)" resources/css/pages.css` no longer matches inside
  the `.quote__panel` rule.
- **Feel check**: run the site and
  - Click "Get a Quote" in the header. The panel must acknowledge the click
    immediately — visible movement within the first two frames — and settle in
    about a third of a second.
  - In DevTools → Animations at 10% playback, confirm the panel travels furthest
    in its first frames and decelerates into place. If it creeps at the start, the
    curve swap did not take.
  - Also at 10% playback: the backdrop fade, the panel fade and the panel travel
    must all begin on the same frame. Nothing lags behind.
  - Open and close the modal five times quickly. Because these are transitions,
    each toggle must retarget from wherever the panel currently is — the panel
    must never snap back to `translateY(24px)` and restart.
  - Press Escape mid-open: the panel must reverse from its current position.
  - Hover the close button: it rotates 90° in 200ms, quicker than before but
    still legible.
  - Toggle `prefers-reduced-motion: reduce` (with plan 008 applied): the panel
    must appear centred and in place with a short fade, no travel and no scale.
  - Check at a narrow viewport (<600px) that the shorter travel still reads —
    the panel is nearly full-screen there, so 24px is a smaller proportion of its
    height. If it looks static, raise the travel to `32px` and note the change.
- **Done when**: the modal's total entrance is 300ms end to end, all three
  transitions start on the same frame, and DevTools → Animations shows a
  decelerating curve.
