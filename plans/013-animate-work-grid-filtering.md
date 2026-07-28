# 013 — Give the work grid a transition when a filter chip is clicked

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: LOW (missed opportunity — additive)
- **Category**: Missed opportunities (AUDIT §8)
- **Estimated scope**: 2 files (`resources/js/core.js`, `resources/css/pages.css`), ~35 lines

## Problem

Filtering the work grid is the single most jarring state change on the site.

```js
/* resources/js/core.js:464-475 — current */
      tiles.forEach(tile => {
        let match = true;
        if (kind === 'cat') {
          match = (tile.dataset.cat || '') === value;
        } else if (kind === 'craft') {
          match = (tile.dataset.crafts || '').split(/\s+/).includes(value);
        }
        tile.hidden = !match;
        if (match) shown++;
      });

      if (empty) empty.hidden = shown > 0;
```

```css
/* resources/css/pages.css:4861 — current */
.work-tile[hidden] { display: none; }
```

`tile.hidden = !match` flips `display: none` synchronously. In one frame, half
the grid vanishes and everything below it jumps up several hundred pixels. The
user clicked a chip labelled "Weddings" and the page teleports — nothing explains
which tiles left, which stayed, or where the remaining ones went.

This is exactly AUDIT §8's first case: *"State changes that teleport (content
swaps, layout jumps) where a brief transition would prevent a jarring change."*

The grid is a masonry / bento layout (`resources/css/pages.css:4940-4994`), so a
true FLIP animation of every surviving tile to its new position is a large piece
of work. It is not needed. A fade-and-settle on the tiles that leave, plus a
short staggered fade-in for the ones that stay, is enough to make the change
legible.

## Target

Two phases, driven by a class rather than by `hidden` alone.

```css
/* target — resources/css/pages.css, add immediately after line 4861 */
/* ---------- Work grid filtering ---------- */
/* Tiles fade and shrink slightly out before they are removed from layout, then
   the survivors fade back in with a short stagger. The stagger is decorative —
   tiles are clickable from frame one (AUDIT §7). */
.work-tile {
  transition: opacity 0.2s var(--ease-out), transform 0.2s var(--ease-out);
}
.work-tile.is-filtering-out {
  opacity: 0;
  transform: scale(0.96);
  pointer-events: none;
}
.work-tile.is-filtering-in {
  animation: workTileIn 0.28s var(--ease-out) backwards;
}
@keyframes workTileIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: none; }
}
@media (prefers-reduced-motion: reduce) {
  .work-tile.is-filtering-out { opacity: 0; transform: none; }
  .work-tile.is-filtering-in { animation: none; }
}
```

`scale(0.96)` is inside AUDIT §3's band — the tile shrinks slightly as it leaves
rather than collapsing to nothing.

```js
/* target — resources/js/core.js:451-476, replacing the chip click handler */
    const FILTER_OUT_MS = 200;
    const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

    bar.addEventListener('click', e => {
      const chip = e.target.closest('[data-filter]');
      if (!chip) return;

      bar.querySelectorAll('[data-filter]').forEach(c => {
        c.classList.toggle('is-on', c === chip);
        c.setAttribute('aria-pressed', c === chip ? 'true' : 'false');
      });

      // Chips are either `all`, `cat:<slug>` or `craft:<slug>`.
      const [kind, value] = (chip.dataset.filter || 'all').split(':');

      const matches = tiles.map(tile => {
        if (kind === 'cat') return (tile.dataset.cat || '') === value;
        if (kind === 'craft') return (tile.dataset.crafts || '').split(/\s+/).includes(value);
        return true;
      });
      const shown = matches.filter(Boolean).length;

      function commit() {
        tiles.forEach((tile, i) => {
          const wasHidden = tile.hidden;
          tile.classList.remove('is-filtering-out', 'is-filtering-in');
          tile.hidden = !matches[i];
          // Only tiles that were not already on screen animate in — surviving
          // tiles must not flicker just because their neighbours left.
          if (matches[i] && wasHidden && !reduceMotion) {
            tile.style.animationDelay = `${Math.min(i, 8) * 40}ms`;
            tile.classList.add('is-filtering-in');
          } else {
            tile.style.animationDelay = '';
          }
        });
        if (empty) empty.hidden = shown > 0;
      }

      // Phase 1: fade out the departing tiles in place, so the layout does not
      // jump while they are still visible.
      const leaving = tiles.filter((tile, i) => !matches[i] && !tile.hidden);
      if (!leaving.length || reduceMotion) { commit(); return; }
      leaving.forEach(tile => tile.classList.add('is-filtering-out'));
      setTimeout(commit, FILTER_OUT_MS);
    });
```

