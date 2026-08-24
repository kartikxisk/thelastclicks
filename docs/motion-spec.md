# Motion specification

The rules the front end's animation obeys, and why each one exists.

Supersedes `plans/001`–`plans/015`, which diagnosed these problems one at a
time. Those files are kept under `docs/archive/blade-motion-plans/` because they
record the reasoning, and the reasoning outlives the code it was written about.
The trailing `*(nnn)*` on each rule points back at the plan that found it.

## Tokens

The three easing curves are declared once, on `:root` in
`resources/css/core.css`. The duration scale below is the intended vocabulary
but is **not** declared there yet — durations are still inlined per rule, which
is exactly what 011 objected to. Adding `--dur-fast/base/slow` to `core.css` and
migrating callers is outstanding work.

Never inline a curve — a timing change should be one edit.

| Token | Value | Use |
|---|---|---|
| `--dur-fast` | 180ms | Hovers, focus rings, filter chips |
| `--dur-base` | 420ms | Entrances, disclosures, the nav's state swap |
| `--dur-slow` | 800ms | Hero crossfades, image scale on hover |
| `--ease` | `cubic-bezier(0.16, 1, 0.3, 1)` | Default. Ease-out: fast departure, long settle |
| `--ease-2` | `cubic-bezier(0.65, 0, 0.35, 1)` | Symmetric, for things that leave and return |
| `--ease-3` | `cubic-bezier(0.85, 0, 0.15, 1)` | Sharp both ends, for wipes and curtains |

*From 011, which found duplicate curves and no duration scale at all.*

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

Was asserted by an end-to-end test (hovering a work tile must not change its
bounding box). That suite lived in the deleted `web/` app; the rule now rests on
review alone, and is worth re-asserting if a browser test harness returns.

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

The bar the deleted e2e suite held it to, still the right one: after scrolling
the homepage, at most a handful of elements may carry a non-`auto`
`will-change`.

### Reduced motion is gentler, not zero

Durations collapse to ~150ms rather than 0. Removing motion outright loses the
spatial cues that make navigation legible — a panel that snaps into place reads
as a bug, not as an accommodation. *(008)*

Genuine exceptions, where the motion *is* the thing being objected to, opt out
entirely: momentum scroll and the route wipe.

### Nothing polls

The nav's scrolled state comes from a passive scroll listener coalesced into a
frame, with the threshold measured once and on resize. The Blade version polled
every 100ms, which could lag a fast scroll by a tenth of a second and forced a
layout read ten times a second forever. *(009, 003)*

The same rule governs the rAF loops in `core.js` and `scene.js`: they do work
only while something is actually animating or near the viewport, never on a
permanent timer. *(003)*

### Nothing scales from zero

An element that grows from `scale(0)` reads as materialising out of nothing.
Entrances start at `0.96` and move. *(012)*

Applied to the lightbox, whose entrance also carries an 8px rise. *(014)*

### Curtains never outlast the navigation

The route wipe is 600ms end to end and `pointer-events: none` throughout. A
transition that is still running when the destination is ready makes the site
feel broken; one that eats a click is worse than none. *(001)*

### Nothing enters on scroll

Content is at its final position in the first paint. There are no scroll
reveals: no `[data-anim]` start state, no `.reveal` / `.split` / `.clip-reveal`
observer, no per-word split, and no `.is-leaving` dim as a section drifts off
centre.

The reveals were removed rather than retuned because their failure mode is
unacceptable and unavoidable. Every one of them hid real copy behind a trigger,
so a slow parse, a restored scroll position, a stale cached bundle or any script
error left the page showing nothing — indistinguishable from an overlay lying
over the site. The observer here had already grown four separate failsafes
(three timeouts, a scroll listener and a rAF pair) chasing that, which is the
tell: a decoration that needs a safety net to avoid hiding the product is not
worth the product.

The `data-anim` and `data-split` attributes are still in the Blade templates and
are inert. Leave them; do not wire a new trigger to them.

What stays: hover and focus feedback, the route curtain, the hero slideshow, the
work marquee, the decorative `.scenebg` backdrops, `[data-float]`, and the
counters. None of those gate content on scroll.

### Scroll is never locked

No pinning, no scroll-jacking, no swallowed keypresses. The services depth
scrub reads progress from the section's own rect rather than pinning it,
specifically so the scrollbar keeps telling the truth. *(002)*

The homepage hero broke this rule and was removed with the reveals: it held
`overflow: hidden` on both `<html>` and `<body>`, kept its own headline at
`opacity: 0`, and waited for a wheel, key or touch event before tweening either
back — so the site opened on an empty frame that would not scroll, for up to
four seconds if no input arrived.

### Grid filtering is a transition, not a swap

Filtering the work grid animates rather than replacing the set in one frame.
*(013)*

Live: `/portfolio` renders every published work once and the category/craft
chips filter client-side, so the transition is the only thing telling the user
the set changed.

### Wizard steps move in the direction of travel

Forward slides in from the right, back from the left, so the motion agrees with
the mental model. *(015)*

Currently obsolete: the quote form is a single page rather than a wizard. Revive
this rule if it is ever split into steps.

## Where the code is

`resources/css/core.css` holds the tokens and the shared chrome's motion;
`resources/js/core.js` owns scroll-linked work (parallax, magnetics, cursor) and
`resources/js/scene.js` drives the decorative backdrops only — which are near
the viewport, and when their loops may run. `resources/js/reveals.js` is gone.
