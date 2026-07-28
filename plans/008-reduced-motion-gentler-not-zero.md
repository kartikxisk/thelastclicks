# 008 — Make reduced motion gentler, not zero

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: MEDIUM
- **Category**: Accessibility (AUDIT §6)
- **Estimated scope**: 1 file (`resources/css/core.css`), ~25 lines

## Problem

```css
/* resources/css/core.css:1277-1305 — current */
/* ============================================================
   REDUCED MOTION — honour the OS "reduce motion" preference.
   The site is heavily animated (reveals, splits, curtains, marquees,
   preloader, tilt, magnetic, spotlight). This block neutralises the
   motion while GUARANTEEING every scroll-revealed element is visible,
   so nothing stays stuck at opacity:0 when the observer motion is off.
   ============================================================ */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.001ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.001ms !important;
    scroll-behavior: auto !important;
  }
  /* Force JS-driven reveal states to their final, visible position */
  .reveal,
  .split-word > span,
  .clip-reveal { opacity: 1 !important; transform: none !important; clip-path: none !important; }
  /* Full-screen motion overlays add nothing without animation — drop them */
  .curtain,
  .preloader { display: none !important; }
  /* Freeze looping marquees in place */
  .marquee__track,
  .foot__marquee-track { animation: none !important; }
  /* No pulsing dots / cursor-tracking spotlight */
  .nav__cta .dot,
  .foot__pulse { animation: none !important; box-shadow: none !important; }
  .spotlight::before { display: none !important; }
}
```

Most of this block is right — killing the curtain, the preloader, the marquees,
the pulsing dots and the spotlight is exactly correct, and forcing the reveal
states visible is the crucial safety net.

The problem is the first rule. `transition-duration: 0.001ms !important` on
`*, *::before, *::after` deletes **every** transition on the site, not just the
ones that move things. AUDIT §6 is explicit: *"Reduced motion means fewer and
gentler animations, **not zero** — keep transitions that aid comprehension,
remove position changes."*

What a reduced-motion user loses that they should keep:

- Every hover and focus colour change snaps — `.nav__drop-link`
  (`resources/css/core.css:404`), `.foot__col > a` (`:893`), every link and
  button in `pages.css`. A hard colour flip on hover is noisier than a 200ms
  fade, not calmer.
- The quote modal appears instantly with no opacity fade
  (`resources/css/pages.css:1717`), so a full-screen overlay materialises with
  zero warning — the exact "jarring change" animation exists to prevent.
- The cookie banner snaps into existence (`resources/css/pages.css:4660`).
- Accordion panels jump open with no continuity between the closed and open
  state.
- The mobile menu overlay appears instantly, full screen.

Vestibular triggers are *movement* — translation, scale, rotation, parallax,
looping motion. Opacity and colour are not. This block should target the former.

### ⚠ A conflicting recommendation exists — this plan overrides it

The `interaction-design` skill
(`~/.claude/skills/interaction-design/SKILL.md`, "Accessibility
Considerations") prescribes exactly the pattern this plan removes:

```css
/* interaction-design skill — do NOT apply this here */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

That snippet is the well-known blanket reset, and it is a reasonable default for
a codebase with no reduced-motion handling at all. This codebase is past that
point: it already has ten per-component `prefers-reduced-motion` blocks doing
the targeted work, and the blanket rule now only destroys the comprehension aids
those blocks were careful to keep.

**If you are working from that skill, this plan wins for this file.** AUDIT §6
is the governing rule here: *"Reduced motion means fewer and gentler animations,
not zero."* Do not re-introduce the global `transition-duration` kill.

## Target

Keep everything the current block does, except swap the blanket
`transition-duration` kill for a targeted one: null out the animatable
*transform* rather than the whole transition, and cap durations rather than
zeroing them.

```css
/* target — resources/css/core.css:1277-1305, replacing the whole block */
/* ============================================================
   REDUCED MOTION — honour the OS "reduce motion" preference.
   AUDIT §6: fewer and gentler, NOT zero. Movement (transform, parallax,
   looping animation) goes; opacity and colour transitions stay, because
   they aid comprehension and are not vestibular triggers.
   Every scroll-revealed element is still forced visible so nothing can
   stay stuck at opacity:0 when the observer motion is off.
   ============================================================ */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    /* Kill looping and keyframe motion outright. */
    animation-duration: 0.001ms !important;
    animation-iteration-count: 1 !important;
    /* Cap, don't delete: opacity/colour transitions survive but stay brisk. */
    transition-duration: 0.15s !important;
    transition-delay: 0s !important;
    scroll-behavior: auto !important;
  }

  /* No element moves, scales or rotates on a state change. Transitions on
     other properties are untouched. */
  *, *::before, *::after {
    transition-property: opacity, color, background-color, border-color,
                         box-shadow, fill, stroke, filter !important;
  }

  /* Force JS-driven reveal states to their final, visible position */
  .reveal,
  .split-word > span,
  .clip-reveal { opacity: 1 !important; transform: none !important; clip-path: none !important; }

  /* Full-screen motion overlays add nothing without animation — drop them */
  .curtain,
  .preloader { display: none !important; }

  /* Freeze looping marquees in place */
  .marquee__track,
  .foot__marquee-track { animation: none !important; }

  /* No pulsing dots / cursor-tracking spotlight */
  .nav__cta .dot,
  .foot__pulse { animation: none !important; box-shadow: none !important; }
  .spotlight::before { display: none !important; }

  /* Panels that must still explain themselves: fade in place, no travel. */
  .quote__panel,
  .cookies,
  .menu {
    transform: none !important;
  }
}
```

The `transition-property` allow-list is what does the real work. Any rule
transitioning `transform`, `width`, `padding`, `max-height`, `clip-path` or
`grid-template-rows` simply stops transitioning — the value still applies
instantly, exactly as today. Anything transitioning `opacity` or a colour keeps
a 150ms transition.

Two consequences to be aware of and accept:

- `.quote__panel` currently relies on `transform: translateY(40px) scale(0.97)`
  → `translateY(0) scale(1)` for its entrance
  (`resources/css/pages.css:1743-1745`). With `transform: none !important` it sits
  at its final position and fades in over 150ms via the `.quote` wrapper's
  opacity (`resources/css/pages.css:1717`). That is the correct reduced-motion
  behaviour: it appears, it does not fly.
- `.menu` is `transform: translateY(-100%)` when closed
  (`resources/css/core.css:526`). `transform: none !important` would make it
  **permanently visible**, covering the page. That is a bug — so `.menu` must
  **not** be in the `transform: none` list. Remove `.menu` from that final rule;
  `.menu`'s transform is a positioning mechanism, not an animation. It is listed
  above only to make this trap explicit. **Final rule should read:**

```css
  /* Panels that must still explain themselves: fade in place, no travel. */
  .quote__panel,
  .cookies {
    transform: none !important;
  }
