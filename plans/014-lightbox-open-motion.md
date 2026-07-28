# 014 — Give the work lightbox an entrance instead of appearing in one frame

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: LOW (missed opportunity — additive)
- **Category**: Missed opportunities (AUDIT §8), Physicality (AUDIT §3)
- **Estimated scope**: 2 files (`resources/js/work-lightbox.js`, `resources/css/pages.css`), ~40 lines

## Problem

```js
/* resources/js/work-lightbox.js:49-70 — current */
  function open(payload, start = 0) {
    items = payload;
    index = start >= 0 && start < payload.length ? start : 0;
    lastFocused = document.activeElement;
    // Portal to <body>, same as the quote modal. The lightbox is rendered inside a
    // <section>, so any ancestor creating a stacking context (transform, will-change,
    // isolation, opacity) would trap it however high its z-index is.
    if (box.parentElement !== document.body) document.body.appendChild(box);
    box.hidden = false;
    document.body.style.overflow = 'hidden';
    render();
    closeBtn.focus();
  }

  function close() {
    box.hidden = true;
    document.body.style.overflow = '';
    stage.innerHTML = ''; // stops video + unloads the iframe
    caption.textContent = '';
    if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    lastFocused = null;
  }
```

```css
/* resources/css/pages.css:5018-5026 — current */
.wlb {
  /* Must sit above every other layer: nav 100, cookies 10400, quote 10500,
     curtain 10000, preloader 11000, skip-link 12000. An open media viewer is
     the topmost thing on the page. */
  position: fixed; inset: 0; z-index: 13000;
  display: grid; grid-template-columns: 64px 1fr 64px; align-items: center;
  background: rgba(0,0,0,0.92);
}
.wlb[hidden] { display: none; }
```

Clicking a work tile replaces the entire viewport with a near-black overlay in a
single frame. There is no transition of any kind — not even a fade. Everything
else on this site that covers the screen animates: the quote modal
(`resources/css/pages.css:1743`), the mobile menu (`resources/css/core.css:527`),
the cookie banner (`resources/css/pages.css:4660`), the page curtain
(`resources/css/core.css:98`). The lightbox is the one full-screen surface that
does not, and it is the one users hit most on the portfolio.

Two of AUDIT §8's cases apply at once:

- *"State changes that teleport … where a brief transition would prevent a
  jarring change."* A 92%-opaque black rectangle arriving instantly is the most
  jarring change on the site.
- *"Spatially-connected UI (a panel that appears from a trigger) with no motion
  explaining where it came from."* The lightbox is born from a specific tile the
  user just clicked, and shows no relationship to it.

The second is worth being honest about: a true shared-element transition from the
tile to the enlarged image is a substantial piece of work (measure the tile, clone
it, FLIP it into place, swap in the full-resolution source) and would be fragile
across images, videos and YouTube iframes. It is not what this plan does. A
backdrop fade plus a subtle scale-up on the stage gives most of the benefit for a
fraction of the risk.

## Target

Animate the backdrop and the stage independently, driven by an `is-open` class
so the `hidden` attribute can be applied after the exit finishes.

```css
/* target — resources/css/pages.css:5018-5026 */
.wlb {
  /* Must sit above every other layer: nav 100, cookies 10400, quote 10500,
     curtain 10000, preloader 11000, skip-link 12000. An open media viewer is
     the topmost thing on the page. */
  position: fixed; inset: 0; z-index: 13000;
  display: grid; grid-template-columns: 64px 1fr 64px; align-items: center;
  background: rgba(0,0,0,0.92);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s var(--ease-out);
}
.wlb.is-open { opacity: 1; pointer-events: auto; }
.wlb[hidden] { display: none; }

/* The media scales up slightly behind the backdrop fade, so the image reads as
   arriving rather than cutting in. scale(0.96) — never scale(0) (AUDIT §3). */
.wlb__stage {
  transform: scale(0.96);
  transition: transform 0.28s var(--ease-out);
}
.wlb.is-open .wlb__stage { transform: scale(1); }

@media (prefers-reduced-motion: reduce) {
  .wlb__stage, .wlb.is-open .wlb__stage { transform: none; }
}
```

Note `.wlb__stage` already has `display: grid; place-items: center; max-height:
82vh;` at `resources/css/pages.css:5027` — keep that rule and add the transform
declarations to it rather than creating a second rule, or place the new rule
immediately after it. Either is fine; do not duplicate the existing declarations.

