# 004 — Name the easing tokens and stop using an ease-in-out curve on entrances

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: HIGH
- **Category**: Easing & duration (AUDIT §2)
- **Estimated scope**: 2 files (`resources/css/core.css`, `resources/css/pages.css`), ~20 changed lines

## Problem

`--ease-3` is a hard ease-in-out:

```css
/* resources/css/core.css:29-31 — current */
  --ease: cubic-bezier(0.16, 1, 0.3, 1);
  --ease-2: cubic-bezier(0.65, 0, 0.35, 1);
  --ease-3: cubic-bezier(0.85, 0, 0.15, 1);
```

`cubic-bezier(0.85, 0, 0.15, 1)` spends its first third barely moving. That is
correct for something travelling *across* the screen and wrong for anything
*arriving* or *leaving*, which must start fast (AUDIT §2). It is currently used
on 18 rules, and most of them are entrances or exits:

| Location | Rule | What it is |
| --- | --- | --- |
| `resources/css/core.css:100` | `.curtain.is-in .curtain__panel` | entering |
| `resources/css/core.css:104` | `.curtain.is-out .curtain__panel` | exiting |
| `resources/css/core.css:148` | `.preloader` | exiting |
| `resources/css/core.css:527` | `.menu` | entering (mobile nav) |
| `resources/css/core.css:576` | `.btn::before` | entering wipe on hover |
| `resources/css/core.css:735` | `.clip-reveal` | entering (scroll reveal) |
| `resources/css/core.css:961` | `.foot__hello .arr` | hover nudge |
| `resources/css/pages.css:373` | `.svc::before` | entering wipe on hover |
| `resources/css/pages.css:829` | `.acc__body` | expanding (see plan 006) |
| `resources/css/pages.css:904` | `.belief::before` | entering wipe on hover |
| `resources/css/pages.css:939` | `.belief__note` | expanding (see plan 006) |
| `resources/css/pages.css:1743` | `.quote__panel` | entering (see plan 010) |
| `resources/css/pages.css:1840` | `@keyframes quoteIn` via `.quote__panel-step` | entering |
| `resources/css/pages.css:1870` | `.quote__chip::before` | entering wipe on hover |
| `resources/css/pages.css:1943` | `.quote__bar-fill` | progress (see plan 005) |
| `resources/css/pages.css:2504` | `.sproc` progress `::before` | entering wipe |
| `resources/css/pages.css:2518` | `.sproc__dot` | width change (see plan 005) |
| `resources/css/pages.css:3222` | `.ind-hi__card::after` | entering wipe on hover |

Every one of the "entering" and "exiting" rows starts slow at exactly the moment
the user is watching for a response. The mobile menu (`core.css:527`) is the
worst of them: it is a 700ms ease-in-out on an overlay opened by a tap, so a
third of a second passes with almost nothing on screen.

There is also a dead token and a naming problem:

- `--ease-2` (`resources/css/core.css:30`) has **zero** uses anywhere in the repo.
- Nothing in the token names says what a curve is *for*. `--ease`, `--ease-2`,
  `--ease-3` gives an author no way to pick correctly, which is how `--ease-3`
  ended up on entrances in the first place.

## Target

Add semantic names, delete the dead token, and repoint every entering/exiting
rule at the ease-out curve.

**Where the curve values come from, and why they are not AUDIT's.** The four
canonical curves are taken **verbatim** from the `interaction-design` skill
(`~/.claude/skills/interaction-design/SKILL.md`, "Easing Functions"). Three of
them are already in this codebase byte-for-byte — `--ease` (`core.css:29`) is
the skill's `--ease-out`, `--ease-2` (`:30`) is its `--ease-in-out`, and
`--ease-spring` (`:1127`) is its `--spring` — so the repo's token set was
evidently authored from that skill, and adopting its names makes the existing
values legible rather than replacing them.

This does diverge from AUDIT.md, which lists
`--ease-out: cubic-bezier(0.23, 1, 0.32, 1)` and
`--ease-in-out: cubic-bezier(0.77, 0, 0.175, 1)`. Those are the same two curve
families a shade weaker. Adding them alongside the repo's would create exactly
the "five hand-typed cubic-beziers that almost match" problem AUDIT §7 warns
about, across ~200 existing rules. **Do not "correct" the values back to
AUDIT's.** Also note `--ease-soft` (`core.css:1128`) is already byte-identical
to AUDIT's recommended `--ease-drawer`.

