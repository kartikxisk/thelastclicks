# 001 — Cut the dead time the curtain and preloader add to every page load

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: HIGH
- **Category**: Purpose & frequency (AUDIT §1), Easing & duration (AUDIT §2)
- **Estimated scope**: 2 files (`resources/js/core.js`, `resources/css/core.css`), ~40 lines

## Problem

Two full-screen overlays run back-to-back on every single navigation, and neither
is waiting on real work.

**A. The curtain blocks navigation for 800ms before the request even starts.**

```js
/* resources/js/core.js:279-284 — current */
  const curtain = document.querySelector('.curtain');
  function curtainOut(href) {
    if (!curtain) { window.location.href = href; return; }
    curtain.classList.remove('is-out');
    curtain.classList.add('is-in');
    setTimeout(() => { window.location.href = href; }, 800);
  }
```

Every internal `<a>` click is intercepted and routed through this
(`resources/js/core.js:299-308`). The 800ms elapses *before* the browser is told
to navigate, so the network request, the server render and the new page's paint
all happen *after* it. Nav links are hit tens of times per session — AUDIT §1
puts that in "remove or drastically reduce".

The 800ms does not even achieve its own goal. The panels take 0.7s to travel and
the last one starts 0.25s late (`resources/css/core.css:100`, `:111`), so full
coverage lands at 950ms — 150ms *after* navigation is triggered:

```css
/* resources/css/core.css:98-101, 106-111 — current */
.curtain.is-in .curtain__panel {
  transform: translateY(0);
  transition: transform 0.7s var(--ease-3);
}
.curtain.is-in .curtain__panel:nth-child(1) { transition-delay: 0.00s; }
/* … */
.curtain.is-in .curtain__panel:nth-child(6) { transition-delay: 0.25s; }
```

**B. The preloader adds ~2.3s of fake progress on top, on every page.**

```js
/* resources/js/core.js:328-344 — current */
    if (preBar) {
      const dur = 1100;
      const start = performance.now();
      function step(now) {
        const t = Math.min(1, (now - start) / dur);
        preBar.style.setProperty('--p', t);
        if (t < 1) requestAnimationFrame(step);
        else {
          clearTimeout(hardKill);
          setTimeout(() => {
            pre.classList.add('is-done');
            setTimeout(() => pre.remove(), 1000);
          }, 150);
        }
      }
      requestAnimationFrame(step);
    }
```

```css
/* resources/css/core.css:141-150 — current */
.preloader {
  position: fixed;
  inset: 0;
  background: var(--ink);
  z-index: 11000;
  display: grid;
  place-items: center;
  transition: transform 0.9s var(--ease-3) 0.2s;
}
.preloader.is-done { transform: translateY(-100%); }
```

The bar is a `performance.now()` timer, not load progress — it reports 40% when
the page is fully interactive and 40% when it is still fetching. Total: 1100ms
bar + 150ms pause + 200ms `transition-delay` + 900ms exit = **2350ms** before
content is visible. Add the curtain and a click costs roughly 3 seconds of
motion for zero information.

The exit curve is also wrong: `--ease-3` is `cubic-bezier(0.85, 0, 0.15, 1)`, an
ease-in-out. An exiting element must be `ease-out` (AUDIT §2) — as written it
creeps for the first third of the 900ms.

## Target

Keep both overlays — they do mask a real white flash between documents — but
make them cost a quarter of what they cost now, and make the preloader
first-visit-only.

**A. Curtain: cover in 260ms, then navigate.**

