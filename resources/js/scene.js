/* Scene engine.
 *
 * Drives the full-height scene sequence: which scene is on screen, how far it
 * has travelled, when its backdrop should run, and the order its content
 * reveals in. Deliberately thin — the reveals themselves are CSS transitions
 * keyed off `.is-in`, so this only decides *when*, never *how*.
 *
 * Everything it writes is a class or a custom property. It never reads layout
 * inside the scroll handler beyond one getBoundingClientRect per visible scene,
 * and it only runs for scenes currently near the viewport.
 */

export function initScenes() {
  const scenes = [...document.querySelectorAll('main > section')];
  if (!scenes.length) return;

  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

  scenes.forEach((s) => s.classList.add('scene'));

  /* -------------------- Stagger order -------------------- */
  // Children of a [data-stagger] group cascade in source order. Writing --i
  // here rather than in CSS means the cascade survives any number of children
  // and any reordering in the Blade template.
  // The cascade is capped, and the step shrinks for big groups. A 30-tile
  // portfolio grid at a flat 90ms per item would put the last tile 2.6s behind
  // the first — long enough that it reads as broken rather than staggered.
  const STAGGER_CAP = 9;
  document.querySelectorAll('[data-stagger]').forEach((group) => {
    const kids = [...group.querySelectorAll(':scope > [data-anim]')];
    const step = kids.length > 10 ? 45 : 90;
    kids.forEach((el, i) => {
      el.style.setProperty('--i', String(Math.min(i, STAGGER_CAP)));
      el.style.setProperty('--anim-step', `${step}ms`);
    });
  });

  /* -------------------- Which scenes are live -------------------- */
  // `is-near` runs the backdrop animations; anything further away is parked.
  const near = new Set();
  const nearIO = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      e.target.classList.toggle('is-near', e.isIntersecting);
      if (e.isIntersecting) near.add(e.target); else near.delete(e.target);
    });
    if (near.size) start(); else stop();
    // 20%, not more: this is what decides how many backdrops loop at once, and
    // three simultaneous scenes is already generous for a snap-scrolled page.
  }, { rootMargin: '20% 0px' });
  scenes.forEach((s) => nearIO.observe(s));

  // `is-live` is the tighter gate that actually starts the backdrop loops. The
  // 20% margin above exists so --p is already correct by the time a scene
  // reaches the fold; running the animations that early costs a full re-raster
  // per frame for a backdrop still below the viewport, so they wait for this.
  const liveIO = new IntersectionObserver((entries) => {
    entries.forEach((e) => e.target.classList.toggle('is-live', e.isIntersecting));
  }, { rootMargin: '0px' });
  scenes.forEach((s) => liveIO.observe(s));

  /* -------------------- Scroll-linked progress -------------------- */
  // --p runs -1 (scene below the fold) .. 0 (centred) .. 1 (scene above it).
  // Parallax and depth layers read it straight out of CSS.
  let raf = 0;
  function frame() {
    const vh = window.innerHeight;
    for (const s of near) {
      const r = s.getBoundingClientRect();
      const centre = r.top + r.height / 2;
      const p = Math.max(-1.5, Math.min(1.5, (vh / 2 - centre) / vh));
      s.style.setProperty('--p', p.toFixed(4));
      // A scene mostly out of frame recedes rather than cutting, so one snap
      // reads as a dissolve into the next.
      s.classList.toggle('is-leaving', !reduce && Math.abs(p) > 0.62);
    }
    raf = requestAnimationFrame(frame);
  }
  function start() { if (!raf) raf = requestAnimationFrame(frame); }
  function stop() { cancelAnimationFrame(raf); raf = 0; }

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) stop(); else if (near.size) start();
  });

  /* -------------------- Freeze backdrops while scrolling -------------------- */
  // A backdrop that animates cannot be cached: its mask + opacity put the whole
  // viewport-sized box on one render surface, so a single looping child forces
  // that surface to re-raster every frame. Measured on an Intel UHD 630, that is
  // the entire difference between 30fps and 60fps while scrolling — a static
  // backdrop costs the same as no backdrop at all.
  //
  // So the loops stop for the duration of the scroll and resume once it settles.
  // It has to be `animation: none` rather than `animation-play-state: paused`:
  // a paused animation keeps the surface live and saves almost nothing (32.3ms
  // vs 20.5ms per frame). The keyframes all carry large negative delays, so they
  // come back staggered rather than in lockstep.
  // ...but `animation: none` destroys the timeline, so on resume every loop
  // restarts from its own frame zero. With a scroll every few seconds that reads
  // as the backdrop stuttering back to the beginning over and over rather than
  // looping. The fix is to carry the phase across the gap: total running time is
  // accumulated while the page is still, and on resume each element gets a
  // negative animation-delay of that much, so the loop picks up where it was.
  //
  // Costs two passes per scroll burst, not per frame, so the 60fps win above
  // survives intact.
  let scrollIdle = 0;
  let running = true;
  let runStart = performance.now();
  let elapsed = 0;

  /** Shift every backdrop loop back by the time the page has been running. */
  const resumePhase = () => {
    document.querySelectorAll('.scene .scenebg *').forEach((el) => {
      // The authored delay is what staggers the loops against each other, so it
      // is captured once and always kept as the base — reading it back later
      // would return the already-shifted value and the stagger would drift
      // further on every resume.
      if (el.dataset.baseDelay === undefined) {
        el.dataset.baseDelay = getComputedStyle(el).animationDelay;
      }

      const base = el.dataset.baseDelay;

      if (base === '' || base === 'none') {
        return;
      }

      // An element can carry several animations, each with its own delay.
      el.style.animationDelay = base
        .split(',')
        .map((d) => `calc(${d.trim()} - ${Math.round(elapsed)}ms)`)
        .join(', ');
    });
  };

  addEventListener('scroll', () => {
    if (running) {
      elapsed += performance.now() - runStart;
      running = false;
    }

    document.documentElement.classList.add('is-scrolling');
    clearTimeout(scrollIdle);
    scrollIdle = setTimeout(() => {
      resumePhase();
      document.documentElement.classList.remove('is-scrolling');
      runStart = performance.now();
      running = true;
    }, 180);
  }, { passive: true });

  /* -------------------- Button ripple -------------------- */
  // Struck from the actual pointer position, so it reads as the press landing
  // rather than a generic flash. Delegated: buttons are added by other
  // components (lightbox, quote modal) after this runs.
  if (!reduce) {
    document.addEventListener('pointerdown', (e) => {
      const btn = e.target.closest('.btn');
      if (!btn) return;
      const r = btn.getBoundingClientRect();
      btn.style.setProperty('--rx', `${e.clientX - r.left}px`);
      btn.style.setProperty('--ry', `${e.clientY - r.top}px`);
      btn.classList.remove('is-rippling');
      // Force a reflow so a rapid second press restarts the animation.
      void btn.offsetWidth;
      btn.classList.add('is-rippling');
    });
  }

  /* -------------------- Cursor sheen -------------------- */
  // Only on devices that actually have a cursor to track.
  if (!reduce && matchMedia('(hover: hover)').matches) {
    document.addEventListener('pointermove', (e) => {
      const el = e.target.closest('[data-sheen]');
      if (!el) return;
      const r = el.getBoundingClientRect();
      el.style.setProperty('--mx', `${((e.clientX - r.left) / r.width) * 100}%`);
      el.style.setProperty('--my', `${((e.clientY - r.top) / r.height) * 100}%`);
    }, { passive: true });
  }
}