```css
/* target — resources/css/core.css:29-31, replacing the three --ease* lines */
  /* ---- Motion ----
     The four curves below are the interaction-design skill's, verbatim:
       entering                        → --ease-out
       moving between states, hover,
       colour                          → --ease-in-out
       playful overshoot               → --spring
       exiting                         → --ease-in  (see the warning)
     --ease-in is defined for completeness and is deliberately UNUSED. AUDIT §2:
     "ease-in on UI is always a finding" — it starts slow, delaying the exact
     moment the user is watching. Reach for it only on a full-screen exit that
     is leaving the viewport entirely, never on a control, a panel or a hover.
     --ease-in-out-strong is the repo's own harder ease-in-out (the old
     --ease-3), kept for long on-screen travel.
     --ease / --ease-3 are legacy aliases so the ~200 existing call sites keep
     resolving without a mass edit; new rules should use the semantic names. */
  --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
  --ease-in: cubic-bezier(0.55, 0, 1, 0.45);
  --ease-in-out: cubic-bezier(0.65, 0, 0.35, 1);
  --spring: cubic-bezier(0.34, 1.56, 0.64, 1);
  --ease-in-out-strong: cubic-bezier(0.85, 0, 0.15, 1);

  --ease: var(--ease-out);
  --ease-3: var(--ease-in-out-strong);
```

`--ease-2` is deleted outright — it has zero call sites, and its value survives
under its proper name as `--ease-in-out`.

One line in the second `:root` block changes too, so `--spring` and
`--ease-spring` never both exist as literals:

```css
/* resources/css/core.css:1127 */
/* current */  --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
/* target  */  --ease-spring: var(--spring);
```

Then swap the curve on the entering/exiting rules. Each is a one-token edit:

```css
/* resources/css/core.css:527 — .menu, mobile nav overlay */
/* current */  transition: transform 0.7s var(--ease-3);
/* target  */  transition: transform 0.3s var(--ease-out);
```

```css
/* resources/css/core.css:576 — .btn::before, the hover fill wipe */
/* current */  transition: transform 0.5s var(--ease-3);
/* target  */  transition: transform 0.4s var(--ease-out);
```

```css
/* resources/css/core.css:735 — .clip-reveal, scroll reveal */
/* current */  transition: clip-path 1.1s var(--ease-3);
/* target  */  transition: clip-path 0.9s var(--ease-out);
```

```css
/* resources/css/core.css:961 — .foot__hello .arr, hover nudge */
/* current */  transition: transform 0.45s var(--ease-3);
/* target  */  transition: transform 0.25s var(--ease-in-out);
```

```css
/* resources/css/pages.css:373 — .svc::before */
/* current */  transition: transform 0.6s var(--ease-3);
/* target  */  transition: transform 0.45s var(--ease-out);
```

```css
/* resources/css/pages.css:904 — .belief::before */
/* current */  transition: transform 0.6s var(--ease-3);
/* target  */  transition: transform 0.45s var(--ease-out);
```

```css
/* resources/css/pages.css:1840-1842 — quote wizard step entrance */
/* current */
.quote__panel-step { display: none; animation: quoteIn 0.45s var(--ease-3); }
/* target */
.quote__panel-step { display: none; animation: quoteIn 0.3s var(--ease-out); }
```

```css
/* resources/css/pages.css:1870 — .quote__chip::before */
/* current */  transition: transform 0.4s var(--ease-3);
/* target  */  transition: transform 0.3s var(--ease-out);
```

```css
/* resources/css/pages.css:2504 — .sproc progress ::before */
/* current */  transition: transform 0.6s var(--ease-3);
/* target  */  transition: transform 0.45s var(--ease-out);
```

```css
/* resources/css/pages.css:3222 — .ind-hi__card::after */
/* current */  transition: transform 0.6s var(--ease-3);
/* target  */  transition: transform 0.45s var(--ease-out);
```

Four `--ease-3` sites are **deliberately left alone here** because another plan
owns them: `core.css:100`, `core.css:104` and `core.css:148` belong to plan 001;
`pages.css:1743` belongs to plan 010; `pages.css:829` and `pages.css:939` belong
to plan 006; `pages.css:1943` and `pages.css:2518` belong to plan 005. Touching
them in this plan will cause a merge conflict with those.

## Repo conventions to follow

