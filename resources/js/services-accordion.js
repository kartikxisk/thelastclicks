import { animate } from 'motion';

/**
 * Hover-expanded services list.
 *
 * The row under the cursor grows and the others give up exactly the space it
 * takes, so the section's total height never changes and nothing below it moves.
 * That is the whole reason the shares are computed rather than eyeballed: an
 * expanded row that added height would shove the rest of the page down every
 * time the cursor crossed the list.
 *
 *   hovered   = base * (1 + SPREAD)
 *   the rest  = base * (1 - SPREAD / (n - 1))
 *
 * ...which sums to n * base for any row count.
 *
 * Height, not transform. The motion spec's transform-only rule is about scroll
 * and entrance animation, where a layout pass per frame is the difference
 * between 60fps and jank; this is a hover on three rows, and scaling them would
 * distort the artwork and the type instead of actually giving the row more room.
 */

const SPREAD = 0.55;
const SPRING = { type: 'spring', stiffness: 220, damping: 30, mass: 0.9 };

export function initServicesAccordion() {
  const list = document.querySelector('[data-svc-accordion]');

  if (!list) {
    return;
  }

  const rows = [...list.querySelectorAll('.svc')];

  // Below two rows there is nothing to trade space with.
  if (rows.length < 2) {
    return;
  }

  // No hover to speak of, and on a phone the rows are already as tall as the
  // screen wants them. Touch would fire this on tap and fight the navigation.
  const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!canHover || reduced) {
    return;
  }

  // Measured once, from the CSS, so the clamp() that sizes a row stays the single
  // source of truth. Re-measured on resize because that clamp is viewport-based.
  let base = 0;

  const measure = () => {
    // Hand the rows back to the stylesheet before reading, or we would measure
    // our own last animation instead of the CSS.
    rows.forEach((row) => {
      row.style.height = '';
      row.style.minHeight = '';
    });

    base = rows[0].getBoundingClientRect().height;

    // .svc carries a min-height that sizes the row for its artwork. It is the
    // right base, and the wrong floor: min-height beats height, so leaving it set
    // would let rows grow and silently refuse to shrink. Taken over here, only
    // once the value has been read out of it.
    rows.forEach((row) => {
      row.style.minHeight = '0px';
      row.style.height = `${base}px`;
    });

    // Pin the list so the rows can be sized freely without the section reflowing
    // around them mid-hover.
    list.style.minHeight = `${base * rows.length}px`;
  };

  const settle = (hovered) => {
    const shrunk = base * (1 - SPREAD / (rows.length - 1));

    rows.forEach((row) => {
      const target = hovered === null
        ? base
        : (row === hovered ? base * (1 + SPREAD) : shrunk);

      animate(row, { height: `${target}px` }, SPRING);
    });
  };

  rows.forEach((row) => {
    row.addEventListener('pointerenter', () => settle(row));
    // Keyboard parity: tabbing through the list should show the same thing the
    // cursor does, or the expanded artwork is mouse-only.
    row.addEventListener('focusin', () => settle(row));
  });

  list.addEventListener('pointerleave', () => settle(null));
  list.addEventListener('focusout', (e) => {
    if (!list.contains(e.relatedTarget)) {
      settle(null);
    }
  });

  measure();

  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(measure, 150);
  });
}
