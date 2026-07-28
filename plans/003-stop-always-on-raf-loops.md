# 003 — Stop two always-on rAF loops, one of which forces layout every frame

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: HIGH
- **Category**: Performance (AUDIT §5)
- **Estimated scope**: 1 file (`resources/js/core.js`), ~70 lines

## Problem

**A. The main loop never stops, and reads a layout property every frame.**

```js
/* resources/js/core.js:31-38 — current */
  let scrollY = 0;
  function tick() {
    scrollY = window.scrollY;
    updateScrollbar();
    updateParallax();
    updateMagnetics();
    requestAnimationFrame(tick);
  }
```

```js
/* resources/js/core.js:44-51 — current */
  const sbFill = document.querySelector('.scrollbar__fill');
  function updateScrollbar() {
    if (!sbFill) return;
    const max = (document.body.scrollHeight - vh) || 1;
    const p = Math.min(100, (scrollY / max) * 100);
    sbFill.style.setProperty('--p', p + '%');
  }
```

`document.body.scrollHeight` is a layout-dependent read. It runs on every frame
of a loop that never exits, and it runs *after* the previous frame wrote inline
transforms in `updateMagnetics()` — so the style tree is dirty and the read
forces a synchronous style recalc + layout, 60 times a second, forever, on every
page of the site.

`sbFill.style.setProperty('--p', …)` also writes every frame whether or not the
value changed, invalidating style on the element each time.

Worse, `.scrollbar` is hidden entirely on touch and narrow screens, yet the loop
keeps measuring and writing for it:

```css
/* resources/css/core.css:1098-1102 — current */
/* A scroll-progress rail is a desktop affordance. On touch it just sits over the
   content next to the OS scrollbar, so hide it on coarse pointers / small screens. */
@media (hover: none), (max-width: 700px) {
  .scrollbar { display: none; }
}
```

`updateMagnetics()` writes a transform for every registered magnet every frame,
including magnets sitting perfectly at rest:

```js
/* resources/js/core.js:70-76 — current */
  function updateMagnetics() {
    for (const m of magnets) {
      m.cx += (m.tx - m.cx) * 0.18;
      m.cy += (m.ty - m.cy) * 0.18;
      m.el.style.transform = `translate3d(${m.cx}px, ${m.cy}px, 0)`;
    }
  }
```

`updateParallax()` iterates `parallaxEls`, which is a hardcoded empty array
(`resources/js/core.js:80`) — the feature was disabled but the per-frame call
was left in.

And the `reduce` / `isCoarse` guard does not guard anything:

```js
/* resources/js/core.js:347-353 — current */
  if (!isCoarse && !reduce) {
    requestAnimationFrame(tick);
  } else {
    // Still tick parallax/magnet/scrollbar via scroll listener
    requestAnimationFrame(tick);
  }
```

Both branches start the identical loop. A user with `prefers-reduced-motion:
reduce` on a phone gets the same per-frame forced layout as everyone else.

**B. A second infinite loop runs on every page for the portfolio hover preview.**

```js
/* resources/js/core.js:380-386 — current */
    function pTick() {
      pcx += (ptx - pcx) * 0.16;
      pcy += (pty - pcy) * 0.16;
      previewBox.style.transform = `translate3d(${pcx}px, ${pcy}px, 0) translate(-50%, -50%)`;
      requestAnimationFrame(pTick);
    }
    pTick();
```

It is guarded on `.hover-preview` existing — but `resources/js/chrome.js:231`
injects `<div class="hover-preview">` into **every** page as part of the shared
chrome. So this loop writes a transform to a hidden element 60 times a second on
the contact page, the privacy policy, everywhere, whether or not any
`[data-preview]` element exists.

## Target

Three independent loops, each running only while it has something to do.

```js
/* target — resources/js/core.js, replacing lines 31-51 and 347-353 */

  /* -------------------- Scroll-driven state -------------------- */
  let scrollY = 0;

  const sbFill = document.querySelector('.scrollbar__fill');
  // The rail is display:none on coarse pointers and narrow screens
  // (core.css:1100) — never measure or write for an element that isn't painted.
  const sbVisible = sbFill && !matchMedia('(hover: none), (max-width: 700px)').matches;
  // scrollHeight is a layout read: cache it and refresh only when the document
  // actually resizes, never inside the frame loop.
  let docHeight = document.documentElement.scrollHeight;
  let lastP = -1;

  function measureDoc() {
    docHeight = document.documentElement.scrollHeight;
    vh = window.innerHeight;
  }

  function updateScrollbar() {
    if (!sbVisible) return;
    const max = (docHeight - vh) || 1;
    const p = Math.min(100, (scrollY / max) * 100);
    // Sub-pixel churn isn't visible on a 2px rail — skip the style write.
    if (Math.abs(p - lastP) < 0.1) return;
    lastP = p;
    sbFill.style.setProperty('--p', p + '%');
  }

  let scrollFrame = 0;
  function onScroll() {
    scrollY = window.scrollY;
    if (scrollFrame) return;
    scrollFrame = requestAnimationFrame(() => {
      scrollFrame = 0;
      updateScrollbar();
    });
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', () => { measureDoc(); onScroll(); });
  if (document.querySelector('.smooth-content')) {
    new ResizeObserver(measureDoc).observe(document.querySelector('.smooth-content'));
  }
  measureDoc();
  onScroll();
```

