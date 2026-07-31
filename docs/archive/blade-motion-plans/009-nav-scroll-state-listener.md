# 009 — Drive the nav's scrolled state from a scroll listener, not a 100ms poll

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: MEDIUM
- **Category**: Performance (AUDIT §5), Interruptibility (AUDIT §4)
- **Estimated scope**: 1 file (`resources/js/core.js`), ~25 lines

## Problem

```js
/* resources/js/core.js:193-210 — current */
  const nav = document.querySelector('.nav');
  // Transparent header while sitting over the hero OR a full-media page header;
  // solid once scrolled past it.
  const heroEl = document.querySelector('.hero');
  const pageHeaderEl = document.querySelector('.page-header--media');
  if (nav && (heroEl || pageHeaderEl)) nav.classList.add('over-hero');
  function navScroll() {
    if (!nav) return;
    // Over a pinned hero, use 0.75 viewport; over a page-header use its real height.
    let threshold = 30;
    if (nav.classList.contains('over-hero')) {
      threshold = heroEl ? window.innerHeight * 0.75 : Math.max(pageHeaderEl.offsetHeight - 80, 120);
    }
    if (scrollY > threshold) nav.classList.add('is-scrolled');
    else nav.classList.remove('is-scrolled');
  }
  setInterval(navScroll, 100);
```

Three problems:

1. **The state can lag scroll by up to 100ms.** The header goes from transparent
   over the hero to a solid blurred bar; that swap is a 400ms transition
   (`resources/css/core.css:259`) that can start as much as a tenth of a second
   after the user has crossed the threshold. On a fast flick past the hero the
   nav visibly changes *after* the hero has left — the causal link between the
   scroll and the response is broken. AUDIT §4: the system's response to an
   input should snap.

2. **`pageHeaderEl.offsetHeight` is a layout read, ten times a second, forever.**
   On any page with a `.page-header--media` the interval forces a layout flush
   every 100ms for the entire session, whether or not the page has scrolled a
   pixel. `window.innerHeight` on the hero branch is cheaper but still a
   per-tick read of a value that only changes on resize.

3. **The interval never stops.** It runs in a background tab, it runs when the
   document is not scrollable, it runs when `nav` is null (the `if (!nav)
   return;` guard runs 10× a second to do nothing).

`scrollY` is also a module-level variable assigned inside the rAF loop
(`resources/js/core.js:33`), which plan `003` removes — after that change this
function reads a stale value. **This plan must run after plan 003**, and it
takes over ownership of `scrollY` for the nav.

## Target

Compute the threshold once (and on resize), then evaluate it on scroll, coalesced
into a frame.

```js
/* target — resources/js/core.js:193-210, replacing the whole block */

  /* -------------------- Nav scroll state -------------------- */
  const nav = document.querySelector('.nav');
  // Transparent header while sitting over the hero OR a full-media page header;
  // solid once scrolled past it.
  const heroEl = document.querySelector('.hero');
  const pageHeaderEl = document.querySelector('.page-header--media');
  if (nav && (heroEl || pageHeaderEl)) nav.classList.add('over-hero');

  if (nav) {
    // Layout reads happen here only — on init and on resize, never per scroll.
    let navThreshold = 30;
    function measureNavThreshold() {
      if (!nav.classList.contains('over-hero')) { navThreshold = 30; return; }
      navThreshold = heroEl
        ? window.innerHeight * 0.75
        : Math.max(pageHeaderEl.offsetHeight - 80, 120);
    }

    let navScrolled = null;
    function navScroll() {
      const next = window.scrollY > navThreshold;
      if (next === navScrolled) return;   // no class write unless the state flips
      navScrolled = next;
      nav.classList.toggle('is-scrolled', next);
    }

    let navFrame = 0;
    function onNavScroll() {
      if (navFrame) return;
      navFrame = requestAnimationFrame(() => { navFrame = 0; navScroll(); });
    }

    measureNavThreshold();
    navScroll();
    window.addEventListener('scroll', onNavScroll, { passive: true });
    window.addEventListener('resize', () => { measureNavThreshold(); navScroll(); });
  }
```