The 40ms per-tile stagger is inside AUDIT §7's 30–80ms band, capped at 8 tiles so
a 40-item grid does not take 1.6 seconds to finish arriving. Departing tiles get
`pointer-events: none` immediately so a tile that is fading out cannot be
clicked.

## Repo conventions to follow

- State classes are `is-*` (`is-on`, `is-open`, `is-in`, `is-previewing`). The
  new ones follow that.
- `--ease-out` comes from plan `004`. **Run plan 004 first.**
- The repo's existing pattern for "animate in on a state change, once" is
  `resources/css/pages.css:2470-2483` (`.sproc__keys li` with per-child
  `transition-delay`). This plan uses a JS-set `animationDelay` instead because
  the tile index is not knowable from CSS after filtering — that is the reason
  for the deviation.
- Reduced-motion handling belongs in a `@media (prefers-reduced-motion: reduce)`
  block adjacent to the rules it neutralises — see
  `resources/css/pages.css:4857`.
- Filter JS lives in the `[data-work-filters]` block at
  `resources/js/core.js:443-477`; keep it there.

## Steps

1. `resources/css/pages.css` — add the target CSS block immediately after line
   4861 (`.work-tile[hidden] { display: none; }`), keeping that line.
2. `resources/js/core.js:451-476` — replace the `bar.addEventListener('click', …)`
   handler with the target version. `const tiles` (line 448) and `const empty`
   (line 449) above it are unchanged and still used.
3. Add `const FILTER_OUT_MS` and `const reduceMotion` above the listener as shown.
   Note `reduce` already exists at `resources/js/core.js:12` in the outer scope —
   use that instead of re-reading `matchMedia` if it is in scope; check and prefer
   the existing binding.
4. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT implement a FLIP / `getBoundingClientRect` position animation for the
  surviving tiles. Out of scope, and the masonry layout makes it fragile.
- Do NOT change the filtering *logic* — which tiles match which chip must be
  byte-identical to today. Only the presentation of the change is in scope.
- Do NOT change any markup in `resources/views/works/index.blade.php` or the
  `data-cat` / `data-crafts` attributes.
- Do NOT animate the `[data-work-empty]` element — it appears and disappears
  instantly, which is fine.
- Do NOT touch the video-preview block above
  (`resources/js/core.js:401-441`).
- Do NOT let a departing tile stay clickable during its fade — the
  `pointer-events: none` is required.
- If `resources/js/core.js:471` does not read `        tile.hidden = !match;`,
  the file has drifted since commit 8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
- **Feel check**: open the works page and
  - Click a category chip. The tiles that no longer match must **fade and shrink
    slightly in place** first; the grid must only reflow after they are gone.
    Before this change the reflow and the disappearance happen in the same frame.
  - The tiles that survive and were already visible must **not** flicker or
    re-animate — only newly-appearing tiles fade in.
  - Click back to "All". The previously-hidden tiles must arrive with a short
    stagger, top-left first, finishing within about half a second even on a large
    grid.
  - Click three chips in rapid succession (faster than 200ms apart). The grid
    must end in the correct state for the **last** chip clicked, with no tiles
    stuck at `opacity: 0` and none left with a stale `is-filtering-out` class.
    Check in the Elements panel afterwards that no tile has either class.
  - Click a chip and immediately try to click a tile that is fading out — it must
    not open the lightbox.
  - Filter down to a chip with zero results: the empty state must appear.
  - Toggle `prefers-reduced-motion: reduce`: filtering must be instant, exactly
    as it is today, with no fade and no stagger.
  - In DevTools → Animations at 10% playback, confirm departing tiles shrink to
    `0.96` and not to zero.
- **Done when**: filtering never produces a single-frame layout jump, rapid
  repeated clicks always settle on the correct final state with no orphaned
  classes, and reduced motion is instant.
