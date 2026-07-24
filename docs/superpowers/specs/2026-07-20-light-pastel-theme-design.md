# Light Pastel Wedding Theme + Typography Trim — Design Spec

**Date:** 2026-07-20
**Scope:** Whole public site (all pages). Filament admin, favicons/app icons, logo assets are out of scope.
**Approach:** CSS variable value swap + hardcoded color audit. Existing variable names are kept (`--ink` family becomes light backgrounds, `--paper` family becomes dark text) to avoid HTML/CSS churn.

## 1. Palette (`:root` in `resources/css/core.css`)

| Var | Old (dark) | New (pastel light) | Role |
|---|---|---|---|
| `--ink` | `#0a0a0a` | `#faf6f1` | page background (ivory) |
| `--ink-2` | `#111111` | `#f3ece4` | raised surface (champagne) |
| `--ink-3` | `#161616` | `#efe6db` | card surface |
| `--ink-4` | `#1c1c1c` | `#e9ddcf` | deepest surface |
| `--line` | `#262626` | `#e5d9cc` | borders |
| `--line-2` | `#1f1f1f` | `#ecdfd3` | subtle borders |
| `--paper` | `#f4f3ef` | `#3d3733` | primary text (warm charcoal) |
| `--paper-dim` | `#c7c5be` | `#6b625c` | secondary text |
| `--muted` | `#8a8784` | `#94897f` | muted text |
| `--muted-2` | `#6a6864` | `#a89d92` | faint text |
| `--red` | `#e80f03` | `#c97481` | accent (blush rose) |
| `--red-deep` | `#b80b02` | `#a85a68` | accent hover (dusty rose) |
| `--red-soft` | `rgba(232,15,3,0.18)` | `rgba(201,116,129,0.16)` | accent wash |
| `--gold` *(new)* | — | `#c9a24b` | eyebrows, small celebration accents |

Shadows: `--sh-red-glow` → soft blush glow (`rgba(201,116,129,…)`); `--sh-card` → light warm shadow (`rgba(61,55,51,0.12)` range, no white inset ring).

Custom cursor SVG red dot → blush `#c97481`.

## 2. Typography — moderate trim (~30% off display sizes)

Desktop max values; `vw` coefficients scaled proportionally so tablet sizes shrink too. Body text untouched.

| Element | Old | New |
|---|---|---|
| Hero title / svp-hero h1 | 128px | 88px |
| Footer wordmark | 180px | 120px |
| Big numeric/stat display | 160px | 108px |
| Page headers | 110px | 80px |
| Section titles | 64px | 48px |
| Sub-heads | 48px | 36px |
| Mid heads (44/38/36px tier) | — | ~-25% |
| Mobile breakpoint overrides | — | trimmed proportionally |

## 3. Hardcoded color sweep

~100 literals across `core.css`/`pages.css` (`#fff`, `#000`, `rgba(255,…)`, `rgba(0,…)`) re-mapped case-by-case:

- Text that was white-on-dark chrome → `var(--paper)` / charcoal.
- White text **over photos/video** → stays white (scrims keep it readable).
- Dark overlays/scrims on media → stay dark.
- White borders/insets on dark chrome → warm line colors.
- Inline styles in 4 blade views (`blog/show`, `components/layouts/app`, `portfolio/index`, `portfolio/show`) fixed with same rules.

## 4. Media stays cinematic

Hero video, film-strip cards, portfolio covers keep dark scrims + white overlay text. Pastel chrome frames cinematic media — photography keeps premium contrast, text over video stays readable.

## 5. Verification

- `npm run build` clean.
- Drive homepage, portfolio index/show, services, about, contact in browser; screenshot check.
- Contrast: charcoal `#3d3733` on ivory `#faf6f1` ≈ 9.4:1 (AAA). Blush reserved for accents/large text, not body copy.
- Existing PHPUnit suite passes.