The state now flips on the same frame the threshold is crossed, no layout is read
while scrolling, and the class is only written when the value actually changes.

## Repo conventions to follow

- rAF-coalesced scroll handlers are the established pattern in this file —
  `resources/js/core.js:117-121`:
  ```js
  let revealScrollFrame = 0;
  window.addEventListener('scroll', () => {
    cancelAnimationFrame(revealScrollFrame);
    revealScrollFrame = requestAnimationFrame(forceRevealVisible);
  }, { passive: true });
  ```
  and `:596-600`, `:903-906`. Match the shape.
- All scroll listeners in this file are `{ passive: true }`.
- Everything stays inside the single IIFE; no new files, no exports.
- Banner comments use the form
  `/* -------------------- Title -------------------- */`.

## Steps

1. Confirm plan `003` has run: `grep -n "setInterval(navScroll" resources/js/core.js`
   should still match (this plan removes it), and
   `grep -n "requestAnimationFrame(tick)" resources/js/core.js` should return
   nothing (plan 003 removed the loop that owned `scrollY`). If the `tick` loop
   is still present, STOP and run plan 003 first.
2. In `resources/js/core.js`, replace lines 193-210 with the target block.
3. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT change any CSS. `.nav`'s `transition: padding 0.4s var(--ease),
  background 0.4s var(--ease)` (`resources/css/core.css:259`) stays exactly as it
  is. The header shrinking on scroll is deliberate design, it fires at most twice
  per scroll pass rather than per hover, and it is scoped to a `position: fixed`
  subtree — it is the one animated layout property in this codebase that earns
  its cost. Plan 005 explicitly excludes it for the same reason.
- Do NOT change `.nav.over-hero`, `.nav.is-scrolled` or any of their descendant
  rules (`resources/css/core.css:269-290`).
- Do NOT touch the mobile menu block (`resources/js/core.js:212-252`) or the
  active-link block (`:355-362`).
- Do NOT change the thresholds (`0.75` of viewport, `offsetHeight - 80`,
  `min 120`, `30`) — the behaviour must be identical, only its timing changes.
- Do NOT add a scroll-direction or hide-on-scroll-down behaviour. Out of scope.
- If `resources/js/core.js:210` does not read `  setInterval(navScroll, 100);`,
  the file has drifted since commit 8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
  `grep -n "setInterval" resources/js/core.js` returns only the clock at line 669
  (`setInterval(tick, 10000)`).
- **Feel check**: run the site and
  - On the homepage, scroll slowly down past 75% of the viewport height. The
    header must flip from transparent to the solid blurred bar the **instant**
    you cross it — put a finger on the trackpad and inch across the boundary,
    then back. Before this change there is a perceptible hitch of up to 100ms;
    after, the flip should track the scroll exactly.
  - Flick-scroll fast past the hero. The header must already be solid by the time
    the hero is off screen, not change afterwards.
  - Scroll back to the top: it must go transparent again on the same frame it
    crosses back.
  - Go to a page with `.page-header--media` (a service or industry page) and do
    the same — the threshold there is derived from the header's height, so verify
    the flip point still lines up with the bottom of that header.
  - Resize the window from wide to narrow while sitting just below the threshold,
    then scroll a pixel — the header must re-evaluate against the new viewport
    height, not the old one.
  - Go to a page with no hero and no media header (e.g. the privacy policy).
    The header must go solid after ~30px of scroll.
  - DevTools → Performance, record 5 seconds sitting completely still on a
    service page. There must be **no recurring 100ms task**. Before this change
    the timeline shows a regular tick with a Layout inside it.
- **Done when**: the transparent↔solid flip happens on the same frame as the
  threshold crossing on every page type, and a 5-second idle Performance
  recording shows no periodic nav task.