```

Check `.cookies` for the same trap before shipping: `resources/css/pages.css:4660`
has `transform: translateY(120%)` in the closed state, so `transform: none`
would leave the banner permanently on screen. **`.cookies` has the same problem —
remove it too.** The final rule keeps only `.quote__panel`, whose closed state
is hidden by `opacity: 0` + `pointer-events: none` on the `.quote` wrapper
(`resources/css/pages.css:1714-1718`), not by its transform.

So the correct final rule is:

```css
  /* The modal panel appears in place rather than flying in; the .quote wrapper's
     opacity + pointer-events still hide it when closed, so nulling the panel's
     transform is safe. Do NOT add .menu or .cookies here — their transforms are
     how they stay off screen, not how they animate. */
  .quote__panel { transform: none !important; }
```

## Repo conventions to follow

- The reduced-motion block is a single `@media` at the very end of
  `resources/css/core.css`. Keep it there and keep the banner comment format.
- Per-component reduced-motion blocks already exist and stay as they are — they
  are the exemplar for the targeted approach this plan generalises:
  `resources/css/pages.css:54-56` (`.hero__slide { transition: none; }`),
  `:873-875` (`.why-list.reveal li`), `:2485-2490` (`.sproc__*`),
  `:3711-3713` (`.vacc__row`), `:4857` (`.work-tile`), and
  `resources/css/core.css:692-694`, `:849-851` (marquees).
- `!important` is used throughout this block by necessity; keep it.

## Steps

1. In `resources/css/core.css`, replace the entire block at lines 1277-1305 with
   the target above, using the corrected final rule (`.quote__panel` only — not
   `.menu`, not `.cookies`).
2. Re-read the finished block and confirm no selector whose *resting* state
   depends on a transform appears under `transform: none !important`. Search the
   repo for other such elements before finishing:
   `grep -n "transform: translateY(1" resources/css/*.css` and check each hit.
3. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT remove or weaken the `.reveal / .split-word > span / .clip-reveal`
  force-visible rule. If it is dropped, scroll-revealed content stays invisible
  forever for reduced-motion users. This is the single most important line in the
  block.
- Do NOT remove the `.curtain` / `.preloader` `display: none`.
- Do NOT add `transform: none !important` for `.menu` or `.cookies` — see above.
- Do NOT touch any of the per-component `@media (prefers-reduced-motion: reduce)`
  blocks in `resources/css/pages.css`.
- Do NOT change any JS. The `reduce` checks in `resources/js/core.js` (lines 12,
  348, 406, 525, 796, 932, 947, 999) are correct and stay.
- If `resources/css/core.css:1288` does not read
  `    transition-duration: 0.001ms !important;`, the file has drifted since
  commit 8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
  `grep -n "transition-duration: 0.001ms" resources/css/core.css` returns no
  matches.
- **Feel check**: turn on `prefers-reduced-motion: reduce` (DevTools → Rendering
  → "Emulate CSS media feature prefers-reduced-motion") and walk the site. In
  this mode:
  - **Nothing may move.** Hover every interactive element you can find — nav
    links, footer links, service rows, timeline rows, buttons, cards, the footer
    social circles. Colours and borders may fade. **Nothing may slide, scale,
    lift or rotate.** If anything moves, a `transition-property` entry is wrong.
  - Nav links: the hovered label must change colour without the two-layer
    vertical roll (`resources/css/core.css:343-356`).
  - Scroll the homepage top to bottom: every section must be visible. Nothing may
    be stuck invisible. Nothing may rise or fade in on scroll.
  - Open the quote modal: it must appear with a short fade, **in place**, not
    flying up from below. Close it — it must fade out.
  - Open the mobile menu at <1080px: it must appear covering the page and
    **disappear completely** when closed. If it stays on screen, `.menu` was
    wrongly given `transform: none` — fix immediately.
  - Clear `localStorage` and reload: the cookie banner must appear and then
    **disappear** when dismissed. Same trap as the menu.
  - Click a nav link: no curtain, no preloader, immediate navigation.
  - Confirm the marquees are frozen and the red dots are not pulsing.
  - Open an FAQ item: it must open instantly with no height animation, and stay
    open.
  - Now turn reduced motion **off** and confirm the full-motion site is
    completely unchanged from before this plan.
- **Done when**: with reduced motion on, no element on any page changes position,
  size or rotation as a result of a transition, while hover colour changes, the
  modal fade and the cookie banner fade all still occur — and the menu, cookie
  banner and modal all still hide correctly when closed.