- Both `:root` blocks live in `resources/css/core.css` — the first at line 8, a
  second "TASTE SKILL OVERRIDES" one at line 1119. Plan 011 merges them. This
  plan rewrites the first block and changes exactly one line of the second
  (`:1127`); do not move anything else between them here.
- Durations are seconds with a leading zero (`0.45s`), never milliseconds.
- Multi-property transitions are written on one line, comma-separated, with the
  curve repeated per property — see `resources/css/core.css:404`:
  `transition: background 0.25s var(--ease), color 0.25s var(--ease);`

## Steps

1. In `resources/css/core.css`, replace lines 29-31 with the target token block
   above (five curve definitions, two aliases, plus the comment).
2. In `resources/css/core.css:1127`, change `--ease-spring` to `var(--spring)`.
   This is the only line of the second `:root` block this plan touches.
3. Change the curve and duration on each of the ten rules listed in "Target",
   in the order given: `core.css:527`, `core.css:576`, `core.css:735`,
   `core.css:961`, then `pages.css:373`, `pages.css:904`, `pages.css:1840`,
   `pages.css:1870`, `pages.css:2504`, `pages.css:3222`.
   Line numbers shift as you edit `core.css` — match on the selector, not the
   line number.
4. Confirm `--ease-2` appears nowhere: `grep -rn "ease-2" resources/`.
5. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT change any curve *value*. Every one is either the interaction-design
  skill's verbatim or the repo's own existing curve renamed:
  `--ease-out: cubic-bezier(0.16, 1, 0.3, 1)`,
  `--ease-in: cubic-bezier(0.55, 0, 1, 0.45)`,
  `--ease-in-out: cubic-bezier(0.65, 0, 0.35, 1)`,
  `--spring: cubic-bezier(0.34, 1.56, 0.64, 1)`,
  `--ease-in-out-strong: cubic-bezier(0.85, 0, 0.15, 1)`.
  In particular do NOT substitute AUDIT.md's values — see the note above.
- Do NOT use `var(--ease-in)` on anything in this plan or any other. It is
  defined so the token set is complete and so nobody hand-types an ease-in
  later; AUDIT §2 forbids it on UI. It should end this plan with zero call
  sites, and that is correct.
- Do NOT bulk-replace `var(--ease-3)` across the codebase. Only the ten rules
  listed change; the rest belong to other plans or are correct as ease-in-out.
- Do NOT touch `--ease-soft` (`resources/css/core.css:1128`) — it stays a
  literal, and it is already byte-identical to AUDIT's `--ease-drawer`.
- Do NOT touch any rule already using `var(--ease)` or `var(--ease-spring)` —
  both now resolve through an alias to exactly the value they had before.
- Do NOT change any JS.
- If `resources/css/core.css:30` does not read
  `  --ease-2: cubic-bezier(0.65, 0, 0.35, 1);`, the file has drifted since
  commit 8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
  `grep -rn "ease-2" resources/` returns no matches.
  `grep -rn "var(--ease-in)" resources/` returns no matches (it is defined but
  unused, by design).
  `grep -c "var(--ease-3)" resources/css/core.css resources/css/pages.css`
  returns `3` for core.css (the three plan-001 lines) and `5` for pages.css
  (1743, 829, 939, 1943, 2518).
- **Feel check**: run the site and
  - Narrow the window under 1080px, tap the burger. The menu must appear to
    *launch* — most of its travel in the first 100ms, settling into place. Before
    the change it drifts for 200ms then rushes. Compare by toggling the rule in
    DevTools.
  - Hover a `.btn` (e.g. "See the reel"). The white fill should sweep up
    immediately on the pointer entering, not lag behind it.
  - Hover a service row on the homepage (`.svc`). The red wash must feel like it
    is chasing the cursor.
  - Open the quote modal, click Continue. Each step should snap in, not float in.
  - In DevTools → Animations, set playback to 10% and watch the mobile menu open:
    the panel must cover the most ground in the first frames and decelerate.
    If it creeps at the start, the swap did not take.
  - Toggle `prefers-reduced-motion: reduce`: everything still resolves (no
    unresolved `var()` — check the Computed panel on `.menu` shows a real
    `cubic-bezier`, not the literal string `var(--ease-out)`).
- **Done when**: no entering or exiting element in the table above still
  resolves to `cubic-bezier(0.85, 0, 0.15, 1)`, verified by inspecting the
  Computed styles of `.menu`, `.btn::before` and `.quote__panel-step` in DevTools.
