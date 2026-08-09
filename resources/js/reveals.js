import { animate, inView } from 'motion';

/**
 * Section reveals, driven by motion.dev.
 *
 * Replaces the CSS-transition half of the [data-anim] system. The markup
 * contract is unchanged — every `data-anim="rise"` in the Blade keeps working —
 * but the playback moves from one shared `transition:` declaration to a per-type
 * curve. That is the whole reason for the swap: a single transition list has to
 * pick one duration and one easing for thirteen different gestures, so a curtain
 * wipe and a card pop were forced to share a timing that suited neither. Springs
 * are also simply not expressible in CSS transitions.
 *
 * The CSS still owns the START state (see [data-anim] in pages.css). That
 * matters: it is applied before this module parses, so nothing flashes unstyled
 * on a slow connection. This module animates from that state to the end state,
 * then adds `.is-in` so the existing `will-change: auto` cleanup still fires.
 */

// Curves, named for what they are for rather than for their numbers.
const SETTLE = { type: 'spring', stiffness: 140, damping: 22, mass: 1 };
const POP = { type: 'spring', stiffness: 260, damping: 18, mass: 0.8 };
const GLIDE = { duration: 0.9, ease: [0.16, 1, 0.3, 1] };
const WIPE = { duration: 1.1, ease: [0.85, 0, 0.15, 1] };
const FOCUS = { duration: 0.95, ease: [0.16, 1, 0.3, 1] };

/**
 * from → to per type. Declared explicitly rather than read off the computed
 * style: a half-finished transform is unreadable mid-flight, and an element
 * revealed while its parent is still animating would otherwise inherit a
 * nonsense starting point.
 */
const ANIMS = {
  'rise': { from: { opacity: 0, y: 46 }, to: { opacity: 1, y: 0 }, opts: SETTLE },
  'mask-up': {
    from: { opacity: 0, y: 28, clipPath: 'inset(100% 0 0 0)' },
    to: { opacity: 1, y: 0, clipPath: 'inset(0% 0 0 0)' },
    opts: GLIDE,
  },
  'mask-down': {
    from: { opacity: 0, y: -24, clipPath: 'inset(0 0 100% 0)' },
    to: { opacity: 1, y: 0, clipPath: 'inset(0 0 0% 0)' },
    opts: GLIDE,
  },
  'curtain': {
    from: { opacity: 0, clipPath: 'inset(0 100% 0 0)' },
    to: { opacity: 1, clipPath: 'inset(0 0% 0 0)' },
    opts: WIPE,
  },
  'curtain-r': {
    from: { opacity: 0, clipPath: 'inset(0 0 0 100%)' },
    to: { opacity: 1, clipPath: 'inset(0 0 0 0%)' },
    opts: WIPE,
  },
  'iris': {
    from: { opacity: 0, clipPath: 'circle(0% at 50% 50%)' },
    to: { opacity: 1, clipPath: 'circle(85% at 50% 50%)' },
    opts: WIPE,
  },
  'blur-focus': {
    from: { opacity: 0, filter: 'blur(16px)', scale: 1.06 },
    to: { opacity: 1, filter: 'blur(0px)', scale: 1 },
    opts: FOCUS,
  },
  'depth': {
    from: { opacity: 0, z: -180, rotateX: 9 },
    to: { opacity: 1, z: 0, rotateX: 0 },
    opts: SETTLE,
  },
  'depth-out': { from: { opacity: 0, z: 220 }, to: { opacity: 1, z: 0 }, opts: SETTLE },
  'rotate-in': {
    from: { opacity: 0, rotate: -5, scale: 0.9 },
    to: { opacity: 1, rotate: 0, scale: 1 },
    opts: POP,
  },
  'slide-l': { from: { opacity: 0, x: -64 }, to: { opacity: 1, x: 0 }, opts: SETTLE },
  'slide-r': { from: { opacity: 0, x: 64 }, to: { opacity: 1, x: 0 }, opts: SETTLE },
  'scale-frame': { from: { opacity: 0, scale: 0.86 }, to: { opacity: 1, scale: 1 }, opts: POP },

  // New with the engine — these need a spring or a compound transform, so the
  // CSS transition system could not express them.
  'flip-up': {
    from: { opacity: 0, rotateX: 34, y: 40, transformPerspective: 900 },
    to: { opacity: 1, rotateX: 0, y: 0 },
    opts: SETTLE,
  },
  'skew-in': {
    from: { opacity: 0, skewY: 4, y: 40 },
    to: { opacity: 1, skewY: 0, y: 0 },
    opts: SETTLE,
  },
  'pop': { from: { opacity: 0, scale: 0.72 }, to: { opacity: 1, scale: 1 }, opts: POP },
};

