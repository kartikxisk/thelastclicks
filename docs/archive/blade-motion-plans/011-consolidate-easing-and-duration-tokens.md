# 011 — Collapse the duplicate easing curves and give durations a scale

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: LOW
- **Category**: Cohesion & tokens (AUDIT §7)
- **Estimated scope**: 2 files (`resources/css/core.css`, `resources/css/pages.css`), ~15 changed lines

## Problem

**Two `:root` blocks, 1090 lines apart, both defining motion tokens.**

```css
/* resources/css/core.css:8-37 — current, first block (abridged) */
:root {
  /* … colours, fonts … */
  --ease: cubic-bezier(0.16, 1, 0.3, 1);
  --ease-2: cubic-bezier(0.65, 0, 0.35, 1);
  --ease-3: cubic-bezier(0.85, 0, 0.15, 1);
  /* … layout … */
}
```

```css
/* resources/css/core.css:1119-1133 — current, second block */
:root {
  /* One typeface everywhere — display, body and label styles all use Outfit. */
  --f-display: 'Outfit', 'Helvetica Neue', Arial, sans-serif;
  --f-body: 'Outfit', 'Helvetica Neue', Arial, sans-serif;
  --f-mono: 'Outfit', 'Helvetica Neue', Arial, sans-serif;
  --f-serif: 'Outfit', 'Helvetica Neue', Arial, sans-serif;

  /* Spring easing tokens (anti-slop: never linear) */
  --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
  --ease-soft: cubic-bezier(0.32, 0.72, 0, 1);

  /* Tinted blush shadow — anti-slop: shadows carry brand hue, not pure black */
  --sh-red-glow: 0 12px 32px -8px rgba(232,15,3,0.32), 0 0 0 1px rgba(232,15,3,0.18);
  --sh-card: 0 18px 40px -12px rgba(5,5,5,0.5), 0 0 0 1px rgba(255,255,255,0.04);
}
```

The second block redefines all four `--f-*` font tokens to values identical to
the first (`resources/css/core.css:24-27` vs `:1121-1124`) — pure duplication —
and adds two more easing curves nowhere near the others. An author looking for
the motion tokens finds three curves at the top and has no reason to scroll to
line 1119 for the other two.

