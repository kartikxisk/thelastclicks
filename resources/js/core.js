/* ============================================================
   TheLastClicks — Core JS Engine
   Smooth scroll · Custom cursor · Page transitions · Splits
   60fps via rAF + transform/opacity only
   ============================================================ */

import { initWorkLightbox } from './work-lightbox';

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
  document.querySelectorAll('.reveal, .split, .clip-reveal').forEach(el => io.observe(el));

  // Failsafe — brute-force activate anything visible (or near it) in case IO is slow to fire on load.
  function forceRevealVisible() {
    const vh = window.innerHeight;
    document.querySelectorAll('.reveal:not(.is-in), .split:not(.is-in), .clip-reveal:not(.is-in)').forEach(el => {
      const r = el.getBoundingClientRect();
      if (r.bottom > 0 && r.top < vh * 1.15) {
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
    burger.addEventListener('click', () => {
      menu.classList.toggle('is-open');
      burger.classList.toggle('is-open');
      const open = menu.classList.contains('is-open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.style.overflow = open ? 'hidden' : '';
    });
    menu.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
      menu.classList.remove('is-open');
      burger.classList.remove('is-open');
      burger.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    }));
  }

  /* -------------------- Work lightbox -------------------- */
  initWorkLightbox();

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
  const preBar = document.querySelector('.preloader__bar');
  if (pre) {
    // Hard failsafe: regardless of rAF/timing, kill the preloader after 1.8s.
    const hardKill = setTimeout(() => {
      if (pre.isConnected) {
        pre.classList.add('is-done');
        setTimeout(() => pre.remove(), 1000);
      }
    }, 1800);
    if (preBar) {
      const dur = 1100;
      const start = performance.now();
      function step(now) {
        const t = Math.min(1, (now - start) / dur);
        preBar.style.setProperty('--p', t);
        if (t < 1) requestAnimationFrame(step);
        else {
          clearTimeout(hardKill);
          setTimeout(() => {
            pre.classList.add('is-done');
            setTimeout(() => pre.remove(), 1000);
          }, 150);
        }
      }
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
        const src = it.dataset.preview;
        if (src) previewBox.querySelector('img').src = src;
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

  /* -------------------- Testimonials carousel -------------------- */
  document.querySelectorAll('[data-carousel]').forEach(car => {
    const viewport = car.querySelector('.car__viewport');
    const track = car.querySelector('.car__track');
    const slides = car.querySelectorAll('.car__slide');
    const prev = car.querySelector('.car__prev');
    const next = car.querySelector('.car__next');
    const dots = car.querySelectorAll('.car__dot');
    let i = 0;
    function show(n) {
      i = (n + slides.length) % slides.length;
      slides.forEach((s, idx) => s.classList.toggle('is-on', idx === i));
      dots.forEach((d, idx) => d.classList.toggle('is-on', idx === i));
      // Slide the track so the active card sits centred in the viewport,
      // clamped so we never scroll past the first/last card.
      if (track && viewport) {
        const card = slides[i];
        const raw = card.offsetLeft - (viewport.clientWidth - card.offsetWidth) / 2;
        const maxOffset = track.scrollWidth - viewport.clientWidth;
        const offset = Math.max(0, Math.min(raw, maxOffset));
        track.style.transform = `translateX(${-offset}px)`;
      }
    }
    prev && prev.addEventListener('click', () => show(i - 1));
    next && next.addEventListener('click', () => show(i + 1));
    dots.forEach((d, idx) => d.addEventListener('click', () => show(idx)));
    show(0);
    window.addEventListener('resize', () => show(i));
    // auto
    setInterval(() => show(i + 1), 7000);
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
  document.querySelectorAll('.hero__bg video').forEach(v => {
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
