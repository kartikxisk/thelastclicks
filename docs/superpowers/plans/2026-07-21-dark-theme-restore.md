# Dark Theme Restore Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restore the original dark palette site-wide, colors only (typography trim stays), in one task.

**Architecture:** Value swap in `resources/css/core.css` `:root` + shadows + cursor SVG, four hardcoded-color fixes in `pages.css`, one meta tag in the layout. Old values come from the spec's restore map (verified against `git show HEAD:resources/css/core.css`).

**Tech Stack:** CSS variables; Pest suite as regression net.

**Spec:** `docs/superpowers/specs/2026-07-21-dark-theme-restore-design.md`

## Global Constraints

- **Do NOT git commit.** User rule: leave all changes uncommitted.
- Colors only — do not touch font sizes, spacing, layout, or any non-color declaration.
- Working tree holds unrelated uncommitted feature work (portfolio revamp, user edits) — touch ONLY the color values named below.
- After the task: `php artisan test && vendor/bin/pint --dirty` green (phpstan unaffected by CSS; skip).

---

### Task 1: One-shot dark restore

**Files:**
- Modify: `resources/css/core.css:5,9-22,53,942-943`
- Modify: `resources/css/pages.css:1566,1603,1811,4553`
- Modify: `resources/views/components/layouts/app.blade.php:17`

**Interfaces:** none downstream.

- [ ] **Step 1: core.css `:root` palette (lines 9-22)**

```css
  --ink: #0a0a0a;
  --ink-2: #111111;
  --ink-3: #161616;
  --ink-4: #1c1c1c;
  --line: #262626;
  --line-2: #1f1f1f;
  --paper: #f4f3ef;
  --paper-dim: #c7c5be;
  --muted: #8a8784;
  --muted-2: #6a6864;
  --red: #e80f03;
  --red-deep: #b80b02;
  --red-soft: rgba(232,15,3,0.18);
```

Delete the `--gold: #c9a24b;` line (grep confirms zero `var(--gold)` usages). Update the palette comment on line ~5 to describe dark backgrounds / light text / `#e80f03` accent.

- [ ] **Step 2: core.css cursor + shadows**

Line 53 cursor SVG: `fill='%23ec3a36'` → `fill='%23e80f03'`.

Lines 942-943:

```css
  --sh-red-glow: 0 12px 32px -8px rgba(232,15,3,0.32), 0 0 0 1px rgba(232,15,3,0.18);
  --sh-card: 0 18px 40px -12px rgba(5,5,5,0.5), 0 0 0 1px rgba(255,255,255,0.04);
```

- [ ] **Step 3: pages.css hardcoded colors**

Check each against `git show HEAD:resources/css/pages.css` (same line vicinity; find by selector) and restore the HEAD value:
- `:1566` `background: #faf6f1;` → HEAD value for that selector (dark equivalent).
- `:1603` gradient `linear-gradient(165deg, #f3ece4 0%, #efe6db 60%, var(--red) 220%)` → HEAD gradient for that selector.
- `:1811` gradient `linear-gradient(120deg, #f3ece4 0%, #efe6db 80%)` → HEAD gradient.
- `:4553` `.cookies__btn--red:hover` `#c62b26` (×2) → `var(--red-deep)` or HEAD's literal, whichever HEAD used.

If a selector did not exist at HEAD (new since), map pastel → dark by the spec table (`#f3ece4`→`#111111`, `#efe6db`→`#161616`, `#faf6f1`→`#0a0a0a`, `#c62b26`→`#b80b02`) and note it in the report.

- [ ] **Step 4: layout meta**

`resources/views/components/layouts/app.blade.php:17`: `<meta name="theme-color" content="#faf6f1">` → `content="#0a0a0a"`.

- [ ] **Step 5: Verification**

Run: `grep -rn "#faf6f1\|#f3ece4\|#efe6db\|#e9ddcf\|#e5d9cc\|#ecdfd3\|#3d3733\|#6b625c\|#94897f\|#a89d92\|#c97481\|#a85a68\|#c9a24b\|#ec3a36\|#c62b26\|var(--gold)" resources/css resources/views`
Expected: no output.

Run: `php artisan test && vendor/bin/pint --dirty`
Expected: all green. Leave uncommitted.