**Four hand-typed cubic-beziers in `pages.css` that duplicate or near-duplicate
existing tokens** (AUDIT §7: "Five hand-typed cubic-beziers that almost match is
a consolidation finding"):

| Location | Value | Should be |
| --- | --- | --- |
| `resources/css/pages.css:3929` | `cubic-bezier(0.34, 1.56, 0.64, 1)` | exactly `--ease-spring` |
| `resources/css/pages.css:4000` | `cubic-bezier(0.34, 1.56, 0.64, 1)` | exactly `--ease-spring` |
| `resources/css/pages.css:3913` | `cubic-bezier(0.22, 1, 0.36, 1)` | near-twin of `--ease-out` |
| `resources/css/pages.css:3974` | `cubic-bezier(0.22, 1, 0.36, 1)` | near-twin of `--ease-out` |
| `resources/css/pages.css:4485` | `cubic-bezier(0.22, 1, 0.36, 1)` | near-twin of `--ease-out` |
| `resources/css/pages.css:4495` | `cubic-bezier(0.34, 1.3, 0.64, 1)` | a fourth spring variant |

`cubic-bezier(0.22, 1, 0.36, 1)` and `cubic-bezier(0.16, 1, 0.3, 1)` are
visually indistinguishable in a transition — they are the same expo-out shape
with a marginally different pull. Nobody chose between them; one was typed in one
sitting and one in another.

**No duration scale.** Durations in the two stylesheets take 22 distinct values
between `0.05s` and `1.6s`. The image-zoom-on-hover effect alone — the same
gesture, on the same kind of element, across the site — is authored at nine
different speeds:

```
0.5s   resources/css/pages.css:658    .clients .client img
0.7s   resources/css/pages.css:658    (transform, same rule, different from filter)
0.8s   resources/css/pages.css:1320   .talent-card__img img
0.8s   resources/css/pages.css:1368   .post__img img
0.8s   resources/css/pages.css:4835   .work-tile img
0.9s   resources/css/pages.css:1646   .ind img
1.0s   resources/css/pages.css:2106   .svp-gallery .g img
1.0s   resources/css/pages.css:3122   .feat__media img
1.0s   resources/css/pages.css:4068   .pp-g img
1.0s   resources/css/pages.css:4090   .pp-case__media img
1.4s   resources/css/pages.css:69     .hero__bg .tile img
1.4s   resources/css/pages.css:2085   .svp-cover img
1.4s   resources/css/pages.css:4032   .pp-hero__cover img
1.6s   resources/css/pages.css:3913   .odo__col-inner (dead — plan 007 deletes)
```

An editorial image zoom being slow is a legitimate house style for a photography
agency. Nine different speeds for it is not a style, it is drift.

## Target

**A. One motion token block.** Merge the second `:root` into the first, delete
the duplicated font tokens, and add a duration scale.

```css
/* target — resources/css/core.css, the motion section of the first :root.
   NOTE: plan 004 already wrote the curve lines. Do NOT re-derive them — this
   target is the post-plan-004 state, with --ease-soft moved up from the second
   block, --ease-spring folded into --spring, and the duration scale added. */

  /* ---- Motion ----
     Curves are the interaction-design skill's, verbatim (see plan 004):
       entering                     → --ease-out
       moving between, hover,
       colour                       → --ease-in-out
       playful overshoot            → --spring
       exiting                      → --ease-in  (defined, deliberately unused;
                                      AUDIT §2 forbids ease-in on UI)
     Plus two repo curves: --ease-in-out-strong (the old --ease-3) for long
     on-screen travel, and --ease-soft, the iOS drawer curve, for sheets.
     --ease / --ease-3 / --ease-spring are legacy aliases so the ~200 existing
     call sites keep resolving. */
  --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
  --ease-in: cubic-bezier(0.55, 0, 1, 0.45);
  --ease-in-out: cubic-bezier(0.65, 0, 0.35, 1);
  --spring: cubic-bezier(0.34, 1.56, 0.64, 1);
  --ease-in-out-strong: cubic-bezier(0.85, 0, 0.15, 1);
  --ease-soft: cubic-bezier(0.32, 0.72, 0, 1);

  --ease: var(--ease-out);
  --ease-3: var(--ease-in-out-strong);
  --ease-spring: var(--spring);

  /* Duration scale. UI motion stays under 300ms; the editorial tiers above it
     are for image zooms and scroll reveals, which are allowed to be slower.
     Mirrors the interaction-design skill's timing table: 100-150ms micro,
     200-300ms small, 300-500ms medium, 500ms+ choreographed. */
  --dur-press: 0.16s;   /* press / tap feedback */
  --dur-fast: 0.2s;     /* hover colour, small popovers */
  --dur-ui: 0.3s;       /* dropdowns, modals, menus — the UI ceiling */
  --dur-panel: 0.45s;   /* wipes, expanding panels */
  --dur-reveal: 0.75s;  /* scroll reveals */
  --dur-editorial: 1s;  /* image zoom on hover */
```

Then delete `--ease-soft` and the now-aliased `--ease-spring` from the second
`:root` block (`resources/css/core.css:1126-1128`) along with the four duplicate
`--f-*` declarations (`:1120-1124`). What remains of that block is the two shadow
tokens, which can stay where they are.

**B. Replace the six hand-typed beziers with tokens.**

```css
/* resources/css/pages.css:3929 → var(--ease-spring)  — but plan 007 deletes
   this rule (dead .is-charified code). If plan 007 has run, skip. */
/* resources/css/pages.css:3913 → deleted by plan 007 (.odo__col-inner). Skip. */
/* resources/css/pages.css:3974 → owned by plan 005, which already rewrites this
   rule to use var(--ease-out). Verify, don't re-edit. */
```

```css
/* target — resources/css/pages.css:4000 (.hero__bg .tile) */
  transition: transform 0.6s var(--ease-spring);
```

```css
/* target — resources/css/pages.css:4485 (.strip__track) */
  transition: transform 0.9s var(--ease-out);
```

```css
/* target — resources/css/pages.css:4495 (.strip__card) */
  transition: transform 0.7s var(--ease-spring), filter 0.6s var(--ease-soft), border-color 0.4s var(--ease-soft), opacity 0.5s var(--ease-soft);
```

`cubic-bezier(0.34, 1.3, 0.64, 1)` → `--ease-spring` is a slightly stronger
overshoot on the film-strip cards. Check it at the feel step; if the extra bounce
reads as too much on a 460px card, keep the local value and note it as an
intentional exception with a comment.

**C. Normalise the image-zoom duration to one value.** Every
`img`-scale-on-hover transition becomes `var(--dur-editorial)` (1s) with
`var(--ease-out)`:

```css
/* target — each of these, keeping every other declaration in the rule intact */
resources/css/pages.css:658    transition: filter var(--dur-panel) var(--ease-out), transform var(--dur-editorial) var(--ease-out);
resources/css/pages.css:1320   transition: transform var(--dur-editorial) var(--ease-out);
resources/css/pages.css:1368   transition: transform var(--dur-editorial) var(--ease-out);
resources/css/pages.css:1646   transition: transform var(--dur-editorial) var(--ease-out);
resources/css/pages.css:2085   transition: transform var(--dur-editorial) var(--ease-out);
resources/css/pages.css:2106   transition: transform var(--dur-editorial) var(--ease-out);
resources/css/pages.css:3122   transition: transform var(--dur-editorial) var(--ease-out);
resources/css/pages.css:3413   transition: transform var(--dur-editorial) var(--ease-out), filter var(--dur-panel) var(--ease-out);
resources/css/pages.css:3486   transition: filter var(--dur-panel) var(--ease-soft), transform var(--dur-editorial) var(--ease-soft);
resources/css/pages.css:4032   transition: transform var(--dur-editorial) var(--ease-soft);
resources/css/pages.css:4068   transition: transform var(--dur-editorial) var(--ease-soft);
resources/css/pages.css:4090   transition: transform var(--dur-editorial) var(--ease-soft);
resources/css/pages.css:4284   transition: transform var(--dur-editorial) var(--ease-soft);
resources/css/pages.css:4835   transition: transform var(--dur-editorial) var(--ease-out);
```

`resources/css/pages.css:69` (`.hero__bg .tile img`, currently 1.4s) is handled by
plan `012` and is **excluded here**.

## Repo conventions to follow

- Tokens live in the first `:root` in `resources/css/core.css`, which starts at
  line 8. Group them with a `/* ---- Group ---- */` comment; the file already
  uses that style (see `resources/css/core.css:22-23`, `:35`).
- Durations in this codebase are seconds with a leading zero. The token *values*
  follow that (`0.16s`, not `160ms`); call sites use `var(--dur-*)`.
- Do not rename existing tokens. Aliasing (`--ease: var(--ease-out)`) is how this
  repo will keep ~200 call sites working without a mass edit.

## Steps

1. Confirm plans `004`, `005` and `007` have run — this plan builds on all three.
   `grep -n "\-\-ease-in-out-strong" resources/css/core.css` must match;
   `grep -n "odo__" resources/css/pages.css` must return nothing.
2. `resources/css/core.css` — replace the `--ease*` lines in the first `:root`
   with the target block (motion + duration scale).
3. `resources/css/core.css:1119-1133` — delete the four `--f-*` duplicates and
   the `--ease-spring` / `--ease-soft` lines and their comment. Keep the two
   `--sh-*` shadow tokens and the block itself.
4. `resources/css/pages.css:4000`, `:4485`, `:4495` — swap the raw beziers for
   tokens.
5. Apply the fourteen image-zoom duration edits listed in section C.
6. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT change the *value* of any existing curve. This is a naming and
  deduplication pass, not a re-tuning pass. The canonical values were fixed by
  plan 004 from the `interaction-design` skill; do not substitute AUDIT.md's
  near-identical alternatives.
- Do NOT add a call site for `var(--ease-in)`. It stays defined and unused.
- Do NOT touch `resources/css/pages.css:69` — plan 012 owns it.
- Do NOT touch `resources/css/pages.css:3974` — plan 005 owns it.
- Do NOT mass-replace `var(--ease)` with `var(--ease-out)` across the codebase.
  The alias makes that unnecessary and the diff would be unreviewable.
- Do NOT introduce `--dur-*` tokens at call sites beyond the fourteen image-zoom
  rules in section C. Rolling the duration scale out across the whole codebase is
  a separate, larger job; this plan only establishes the tokens and proves them on
  one coherent set of rules.
- Do NOT delete the second `:root` block entirely — the shadow tokens live there
  and are referenced at `resources/css/core.css:1201`.
- If `resources/css/core.css:1121` does not read
  `  --f-display: 'Outfit', 'Helvetica Neue', Arial, sans-serif;`, the file has
  drifted — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
  `grep -c "cubic-bezier" resources/css/pages.css` returns `0`.
  `grep -c "^  --f-" resources/css/core.css` returns `4` (the first block only).
  `grep -n "ease-spring\|ease-soft" resources/css/core.css` shows each defined
  exactly once.
- **Feel check**: run the site and
  - Open DevTools → Elements → Computed on `.marquee__track` and any element
    using `var(--ease)`. The resolved value must be a real
    `cubic-bezier(0.16, 1, 0.3, 1)` — **not** the literal string `var(--ease-out)`
    and not `initial`. If any token resolves to nothing, the alias chain is
    broken and every transition using it silently falls back to `ease`.
  - Hover an image card on the works grid, the blog index, the industries page
    and a service page in turn. All four zooms must now feel like the same
    gesture at the same speed. Before this change they were noticeably different.
  - Hover a film-strip card. If the overshoot now reads as too bouncy for a
    460px card, revert `resources/css/pages.css:4495` to
    `cubic-bezier(0.34, 1.3, 0.64, 1)` with a comment explaining why, and say so
    in your report.
  - Hover a hero tile (this rule moved to `--ease-spring`): the tilt settle must
    still look the same as before.
  - Scroll through the whole homepage and one service page looking for anything
    that now animates at a visibly wrong speed — a token resolving to a fallback
    shows up as a snappy 0.3s where a 1s zoom used to be.
  - Toggle `prefers-reduced-motion: reduce` and confirm nothing regressed.
- **Done when**: `pages.css` contains zero literal `cubic-bezier(` strings, every
  motion token is defined exactly once, and the image-zoom hover on four
  different page types is visually identical in speed.