```css
/* target — resources/css/core.css, replacing lines 98-117 */
.curtain.is-in .curtain__panel {
  transform: translateY(0);
  transition: transform 0.22s var(--ease-out);
}
.curtain.is-out .curtain__panel {
  transform: translateY(-100%);
  transition: transform 0.3s var(--ease-out);
}
.curtain.is-in .curtain__panel:nth-child(1) { transition-delay: 0.00s; }
.curtain.is-in .curtain__panel:nth-child(2) { transition-delay: 0.008s; }
.curtain.is-in .curtain__panel:nth-child(3) { transition-delay: 0.016s; }
.curtain.is-in .curtain__panel:nth-child(4) { transition-delay: 0.024s; }
.curtain.is-in .curtain__panel:nth-child(5) { transition-delay: 0.032s; }
.curtain.is-in .curtain__panel:nth-child(6) { transition-delay: 0.04s; }
.curtain.is-out .curtain__panel:nth-child(1) { transition-delay: 0.00s; }
.curtain.is-out .curtain__panel:nth-child(2) { transition-delay: 0.02s; }
.curtain.is-out .curtain__panel:nth-child(3) { transition-delay: 0.04s; }
.curtain.is-out .curtain__panel:nth-child(4) { transition-delay: 0.06s; }
.curtain.is-out .curtain__panel:nth-child(5) { transition-delay: 0.08s; }
.curtain.is-out .curtain__panel:nth-child(6) { transition-delay: 0.10s; }
```

Full coverage now lands at 220 + 40 = 260ms. Navigate at 260ms, not 800ms:

```js
/* target — resources/js/core.js */
  function curtainOut(href) {
    if (!curtain) { window.location.href = href; return; }
    curtain.classList.remove('is-out');
    curtain.classList.add('is-in');
    setTimeout(() => { window.location.href = href; }, 260);
  }
```

The `.curtain__mark` reveal (`resources/css/core.css:127-130`) has a 0.4s delay
and can never be seen inside 260ms — delete that rule and the `.curtain__mark`
opacity transition with it.

**B. Preloader: first visit per tab only, driven by the real `load` event.**

```js
/* target — resources/js/core.js, replacing lines 317-345 */
  const pre = document.querySelector('.preloader');
  const preBar = document.querySelector('.preloader__bar');
  if (pre) {
    // Second and later navigations in this tab get no preloader — the assets are
    // cached and the curtain already covers the swap.
    if (sessionStorage.getItem('tlc-preloaded')) {
      pre.remove();
    } else {
      sessionStorage.setItem('tlc-preloaded', '1');
      const MAX = 900;   // hard ceiling; the bar never outlives this
      const start = performance.now();
      let loaded = document.readyState === 'complete';
      window.addEventListener('load', () => { loaded = true; }, { once: true });

      function finish() {
        pre.classList.add('is-done');
        setTimeout(() => pre.remove(), 400);
      }
      function step(now) {
        const elapsed = now - start;
        // Ease toward 90% while loading; snap to 100% once `load` has fired.
        const t = loaded
          ? Math.min(1, elapsed / MAX + 0.35)
          : Math.min(0.9, elapsed / MAX);
        if (preBar) preBar.style.setProperty('--p', t);
        if (t < 1 && elapsed < MAX) requestAnimationFrame(step);
        else {
          if (preBar) preBar.style.setProperty('--p', 1);
          finish();
        }
      }
      requestAnimationFrame(step);
    }
  }
```

```css
/* target — resources/css/core.css:141-150 */
.preloader {
  position: fixed;
  inset: 0;
  background: var(--ink);
  z-index: 11000;
  display: grid;
  place-items: center;
  transition: transform 0.4s var(--ease-out);
}
.preloader.is-done { transform: translateY(-100%); }
```

Worst case is now 900ms + 400ms exit on the very first page of a session, and
zero on every page after it.

The logo entrance (`resources/css/core.css:161-167`) must shrink to fit inside
the shorter window:

```css
/* target — resources/css/core.css:156-167 */
.preloader__logo {
  height: clamp(84px, 12vw, 150px);
  width: auto;
  display: block;
  margin: 0 auto;
  opacity: 0;
  transform: scale(0.94);
  animation: preLogoIn 0.35s var(--ease-out) forwards;
}
@keyframes preLogoIn {
  to { opacity: 1; transform: scale(1); }
}
```

## Repo conventions to follow

