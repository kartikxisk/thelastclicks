# Container Component Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** One `<x-container>` Blade component for horizontal page alignment, adopted across the entire frontend, replacing the `.wrap` alias and the portfolio pages' hardcoded gutters.

**Architecture:** Anonymous Blade component that attribute-merges the existing canonical `.container` class (`max-width: var(--maxw); margin: 0 auto; padding: 0 var(--pad-x)` — `resources/css/core.css:194-198`). Mechanical sweep converts every `.wrap`/`.container` div; portfolio index/show sections restructure to outer full-bleed `<section>` + inner `<x-container>`, dropping their hardcoded `40px/20px` horizontal padding.

**Tech Stack:** Laravel 11 Blade anonymous components, Pest (`$this->blade()` via InteractsWithViews).

**Spec:** `docs/superpowers/specs/2026-07-20-container-component-design.md`

## Global Constraints

- **Do NOT git commit.** User rule: leave all changes uncommitted; commit only on explicit ask. Every template "Commit" step = "leave uncommitted".
- Component contract (Task 2 depends on it): `resources/views/components/container.blade.php` renders `<div {{ $attributes->merge(['class' => 'container']) }}>{{ $slot }}</div>` — extra classes and style/attributes pass through.
- Gutter change is deliberate: fixed `40px/20px` becomes `var(--pad-x)` = `clamp(20px, 4vw, 56px)` + `max-width: var(--maxw)` centering. Do not "preserve" old paddings.
- Narrow reading measures are NOT containers: `blog/show.blade.php` `.art-*` sections (max-width 760px) stay untouched.
- Full-bleed elements stay outside containers: `.pfx-marquee`, hero backgrounds, section top/bottom borders.
- Working tree contains unrelated user edits (e.g. `resources/views/components/hero.blade.php` fallback tiles, `resources/views/home.blade.php` WHY US removal) — do not revert or "fix" them.
- After each task: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M` — all green.

---

### Task 1: Component + mechanical `.wrap`/`.container` sweep

**Files:**
- Create: `resources/views/components/container.blade.php`
- Test: `tests/Feature/Public/ContainerComponentTest.php` (new)
- Modify (blade sweep — every `class="wrap…"` div and footer's `class="container"` div):
  - `resources/views/home.blade.php:84,122,147,160,191,225`
  - `resources/views/contact.blade.php:36,62,74,111`
  - `resources/views/industries/index.blade.php:21,54,91`
  - `resources/views/blog/show.blade.php:137`
  - `resources/views/blog/index.blade.php:21,92,118`
  - `resources/views/pages/cookie-policy.blade.php:11`, `resources/views/pages/terms-of-service.blade.php:11`, `resources/views/pages/privacy-policy.blade.php:11`, `resources/views/pages/disclaimer.blade.php:11`
  - `resources/views/pages/about.blade.php:18,32,51,90,102,149`
  - `resources/views/services/show.blade.php:56,65,78,98,118,137,157,176,195,218,227`
  - `resources/views/components/footer.blade.php:7`
- Modify: `resources/css/pages.css:753` (`.cta-strip > .wrap` selector)
- Modify: `resources/css/core.css:194-198` (drop `.wrap` from selector)

**Interfaces:**
- Produces: `<x-container>` component per the Global Constraints contract. Task 2 uses it inside portfolio sections.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Public/ContainerComponentTest.php`:

```php
<?php

it('renders slot content inside the canonical container class', function () {
    $html = $this->blade('<x-container>Hello inside</x-container>')->toHtml();

    expect($html)->toContain('Hello inside')
        ->and($html)->toContain('class="container"');
});

it('merges extra classes and attributes', function () {
    $html = $this->blade('<x-container class="contact-grid" style="margin-bottom:32px">X</x-container>')->toHtml();

    expect($html)->toContain('class="container contact-grid"')
        ->and($html)->toContain('style="margin-bottom:32px"');
});
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test tests/Feature/Public/ContainerComponentTest.php`
Expected: FAIL — `Unable to locate a class or view for component [container]`.

- [ ] **Step 3: Create the component**

Create `resources/views/components/container.blade.php` (one line):

```blade
<div {{ $attributes->merge(['class' => 'container']) }}>{{ $slot }}</div>
```

- [ ] **Step 4: Run component test**

Run: `php artisan test tests/Feature/Public/ContainerComponentTest.php`
Expected: 2 passing.

- [ ] **Step 5: Blade sweep**

Transformation rules (apply to every listed site; closing `</div>` of each converted div becomes `</x-container>` — match them by indentation):

| Before | After |
|---|---|
| `<div class="wrap">` | `<x-container>` |
| `<div class="wrap contact-grid">` | `<x-container class="contact-grid">` |
| `<div class="wrap about-grid">` | `<x-container class="about-grid">` |
| `<div class="wrap" style="margin-bottom:32px">` | `<x-container style="margin-bottom:32px">` |
| `<div class="container">` (footer) | `<x-container>` |

Legal pages one-liner pattern (`pages/cookie-policy.blade.php:11` and 3 siblings):

```blade
{{-- before --}}
<section class="section"><div class="wrap"><div class="legal">
{{-- after --}}
<section class="section"><x-container><div class="legal">
```

