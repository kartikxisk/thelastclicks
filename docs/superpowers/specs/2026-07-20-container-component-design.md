# Container Component Design

**Date:** 2026-07-20
**Status:** Approved

## Goal

One Blade component for horizontal page alignment, adopted across the whole frontend, replacing the `.wrap` alias and the 14 hardcoded `40px/20px` section paddings.

## Decisions (from brainstorming)

- Full sweep adoption (user choice): every `.wrap`/`.container` div and every hardcoded-padding section converts.
- Approach A: anonymous component wrapping the existing canonical `.container` CSS (max-width `var(--maxw)`, centered, `padding: 0 var(--pad-x)`). No props, no new CSS system.
- `.wrap` alias removed from CSS after the sweep (zero remaining usages).

## Architecture

### 1. Component

`resources/views/components/container.blade.php`:

```blade
<div {{ $attributes->merge(['class' => 'container']) }}>{{ $slot }}</div>
```

Attribute merge lets callers add extra classes (`<x-container class="pfx-hero__inner">`).

### 2. Sweep rules

- `<div class="wrap">…</div>` → `<x-container>…</x-container>` (extra classes carried via the class attribute).
- Footer's `<div class="container">` → `<x-container>`.
- Hardcoded-padding sections (portfolio index `pfx-hero`/`pfx-chips`/`pfx-stack`, portfolio show `case-quote`/`case-cta` and siblings, plus the remaining grep hits): outer `<section>` keeps vertical padding, background, borders; a new inner `<x-container>` takes over horizontal alignment; the page CSS drops its horizontal padding values (e.g. `padding: 140px 40px 40px` → `padding: 140px 0 40px`).
- Full-bleed elements stay outside the container (marquee strip, hero backgrounds); reel rows sit inside the container gutter as they already do inside the padded stack.
- Gutter change is deliberate: fixed 40px/20px becomes `var(--pad-x)` = `clamp(20px, 4vw, 56px)`, and wide screens gain `max-width: var(--maxw)` centering — this is the consistency win, not a regression.
- `.pfx-chips` is a `<fieldset>` — it keeps its element but gets wrapped by (or replaced with a class on) the container per simplest markup; implementation plan decides per call site with the rule: container handles alignment, existing element keeps semantics.

### 3. CSS cleanup

- Remove `.wrap` from the `.container, .wrap` selector in `resources/css/core.css` once no view uses it.
- Remove now-dead horizontal-padding declarations from page style blocks converted in the sweep.

## Testing

- Component test: renders slot content, merges extra classes onto `container`.
- Existing page tests must stay green (they assert content, not paddings).
- Sweep verification step: grep proves zero `class="wrap"` remain and zero hardcoded `40px` horizontal paddings remain in converted views.

## Out of scope

- Size/tag variants on the component (YAGNI).
- Vertical rhythm changes (`.section` spacing untouched).
- Any visual redesign beyond gutter alignment.