- Easing tokens live in the `:root` blocks of `resources/css/core.css`
  (lines 29-31 and 1126-1132). **This plan depends on plan `004` having run
  first**, which adds `--ease-out` there. If `--ease-out` is not yet defined in
  `resources/css/core.css`, STOP and run plan 004 first.
- Durations are written in seconds with a leading `0.` (`0.22s`, not `220ms`) —
  match that everywhere in CSS.
- All JS lives inside the single IIFE in `resources/js/core.js`; do not add
  modules or exports.
- Exemplar of a correctly-timed exiting overlay already in the repo:
  `resources/css/pages.css:4660` — `.cookies { transition: transform 0.7s
  var(--ease-soft), opacity 0.5s var(--ease-soft); }`. `--ease-soft` is
  `cubic-bezier(0.32, 0.72, 0, 1)`, a drawer curve, and it is applied to a panel
  that slides rather than to a full-screen blocker.

## Steps

1. In `resources/css/core.css`, replace lines 98-117 (`.curtain.is-in
   .curtain__panel` through the last `.curtain.is-out … nth-child(6)` delay) with
   the target block above.
2. In `resources/css/core.css`, delete lines 127-130 — the
   `.curtain.is-in .curtain__mark { opacity: 1; transition: opacity 0.4s
   var(--ease) 0.4s; }` rule. Leave `.curtain__mark` itself (lines 119-126) and
   `.curtain__mark span` (131-138) in place; the mark simply stays at `opacity: 0`.
3. In `resources/js/core.js:283`, change `800` to `260`.
4. In `resources/css/core.css:148`, change the `.preloader` transition to
   `transform 0.4s var(--ease-out)`.
5. In `resources/css/core.css:163`, change the `.preloader__logo` animation
   duration from `0.8s` to `0.35s` and its curve from `var(--ease-soft)` to
   `var(--ease-out)`.
6. In `resources/js/core.js`, replace the whole preloader block (lines 317-345,
   from `const pre = document.querySelector('.preloader');` through the closing
   `}` of `if (pre) {`) with the target JS above. The `hardKill` timeout is
   deleted — the `MAX` ceiling inside `step()` replaces it.
7. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT remove the curtain or the preloader elements from
  `resources/js/chrome.js` — the markup stays.
- Do NOT touch `curtainIn()` (`resources/js/core.js:285-297`) or the `pageshow`
  wiring below it. Only `curtainOut`'s timeout changes.
- Do NOT touch the link interception logic at `resources/js/core.js:299-308`.
- Do NOT change `--ease-3`'s value — plan 004 owns the easing tokens.
- Do NOT add dependencies, and do NOT introduce a view-transition or SPA router.
- If `resources/js/core.js:283` does not read `}, 800);` when you open it, the
  file has drifted since commit 8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0. `grep -n "800" resources/js/core.js`
  no longer matches inside `curtainOut`.
- **Feel check**: run the site, then
  - Click a nav link. The red panels must fully cover the viewport *before* the
    page changes — no sliver of the old page visible at the moment of swap — and
    the whole cover must read as a quarter-second, not a beat.
  - Click three nav links in a row. Each transition should feel like a wipe, not
    a wait.
  - Hard-reload with a cold session (new tab or clear `sessionStorage`): the
    preloader shows once, its bar reaches 100% at or before the moment content is
    ready, and it exits upward without creeping at the start.
  - Navigate to a second page in that same tab: **no preloader at all**.
  - In DevTools → Animations, set playback to 10% and confirm the preloader exit
    starts fast and decelerates (ease-out), rather than easing in from a standstill.
  - Toggle `prefers-reduced-motion: reduce` (DevTools → Rendering): the curtain
    and preloader are `display: none` (`resources/css/core.css:1296-1297`) and
    navigation must be immediate — verify a nav click still works and does not
    hang for 260ms with a blank screen.
- **Done when**: the wall-clock gap between clicking an internal link and the
  browser starting the request is ≤300ms (measure in DevTools → Network: the
  document request's start time relative to the click), and a second navigation
  in the same tab shows no preloader.
