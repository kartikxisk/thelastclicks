/* ============================================================
   TheLastClicks — Core JS Engine
   Smooth scroll · Custom cursor · Page transitions · Splits
   60fps via rAF + transform/opacity only
   ============================================================ */

import { initWorkLightbox } from './work-lightbox';
import { initWorkMarquee } from './work-marquee';
import { initScenes } from './scene';

(() => {
  const root = document.documentElement;
  const isCoarse = matchMedia('(hover: none) and (pointer: coarse)').matches;
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* -------------------- Smooth Scroll (Lenis-style) -------------------- */
  // We virtualize body scrolling via translate3d on a fixed wrapper.
  const wrap = document.querySelector('.smooth-wrap');
  const content = document.querySelector('.smooth-content');
  let target = 0, current = 0, h = 0, vh = window.innerHeight;
  const ease = 0.09;

  function setHeight() {
    h = content ? content.getBoundingClientRect().height : 0;
    document.body.style.height = h + 'px';
    vh = window.innerHeight;
  }
  if (content) {
    new ResizeObserver(setHeight).observe(content);
    setHeight();
  }

  let scrollY = 0;
  function tick() {
    scrollY = window.scrollY;
    updateScrollbar();
    updateParallax();
    updateMagnetics();
    requestAnimationFrame(tick);
  }
  // Native scroll — clear any legacy transform/height
  if (wrap) { wrap.style.position = 'relative'; wrap.style.overflow = 'visible'; }
  if (content) content.style.transform = 'none';
  document.body.style.height = '';

  /* -------------------- Scrollbar fill -------------------- */
  const sbFill = document.querySelector('.scrollbar__fill');
  function updateScrollbar() {
    if (!sbFill) return;
    const max = (document.body.scrollHeight - vh) || 1;
    const p = Math.min(100, (scrollY / max) * 100);
    sbFill.style.setProperty('--p', p + '%');
  }

  /* -------------------- Magnetic elements -------------------- */
  const magnets = [];
  document.querySelectorAll('[data-magnetic]').forEach(el => {
    magnets.push({ el, tx: 0, ty: 0, cx: 0, cy: 0, hovered: false });
    el.addEventListener('mouseenter', () => { magnets.find(m => m.el === el).hovered = true; });
    el.addEventListener('mouseleave', () => {
      const m = magnets.find(m => m.el === el);
      m.hovered = false;
      m.tx = 0; m.ty = 0;
    });
    el.addEventListener('mousemove', e => {
      const r = el.getBoundingClientRect();
      const m = magnets.find(m => m.el === el);
      m.tx = (e.clientX - (r.left + r.width/2)) * 0.25;
      m.ty = (e.clientY - (r.top + r.height/2)) * 0.25;
    });
  });
  function updateMagnetics() {
    for (const m of magnets) {
      m.cx += (m.tx - m.cx) * 0.18;
      m.cy += (m.ty - m.cy) * 0.18;
      m.el.style.transform = `translate3d(${m.cx}px, ${m.cy}px, 0)`;
    }
  }

  /* -------------------- Parallax (scroll-linked) -------------------- */
  // Parallax disabled — scroll-linked drift added visual noise without payoff.
  const parallaxEls = [];
  function updateParallax() {
    for (const p of parallaxEls) {
      const r = p.el.getBoundingClientRect();
      const center = r.top + r.height/2 - vh/2;
      const offset = -center * p.speed;
      p.el.style.transform = `translate3d(0, ${offset}px, 0)`;
    }
  }

  /* -------------------- IntersectionObserver reveals -------------------- */
  const io = new IntersectionObserver(entries => {
    entries.forEach(en => {
      if (en.isIntersecting) {
        en.target.classList.add('is-in');
        io.unobserve(en.target);
      }
    });
  }, { threshold: 0.05, rootMargin: '0px 0px -2% 0px' });
  document.querySelectorAll('.reveal, .split, .clip-reveal, [data-anim]').forEach(el => io.observe(el));

  // Failsafe — brute-force activate anything visible (or near it) in case IO is slow to fire on load.
  function forceRevealVisible() {
    const vh = window.innerHeight;
    document.querySelectorAll('.reveal:not(.is-in), .split:not(.is-in), .clip-reveal:not(.is-in), [data-anim]:not(.is-in)').forEach(el => {
      const r = el.getBoundingClientRect();
      // In view, or approaching: play the reveal.
      // Already scrolled past (bottom above the viewport): reveal it outright —
      // a fast flick or a restored scroll position can carry the viewport clean
      // over an element, and without this it stays invisible forever rather
      // than merely un-animated.
      if (r.bottom <= 0 || (r.bottom > 0 && r.top < vh * 1.15)) {
        el.classList.add('is-in');
        io.unobserve(el);
      }
    });
  }
  requestAnimationFrame(() => requestAnimationFrame(forceRevealVisible));
  setTimeout(forceRevealVisible, 200);
  setTimeout(forceRevealVisible, 700);
  setTimeout(forceRevealVisible, 1500);
  // On scroll, also catch any missed elements as a backup
  let revealScrollFrame = 0;
  window.addEventListener('scroll', () => {
    cancelAnimationFrame(revealScrollFrame);
    revealScrollFrame = requestAnimationFrame(forceRevealVisible);
  }, { passive: true });

  /* -------------------- Form error summary focus -------------------- */
  // The form reloads server-side on a validation error; move focus to the
  // summary so a screen reader announces it and the fixes are one tab away.
  const errorSummary = document.querySelector('[data-error-summary]');
  if (errorSummary) {
    errorSummary.focus();
    errorSummary.scrollIntoView({ block: 'center' });
  }

  /* -------------------- Split text (auto-wrap words) -------------------- */
  document.querySelectorAll('[data-split]').forEach(el => {
    if (el.dataset.splitDone) return;
    el.dataset.splitDone = '1';
    el.classList.add('split');
    const html = el.innerHTML;
    const tmp = document.createElement('div'); tmp.innerHTML = html;
    function process(node) {
      const out = [];
      node.childNodes.forEach(c => {
        if (c.nodeType === 3) {
          const words = c.textContent.split(/(\s+)/);
          words.forEach(w => {
            if (/^\s+$/.test(w)) out.push(document.createTextNode(' '));
            else if (w.length) {
              const span = document.createElement('span');
              span.className = 'split-word';
              const inner = document.createElement('span');
              inner.textContent = w;
              span.appendChild(inner);
              out.push(span);
            }
          });
        } else if (c.nodeType === 1) {
          const clone = c.cloneNode(false);
          const sub = process(c);
          sub.forEach(s => clone.appendChild(s));
          out.push(clone);
        }
      });
      return out;
    }
    const result = process(tmp);
    el.innerHTML = '';
    result.forEach(n => el.appendChild(n));
    io.observe(el);
  });

  /* -------------------- Counters -------------------- */
  const cIO = new IntersectionObserver(entries => {
    entries.forEach(en => {
      if (!en.isIntersecting) return;
      const el = en.target;
      const target = parseFloat(el.dataset.count);
      const dec = parseInt(el.dataset.decimals || '0');
      const dur = parseInt(el.dataset.dur || '1800');
      const start = performance.now();
      function step(now) {
        const t = Math.min(1, (now - start) / dur);
        const eased = 1 - Math.pow(1 - t, 3);
        const v = (target * eased).toFixed(dec);
        el.textContent = v;
        if (t < 1) requestAnimationFrame(step);
        else el.textContent = target.toFixed(dec);
      }
      requestAnimationFrame(step);
      cIO.unobserve(el);
    });
  }, { threshold: 0.4 });
  document.querySelectorAll('[data-count]').forEach(el => cIO.observe(el));

  /* -------------------- Nav scroll + active link -------------------- */
  const nav = document.querySelector('.nav');
  // Transparent header while sitting over the hero OR a full-media page header;
  // solid once scrolled past it.
  const heroEl = document.querySelector('.hero');
  const pageHeaderEl = document.querySelector('.page-header--media');
  if (nav && (heroEl || pageHeaderEl)) nav.classList.add('over-hero');
  function navScroll() {
    if (!nav) return;
    // Over a pinned hero, use 0.75 viewport; over a page-header use its real height.
    let threshold = 30;
    if (nav.classList.contains('over-hero')) {
      threshold = heroEl ? window.innerHeight * 0.75 : Math.max(pageHeaderEl.offsetHeight - 80, 120);
    }
    if (scrollY > threshold) nav.classList.add('is-scrolled');
    else nav.classList.remove('is-scrolled');
  }
  setInterval(navScroll, 100);

  /* -------------------- Mobile menu -------------------- */
  const burger = document.querySelector('.nav__burger');
  const menu = document.querySelector('.menu');
  if (burger && menu) {
    burger.setAttribute('aria-expanded', 'false');
    const menuLinks = () => [...menu.querySelectorAll('a, button')].filter((x) => x.getClientRects().length > 0);

    const closeMenu = () => {
      if (!menu.classList.contains('is-open')) return;
      menu.classList.remove('is-open');
      burger.classList.remove('is-open');
      burger.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
      burger.focus(); // return focus to the control that opened it
    };

    burger.addEventListener('click', () => {
      const open = menu.classList.toggle('is-open');
      burger.classList.toggle('is-open', open);
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.style.overflow = open ? 'hidden' : '';
      if (open) menuLinks()[0]?.focus(); // move focus into the overlay
    });

    menu.querySelectorAll('a').forEach((a) => a.addEventListener('click', closeMenu));

    // Escape closes; Tab is trapped inside the open overlay.
    document.addEventListener('keydown', (e) => {
      if (!menu.classList.contains('is-open')) return;
      if (e.key === 'Escape') { closeMenu(); return; }
      if (e.key === 'Tab') {
        const f = menuLinks();
        if (!f.length) return;
        const first = f[0];
        const last = f[f.length - 1];
        // The burger stays in the tab order as the overlay's first stop.
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });
  }

  /* -------------------- Work lightbox -------------------- */
  initWorkLightbox();

  /* -------------------- Work marquee -------------------- */
  // After the lightbox, so the tiles already carry their click handlers by the
  // time the strip starts moving.
  initWorkMarquee();

  /* -------------------- Scene engine -------------------- */
  initScenes();

  /* -------------------- YouTube poster fallback -------------------- */
  // maxresdefault is the only 16:9 poster (hqdefault is 4:3 with black bars
  // baked in, which show the moment a bento tile crops to square). Not every
  // video has one, and YouTube is inconsistent about how it says so: sometimes
  // a 404, sometimes a 120x90 grey placeholder at HTTP 200. Handle both — the
  // 404 fires `error`, the placeholder only fires `load`, so we need each.
  function fixYouTubePoster(img) {
    if (img.tagName !== 'IMG' || !img.src.includes('maxresdefault')) return;
    if (img.naturalWidth > 120) return;
    // The replace only matches maxresdefault, so this cannot loop.
    img.src = img.src.replace('maxresdefault', 'hqdefault');
  }
  // Capture phase: neither `load` nor `error` bubbles.
  document.addEventListener('load', (e) => fixYouTubePoster(e.target), true);
  document.addEventListener('error', (e) => fixYouTubePoster(e.target), true);
  // Anything already decoded from cache before this listener attached.
  document.querySelectorAll('img[src*="maxresdefault"]').forEach((img) => {
    if (img.complete) fixYouTubePoster(img);
  });

  /* -------------------- Page transitions (red curtain) -------------------- */
  const curtain = document.querySelector('.curtain');
  function curtainOut(href) {
    if (!curtain) { window.location.href = href; return; }
    curtain.classList.remove('is-out');
    curtain.classList.add('is-in');
    setTimeout(() => { window.location.href = href; }, 800);
  }
  function curtainIn() {
    if (!curtain) return;
    curtain.classList.add('is-in');
    // Force the swap to is-out after a short reveal, regardless of pageshow timing.
    setTimeout(() => {
      curtain.classList.remove('is-in');
      curtain.classList.add('is-out');
    }, 300);
    // Hard failsafe — clear all classes after the exit animation finishes.
    setTimeout(() => {
      curtain.classList.remove('is-in', 'is-out');
    }, 1400);
  }
  // Intercept internal nav clicks
  document.addEventListener('click', e => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    const href = a.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('http')) return;
    if (a.target === '_blank') return;
    if (a.hasAttribute('data-noswap')) return;
    e.preventDefault();
    curtainOut(href);
  });
  // On load, slide curtain out — both pageshow AND immediate, whichever fires first.
  window.addEventListener('pageshow', curtainIn);
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    curtainIn();
  } else {
    document.addEventListener('DOMContentLoaded', curtainIn);
  }

  /* -------------------- Preloader -------------------- */
  const pre = document.querySelector('.preloader');
  if (pre) {
    // Hard failsafe: regardless of rAF/timing, kill the preloader after 3.4s.
    // Has to clear the whole sequence below (reveal + hand-off pause + flight =
    // ~2970ms) or the failsafe fires mid-animation and the shutter never
    // finishes opening — which is exactly what a 1.8s value did once the reveal
    // was lengthened past it.
    const hardKill = setTimeout(() => {
      if (pre.isConnected) {
        pre.classList.add('is-done');
        setTimeout(() => pre.remove(), 1000);
      }
    }, 3400);
    // The wordmark is the only progress indicator. Each character resolves out
    // of noise as progress passes its position, so the sweep of settled letters
    // across the word is the fill itself.
    const markEl = document.querySelector('[data-pboot-mark]');
    const logoEl = document.querySelector('[data-pboot-logo]');
    const stageEl = document.querySelector('.pboot__stage');
    const word = markEl ? markEl.textContent.trim() : '';
    // Letters only. The charset used to include #%&/\ and the unresolved half of
    // the word read as line noise rather than as letters that had not landed yet.
    const NOISE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    // One span per character, built once. Rewriting textContent every frame
    // would re-layout the whole word ~60 times a second; swapping the text of a
    // fixed set of equal-width mono cells does not move anything.
    let cells = [];
    if (markEl && word) {
      markEl.textContent = '';
      cells = [...word].map((ch) => {
        const span = document.createElement('span');
        span.className = 'pboot__ch';
        span.dataset.ch = ch;
        span.textContent = ch;
        markEl.appendChild(span);
        return span;
      });
    }

    // Fly the oversized logo into the slot the real nav logo occupies, so the
    // mark the visitor watched while waiting becomes the mark that is there when
    // they arrive. Both rects are measured rather than assumed — a guessed offset
    // lands wrong the moment the viewport or the logo's aspect changes.
    const handOffLogo = () => {
      const target = document.querySelector('.nav__brand-img');

      if (!logoEl || !target || reduce) {
        return;
      }

      const from = logoEl.getBoundingClientRect();
      const to = target.getBoundingClientRect();
      // Layout width, NOT the rect: the mark already carries a progress-driven
      // scale, and the flight's transform REPLACES that rather than multiplying
      // it. Measuring the scaled rect made the ratio compound and the landing
      // came in ~8% wide. offsetWidth is the untransformed box, so this is right
      // whatever the reveal happened to leave --p at.
      const base = logoEl.offsetWidth;

      if (!base || !to.width) {
        return;
      }

      const scale = to.width / base;
      // Scaling about the centre does not move the centre, so the current visual
      // centre and the layout centre are the same point — this stays correct.
      const dx = (to.left + to.width / 2) - (from.left + from.width / 2);
      const dy = (to.top + to.height / 2) - (from.top + from.height / 2);

      // Promoted only for the duration of the flight, then dropped — a permanent
      // compositor layer on a node about to be removed is pure cost.
      logoEl.style.willChange = 'transform';
      logoEl.classList.add('is-flying');
      logoEl.style.transform = `translate3d(${dx}px, ${dy}px, 0) scale(${scale})`;

      logoEl.addEventListener('transitionend', () => {
        logoEl.style.willChange = '';
      }, { once: true });
    };

    const paint = (t) => {
      // Written on the stage so the shutter blades AND the mark scaling inside
      // them read the same number. Two elements, one source of truth.
      if (stageEl) stageEl.style.setProperty('--p', t.toFixed(4));

      const settled = Math.round(t * cells.length);
      for (let i = 0; i < cells.length; i++) {
        const on = i < settled;
        const cell = cells[i];
        cell.textContent = on ? cell.dataset.ch : NOISE[(Math.random() * NOISE.length) | 0];
        // toggle() with a boolean is a no-op when the state already matches, so
        // settled characters stop being touched once they land.
        cell.classList.toggle('is-on', on);
      }
    };

    if (cells.length) {
      // Doubled from 1100. The shutter is the whole point of this screen and at
      // 1100ms the blades were open before the eye had found them — the mark
      // appeared to be there from the start rather than to have come through the
      // aperture. Raising this means raising hardKill above, which is the failsafe
      // that would otherwise cut the reveal off partway.
      const dur = 2200;
      const start = performance.now();
      const step = (now) => {
        // Clamped at both ends. rAF hands back the frame's start timestamp, which
        // can precede the performance.now() captured just above it, so the first
        // frame's t is often slightly negative — and a negative settled count
        // would leave the whole word scrambling backwards.
        const t = Math.min(1, Math.max(0, (now - start) / dur));
        paint(t);
        if (t < 1) {
          requestAnimationFrame(step);

          return;
        }
        // Land on the real wordmark rather than whatever the last frame rounded to.
        paint(1);
        clearTimeout(hardKill);
        setTimeout(() => {
          // Logo leaves first and the panel follows, so the mark reads as
          // travelling to the nav rather than being wiped away with everything
          // else. The 620ms below matches the flight in core.css.
          handOffLogo();
          pre.classList.add('is-leaving');
          setTimeout(() => {
            pre.classList.add('is-done');
            setTimeout(() => pre.remove(), 1000);
          }, 620);
        }, 150);
      };
      requestAnimationFrame(step);
    }
  }

  /* -------------------- Start engine -------------------- */
  if (!isCoarse && !reduce) {
    requestAnimationFrame(tick);
  } else {
    // Still tick parallax/magnet/scrollbar via scroll listener
    requestAnimationFrame(tick);
  }

  /* -------------------- Active nav link -------------------- */
  const path = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav__links a').forEach(a => {
    const href = a.getAttribute('href');
    if (!href) return;
    if ((path === 'index.html' || path === '') && href === 'index.html') a.classList.add('is-active');
    else if (href === path) a.classList.add('is-active');
  });

  /* -------------------- Hover image preview (portfolio) -------------------- */
  const previewBox = document.querySelector('.hover-preview');
  if (previewBox) {
    const items = document.querySelectorAll('[data-preview]');
    let pcx = 0, pcy = 0, ptx = 0, pty = 0;
    items.forEach(it => {
      it.addEventListener('mouseenter', () => {
        // No image = no ghost panel. Showing an empty framed box reads as broken.
        const src = it.dataset.preview;
        if (!src) return;
        previewBox.querySelector('img').src = src;
        previewBox.classList.add('is-on');
      });
      it.addEventListener('mouseleave', () => previewBox.classList.remove('is-on'));
      it.addEventListener('mousemove', e => { ptx = e.clientX; pty = e.clientY; });
    });
    function pTick() {
      pcx += (ptx - pcx) * 0.16;
      pcy += (pty - pcy) * 0.16;
      previewBox.style.transform = `translate3d(${pcx}px, ${pcy}px, 0) translate(-50%, -50%)`;
      requestAnimationFrame(pTick);
    }
    pTick();
  }

  /* -------------------- Accordion (FAQ) -------------------- */
  document.querySelectorAll('[data-acc]').forEach(acc => {
    acc.querySelectorAll('.acc__item').forEach(item => {
      const head = item.querySelector('.acc__head');
      head.addEventListener('click', () => {
        const open = item.classList.contains('is-open');
        acc.querySelectorAll('.acc__item').forEach(i => i.classList.remove('is-open'));
        if (!open) item.classList.add('is-open');
      });
    });
  });

  /* -------------------- Work tile video previews -------------------- */
  /* Tiles ship preload="none", so the fetch starts on first hover rather than
     costing a dozen video requests on page load. */
  (() => {
    const tiles = document.querySelectorAll('.work-tile:has(.work-tile__preview)');
    if (!tiles.length || reduce) return;

    function start(tile) {
      const v = tile.querySelector('.work-tile__preview');
      if (!v) return;
      if (v.preload === 'none') v.preload = 'auto';
      tile.classList.add('is-previewing');
      const p = v.play();
      if (p && typeof p.catch === 'function') p.catch(() => {});
    }

    function stop(tile) {
      const v = tile.querySelector('.work-tile__preview');
      if (!v) return;
      tile.classList.remove('is-previewing');
      v.pause();
      v.currentTime = 0;
    }

    if (isCoarse) {
      // No hover on touch — play whichever tile is centred in the viewport, one at
      // a time so a long grid never decodes several films at once.
      const io = new IntersectionObserver(entries => {
        entries.forEach(e => (e.isIntersecting ? start(e.target) : stop(e.target)));
      }, { rootMargin: '-35% 0px -35% 0px' });
      tiles.forEach(t => io.observe(t));
      return;
    }

    tiles.forEach(tile => {
      tile.addEventListener('mouseenter', () => start(tile));
      tile.addEventListener('mouseleave', () => stop(tile));
      tile.addEventListener('focusin', () => start(tile));
      tile.addEventListener('focusout', () => stop(tile));
    });
  })();

  /* -------------------- Work filters (category + craft) -------------------- */
  document.querySelectorAll('[data-work-filters]').forEach(bar => {
    const grid = document.querySelector(bar.dataset.workFilters || '[data-work-grid]');
    if (!grid) return;

    const tiles = Array.from(grid.querySelectorAll('.work-tile'));
    const empty = document.querySelector('[data-work-empty]');

    bar.addEventListener('click', e => {
      const chip = e.target.closest('[data-filter]');
      if (!chip) return;

      bar.querySelectorAll('[data-filter]').forEach(c => {
        c.classList.toggle('is-on', c === chip);
        c.setAttribute('aria-pressed', c === chip ? 'true' : 'false');
      });

      // Chips are either `all`, `cat:<slug>` or `craft:<slug>`.
      const [kind, value] = (chip.dataset.filter || 'all').split(':');
      let shown = 0;

      tiles.forEach(tile => {
        let match = true;
        if (kind === 'cat') {
          match = (tile.dataset.cat || '') === value;
        } else if (kind === 'craft') {
          match = (tile.dataset.crafts || '').split(/\s+/).includes(value);
        }
        tile.hidden = !match;
        if (match) shown++;
      });

      if (empty) empty.hidden = shown > 0;
    });
  });

  /* -------------------- Hero slides -------------------- */
  /* Only mounted when the admin has two or more active slides. A video slide holds
     for its own duration so a film is never cut mid-shot; stills hold a fixed beat. */
  document.querySelectorAll('[data-hero-slides]').forEach(stage => {
    const slides = Array.from(stage.querySelectorAll('.hero__slide'));
    if (slides.length < 2) return;

    const STILL_MS = 6000;
    let i = 0;
    let timer = 0;

    const mediaOf = s => s.querySelector('video, img');

    function holdFor(slide) {
      const el = mediaOf(slide);
      if (el && el.tagName === 'VIDEO' && Number.isFinite(el.duration) && el.duration > 1) {
        return Math.min(el.duration * 1000, 20000);
      }
      return STILL_MS;
    }

    function show(n) {
      const prev = slides[i];
      i = (n + slides.length) % slides.length;
      const next = slides[i];

      slides.forEach((s, idx) => s.classList.toggle('is-on', idx === i));

      // Pause what's leaving so offscreen films don't keep decoding.
      const prevEl = mediaOf(prev);
      if (prev !== next && prevEl && prevEl.tagName === 'VIDEO') prevEl.pause();

      const nextEl = mediaOf(next);
      if (nextEl && nextEl.tagName === 'VIDEO') {
        // preload="none" on non-first slides means the source may not be fetched yet.
        if (nextEl.preload === 'none') nextEl.preload = 'metadata';
        nextEl.currentTime = 0;
        const p = nextEl.play();
        if (p && typeof p.catch === 'function') p.catch(() => {});
      }

      clearTimeout(timer);
      timer = setTimeout(() => show(i + 1), holdFor(next));
    }

    // Reduced motion: show the first slide and never rotate.
    if (reduce) return;

    timer = setTimeout(() => show(1), holdFor(slides[0]));

    // Rotating behind a hidden tab burns decode for nothing.
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        clearTimeout(timer);
      } else {
        clearTimeout(timer);
        timer = setTimeout(() => show(i + 1), holdFor(slides[i]));
      }
    });
  });

  /* -------------------- Testimonials carousel -------------------- */
  /* Native scroll-snap does the scrolling; JS only mirrors state into the dots
     and arrows, and adds pointer-drag for mouse users (touch already has it). */
  document.querySelectorAll('[data-car]').forEach(car => {
    const viewport = car.querySelector('.car__viewport');
    const track = car.querySelector('.car__track');
    if (!viewport || !track) return;

    const slides = Array.from(track.children);
    if (!slides.length) return;

    const prev = car.querySelector('.car__prev');
    const next = car.querySelector('.car__next');
    const dots = Array.from(car.querySelectorAll('.car__dot'));
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const behavior = reduce ? 'auto' : 'smooth';
    const EDGE = 2; // px tolerance for "we're at the end"

    // Distance between two card starts — one arrow click / arrow key = one card.
    const step = () => (slides.length > 1
      ? slides[1].getBoundingClientRect().left - slides[0].getBoundingClientRect().left
      : slides[0].getBoundingClientRect().width);

    // Snap anchor = left content edge of the viewport (scroll-padding accounted for).
    const anchorX = () => viewport.getBoundingClientRect().left
      + parseFloat(getComputedStyle(track).paddingLeft || 0);

    const atStart = () => viewport.scrollLeft <= EDGE;
    const atEnd = () => viewport.scrollLeft >= viewport.scrollWidth - viewport.clientWidth - EDGE;

    function activeIndex() {
      if (atEnd()) return slides.length - 1;
      const x = anchorX();
      let best = 0, min = Infinity;
      slides.forEach((s, idx) => {
        const d = Math.abs(s.getBoundingClientRect().left - x);
        if (d < min) { min = d; best = idx; }
      });
      return best;
    }

    function sync() {
      const i = activeIndex();
      dots.forEach((d, idx) => {
        d.classList.toggle('is-on', idx === i);
        d.setAttribute('aria-current', idx === i ? 'true' : 'false');
      });
      if (prev) prev.disabled = atStart();
      if (next) next.disabled = atEnd();
    }

    function goTo(i) {
      const target = slides[Math.max(0, Math.min(slides.length - 1, i))];
      viewport.scrollBy({ left: target.getBoundingClientRect().left - anchorX(), behavior });
    }

    let syncFrame = 0;
    viewport.addEventListener('scroll', () => {
      cancelAnimationFrame(syncFrame);
      syncFrame = requestAnimationFrame(sync);
    }, { passive: true });
    window.addEventListener('resize', sync);

    prev && prev.addEventListener('click', () => viewport.scrollBy({ left: -step(), behavior }));
    next && next.addEventListener('click', () => viewport.scrollBy({ left: step(), behavior }));
    dots.forEach((d, idx) => d.addEventListener('click', () => goTo(idx)));

    viewport.addEventListener('keydown', e => {
      if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
      e.preventDefault();
      viewport.scrollBy({ left: e.key === 'ArrowRight' ? step() : -step(), behavior });
    });

    /* Click-and-drag for mouse/pen. Touch keeps native momentum, so it opts out. */
    let dragging = false, startX = 0, startScroll = 0, moved = 0;
    viewport.addEventListener('pointerdown', e => {
      if (e.pointerType === 'touch' || e.button !== 0) return;
      dragging = true; moved = 0;
      startX = e.clientX;
      startScroll = viewport.scrollLeft;
      viewport.classList.add('is-dragging');
    });
    viewport.addEventListener('pointermove', e => {
      if (!dragging) return;
      const dx = e.clientX - startX;
      moved = Math.max(moved, Math.abs(dx));
      if (moved > 3 && !viewport.hasPointerCapture(e.pointerId)) viewport.setPointerCapture(e.pointerId);
      viewport.scrollLeft = startScroll - dx;
    });
    function endDrag(e) {
      if (!dragging) return;
      dragging = false;
      if (e && viewport.hasPointerCapture(e.pointerId)) viewport.releasePointerCapture(e.pointerId);
      // Dropping .is-dragging restores mandatory snap; the browser settles itself.
      viewport.classList.remove('is-dragging');
      requestAnimationFrame(sync);
    }
    viewport.addEventListener('pointerup', endDrag);
    viewport.addEventListener('pointercancel', endDrag);
    // Swallow the click that terminates a drag so cards don't fire on release.
    viewport.addEventListener('click', e => {
      if (moved > 4) { e.preventDefault(); e.stopPropagation(); moved = 0; }
    }, true);

    sync();
  });

  /* -------------------- CTA background video -------------------- */
  /* Mounted from JS into every .cta-strip so a new CTA section gets it for
     free. It only ever decodes while on screen, and never mounts at all for
     reduced-motion or data-saver users — a full-bleed autoplaying video is a
     WCAG 2.2.2 failure and an expensive default on a metered connection. */
  (() => {
    // Admin-managed via Site Settings; the meta carries the resolved URL and
    // already falls back to the bundled clip when nothing is uploaded.
    const SRC = document.querySelector('meta[name="cta-video"]')?.content
      || '/videos/bg-footer.mp4';
    const strips = document.querySelectorAll('.cta-strip');
    if (!strips.length || reduce) return;
    if (navigator.connection?.saveData) return;

    const mounted = [];

    strips.forEach(strip => {
      const bg = document.createElement('div');
      bg.className = 'cta-strip__bg';
      bg.setAttribute('aria-hidden', 'true');

      const v = document.createElement('video');
      v.src = SRC;
      v.muted = true;          // must be set before play() for autoplay to be allowed
      v.loop = true;
      v.playsInline = true;
      v.preload = 'none';      // the observer promotes this on first approach
      v.tabIndex = -1;

      // Fade in on the first decoded frame rather than on mount, so the strip
      // never shows an empty black box waiting for the network.
      v.addEventListener('loadeddata', () => bg.classList.add('is-ready'), { once: true });

      bg.appendChild(v);
      // First child: the content container is z-index 1 and stays above it.
      strip.prepend(bg);
      mounted.push(v);
    });

    // Decode only what's visible — offscreen CTAs on a long page cost nothing.
    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        const v = e.target;
        if (e.isIntersecting) {
          if (v.preload === 'none') v.preload = 'auto';
          const p = v.play();
          if (p && typeof p.catch === 'function') p.catch(() => {});
        } else {
          v.pause();
        }
      });
    }, { rootMargin: '200px 0px' });
    mounted.forEach(v => io.observe(v));

    // A hidden tab should not keep decoding video.
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden) return;
      mounted.forEach(v => v.pause());
    });
  })();

  /* -------------------- Industries 3D coverflow -------------------- */
  /* Cards are real links sitting in 3D. Advancing re-assigns position classes
     only, so each card retargets its transition from wherever it currently is. */
  document.querySelectorAll('[data-i3d]').forEach(root => {
    const stage = root.querySelector('.i3d__stage');
    const cards = Array.from(root.querySelectorAll('[data-i3d-card]'));
    if (!stage || !cards.length) return;

    const prev = root.querySelector('[data-i3d-prev]');
    const next = root.querySelector('[data-i3d-next]');
    const dots = Array.from(root.querySelectorAll('[data-i3d-dot]'));
    const status = root.querySelector('[data-i3d-status]');
    const total = cards.length;
    const POS = ['is-centre', 'is-p1-right', 'is-p2-right', 'is-p2-left', 'is-p1-left'];
    let i = 0;

    function render() {
      cards.forEach((card, n) => {
        // Signed distance from centre, wrapped to the shorter way round.
        let d = (n - i + total) % total;
        if (d > total / 2) d -= total;
        card.classList.remove(...POS);
        if (d === 0) card.classList.add('is-centre');
        else if (d === 1) card.classList.add('is-p1-right');
        else if (d === -1) card.classList.add('is-p1-left');
        else if (d === 2) card.classList.add('is-p2-right');
        else if (d === -2) card.classList.add('is-p2-left');
        // Off-deck cards must not be tab stops; the visible ones stay reachable.
        card.setAttribute('aria-hidden', Math.abs(d) > 2 ? 'true' : 'false');
        card.tabIndex = Math.abs(d) > 2 ? -1 : 0;
        card.dataset.i3dOffset = String(d);
      });
      dots.forEach((dot, n) => {
        dot.classList.toggle('is-on', n === i);
        dot.setAttribute('aria-current', n === i ? 'true' : 'false');
      });
      if (status) status.textContent = `${cards[i].textContent.trim()} — ${i + 1} of ${total}`;
    }

    function go(step) { i = (i + step + total) % total; render(); }
    function goTo(n) { i = ((n % total) + total) % total; render(); }

    prev && prev.addEventListener('click', () => go(-1));
    next && next.addEventListener('click', () => go(1));
    dots.forEach((dot, n) => dot.addEventListener('click', () => goTo(n)));

    // A click on a side card rotates the deck to it rather than navigating —
    // following a link you can barely see is never what was meant. The centre
    // card is a plain link and is left alone.
    cards.forEach((card, n) => {
      // Whether this card was the centre one when the press STARTED. Focus fires
      // before click and rotates the card into the centre, so checking the class
      // inside the click handler would always say "centre" and let a side-card
      // click navigate — the opposite of what's intended.
      let pressedFromCentre = false;
      card.addEventListener('pointerdown', () => {
        pressedFromCentre = card.classList.contains('is-centre');
      });
      card.addEventListener('click', e => {
        // Keyboard activation has no preceding pointerdown; fall back to the
        // live class, which is correct there because focus already centred it.
        const fromCentre = e.detail === 0 ? card.classList.contains('is-centre') : pressedFromCentre;
        if (fromCentre) return;
        e.preventDefault();
        // ...and stop it here. preventDefault alone only cancels the navigation;
        // the delegated [data-quote-trigger] listener on document still fired, so
        // bringing a side card round also threw the quote wizard open on top of
        // the deck the visitor was still looking through.
        e.stopPropagation();
        goTo(n);
      });
      // Tabbing to an off-centre card brings it round, so keyboard focus and
      // what's legible on screen never disagree.
      card.addEventListener('focus', () => { if (!card.classList.contains('is-centre')) goTo(n); });
    });

    stage.addEventListener('keydown', e => {
      if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
      e.preventDefault();
      go(e.key === 'ArrowRight' ? 1 : -1);
    });

    /* Drag / swipe. One card per 90px of travel, direction-matched. */
    let down = false, startX = 0, moved = 0, settled = 0;
    stage.addEventListener('pointerdown', e => {
      if (e.button !== 0) return;
      down = true; startX = e.clientX; moved = 0; settled = 0;
      // Capture, so a drag that wanders off the deck keeps sending moves here
      // instead of dying the moment the pointer crosses the edge. This is also
      // what makes pointerleave the wrong place to end a drag.
      try { stage.setPointerCapture(e.pointerId); } catch { /* no capture: fall back to the window */ }
    });
    stage.addEventListener('pointermove', e => {
      if (!down) return;
      const dx = e.clientX - startX;
      moved = Math.max(moved, Math.abs(dx));
      // Advance by the full delta, not one card per move event — a fast flick
      // that crosses several thresholds in one frame must not under-scroll.
      const steps = Math.trunc(dx / 90);
      if (steps !== settled) { go(settled - steps); settled = steps; }
    });
    const endDrag = (e) => {
      down = false;
      if (e && e.pointerId !== undefined && stage.hasPointerCapture?.(e.pointerId)) {
        stage.releasePointerCapture(e.pointerId);
      }
    };
    stage.addEventListener('pointerup', endDrag);
    stage.addEventListener('pointercancel', endDrag);
    // No pointerleave: with capture held the pointer legitimately travels outside
    // the deck mid-drag, and ending there cut every drag short at the edge.
    // Swallow the click that ends a drag so a card doesn't also navigate.
    stage.addEventListener('click', e => {
      if (moved > 6) { e.preventDefault(); e.stopPropagation(); moved = 0; }
    }, true);

    render();
  });

  /* -------------------- Testimonial deck -------------------- */
  /* Every testimonial is a card in one stack. Advancing only re-assigns state
     classes, so each card transitions from wherever it currently is — spamming
     the arrows never restarts anything from zero. */
  document.querySelectorAll('[data-tdeck]').forEach(deck => {
    const cards = Array.from(deck.querySelectorAll('[data-tdeck-card]'));
    if (!cards.length) return;

    const prev = deck.querySelector('[data-tdeck-prev]');
    const next = deck.querySelector('[data-tdeck-next]');
    const status = deck.querySelector('[data-tdeck-status]');
    const total = cards.length;
    let i = 0;

    function render() {
      cards.forEach((card, n) => {
        // Distance forward from the active card, wrapping at the end.
        const d = (n - i + total) % total;
        card.classList.toggle('is-active', d === 0);
        card.classList.toggle('is-behind-1', d === 1 && total > 1);
        card.classList.toggle('is-behind-2', d === 2 && total > 2);
        // Only the readable card is reachable by keyboard or screen reader.
        card.setAttribute('aria-hidden', d === 0 ? 'false' : 'true');
      });
      if (status) status.textContent = `Testimonial ${i + 1} of ${total}`;
    }

    function go(step) {
      i = (i + step + total) % total;
      render();
    }

    prev && prev.addEventListener('click', () => go(-1));
    next && next.addEventListener('click', () => go(1));

    // Arrow keys work when the deck itself has focus. No animation is tied to
    // the keypress beyond the same card transition a click produces.
    deck.querySelector('.tdeck__deck')?.addEventListener('keydown', e => {
      if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
      e.preventDefault();
      go(e.key === 'ArrowRight' ? 1 : -1);
    });

    render();
  });

  /* -------------------- Audio toggle -------------------- */
  document.querySelectorAll('[data-audio-toggle]').forEach(btn => {
    btn.addEventListener('click', () => {
      const sel = btn.dataset.audioToggle;
      const v = document.querySelector(sel);
      if (!v) return;
      v.muted = !v.muted;
      btn.classList.toggle('is-on', !v.muted);
    });
  });

  /* -------------------- Back to top -------------------- */
  document.querySelectorAll('[data-scroll-top]').forEach(btn => {
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  });

  /* -------------------- Local time (IST) -------------------- */
  const clockEls = document.querySelectorAll('[data-clock]');
  if (clockEls.length) {
    const fmt = new Intl.DateTimeFormat('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Kolkata' });
    const tick = () => { const t = fmt.format(new Date()) + ' IST'; clockEls.forEach(el => { el.textContent = t; }); };
    tick();
    setInterval(tick, 10000);
  }


  /* -------------------- Sticky Process scroll-sync -------------------- */
  document.querySelectorAll('[data-sproc]').forEach(stage => {
    const scenes = stage.querySelectorAll('.sproc__scene');
    const panels = stage.querySelectorAll('.sproc__panel');
    const dots = stage.querySelectorAll('.sproc__dot');
    const now = stage.querySelector('.sproc__now');
    const fill = stage.querySelector('.sproc__progress-fill');
    if (!scenes.length) return;
    const total = scenes.length;

    function setActive(i) {
      i = Math.max(0, Math.min(total - 1, i));
      scenes.forEach((s, n) => s.classList.toggle('is-active', n === i));
      panels.forEach((p, n) => p.classList.toggle('is-on', n === i));
      dots.forEach((d, n) => d.classList.toggle('is-on', n === i));
      if (now) now.textContent = String(i + 1).padStart(2, '0');
      if (fill) fill.style.transform = 'scaleX(' + ((i + 1) / total) + ')';
    }
    setActive(0);

    // Pick whichever scene is closest to viewport center
    const sceneIO = new IntersectionObserver((entries) => {
      entries.forEach(e => { e.target.__ratio = e.intersectionRatio; });
      let best = 0, bestRatio = -1;
      scenes.forEach((s, n) => {
        const r = s.__ratio || 0;
        if (r > bestRatio) { bestRatio = r; best = n; }
      });
      if (bestRatio > 0) setActive(best);
    }, { threshold: [0, 0.25, 0.5, 0.75, 1] });
    scenes.forEach(s => sceneIO.observe(s));

    // Dots jump-scroll
    dots.forEach((d, n) => d.addEventListener('click', () => {
      scenes[n].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }));
  });


  /* -------------------- 3D mouse tilt (disabled — too busy) -------------------- */
  document.querySelectorAll('[data-tilt-disabled]').forEach((el) => {
    let tx = 0, ty = 0, rx = 0, ry = 0;
    let raf = 0;
    function loop() {
      rx += (tx - rx) * 0.15;
      ry += (ty - ry) * 0.15;
      el.style.transform = 'perspective(900px) rotateX(' + ry + 'deg) rotateY(' + rx + 'deg)';
      if (Math.abs(tx - rx) + Math.abs(ty - ry) > 0.05) raf = requestAnimationFrame(loop);
      else raf = 0;
    }
    el.addEventListener('mouseenter', () => { el.classList.add('is-tilting'); });
    el.addEventListener('mousemove', (e) => {
      const r = el.getBoundingClientRect();
      const max = parseFloat(el.dataset.tilt) || 8;
      tx = ((e.clientX - r.left) / r.width - 0.5) * 2 * max;
      ty = -((e.clientY - r.top) / r.height - 0.5) * 2 * max;
      if (!raf) raf = requestAnimationFrame(loop);
    });
    el.addEventListener('mouseleave', () => {
      tx = 0; ty = 0;
      el.classList.remove('is-tilting');
      if (!raf) raf = requestAnimationFrame(loop);
    });
  });

  /* -------------------- Number scramble (Vegas-style stats) -------------------- */
  const SCRAMBLE_CHARS = '0123456789';
  const numSIO = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (!e.isIntersecting) return;
      const el = e.target;
      const target = parseFloat(el.dataset.scrambleCount);
      const dec = parseInt(el.dataset.decimals || '0');
      const dur = parseInt(el.dataset.dur || '2200');
      const start = performance.now();
      const digits = Math.max(1, Math.floor(target).toString().length);
      function step(now) {
        const t = Math.min(1, (now - start) / dur);
        const eased = 1 - Math.pow(1 - t, 3);
        el.textContent = (target * eased).toFixed(dec);
        if (t < 1) requestAnimationFrame(step);
        else el.textContent = target.toFixed(dec);
      }
      requestAnimationFrame(step);
      numSIO.unobserve(el);
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('[data-scramble-count]').forEach((el) => numSIO.observe(el));

  /* -------------------- Text scramble (decoder reveal) -------------------- */
  const TXT_CHARS = '!<>-_\\/[]{}—=+*^?#XO';
  function textScramble(el, dur) {
    const final = el.textContent;
    dur = dur || 1300;
    const len = final.length;
    const start = performance.now();
    function step(now) {
      const t = Math.min(1, (now - start) / dur);
      const reveal = t * (len + 5);
      let out = '';
      for (let i = 0; i < len; i++) {
        if (i < reveal - 5) out += final[i];
        else if (i < reveal) {
          out += final[i] === ' ' ? ' ' : TXT_CHARS[Math.floor(Math.random() * TXT_CHARS.length)];
        } else {
          out += final[i] === ' ' ? ' ' : TXT_CHARS[Math.floor(Math.random() * TXT_CHARS.length)];
        }
      }
      el.textContent = out;
      if (t < 1) requestAnimationFrame(step);
      else el.textContent = final;
    }
    requestAnimationFrame(step);
  }
  // Text decoder-scramble disabled — random char churn on every eyebrow was noisy.
  // Elements keep their final text as authored.
  void textScramble;

  /* -------------------- Scroll-velocity skew (disabled — marquee stays steady) -------------------- */
  document.querySelectorAll('.marquee').forEach((m) => m.style.setProperty('--skew', '0deg'));


  /* -------------------- Spotlight cursor tracking -------------------- */
  if (!isCoarse && !reduce) {
    document.querySelectorAll('.spotlight').forEach((el) => {
      el.addEventListener('mousemove', (e) => {
        const r = el.getBoundingClientRect();
        const x = ((e.clientX - r.left) / r.width) * 100;
        const y = ((e.clientY - r.top) / r.height) * 100;
        el.style.setProperty('--mx', x + '%');
        el.style.setProperty('--my', y + '%');
      });
    });
  }


  /* -------------------- Odometer flipboard digits -------------------- */
  // Converts numbers in elements with [data-count] or [data-scramble-count]
  // into stacked 0-9 digit columns that translateY to land. Stagger by digit.
  function odometerize(el, target, dec, dur, jitter) {
    dec = dec || 0; dur = dur || 1600; jitter = jitter || 70;
    const text = (target).toFixed(dec);
    el.classList.add('odo');
    el.innerHTML = '';
    const cols = [];
    for (let i = 0; i < text.length; i++) {
      const ch = text[i];
      if (/\d/.test(ch)) {
        const col = document.createElement('span');
        col.className = 'odo__col';
        const inner = document.createElement('span');
        inner.className = 'odo__col-inner';
        // Add 0–9 for the slot animation, then spin extra full cycles for higher digits
        const targetDigit = parseInt(ch, 10);
        // Repeat the 0-9 sequence twice so the digit "spins" before landing
        for (let r = 0; r < 2; r++) {
          for (let n = 0; n <= 9; n++) {
            const d = document.createElement('span');
            d.textContent = n;
            inner.appendChild(d);
          }
        }
        // Land on the second cycle's target
        const landIdx = 10 + targetDigit;
        col.appendChild(inner);
        el.appendChild(col);
        cols.push({ inner, landIdx, delay: i * jitter });
      } else {
        const sep = document.createElement('span');
        sep.className = 'odo__sep';
        sep.textContent = ch;
        el.appendChild(sep);
      }
    }
    // Trigger animation
    requestAnimationFrame(() => {
      cols.forEach(({ inner, landIdx, delay }) => {
        inner.style.transitionDelay = delay + 'ms';
        inner.style.transitionDuration = dur + 'ms';
        inner.style.transform = 'translateY(-' + landIdx + 'em)';
      });
    });
  }

  const odoIO = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (!e.isIntersecting) return;
      const el = e.target;
      const target = parseFloat(el.dataset.count || el.dataset.scrambleCount);
      const dec = parseInt(el.dataset.decimals || '0');
      odometerize(el, target, dec, 1600 + Math.random() * 400, 60 + Math.random() * 50);
      odoIO.unobserve(el);
    });
  }, { threshold: 0.35 });
  // Odometer flipboard disabled — clashed with the plain count-up and read as clutter.
  void odometerize; void odoIO;

  /* -------------------- Marquee per-character wave (disabled) -------------------- */
  document.querySelectorAll('.marquee__item--disabled').forEach((item) => {
    if (item.classList.contains('is-charified')) return;
    if (item.querySelector('.dot') || item.querySelector('em')) return;
    const text = item.textContent;
    item.textContent = '';
    [...text].forEach((ch, i) => {
      const s = document.createElement('span');
      s.textContent = ch === ' ' ? '\u00A0' : ch;
      s.style.setProperty('--y', '0');
      item.appendChild(s);
      s.addEventListener('mouseenter', () => {
        s.style.setProperty('--y', '-12px');
        setTimeout(() => s.style.setProperty('--y', '0'), 380);
      });
    });
    item.classList.add('is-charified');
  });

  /* -------------------- Beliefs scroll-linked progress rail -------------------- */
  const beliefsList = document.querySelector('.beliefs__list');
  if (beliefsList) {
    let bf = 0;
    function beliefsTick() {
      const r = beliefsList.getBoundingClientRect();
      const vh = window.innerHeight;
      const total = r.height;
      const start = r.top - vh * 0.6;
      const end = r.top + total - vh * 0.4;
      const span = end - start;
      const progress = Math.max(0, Math.min(1, -start / span));
      beliefsList.style.setProperty('--p', (progress * 100) + '%');
    }
    window.addEventListener('scroll', () => {
      cancelAnimationFrame(bf);
      bf = requestAnimationFrame(beliefsTick);
    }, { passive: true });
    beliefsTick();
  }

  /* -------------------- Hover preview motion blur on movement -------------------- */
  const hovEl = document.querySelector('.hover-preview');
  if (hovEl) {
    let movingTimer = 0;
    let lastX = 0, lastY = 0;
    window.addEventListener('mousemove', (e) => {
      const dx = Math.abs(e.clientX - lastX);
      const dy = Math.abs(e.clientY - lastY);
      lastX = e.clientX; lastY = e.clientY;
      if (hovEl.classList.contains('is-on') && (dx + dy) > 8) {
        hovEl.classList.add('is-moving');
        clearTimeout(movingTimer);
        movingTimer = setTimeout(() => hovEl.classList.remove('is-moving'), 80);
      }
    }, { passive: true });
  }

  /* -------------------- Hero video autoplay nudge -------------------- */
  // Some browsers defer muted autoplay until interaction/scroll; force-play on load.
  // Reduced-motion users must not get a large autoplaying video (WCAG 2.2.2 / 2.3),
  // so pause it and drop the poster in place instead.
  document.querySelectorAll('.hero__bg video').forEach(v => {
    if (reduce) {
      v.removeAttribute('autoplay');
      v.pause();
      return;
    }
    v.muted = true;
    const p = v.play();
    if (p && p.catch) p.catch(() => {});
  });

  /* -------------------- Hero content reveal (scroll-locked) -------------------- */
  // Scroll is held at the top until the overlay text has animated in; the first
  // scroll intent triggers the reveal, and scrolling is released once it's done.
  const heroCenter = document.querySelector('.hero__center');
  if (heroCenter) {
    if (reduce) {
      heroCenter.style.setProperty('--hero-reveal', '1');
    } else {
      let played = false;
      const lock = () => {
        root.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
        window.scrollTo(0, 0);
      };
      const unlock = () => {
        root.style.overflow = '';
        document.body.style.overflow = '';
      };
      const removeIntent = () => {
        window.removeEventListener('wheel', onWheel);
        window.removeEventListener('keydown', onKey);
        window.removeEventListener('touchmove', onTouch);
      };
      function playReveal() {
        if (played) return;
        played = true;
        removeIntent();
        // rAF tween of --hero-reveal 0 → 1 with an easeOutCubic curve.
        const dur = 1100;
        const start = performance.now();
        const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);
        function step(now) {
          const t = Math.min(1, (now - start) / dur);
          heroCenter.style.setProperty('--hero-reveal', easeOutCubic(t).toFixed(4));
          if (t < 1) requestAnimationFrame(step);
          else unlock();
        }
        requestAnimationFrame(step);
      }
      const onWheel = (e) => { if (e.deltaY > 0) playReveal(); };
      const onKey = (e) => {
        if (['ArrowDown', 'PageDown', 'End', ' ', 'Spacebar'].includes(e.key)) playReveal();
      };
      const onTouch = () => playReveal();

      lock();
      window.addEventListener('wheel', onWheel, { passive: true });
      window.addEventListener('keydown', onKey);
      window.addEventListener('touchmove', onTouch, { passive: true });
      // Fallback — reveal (and release scroll) if no scroll intent arrives, so
      // the page can never get stuck locked.
      setTimeout(playReveal, 4000);
    }
  }

  /* -------------------- Subtle 3D tilt on hero video tiles -------------------- */
  const heroTiles = document.querySelectorAll('.hero__bg .tile');
  if (heroTiles.length && !isCoarse && !reduce) {
    let hx = 0, hy = 0, htx = 0, hty = 0, hraf = 0;
    function heroTilt() {
      htx += (hx - htx) * 0.08;
      hty += (hy - hty) * 0.08;
      heroTiles.forEach((tile, i) => {
        const depth = 1 + (i % 4) * 0.4;
        tile.style.transform = 'perspective(1400px) rotateX(' + (-hty * 1.5 * depth).toFixed(2) + 'deg) rotateY(' + (htx * 1.5 * depth).toFixed(2) + 'deg)';
      });
      if (Math.abs(hx - htx) + Math.abs(hy - hty) > 0.02) hraf = requestAnimationFrame(heroTilt);
      else hraf = 0;
    }
    window.addEventListener('mousemove', (e) => {
      hx = (e.clientX / window.innerWidth - 0.5) * 2;
      hy = (e.clientY / window.innerHeight - 0.5) * 2;
      if (!hraf) hraf = requestAnimationFrame(heroTilt);
    }, { passive: true });
  }


  /* -------------------- Film Strip Carousel -------------------- */
  document.querySelectorAll('[data-strip]').forEach((rail) => {
    const track = rail.querySelector('[data-strip-track]');
    const cards = track.querySelectorAll('.strip__card');
    if (!cards.length) return;
    const total = cards.length;
    const root = rail.closest('.strip');
    const prevBtn = root.querySelector('[data-strip-prev]');
    const nextBtn = root.querySelector('[data-strip-next]');
    const dots = root.querySelectorAll('[data-strip-jump]');
    const timeEl = root.querySelector('[data-strip-time]');
    const eyebrow = root.querySelector('.section__eyebrow');
    let i = 0;
    let timer = 0;

    function update(n) {
      i = (n + total) % total;
      cards.forEach((c, idx) => {
        c.classList.remove('is-on','is-near');
        if (idx === i) c.classList.add('is-on');
        else if (idx === i - 1 || idx === i + 1 || idx === (i - 1 + total) % total || idx === (i + 1) % total) c.classList.add('is-near');
      });
      const card = cards[i];
      const rect = card.getBoundingClientRect();
      const railRect = track.parentElement.getBoundingClientRect();
      const targetCenter = railRect.left + railRect.width / 2;
      const cardCenter = rect.left + rect.width / 2;
      const current = parseFloat(track.style.getPropertyValue('--tx') || '0');
      track.style.transform = 'translate3d(' + (current - (cardCenter - targetCenter)) + 'px, 0, 0)';
      track.style.setProperty('--tx', String(current - (cardCenter - targetCenter)));
      dots.forEach((d, idx) => d.classList.toggle('is-on', idx === i));
      if (timeEl) timeEl.textContent = String(((i + 1) * 6).toString().padStart(2,'0')) + ':00 / 36:00';
      if (eyebrow && !eyebrow.classList.contains('split')) eyebrow.textContent = 'Frame · ' + String(i + 1).padStart(2,'0') + ' of ' + String(total).padStart(2,'0');
    }

    function tick() { update(i + 1); }
    function startTimer() { clearInterval(timer); timer = setInterval(tick, 4800); }

    prevBtn && prevBtn.addEventListener('click', () => { update(i - 1); startTimer(); });
    nextBtn && nextBtn.addEventListener('click', () => { update(i + 1); startTimer(); });
    dots.forEach((d, idx) => d.addEventListener('click', () => { update(idx); startTimer(); }));
    cards.forEach((c, idx) => c.addEventListener('click', () => { update(idx); startTimer(); }));

    // Pause on hover
    root.addEventListener('mouseenter', () => clearInterval(timer));
    root.addEventListener('mouseleave', startTimer);

    // Keyboard
    root.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') { update(i - 1); startTimer(); }
      else if (e.key === 'ArrowRight') { update(i + 1); startTimer(); }
    });

    // Initial layout (wait a frame for sizes to settle)
    requestAnimationFrame(() => requestAnimationFrame(() => update(0)));
    startTimer();

    // Re-layout on resize
    window.addEventListener('resize', () => {
      track.style.setProperty('--tx', '0');
      track.style.transform = 'translate3d(0,0,0)';
      requestAnimationFrame(() => update(i));
    });
  });


})();
