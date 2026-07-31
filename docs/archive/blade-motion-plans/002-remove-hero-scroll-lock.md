# 002 — Stop locking scroll and swallowing the first keypress on the homepage hero

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: HIGH
- **Category**: Purpose & frequency (AUDIT §1), Performance (AUDIT §5)
- **Estimated scope**: 2 files (`resources/js/core.js`, `resources/css/pages.css`), ~55 lines

## Problem

The homepage disables scrolling on load and holds it until an animation finishes.

```js
/* resources/js/core.js:942-995 — current */
  const heroCenter = document.querySelector('.hero__center');
  if (heroCenter) {
    if (reduce) {
      heroCenter.style.setProperty('--hero-reveal', '1');
    } else {
      let played = false;
      const lock = () => {
        root.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
        window.scrollTo(0, 0);
      };
      const unlock = () => {
        root.style.overflow = '';
        document.body.style.overflow = '';
      };
      const removeIntent = () => {
        window.removeEventListener('wheel', onWheel);
        window.removeEventListener('keydown', onKey);
        window.removeEventListener('touchmove', onTouch);
      };
      function playReveal() {
        if (played) return;
        played = true;
        removeIntent();
        // rAF tween of --hero-reveal 0 → 1 with an easeOutCubic curve.
        const dur = 1100;
        const start = performance.now();
        const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);
        function step(now) {
          const t = Math.min(1, (now - start) / dur);
          heroCenter.style.setProperty('--hero-reveal', easeOutCubic(t).toFixed(4));
          if (t < 1) requestAnimationFrame(step);
          else unlock();
        }
        requestAnimationFrame(step);
      }
      const onWheel = (e) => { if (e.deltaY > 0) playReveal(); };
      const onKey = (e) => {
        if (['ArrowDown', 'PageDown', 'End', ' ', 'Spacebar'].includes(e.key)) playReveal();
      };
      const onTouch = () => playReveal();

      lock();
      window.addEventListener('wheel', onWheel, { passive: true });
      window.addEventListener('keydown', onKey);
      window.addEventListener('touchmove', onTouch, { passive: true });
      // Fallback — reveal (and release scroll) if no scroll intent arrives, so
      // the page can never get stuck locked.
      setTimeout(playReveal, 4000);
    }
  }
```

Four separate problems, in order of how badly they hurt:

1. **The user's first scroll does not scroll.** `lock()` sets `overflow: hidden`
   on both `<html>` and `<body>` before any input. The first wheel event is spent
   starting a 1100ms animation; the page only becomes scrollable when that
   animation ends. Someone who lands and immediately flicks down gets nothing for
   over a second.
2. **A keyboard action animates.** `ArrowDown`, `PageDown`, `End` and `Space` all
   trigger the reveal instead of moving the page. AUDIT §1 is unambiguous:
   keyboard-initiated actions never animate.
3. **Up to 4 seconds of a locked page if no input arrives** — the
   `setTimeout(playReveal, 4000)` fallback. A visitor who reads the hero for
   three seconds before reaching for the trackpad is looking at a page that
   cannot scroll.
4. **The reveal drives a transform through a CSS variable**, which AUDIT §5
   calls out directly. Every rAF frame writes a custom property, and the
   `calc()` consuming it re-resolves on the element and everything inheriting it:

```css
/* resources/css/pages.css:113-119 — current */
  /* Hidden on load; JS locks scroll then tweens --hero-reveal 0 → 1 (GSAP)
     when the first scroll intent arrives, releasing scroll when done. */
  --hero-reveal: 0;
  opacity: var(--hero-reveal);
  transform: translateY(calc((1 - var(--hero-reveal)) * 36px));
  will-change: opacity, transform;
```

(The comment says GSAP; there is no GSAP in this project. `package.json` has no
motion library at all.)

The animation itself is fine — a 36px rise with a fade is exactly right for a
hero. Nothing about it requires holding the page hostage.

## Target

Play the reveal on load as a plain CSS transition. Never touch `overflow`.

```css
/* target — resources/css/pages.css:101-119, replacing the current .hero__center */
.hero__center {
  position: relative;
  z-index: 2;
  display: grid;
  justify-items: start;
  text-align: left;
  gap: 28px;
  margin: auto;
  padding: 32px var(--pad-x);
  width: 100%;
  max-width: var(--maxw);
  text-shadow: 0 2px 24px rgba(20,14,12,0.35);
  /* Revealed on load by JS adding .is-revealed — no scroll lock, no rAF tween. */
  opacity: 0;
  transform: translateY(36px);
  transition: opacity 0.6s var(--ease-out), transform 0.6s var(--ease-out);
}
.hero__center.is-revealed {
  opacity: 1;
  transform: none;
}
@media (prefers-reduced-motion: reduce) {
  .hero__center {
    opacity: 1;
    transform: none;
    transition: opacity 0.2s var(--ease-out);
  }
}
```