with the matching `</div></div></section>` at the bottom of each file becoming `</div></x-container></section>` (verify each file's exact closing sequence before editing).

- [ ] **Step 6: CSS updates**

`resources/css/pages.css:753`: change `.cta-strip > .wrap,` to `.cta-strip > .container,` (child selector must keep matching the converted markup).

`resources/css/core.css:194-198`: remove the `.wrap` alias —

```css
/* before */
.container,
.wrap {
/* after */
.container {
```

and update the comment above it to drop the alias mention.

- [ ] **Step 7: Verification greps**

Run: `grep -rn 'class="wrap' resources/views` → expected: no output.
Run: `grep -rn '\.wrap\b' resources/css resources/js` → expected: no output.
Run: `grep -rn '<div class="container"' resources/views` → expected: no output.

- [ ] **Step 8: Full checks**

Run: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: all green (page tests assert content, not markup wrappers; if any test asserted `class="wrap"` markup, update it to the new component output and list the change in the report). Leave uncommitted.

---

### Task 2: Portfolio sections adopt the container

**Files:**
- Modify: `resources/views/portfolio/index.blade.php` (markup + its `<style>` block)
- Modify: `resources/views/portfolio/show.blade.php` (case-quote/case-cta markup + `<style>` block)
- Test: existing `tests/Feature/Public/PortfolioIndexTest.php`, `tests/Feature/Public/PortfolioPageTest.php` must stay green (content-based; no new tests needed — this is CSS/markup alignment)

**Interfaces:**
- Consumes: `<x-container>` from Task 1.

- [ ] **Step 1: Portfolio index markup**

In `resources/views/portfolio/index.blade.php`:

Hero — wrap contents:

```blade
    <section class="pfx-hero" data-screen-label="01 Intent">
        <x-container>
            <div class="pfx-hero__crumb">…(unchanged)…</div>
            <h1 data-split>Films that make <em>people act.</em></h1>
            <p class="pfx-hero__sub">…(unchanged)…</p>
            @if ($stats['count'])
                <p class="pfx-hero__proof">…(unchanged)…</p>
            @endif
        </x-container>
    </section>
```

Chips — container wraps the fieldset (fieldset keeps semantics per spec):

```blade
    @if ($chipServices->count() > 1)
        <x-container>
            <fieldset class="pfx-chips" aria-label="Filter by service">
                …buttons unchanged…
            </fieldset>
        </x-container>
    @endif
```

Reel stack — container becomes the flex column via a new inner class:

```blade
    <section class="pfx-stack" data-screen-label="02 Work">
        <x-container class="pfx-stack__rows">
            @foreach ($reels as $item)
                …rows unchanged…
            @endforeach
        </x-container>
    </section>
```

(The marquee section stays outside any container — full-bleed by design.)

- [ ] **Step 2: Portfolio index CSS**

In the same file's `<style>` block:

```css
/* before → after */
.pfx-hero { padding: 140px 40px 40px; }            → .pfx-hero { padding: 140px 0 40px; }
.pfx-chips { …; padding: 28px 40px 8px; border: 0; margin: 0; }
                                                   → .pfx-chips { …; padding: 28px 0 8px; border: 0; margin: 0; }
.pfx-stack { display: flex; flex-direction: column; gap: 22px; padding: 26px 40px 80px; }
                                                   → .pfx-stack { padding: 26px 0 80px; }
                                                     .pfx-stack__rows { display: flex; flex-direction: column; gap: 22px; }
```

Mobile block:

```css
/* before → after */
.pfx-hero { padding: 110px 20px 28px; }  → .pfx-hero { padding: 110px 0 28px; }
.pfx-chips { padding: 20px 20px 4px; }   → .pfx-chips { padding: 20px 0 4px; }
.pfx-stack { padding: 18px 20px 60px; gap: 14px; }
                                         → .pfx-stack { padding: 18px 0 60px; }
                                           .pfx-stack__rows { gap: 14px; }
```

- [ ] **Step 3: Portfolio show markup + CSS**

In `resources/views/portfolio/show.blade.php` wrap the two new sections' contents:

```blade
    <section class="case-quote">
        <x-container>
            <blockquote>…unchanged…</blockquote>
        </x-container>
    </section>

    <section class="case-cta">
        <x-container>
            <h2>Want a film like this?</h2>
            <div class="case-cta__actions">…unchanged…</div>
        </x-container>
    </section>
```

CSS in the same file's `<style>` block:

```css
/* before → after */
.case-quote { padding: 72px 40px; border-top: 1px solid var(--line); }
                                   → .case-quote { padding: 72px 0; border-top: 1px solid var(--line); }
.case-cta { padding: 80px 40px; border-top: 1px solid var(--line); text-align: center; }
                                   → .case-cta { padding: 80px 0; border-top: 1px solid var(--line); text-align: center; }
@media (max-width: 760px) { .case-quote, .case-cta { padding: 56px 20px; } }
                                   → @media (max-width: 760px) { .case-quote, .case-cta { padding: 56px 0; } }
```

(Section borders stay on the outer full-bleed `<section>` — correct per spec. `.case-body`, `.gallery`, `.case-credits`, `.case-next`, `.case-hero` already use the `max-width: var(--maxw)`/`var(--pad-x)` pattern — leave them alone. `blog/show.blade.php` `.art-*` reading measures — leave alone.)

- [ ] **Step 4: Verification greps**

Run: `grep -n "40px\|20px" resources/views/portfolio/index.blade.php resources/views/portfolio/show.blade.php | grep -v "padding: [0-9]*px 0\|140px 0\|110px 0" | grep "padding:.*px 40px\|padding:.*px 20px\|padding: 40px\|padding: 20px"`
Expected: no horizontal 40px/20px paddings remain in converted rules (other px values like element sizes are fine).

- [ ] **Step 5: Run page tests, then full checks**

Run: `php artisan test tests/Feature/Public/PortfolioIndexTest.php tests/Feature/Public/PortfolioPageTest.php`
Expected: PASS unchanged.
Then: `php artisan test && vendor/bin/pint --dirty && vendor/bin/phpstan analyse --memory-limit=512M`
Expected: all green. Leave uncommitted.