```js
/* target — resources/js/work-lightbox.js:49-70 */
  const EXIT_MS = 200;
  let closeTimer = 0;

  function open(payload, start = 0) {
    items = payload;
    index = start >= 0 && start < payload.length ? start : 0;
    lastFocused = document.activeElement;
    // Portal to <body>, same as the quote modal. The lightbox is rendered inside a
    // <section>, so any ancestor creating a stacking context (transform, will-change,
    // isolation, opacity) would trap it however high its z-index is.
    if (box.parentElement !== document.body) document.body.appendChild(box);
    clearTimeout(closeTimer);
    box.hidden = false;
    document.body.style.overflow = 'hidden';
    render();
    // Two frames: the element must be painted at opacity 0 / scale(0.96) before
    // the class flips, or the transition is skipped entirely.
    requestAnimationFrame(() => requestAnimationFrame(() => box.classList.add('is-open')));
    closeBtn.focus();
  }

  function close() {
    if (!box.classList.contains('is-open') && box.hidden) return;
    box.classList.remove('is-open');
    document.body.style.overflow = '';
    // Focus returns immediately — never make a keyboard user wait out an animation.
    if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    lastFocused = null;
    clearTimeout(closeTimer);
    closeTimer = setTimeout(() => {
      box.hidden = true;
      stage.innerHTML = ''; // stops video + unloads the iframe
      caption.textContent = '';
    }, EXIT_MS);
  }
```

Two details that matter:

- **Focus returns synchronously**, before the exit animation. AUDIT §1's spirit
  and basic keyboard etiquette: never delay a focus change behind a transition.
- **`stage.innerHTML = ''` is deferred to the end of the exit**, so the media does
  not vanish while the overlay is still fading. The `clearTimeout` in `open()`
  handles reopening mid-exit — without it, a fast reopen would have its content
  wiped by the pending timer.

The navigation between items (`step()` at `resources/js/work-lightbox.js:72`)
stays instant. Arrow keys are a high-frequency, keyboard-initiated action —
AUDIT §1 says those never animate.

## Repo conventions to follow

- `--ease-out` comes from plan `004`. **Run plan 004 first.**
- The `is-open` + `pointer-events` + `opacity` overlay pattern is exactly how the
  quote modal already works — copy it from `resources/css/pages.css:1714-1718`:
  ```css
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.4s var(--ease);
  ```
  and `:1719` — `.quote.is-open { opacity: 1; pointer-events: auto; }`.
- The double-rAF-before-class-flip idiom is already used at
  `resources/js/core.js:112` and `:1073`.
- `work-lightbox.js` is the one ES module in the JS layer; it exports
  `initWorkLightbox` and is imported at `resources/js/core.js:7`. Keep that shape.

## Steps

1. `resources/css/pages.css:5018-5026` — add `opacity`, `pointer-events` and the
   `transition` to `.wlb`, and add the `.wlb.is-open` rule. Keep the existing
   comment and `.wlb[hidden]`.
2. `resources/css/pages.css:5027` — add the `transform` and `transition`
   declarations to the existing `.wlb__stage` rule, then add
   `.wlb.is-open .wlb__stage { transform: scale(1); }` after it.
3. Add the `@media (prefers-reduced-motion: reduce)` block after those rules.
4. `resources/js/work-lightbox.js` — add `const EXIT_MS = 200;` and
   `let closeTimer = 0;` near the other module-level state (beside
   `let lastFocused = null;` at line 12), then replace `open()` and `close()`
   with the target versions.
5. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT implement a shared-element / FLIP transition from the clicked tile. Out
  of scope — see the note above.
- Do NOT animate `step()` (prev/next). Arrow keys and the nav buttons are
  high-frequency; they must stay instant (AUDIT §1).
- Do NOT change the focus trap (`resources/js/work-lightbox.js:87-105`) or
  `focusableEls()`.
- Do NOT delay the focus return in `close()` behind the exit — it must be
  synchronous.
- Do NOT change `render()` or the media-type branching.
- Do NOT change `.wlb`'s `z-index`, `grid-template-columns` or background colour.
- Do NOT remove `box.hidden` — it is what unloads the iframe and it must still be
  applied, just later.
- If `resources/js/work-lightbox.js:57` does not read `    box.hidden = false;`,
  the file has drifted since commit 8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
- **Feel check**: open the works page and
  - Click a tile. The overlay must fade up over ~200ms with the media scaling
    from 96% to 100%, rather than cutting in. At 10% playback in DevTools →
    Animations, confirm the stage grows — and confirm it starts at 0.96, not 0.
  - Press Escape. The overlay must fade out, and **focus must return to the tile
    immediately**, not after 200ms. Test by pressing Escape then immediately
    pressing Enter — it should reopen the same tile.
  - Open a tile containing a **video**, close it, and confirm the video stops.
    Then open a **YouTube** tile, close it, and confirm the iframe is unloaded
    (Network panel: no continuing requests; Elements: `.wlb__stage` is empty).
    This is the check that the deferred `stage.innerHTML = ''` still works.
  - Open a tile, close it, and **reopen within 200ms** (before the exit
    finishes). The media must be present and correct — not blank. This is the
    check that `clearTimeout(closeTimer)` in `open()` works.
  - Click the backdrop to close: same behaviour as Escape.
  - Inside an open lightbox press Left/Right repeatedly. Items must swap
    **instantly** with no fade — if they animate, something was added to `step()`
    that should not have been.
  - Toggle `prefers-reduced-motion: reduce`: the overlay may fade, but the stage
    must not scale.
  - Confirm the page behind cannot be scrolled while open, and can be again after
    close.
- **Done when**: the lightbox fades in and out over ~200ms, focus returns
  synchronously on close, media is fully unloaded after the exit completes, and a
  close-then-immediately-reopen shows the correct content.