Note what changed beyond the lock: `--hero-reveal` and the `calc()` are gone,
`will-change` is gone (a permanent compositor layer for an animation that runs
once — see plan 007), and the reduced-motion branch keeps a short opacity fade
rather than nothing at all (AUDIT §6: gentler, not zero).

```js
/* target — resources/js/core.js, replacing lines 942-995 */
  const heroCenter = document.querySelector('.hero__center');
  if (heroCenter) {
    // Two frames so the initial opacity:0 / translateY(36px) is painted before
    // the class flips — one frame is not enough after a style recalc.
    requestAnimationFrame(() => requestAnimationFrame(() => {
      heroCenter.classList.add('is-revealed');
    }));
  }
```

## Repo conventions to follow

- `--ease-out` comes from plan `004`. **Run plan 004 first.** If
  `grep -n "\-\-ease-out" resources/css/core.css` returns nothing, STOP.
- State is expressed as a class on the element and a matching CSS rule, never as
  inline style written per frame. Exemplar already in the repo:
  `resources/css/core.css:697-703` —
  ```css
  .reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.75s var(--ease), transform 0.75s var(--ease);
  }
  .reveal.is-in { opacity: 1; transform: translateY(0); }
  ```
  and its JS at `resources/js/core.js:91-99`, which adds `.is-in` and stops.
  `.hero__center` should look like that.
- Per-page CSS lives in `resources/css/pages.css`; shared chrome in
  `resources/css/core.css`. `.hero__center` stays in `pages.css`.

## Steps

1. In `resources/css/pages.css`, replace lines 101-119 (`.hero__center { … }`)
   with the target rule above, and add the `.hero__center.is-revealed` rule and
   the `@media (prefers-reduced-motion: reduce)` block immediately after it.
2. Leave `resources/css/pages.css:120-125` untouched — the
   `.hero__center .split-word > span, .hero__center .reveal { opacity: 1
   !important; transform: none !important; }` rule still does its job of stopping
   inner reveals from double-animating.
3. In `resources/js/core.js`, delete lines 942-995 in full (the block starting
   `const heroCenter = document.querySelector('.hero__center');` and ending with
   the `}` that closes `if (heroCenter) {`) and replace with the six-line target
   JS above. Keep the surrounding comment banner
   `/* -------------------- Hero content reveal (scroll-locked) -------------------- */`
   but rewrite it to `/* -------------------- Hero content reveal -------------------- */`.
4. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT touch the hero video autoplay block above it
  (`resources/js/core.js:927-940`) — the reduced-motion pause there is correct
  and stays.
- Do NOT touch the hero tile tilt block below it
  (`resources/js/core.js:997-1016`).
- Do NOT change `.hero__title`, `.hero__meta`, `.hero__scroll` or any other hero
  child rule.
- Do NOT add a scroll listener or IntersectionObserver for this — the hero is at
  the top of the document and is always in view on load.
- Do NOT re-add `will-change` anywhere in this change.
- After this change, `--hero-reveal` must appear nowhere in the repo. Verify with
  `grep -rn "hero-reveal" resources/`.

## Verification

- **Mechanical**: `npm run build` exits 0.
  `grep -rn "hero-reveal" resources/` returns no matches.
  `grep -n "style.overflow" resources/js/core.js` returns only the mobile-menu
  (`:224`, `:232`) and quote-modal lines — none inside a hero block.
- **Feel check**: load the homepage and
  - Scroll immediately, before the hero text has settled. The page must move on
    that very first wheel tick. The reveal continues underneath; it does not
    block.
  - Reload, then press `Space` as the first input. The page must page down.
    Reload again and press `ArrowDown` — the page must scroll one step.
  - Reload and touch nothing for 5 seconds. The hero text must already be fully
    revealed (it should finish ~700ms after load), and the page must be
    scrollable the whole time.
  - In DevTools → Animations at 10% playback, confirm the hero block rises and
    fades once, decelerating into place — no jump at the end, no restart.
  - Toggle `prefers-reduced-motion: reduce`: the hero text is visible with a
    short fade and **no vertical movement**.
  - On a real phone, load and immediately swipe up — the page must scroll.
- **Done when**: `document.documentElement.style.overflow` and
  `document.body.style.overflow` are both `''` at every moment after load on the
  homepage (check in the console right after load), and the first scroll input
  always scrolls.
