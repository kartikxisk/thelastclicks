# Dark Theme Restore Design

**Date:** 2026-07-21
**Status:** Approved

## Goal

Restore the original dark palette site-wide ("dark mode like before"), undoing the light-pastel palette from `2026-07-20-light-pastel-theme-design.md`. Colors only — the typography trim from that change stays.

## Decisions (from brainstorming)

- Dark only, permanent. No light mode, no toggle.
- Colors only: keep the ~30% display-type trim and any non-color changes from the pastel pass.
- Accent returns to the original red `#e80f03` / `#b80b02` (current `#ec3a36` divergence discarded).
- `--gold` variable (added by the pastel pass) is removed along with its usages.
- Reference source of truth for old values: the pastel spec's "Old (dark)" table and the HEAD commit's `resources/css/core.css` (dark palette is still committed there).

## Restore map (`:root` in `resources/css/core.css`)

| Var | Restore to |
|---|---|
| `--ink` | `#0a0a0a` |
| `--ink-2` | `#111111` |
| `--ink-3` | `#161616` |
| `--ink-4` | `#1c1c1c` |
| `--line` | `#262626` |
| `--line-2` | `#1f1f1f` |
| `--paper` | `#f4f3ef` |
| `--paper-dim` | `#c7c5be` |
| `--muted` | `#8a8784` |
| `--muted-2` | `#6a6864` |
| `--red` | `#e80f03` |
| `--red-deep` | `#b80b02` |
| `--red-soft` | `rgba(232,15,3,0.18)` |
| `--gold` | remove (and remove all usages) |

Shadows (`--sh-red-glow`, `--sh-card`) and the custom cursor SVG dot restore to their HEAD values.

## Hardcoded color audit (reverse pass)

- Grep the pastel-era hexes and washes across `resources/css/` and all blade `<style>` blocks: `#faf6f1 #f3ece4 #efe6db #e9ddcf #e5d9cc #ecdfd3 #3d3733 #6b625c #94897f #a89d92 #c97481 #a85a68 #c9a24b #ec3a36 #c62b26` plus light `rgba(...)` washes introduced by the pastel audit.
- Each hit restores to its dark-era equivalent, using `git show HEAD:<file>` as the reference where the file existed at HEAD.
- Sections authored after the pastel pass that already use dark idiom (portfolio `pfx-*` scrims `rgba(0,0,0,…)`, `#fff` text on media) stay as they are.
- `--gold` usages (eyebrows/accents) revert to whatever those elements used at HEAD (typically `--red` or `--muted`); where the element is new since HEAD, use `--red`.

## Verification

- Grep proves zero pastel hexes remain anywhere in `resources/`.
- Full Pest suite stays green (tests are content-based).
- Visual spot check: homepage, portfolio index, case page, a legal page render with dark background, light text, red accent.

## Out of scope

- Typography sizes, spacing, layout (trim stays).
- Filament admin, favicons, logo assets (were out of scope for the pastel pass too).
- Light-mode support of any kind.
