# Motion specification

The rules the frontend's animation obeys, and why each one exists.

Supersedes `plans/001`–`plans/015`, which diagnosed these problems against the
Blade implementation. Those files are kept under `docs/archive/blade-motion-plans/`
because they record the reasoning, and the reasoning outlives the code it was
written about.

Where a rule below says "ported", the fix landed in the Next application; where
it says "obsolete", the Blade construct it corrected does not exist here.

## Tokens

Declared once in `web/src/styles/motion.css` and `web/src/styles/tokens.css`.
Never inline a duration or a curve — a timing change should be one edit.

| Token | Value | Use |
|---|---|---|
| `--dur-fast` | 180ms | Hovers, focus rings, filter chips |
| `--dur-base` | 420ms | Entrances, disclosures, the nav's state swap |
| `--dur-slow` | 800ms | Hero crossfades, image scale on hover |
| `--ease` | `cubic-bezier(0.16, 1, 0.3, 1)` | Default. Ease-out: fast departure, long settle |
| `--ease-2` | `cubic-bezier(0.65, 0, 0.35, 1)` | Symmetric, for things that leave and return |
| `--ease-3` | `cubic-bezier(0.85, 0, 0.15, 1)` | Sharp both ends, for wipes and curtains |

*Ported from 011, which found duplicate curves and no duration scale at all.*

## Rules

### Entrances ease out, never ease in-out

An entrance that starts slowly reads as hesitant. `--ease` departs immediately
and settles long, which is what makes an element feel like it arrived rather
than faded up. *(004)*

### Nothing animates a layout property

Hovers, progress bars and tiles animate `transform` and `opacity` only. Animating
`width`, `padding`, `margin` or `top` reflows the document on every frame, and
on a grid that means recalculating every sibling. The visible motion is
identical; the cost is not. *(005)*

Asserted by an end-to-end test: hovering a work tile must not change its
bounding box.

### Collapsibles use `grid-template-rows`, not `max-height`

`max-height` animates toward a guessed ceiling, so the easing is wrong whenever
the content is shorter than the guess — the last part of the open is a pause.
`grid-template-rows: 0fr → 1fr` eases to the content's real height. *(006)*

Applied to the service FAQ disclosures, which are native `<details>` so they
also work without JavaScript.

### `will-change` is set on start and cleared on finish

Held permanently it forces a compositor layer per element and exhausts GPU
memory on a long page. Set it when an animation begins, clear it in the
completion callback. *(007)*

Asserted: after scrolling the homepage, at most a handful of elements may carry
a non-`auto` `will-change`.

### Reduced motion is gentler, not zero

Durations collapse to ~150ms rather than 0. Removing motion outright loses the
spatial cues that make navigation legible — a panel that snaps into place reads
as a bug, not as an accommodation. *(008)*

Genuine exceptions, where the motion *is* the thing being objected to, opt out
entirely: Lenis momentum scroll, the route wipe, and all six WebGL moments.

### Nothing polls

The nav's scrolled state comes from a passive scroll listener coalesced into a
frame, with the threshold measured once and on resize. The Blade version polled
every 100ms, which could lag a fast scroll by a tenth of a second and forced a
layout read ten times a second forever. *(009, 003)*

The same rule governs WebGL: `frameloop="demand"`, and scenes call
`invalidate()` only while something is actually animating.

### Nothing scales from zero

An element that grows from `scale(0)` reads as materialising out of nothing.
Entrances start at `0.96` and move. *(012)*

Applied to the lightbox, whose entrance also carries an 8px rise. *(014)*

### Curtains never outlast the navigation

The route wipe is 600ms end to end and `pointer-events: none` throughout. A
transition that is still running when the destination is ready makes the site
feel broken; one that eats a click is worse than none. *(001)*

### Scroll is never locked

No pinning, no scroll-jacking, no swallowed keypresses. The services depth
scrub reads progress from the section's own rect rather than pinning it,
specifically so the scrollbar keeps telling the truth. *(002)*

### Grid filtering is a transition, not a swap

Filtering the work grid animates rather than replacing the set in one frame.
*(013)*

Partly obsolete: filtering is now a server-rendered navigation rather than
client state, so the route wipe covers the change. The rule stands for any
future in-place filtering.

### Wizard steps move in the direction of travel

Forward slides in from the right, back from the left, so the motion agrees with
the mental model. *(015)*

Currently obsolete: the quote form is a single page rather than a wizard. Revive
this rule if it is ever split into steps.

## What is not covered here

WebGL scene motion — displacement, curvature, depth scrub — lives with each
scene in `web/src/webgl/scenes/`. It follows the same easing and reduced-motion
rules, but its parameters are visual decisions rather than system tokens.