```js
/* target — resources/js/core.js, replacing lines 53-76 */

  /* -------------------- Magnetic elements -------------------- */
  // The loop runs only while a magnet is displaced; it stops once every magnet
  // has settled back to 0,0 (within half a pixel).
  const magnets = [];
  let magnetFrame = 0;

  function updateMagnetics() {
    let moving = false;
    for (const m of magnets) {
      m.cx += (m.tx - m.cx) * 0.18;
      m.cy += (m.ty - m.cy) * 0.18;
      if (Math.abs(m.tx - m.cx) > 0.5 || Math.abs(m.ty - m.cy) > 0.5) {
        moving = true;
        m.el.style.transform = `translate3d(${m.cx}px, ${m.cy}px, 0)`;
      } else if (!m.settled) {
        m.settled = true;
        m.el.style.transform = `translate3d(${m.tx}px, ${m.ty}px, 0)`;
      }
    }
    magnetFrame = moving ? requestAnimationFrame(updateMagnetics) : 0;
  }

  function wakeMagnets() {
    if (!magnetFrame) magnetFrame = requestAnimationFrame(updateMagnetics);
  }

  document.querySelectorAll('[data-magnetic]').forEach(el => {
    const m = { el, tx: 0, ty: 0, cx: 0, cy: 0, hovered: false, settled: true };
    magnets.push(m);
    el.addEventListener('mouseenter', () => { m.hovered = true; });
    el.addEventListener('mouseleave', () => {
      m.hovered = false;
      m.tx = 0; m.ty = 0; m.settled = false;
      wakeMagnets();
    });
    el.addEventListener('mousemove', e => {
      const r = el.getBoundingClientRect();
      m.tx = (e.clientX - (r.left + r.width / 2)) * 0.25;
      m.ty = (e.clientY - (r.top + r.height / 2)) * 0.25;
      m.settled = false;
      wakeMagnets();
    });
  });
```

Note the `magnets.find(m => m.el === el)` lookups in the current handlers are
gone too — each listener closes over its own `m` instead of linear-searching the
array on every `mousemove`.

```js
/* target — resources/js/core.js, replacing lines 364-387 */

  /* -------------------- Hover image preview (portfolio) -------------------- */
  const previewBox = document.querySelector('.hover-preview');
  const previewItems = document.querySelectorAll('[data-preview]');
  // chrome.js injects .hover-preview on every page; only run where there is
  // something to preview.
  if (previewBox && previewItems.length) {
    let pcx = 0, pcy = 0, ptx = 0, pty = 0, pFrame = 0;

    function pTick() {
      pcx += (ptx - pcx) * 0.16;
      pcy += (pty - pcy) * 0.16;
      previewBox.style.transform = `translate3d(${pcx}px, ${pcy}px, 0) translate(-50%, -50%)`;
      // Keep going while visible or still catching up to the cursor.
      pFrame = (previewBox.classList.contains('is-on')
        || Math.abs(ptx - pcx) > 0.5 || Math.abs(pty - pcy) > 0.5)
        ? requestAnimationFrame(pTick)
        : 0;
    }
    function wakePreview() { if (!pFrame) pFrame = requestAnimationFrame(pTick); }

    previewItems.forEach(it => {
      it.addEventListener('mouseenter', () => {
        // No image = no ghost panel. Showing an empty framed box reads as broken.
        const src = it.dataset.preview;
        if (!src) return;
        previewBox.querySelector('img').src = src;
        previewBox.classList.add('is-on');
        wakePreview();
      });
      it.addEventListener('mouseleave', () => {
        previewBox.classList.remove('is-on');
        wakePreview();
      });
      it.addEventListener('mousemove', e => {
        ptx = e.clientX; pty = e.clientY;
        wakePreview();
      });
    });
  }
```

Finally, delete the dead parallax code and the no-op guard entirely.

## Repo conventions to follow

