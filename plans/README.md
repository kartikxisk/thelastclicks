# Animation improvement plans

Produced by an `improve-animations` audit of the repo at commit **`8d8716b`**,
against the rule catalog in `~/.claude/skills/improve-animations/AUDIT.md`, then
cross-checked against the `interaction-design` skill. Where the two disagree, see
[Two sources, and which one wins where](#two-sources-and-which-one-wins-where) —
it matters for plans 004 and 008.

Each plan is self-contained: it names the exact files, quotes the current code,
gives the exact target values, and ends with a mechanical check and a feel check.
An executor with no context from the audit conversation should be able to run any
one of them alone, in the order below.

**These plans are specs, not changes.** Nothing under `resources/` has been
modified.

## Plans

| # | Plan | Severity | Category | Status |
| --- | --- | --- | --- | --- |
| 001 | [Cut the dead time the curtain and preloader add to every page load](001-unblock-navigation-curtain-preloader.md) | HIGH | Purpose & frequency | TODO |
| 002 | [Stop locking scroll and swallowing the first keypress on the homepage hero](002-remove-hero-scroll-lock.md) | HIGH | Purpose & frequency | TODO |
| 003 | [Stop two always-on rAF loops, one of which forces layout every frame](003-stop-always-on-raf-loops.md) | HIGH | Performance | TODO |
| 004 | [Name the easing tokens and stop using an ease-in-out curve on entrances](004-ease-out-on-entrances.md) | HIGH | Easing & duration | TODO |
| 005 | [Replace animated padding/width on hovers and progress bars with transforms](005-hover-layout-properties-to-transform.md) | HIGH | Performance | TODO |
| 006 | [Replace `max-height` collapsibles with `grid-template-rows`](006-max-height-collapsibles-to-grid-rows.md) | MEDIUM | Performance | TODO |
| 007 | [Stop holding permanent compositor layers with `will-change`](007-release-will-change.md) | MEDIUM | Performance | TODO |
| 008 | [Make reduced motion gentler, not zero](008-reduced-motion-gentler-not-zero.md) | MEDIUM | Accessibility | TODO |
| 009 | [Drive the nav's scrolled state from a scroll listener, not a 100ms poll](009-nav-scroll-state-listener.md) | MEDIUM | Performance | TODO |
| 010 | [Bring the quote modal's entrance inside the duration budget](010-quote-modal-entrance-timing.md) | MEDIUM | Easing & duration | TODO |
| 011 | [Collapse the duplicate easing curves and give durations a scale](011-consolidate-easing-and-duration-tokens.md) | LOW | Cohesion & tokens | TODO |
| 012 | [Stop `scale(0)` on the process glyph and the 1.4s hero tile zoom](012-physicality-scale-zero-and-hero-zoom.md) | LOW | Physicality | TODO |
| 013 | [Give the work grid a transition when a filter chip is clicked](013-animate-work-grid-filtering.md) | LOW | Missed opportunity | TODO |
| 014 | [Give the work lightbox an entrance instead of appearing in one frame](014-lightbox-open-motion.md) | LOW | Missed opportunity | TODO |
| 015 | [Make the quote wizard's steps move in the direction the user is going](015-quote-wizard-step-continuity.md) | LOW | Missed opportunity | TODO |

## Recommended execution order

**Run 004 first.** It establishes the `--ease-out` / `--ease-in-out` / `--spring`
/ `--ease-in-out-strong` token set that eight other plans reference. Every plan
that needs them says so and tells the executor to stop if they are missing, but
running 004 first avoids that entirely.

```
004  ─┬─→ 001                          (curtain + preloader curves)
      ├─→ 002                          (hero reveal curve)
      ├─→ 005 ─→ 015                   (scaleX progress bar → wizard render())
      ├─→ 006
      ├─→ 010
      ├─→ 012
      ├─→ 013
      └─→ 014

003  ─→ 009                            (003 removes the rAF loop that owned scrollY)

007  ─→ 011                            (007 deletes the dead rules 011 would edit)
005  ─→ 011                            (005 rewrites .svc::after, 011 must not re-edit)
002  ─→ 007                            (002 already removes .hero__center's will-change)
```

A safe linear order that satisfies every dependency:

```
004 → 003 → 009 → 002 → 001 → 005 → 006 → 007 → 008 → 010 → 011 → 012 → 013 → 014 → 015
```

If you want impact fastest and are willing to run plans one at a time with a feel
check between each, the first four in that order (004, 003, 009, 002) remove the
two worst frame-rate problems and the worst interaction problem on the site.

## Dependencies in detail

| Plan | Requires | Why |
| --- | --- | --- |
| 001 | 004 | Uses `var(--ease-out)` on the curtain and preloader |
| 002 | 004 | Uses `var(--ease-out)` on the hero reveal |
| 005 | 004 | Uses `var(--ease-out)` and `var(--ease-in-out)` throughout |
| 006 | 004 | Uses `var(--ease-out)` on the collapsibles |
| 009 | 003 | 003 deletes the rAF loop that assigns the shared `scrollY` |
| 010 | 004 | Uses `var(--ease-out)` and `var(--ease-in-out)` |
| 011 | 004, 005, 007 | 004 owns the token block; 005 rewrites `.svc::after`; 007 deletes the dead `.odo__*` / `.is-charified` rules 011 would otherwise edit |
| 012 | 004 | Uses `var(--ease-out)` |
| 013 | 004 | Uses `var(--ease-out)` |
| 014 | 004 | Uses `var(--ease-out)` |
| 015 | 004, 005 | 005 converts `.quote__bar-fill` to `scaleX`, which 015's `render()` writes to. 015 documents a fallback if 005 has not run |

Plans **007** and **008** have no hard prerequisites but read more cleanly after
002 (which removes one of the `will-change` declarations 007 targets) and after
the transform-based rewrites in 005/006 (which 008's `transition-property`
allow-list then correctly neutralises).

## Overlapping files

More than one plan touches these files. Run them in the order above rather than
in parallel, and re-read each file before editing — line numbers shift.

- `resources/css/core.css` — 001, 004, 005, 007, 008, 011
- `resources/css/pages.css` — 004, 005, 006, 007, 010, 011, 012, 013, 014, 015
- `resources/js/core.js` — 001, 002, 003, 007, 009, 013
- `resources/js/chrome.js` — 005, 015
- `resources/js/work-lightbox.js` — 014

Four `var(--ease-3)` call sites are deliberately claimed by a specific plan so
two plans never edit the same line: `core.css:100`, `:104`, `:148` belong to 001;
`pages.css:1743` to 010; `:829` and `:939` to 006; `:1943` and `:2518` to 005.
Plan 004 lists all of them and explicitly leaves them alone.

## Considered and not planned

- **The nav's `padding` transition** (`resources/css/core.css:259`). It is an
  animated layout property, which plan 005 removes everywhere else — but the
  header shrinking on scroll is deliberate design, it fires at most twice per
  scroll pass rather than per hover, and it is scoped to a `position: fixed`
  subtree. Plans 005 and 009 both exclude it on purpose and say why.
- **`@keyframes quoteIn` being a keyframe rather than a transition.** AUDIT §4
  flags keyframes on rapidly-triggered UI because they restart from zero instead
  of retargeting. Here each wizard step is a separate element toggling `display`,
  so there is nothing to retarget and a restart is correct. Plan 015 says so
  explicitly to stop an executor "fixing" it.
- **The nav active-link underline** (`resources/css/core.css:357-363`) hard-cuts
  between pages. There is nothing to animate — each page is a fresh document, and
  the curtain already masks the swap. Genuinely fixing it would need view
  transitions or an SPA router, which is far beyond an animation pass.
- **`transform-origin` on the nav dropdown** (`resources/css/core.css:367-384`).
  AUDIT §3 wants trigger-anchored panels to scale from their trigger — but this
  dropdown only translates 8px and never scales, so the origin is not applied to
  anything. Not a finding.
- **`transform-origin: center` on the quote modal.** Correct; AUDIT §3 exempts
  centred modals explicitly.
- **A shared-element transition from a work tile into the lightbox.** The right
  answer in the abstract, but fragile across images, videos and YouTube iframes
  in a masonry grid. Plan 014 takes the 80% version and says what it is skipping.
- **A FLIP animation of surviving tiles during work-grid filtering.** Same
  reasoning; plan 013 records the decision.

## What the audit found to be already correct

Worth knowing so nobody "fixes" it later:

- No literal `ease-in` anywhere in the stylesheets.
- No `transition: all` anywhere.
- Per-component `prefers-reduced-motion` blocks already exist for the marquees,
  hero slides, `.why-list`, the sticky process section, the vertical accordion
  and the work tiles.
- `.strip__track` sets `transform` directly with a CSS transition, so it
  retargets correctly when clicked mid-animation (AUDIT §4).
- `--ease-soft` (`cubic-bezier(0.32, 0.72, 0, 1)`) is byte-identical to AUDIT's
  recommended `--ease-drawer`.
- The press-feedback rule at `resources/css/core.css:1153-1158` uses
  `translateY(1px) scale(0.985)`, inside AUDIT §3's 0.95–0.98 band.
- Focus management in the quote modal, the mobile menu and the lightbox — focus
  trap, restore-on-close, `aria-expanded` — is thorough and is out of scope for
  every plan here.

## Two sources, and which one wins where

These plans were written against `improve-animations`' rule catalog
(`~/.claude/skills/improve-animations/AUDIT.md`) and then cross-checked against
the `interaction-design` skill
(`~/.claude/skills/interaction-design/SKILL.md`). The two agree on nearly
everything — "avoid animating width/height/top/left", "never prevent user input
during animations", "use `will-change` sparingly", "allow users to cancel long
animations" are all in both, and each corroborates a plan here (005, 002, 007,
001/006 respectively). They differ in two places.

**1. Easing values — `interaction-design` wins.** Three of that skill's four
curves are already in this codebase byte-for-byte:

| Skill token | Value | Already in repo as |
| --- | --- | --- |
| `--ease-out` | `cubic-bezier(0.16, 1, 0.3, 1)` | `--ease` (`core.css:29`) |
| `--ease-in-out` | `cubic-bezier(0.65, 0, 0.35, 1)` | `--ease-2` (`core.css:30`) |
| `--spring` | `cubic-bezier(0.34, 1.56, 0.64, 1)` | `--ease-spring` (`core.css:1127`) |
| `--ease-in` | `cubic-bezier(0.55, 0, 1, 0.45)` | — not present |

The repo's token set was evidently authored from that skill, so plan 004 adopts
all four names and values verbatim and aliases the old names onto them. AUDIT §2
gives slightly different values for the same two curve families
(`cubic-bezier(0.23, 1, 0.32, 1)` and `cubic-bezier(0.77, 0, 0.175, 1)`);
introducing those alongside ~200 existing rules would create precisely the
"cubic-beziers that almost match" problem AUDIT §7 warns about. Plan 004 tells
the executor not to "correct" the values back.

`--ease-in` is defined but **deliberately has zero call sites** — AUDIT §2's
"ease-in on UI is always a finding" stands, and it is the stronger rule. It
exists so nobody hand-types an ease-in later.

**2. The reduced-motion snippet — `improve-animations` wins.** The
`interaction-design` skill prescribes the blanket
`transition-duration: 0.01ms !important` on `*`, which is exactly what
`core.css:1284-1290` does today and exactly what plan 008 replaces. That blanket
reset is a good default for a codebase with no reduced-motion handling; this one
has ten targeted per-component blocks already, and the global rule now only
destroys the comprehension aids they were careful to keep. AUDIT §6 — "fewer and
gentler, not zero" — governs. Plan 008 states the conflict inline so an executor
reading both docs cannot re-introduce it.

Everything else in `interaction-design` that applies here — its timing table,
the loading/skeleton and gesture patterns — is either already satisfied by these
plans or targets React + Framer Motion, which this Laravel/Blade codebase does
not use.

## After executing

Re-run `improve-animations reconcile` to mark completed plans DONE, refresh any
`file:line` references that have moved, and retire findings that are no longer
present.