/** Horizontal travel is halved on narrow screens — 64px is a long way on a phone. */
function scaleForViewport(from) {
  if (window.innerWidth > 860) {
    return from;
  }

  const scaled = { ...from };
  if (typeof scaled.x === 'number') scaled.x *= 0.44;
  if (typeof scaled.y === 'number') scaled.y *= 0.6;

  return scaled;
}

export function initReveals() {
  const targets = [...document.querySelectorAll('[data-anim]')];

  if (!targets.length) {
    return;
  }

  // Tell the stylesheet to stand down. Set here rather than in the template so
  // that a JS failure leaves the CSS transitions in charge instead of leaving
  // every reveal stuck at opacity:0 forever.
  document.documentElement.classList.add('js-motion');

  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const settle = (el) => {
    el.classList.add('is-in');
    el.style.willChange = '';
  };

  if (reduced) {
    // Reduced motion is gentler, not absent (docs/motion-spec.md): a short fade,
    // no travel.
    targets.forEach((el) => {
      animate(el, { opacity: [0, 1] }, { duration: 0.2 }).finished.then(() => settle(el));
    });

    return;
  }

  // Everything still waiting for its cue. An element leaves this set the moment
  // it plays, whichever path got to it first.
  const pending = new Set();

  targets.forEach((el) => {
    const spec = ANIMS[el.dataset.anim];

    // An unknown data-anim value must still end up visible. Failing open here is
    // the difference between a missing animation and missing content.
    if (!spec) {
      settle(el);

      return;
    }

    // scene.js already wrote --i for [data-stagger] groups; reuse it so both
    // engines agree on cascade order rather than each inventing one.
    const index = Number.parseInt(el.style.getPropertyValue('--i'), 10) || 0;
    const step = Number.parseFloat(el.style.getPropertyValue('--anim-step')) || 90;
    const delay = (index * step) / 1000;

    const from = scaleForViewport(spec.from);
    const keyframes = {};
    for (const key of Object.keys(spec.to)) {
      keyframes[key] = [from[key], spec.to[key]];
    }
    // Perspective is a one-shot value, not something to tween between.
    if (from.transformPerspective) {
      keyframes.transformPerspective = from.transformPerspective;
    }

    const play = () => {
      if (!pending.has(el)) {
        return;
      }
      pending.delete(el);

      el.style.willChange = 'transform, opacity, filter, clip-path';
      animate(el, keyframes, { ...spec.opts, delay })
        .finished.then(() => settle(el))
        .catch(() => settle(el));
    };

    el._reveal = play;
    pending.add(el);

    // Already scrolled past — a restored scroll position can put an element
    // above the fold before anything observes it. Show it outright rather than
    // animating something nobody will see.
    if (el.getBoundingClientRect().bottom <= 0) {
      pending.delete(el);
      settle(el);

      return;
    }

    // One-shot by construction: play() is a no-op once the element has left
    // `pending`, so a scroll back up the page cannot re-animate a section.
    inView(el, () => play(), { margin: '0px 0px -2% 0px', amount: 0.05 });
  });

  /**
   * IntersectionObserver is the trigger, not the guarantee.
   *
   * A fast flick — or programmatic scrolling — can carry the viewport across an
   * element between two IO dispatches, and the browser coalesces that into a
   * single "not intersecting" callback that never fires. The element then sits
   * at opacity 0 forever, which is content loss, not a missing animation. The
   * CSS engine this replaced ran the same sweep for the same reason; dropping it
   * left eleven of seventeen reveals stuck invisible after a quick scroll.
   */
  const sweep = () => {
    if (!pending.size) {
      return;
    }

    const vh = window.innerHeight;

    for (const el of [...pending]) {
      const r = el.getBoundingClientRect();

      if (r.bottom <= 0) {
        pending.delete(el);
        settle(el);
      } else if (r.top < vh * 1.15) {
        el._reveal();
      }
    }
  };

  requestAnimationFrame(() => requestAnimationFrame(sweep));
  [200, 700, 1500].forEach((t) => setTimeout(sweep, t));

  let frame = 0;
  window.addEventListener('scroll', () => {
    if (frame) {
      return;
    }
    frame = requestAnimationFrame(() => {
      frame = 0;
      sweep();
    });
  }, { passive: true });
}