- Everything lives inside the single IIFE in `resources/js/core.js`. Do not add
  modules, exports or new files.
- rAF-coalesced scroll handlers are already the house pattern — copy the shape
  from `resources/js/core.js:117-121`:
  ```js
  let revealScrollFrame = 0;
  window.addEventListener('scroll', () => {
    cancelAnimationFrame(revealScrollFrame);
    revealScrollFrame = requestAnimationFrame(forceRevealVisible);
  }, { passive: true });
  ```
  and `resources/js/core.js:596-600` (`syncFrame` in the testimonials carousel).
- Self-terminating rAF loops that restart on input are also already in the repo —
  `resources/js/core.js:1000-1015` (hero tilt) is the exemplar:
  `if (…still moving…) hraf = requestAnimationFrame(heroTilt); else hraf = 0;`
  and `if (!hraf) hraf = requestAnimationFrame(heroTilt);` in the listener.
  Match that structure.
- Scroll listeners are always `{ passive: true }` in this file.

## Steps

1. In `resources/js/core.js`, delete lines 31-38 (`let scrollY = 0;` through the
   closing `}` of `function tick()`).
2. Delete lines 44-51 (the `sbFill` / `updateScrollbar` block) and insert the
   target "Scroll-driven state" block in its place, keeping the
   `/* -------------------- Scrollbar fill -------------------- */` banner
   renamed to `/* -------------------- Scroll-driven state -------------------- */`.
3. Replace lines 53-76 (the `/* Magnetic elements */` banner through the closing
   `}` of `updateMagnetics`) with the target magnetics block.
4. Delete lines 78-88 entirely — the `/* Parallax (scroll-linked) */` banner,
   `const parallaxEls = [];` and `function updateParallax()`. The feature is
   already disabled (the array is permanently empty) and nothing else references
   it after step 1.
5. Delete lines 347-353 — the whole `/* -------------------- Start engine
   -------------------- */` block including its banner comment. There is no
   longer a `tick` to start.
6. Replace lines 364-387 (the `/* Hover image preview (portfolio) */` banner
   through `pTick();`) with the target preview block.
7. Confirm `isCoarse` (line 11) is still referenced elsewhere — it is, at lines
   406, 425, 796 and 999 — so leave the declaration. Same for `reduce` (line 12).
8. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT touch the `setHeight` / `ResizeObserver` block at lines 21-29, or the
  native-scroll reset at lines 39-42. They run once at init and are correct.
- Do NOT touch the IntersectionObserver reveal system (lines 90-121).
- Do NOT touch the hero tilt loop (lines 997-1016) or the beliefs progress rail
  (lines 889-908) — both already self-terminate or are scroll-coalesced.
- Do NOT change any CSS in this plan. `.scrollbar`'s media query stays as is.
- Do NOT remove `.hover-preview` from `resources/js/chrome.js`.
- If `resources/js/core.js:47` does not read
  `const max = (document.body.scrollHeight - vh) || 1;`, the file has drifted
  since commit 8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
  `grep -n "requestAnimationFrame(tick)" resources/js/core.js` returns nothing.
  `grep -n "updateParallax\|parallaxEls" resources/js/core.js` returns nothing.
  `grep -n "document.body.scrollHeight" resources/js/core.js` returns nothing.
- **Feel check**: open the homepage and
  - **Idle cost.** Open DevTools → Performance, record 5 seconds with the mouse
    completely still and the page not scrolling. Before this change the timeline
    shows a continuous band of scripting + "Recalculate Style" / "Layout" at
    ~60fps. After, the main thread must be **flat** — zero rAF callbacks while
    idle. This is the primary check.
  - Scroll the page: the red rail on the right must still fill in step with the
    scroll, with no lag and no stutter.
  - Hover the "Get a Quote" button in the nav (`[data-magnetic]`) and move around
    inside it — it must still pull toward the cursor, and must glide back to
    centre when you leave. Record Performance during this: the loop appears while
    moving and disappears within ~1s of the cursor leaving.
  - Go to the portfolio / works page, hover a `[data-preview]` item — the floating
    preview must still track the cursor smoothly.
  - Load the privacy policy page (no `[data-preview]` anywhere) and record 5
    seconds idle — flat main thread, no preview loop.
  - Resize the window, then scroll to the very bottom: the rail must read 100%,
    not overshoot or stop short (this proves the cached `docHeight` is being
    refreshed).
  - Load a page that grows after load (the blog index, where images settle): the
    rail must still reach 100% at the bottom.
- **Done when**: a 5-second idle Performance recording on any page shows no
  recurring rAF callback, and the scroll rail, magnetic buttons and hover preview
  all behave exactly as before.
