/* ============================================================
   Shared chrome (nav, footer, curtain, cursor, preloader).
   Each page calls TLC.mount({ active: 'home' }) before /assets/core.js loads.
   We inject the shared HTML, then core.js wires behaviour.
   ============================================================ */
window.TLC = (function(){

  // Mirrors <meta name="brand-logo"> emitted by the Blade layout (admin-managed, stored on S3).
  // Meta absent = nothing uploaded → render no logo at all, never a bundled fallback.
  const BRAND_LOGO = document.querySelector('meta[name="brand-logo"]')?.content || '';
  function brandImg(cls) {
    if (!BRAND_LOGO) return '';
    const classAttr = cls ? ` class="${cls}"` : '';
    return `<img${classAttr} src="${BRAND_LOGO}" alt="The Last Clicks">`;
  }

  const NAV_LINKS = [
    { href: '/#services', label: 'Services' },
    { href: '/industries', label: 'Industries' },
    { href: '/blog', label: 'Blog' },
    { href: '/about', label: 'About' },
    { href: '/contact', label: 'Contact' },
  ];

  function navHTML() {
    return `
    <header class="nav">
      <a class="nav__brand" href="/" data-cursor="HOME" aria-label="The Last Clicks — home">
        ${brandImg('nav__brand-img')}
      </a>
      <nav>
        <ul class="nav__links">
          ${NAV_LINKS.map(l => `<li><a href="${l.href}" data-cursor="VIEW"><span class="a">${l.label}</span><span class="b">${l.label}</span></a></li>`).join('')}
        </ul>
      </nav>
      <a class="nav__cta" href="#quote" data-quote-trigger data-magnetic data-cursor="LET'S TALK">
        <span class="dot"></span>
        <span>Get a Quote</span>
      </a>
      <button class="nav__burger" aria-label="Menu" aria-controls="mobile-menu" aria-expanded="false"><span></span><span></span></button>
    </header>
    <div class="menu" id="mobile-menu">
      <ul class="menu__list">
        ${NAV_LINKS.map(l => `<li><a href="${l.href}"><span>${l.label}</span></a></li>`).join('')}
        <li><a href="#quote" data-quote-trigger><span>Get a Quote →</span></a></li>
      </ul>
      <div class="menu__foot">
        <p style="font-family:var(--f-mono);font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:var(--muted)">+91 87701 55842 · info@thelastclicks.com</p>
      </div>
    </div>`;
  }

  function footerHTML() {
    return `
    <footer class="foot">
      <div class="container">
        <a href="/" class="foot__big" data-parallax="0.04" data-cursor="HOME" aria-label="The Last Clicks — home">The Last <em>Clicks</em></a>
        <div class="foot__grid">
          <div class="foot__col foot__intro">
            <p>Cinematic photography, videography &amp; production for brands, events and weddings — built to scale with your story.</p>
          </div>
          <div class="foot__col">
            <h5>Studio</h5>
            <a href="/about">About</a>
            <a href="/industries">Industries</a>
            <a href="/blog">Journal</a>
          </div>
          <div class="foot__col">
            <h5>Work</h5>
            <a href="/services/editing">Editing</a>
            <a href="/services/videography">Videography</a>
            <a href="/services/photography">Photography</a>
            <a href="/contact">Start a project</a>
          </div>
          <div class="foot__col">
            <h5>Contact</h5>
            <a href="tel:+918770155842">+91 87701 55842</a>
            <a href="https://wa.me/918770155842" target="_blank" rel="noopener" data-noswap>WhatsApp</a>
            <a href="/login">Sign In</a>
            <a href="/signup">Create your page</a>
          </div>
        </div>
        <div class="foot__copy">
          <span>© 2026 TheLastClicks — All rights reserved</span>
          <span class="foot__legal">
            <a href="/privacy-policy">Privacy</a>
            <a href="/cookie-policy">Cookies</a>
            <a href="/terms-of-service">Terms</a>
            <a href="/disclaimer">Disclaimer</a>
          </span>
        </div>
      </div>
    </footer>`;
  }

  function quoteHTML() {
    return `
    <div class="quote" aria-hidden="true">
      <div class="quote__overlay" data-quote-close></div>
      <div class="quote__panel" role="dialog" aria-modal="true" aria-label="Get a quote">
        <button class="quote__close" data-quote-close aria-label="Close">
          <span></span><span></span>
        </button>
        <aside class="quote__aside">
          <div class="quote__brand">${brandImg()}</div>
          <div class="quote__head">
            <div class="quote__eyebrow">Start a project</div>
            <h2 class="quote__title">Let's make<br><em>something real.</em></h2>
            <p class="quote__lead">Tell us a bit about your project. We'll respond within 4 working hours with next steps and a tailored estimate.</p>
          </div>
          <div class="quote__steps">
            <div class="quote__step is-on" data-step="1"><span>01</span> Project</div>
            <div class="quote__step" data-step="2"><span>02</span> Scope</div>
            <div class="quote__step" data-step="3"><span>03</span> About you</div>
            <div class="quote__step" data-step="4"><span>04</span> Done</div>
          </div>
          <div class="quote__foot">
            <span class="quote__pill">info@thelastclicks.com</span>
            <span class="quote__pill">+91 87701 55842</span>
          </div>
        </aside>
        <div class="quote__body">
          <form class="quote__form" autocomplete="off">
            <!-- STEP 1: type -->
            <section class="quote__panel-step is-on" data-panel="1">
              <header class="quote__step-head">
                <span class="quote__count">01 / 04</span>
                <h3>What are we creating?</h3>
                <p>Pick the closest match — you can refine later.</p>
              </header>
              <div class="quote__chips" data-name="type">
                ${['Post-production only','Wedding film','Brand commercial','Product shoot','Event coverage','Music video','Documentary','Real estate','Editorial','Something else'].map(t => `<button type="button" class="quote__chip" data-value="${t}"><span>${t}</span></button>`).join('')}
              </div>
            </section>
            <!-- STEP 2: scope -->
            <section class="quote__panel-step" data-panel="2">
              <header class="quote__step-head">
                <span class="quote__count">02 / 04</span>
                <h3>Shape the scope.</h3>
                <p>Rough is fine — we'll firm it up together.</p>
              </header>
              <div class="quote__group">
                <label class="quote__label">Budget range</label>
                <div class="quote__chips quote__chips--row" data-name="budget">
                  ${['< ₹1L','₹1L – ₹3L','₹3L – ₹7L','₹7L – ₹15L','₹15L+'].map(t => `<button type="button" class="quote__chip" data-value="${t}"><span>${t}</span></button>`).join('')}
                </div>
              </div>
              <div class="quote__group">
                <label class="quote__label">Timeline</label>
                <div class="quote__chips quote__chips--row" data-name="timeline">
                  ${['ASAP','In 1 month','1–3 months','3+ months','Just exploring'].map(t => `<button type="button" class="quote__chip" data-value="${t}"><span>${t}</span></button>`).join('')}
                </div>
              </div>
              <div class="quote__group">
                <label class="quote__label" for="q-loc">Location</label>
                <input class="quote__input" id="q-loc" name="location" placeholder="Where will we be shooting?">
              </div>
            </section>
            <!-- STEP 3: about you -->
            <section class="quote__panel-step" data-panel="3">
              <header class="quote__step-head">
                <span class="quote__count">03 / 04</span>
                <h3>About you.</h3>
                <p>So we know who to write back to.</p>
              </header>
              <div class="quote__row">
                <div class="quote__group"><label class="quote__label" for="q-name">Your name</label><input class="quote__input" id="q-name" name="name" required></div>
                <div class="quote__group"><label class="quote__label" for="q-comp">Company / brand</label><input class="quote__input" id="q-comp" name="company" placeholder="Optional"></div>
              </div>
              <div class="quote__row">
                <div class="quote__group"><label class="quote__label" for="q-email">Email</label><input class="quote__input" id="q-email" name="email" type="email" required></div>
                <div class="quote__group"><label class="quote__label" for="q-phone">Phone / WhatsApp</label><input class="quote__input" id="q-phone" name="phone"></div>
              </div>
              <div class="quote__group">
                <label class="quote__label" for="q-msg">Tell us more</label>
                <textarea class="quote__input quote__textarea" id="q-msg" name="message" rows="4" placeholder="Vibe, references, deliverables, anything we should know…"></textarea>
              </div>
            </section>
            <!-- STEP 4: success -->
            <section class="quote__panel-step quote__success" data-panel="4">
              <div class="quote__check"><svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2"><circle cx="32" cy="32" r="28"/><path d="M20 33 L29 42 L46 24" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
              <h3>Brief received.</h3>
              <p>Thanks <span data-out="name">friend</span> — we'll be in touch within 4 working hours at <span data-out="email">your inbox</span>. In the meantime, peek at the portfolio.</p>
              <div class="quote__success-actions">
                <a class="btn" href="/portfolio"><span>See the portfolio</span><span class="arr"></span></a>
                <button type="button" class="btn btn--ghost" data-quote-close><span>Close</span></button>
              </div>
            </section>
          </form>
          <footer class="quote__nav">
            <button type="button" class="quote__back" data-quote-back><span>← Back</span></button>
            <div class="quote__bar"><div class="quote__bar-fill"></div></div>
            <button type="button" class="quote__next" data-quote-next>
              <span class="quote__next-label">Continue</span>
              <span class="quote__next-arr">→</span>
            </button>
          </footer>
        </div>
      </div>
    </div>`;
  }

  /* Exploded-view camera that assembles itself while the page loads. Inline SVG
     rather than an image or a 3D model: nothing to fetch, no CDN, works offline,
     and every part is a node CSS can drive on its own. Each piece starts off its
     mark and converges — the animation is defined entirely in core.css so this
     stays markup. Reduced-motion users never see it; the preloader is
     display:none for them. */
  function cameraRigHTML() {
    return `
    <div class="prig" aria-hidden="true">
      <svg class="prig__svg" viewBox="0 0 260 170" fill="none" xmlns="http://www.w3.org/2000/svg"
           stroke-linecap="round" stroke-linejoin="round">
        <!-- rear screen + articulating hinge + control cluster, from the right -->
        <g class="prig__p prig__p--board">
          <rect x="178" y="54" width="54" height="44" rx="3" stroke="currentColor" stroke-width="2"/>
          <rect x="183" y="59" width="34" height="34" rx="1.5" stroke="currentColor" stroke-width="1.2" opacity=".5"/>
          <path d="M178 62v28" stroke="currentColor" stroke-width="3" opacity=".7"/>
          <circle cx="225" cy="66" r="4.5" stroke="currentColor" stroke-width="1.4" opacity=".65"/>
          <circle cx="225" cy="66" r="1.2" fill="currentColor" opacity=".65"/>
          <path d="M222 82h6M222 88h6" stroke="currentColor" stroke-width="1.4" opacity=".5"/>
        </g>
        <!-- sensor + mount contacts, sits behind the mount -->
        <g class="prig__p prig__p--sensor">
          <rect x="119" y="70" width="28" height="20" rx="1.5" stroke="currentColor" stroke-width="1.6" opacity=".8"/>
          <path d="M119 74h-4M119 86h-4M147 74h4M147 86h4" stroke="currentColor" stroke-width="1.2" opacity=".55"/>
        </g>
        <!-- top plate: mode dial, shutter, front dial, hotshoe -->
        <g class="prig__p prig__p--top">
          <path d="M96 40h66a3 3 0 0 1 3 3v7H96z" stroke="currentColor" stroke-width="2"/>
          <!-- hotshoe with contact rail -->
          <path d="M116 28h22v12h-22z" stroke="currentColor" stroke-width="2"/>
          <path d="M120 33h14" stroke="currentColor" stroke-width="1.2" opacity=".6"/>
          <!-- mode dial, knurled -->
          <circle cx="153" cy="33" r="7.5" stroke="currentColor" stroke-width="2"/>
          <path d="M153 25.5v3M153 37.5v3M145.5 33h3M157.5 33h3" stroke="currentColor" stroke-width="1.2" opacity=".7"/>
          <!-- shutter release + front dial -->
          <circle cx="104" cy="36" r="4" stroke="currentColor" stroke-width="1.8"/>
          <circle cx="104" cy="36" r="1.6" fill="currentColor" opacity=".8"/>
        </g>
        <!-- EVF hump with eyecup -->
        <g class="prig__p prig__p--finder">
          <path d="M168 28h24a4 4 0 0 1 4 4v12h-32V32a4 4 0 0 1 4-4z" stroke="currentColor" stroke-width="2"/>
          <ellipse cx="180" cy="24" rx="11" ry="5" stroke="currentColor" stroke-width="1.6" opacity=".75"/>
        </g>
        <!-- body: the anchor. contoured shell, mount ring, card door, lugs -->
        <g class="prig__p prig__p--body">
          <path d="M95 50h72a8 8 0 0 1 8 8v48a8 8 0 0 1-8 8H95a8 8 0 0 1-8-8V58a8 8 0 0 1 8-8z" stroke="currentColor" stroke-width="2"/>
          <circle cx="133" cy="83" r="24" stroke="currentColor" stroke-width="2"/>
          <circle cx="133" cy="83" r="18" stroke="currentColor" stroke-width="1.3" opacity=".55"/>
          <!-- mount index dot -->
          <circle cx="133" cy="57" r="2.2" fill="var(--red)"/>
          <!-- strap lugs -->
          <path d="M87 62h-5M180 62h5" stroke="currentColor" stroke-width="2.4"/>
          <!-- card door seam -->
          <path d="M163 96v14" stroke="currentColor" stroke-width="1.2" opacity=".45"/>
        </g>
        <!-- deep contoured grip -->
        <g class="prig__p prig__p--grip">
          <path d="M87 54h-9a12 12 0 0 0-12 12v34a12 12 0 0 0 12 12h9" stroke="currentColor" stroke-width="2"/>
          <path d="M72 72v22" stroke="currentColor" stroke-width="1.2" opacity=".5"/>
        </g>
        <!-- lens barrel: knurled zoom + focus rings, distance scale -->
        <g class="prig__p prig__p--barrel">
          <path d="M34 60h53v46H34a4 4 0 0 1-4-4V64a4 4 0 0 1 4-4z" stroke="currentColor" stroke-width="2"/>
          <path d="M46 60v46M62 60v46" stroke="currentColor" stroke-width="1.6" opacity=".6"/>
          <path d="M49 66v6M53 66v6M57 66v6M65 94v6M69 94v6M73 94v6" stroke="currentColor" stroke-width="1" opacity=".45"/>
          <path d="M76 68h8" stroke="currentColor" stroke-width="1.2" opacity=".5"/>
        </g>
        <!-- front element with highlight -->
        <g class="prig__p prig__p--glass1">
          <ellipse cx="27" cy="83" rx="10" ry="20" stroke="currentColor" stroke-width="2"/>
          <path d="M24 72a16 16 0 0 0 0 22" stroke="currentColor" stroke-width="1.2" opacity=".5"/>
        </g>
        <!-- hood / rear element -->
        <g class="prig__p prig__p--glass2">
          <ellipse cx="13" cy="83" rx="8" ry="15" stroke="currentColor" stroke-width="2" opacity=".75"/>
        </g>
        <!-- loose hardware -->
        <g class="prig__p prig__p--screws" opacity=".7">
          <circle cx="100" cy="46" r="2" fill="currentColor"/>
          <circle cx="172" cy="108" r="2" fill="currentColor"/>
          <circle cx="82" cy="120" r="2" fill="currentColor"/>
          <circle cx="200" cy="58" r="2" fill="currentColor"/>
          <circle cx="60" cy="52" r="1.6" fill="currentColor"/>
        </g>
        <!-- shutter blink, fires once the body has seated -->
        <circle class="prig__flash" cx="133" cy="83" r="7" fill="var(--red)"/>
      </svg>
    </div>`;
  }

  function chromeHTML() {
    return `
    <div class="curtain" aria-hidden="true">
      <div class="curtain__panel"></div><div class="curtain__panel"></div><div class="curtain__panel"></div>
      <div class="curtain__panel"></div><div class="curtain__panel"></div><div class="curtain__panel"></div>
      <div class="curtain__mark"><span>TLC.</span></div>
    </div>
    <div class="preloader">
      <div class="preloader__inner">
        ${cameraRigHTML()}
        <div class="preloader__bar"></div>
      </div>
    </div>
    <div class="scrollbar"><div class="scrollbar__fill"></div></div>
    <div class="cookies" role="dialog" aria-label="Cookie consent" aria-hidden="true">
      <div class="cookies__inner">
        <div class="cookies__copy">
          <strong>Cookies on TheLastClicks</strong>
          <p>We use a few small cookies to make this site work and to understand which essays land. Read the <a href="/cookie-policy">cookie policy</a> or pick below.</p>
        </div>
        <div class="cookies__btns">
          <button class="cookies__btn cookies__btn--ghost" data-cookies="declined">Only essential</button>
          <button class="cookies__btn cookies__btn--red" data-cookies="accepted">Accept all</button>
        </div>
      </div>
    </div>
    ${quoteHTML()}
    <!-- 1x1 transparent GIF rather than a src-less <img>: src-less counts as a
         broken image to validators and audit tools. core.js swaps in the real
         preview on first hover. -->
    <div class="hover-preview"><img alt="" decoding="async"
      src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"></div>`;
  }

  function mount(opts = {}) {
    const headInject = document.createElement('div');
    headInject.innerHTML = chromeHTML();
    while (headInject.firstChild) document.body.insertBefore(headInject.firstChild, document.body.firstChild);

    // wrap everything else in smooth-wrap > smooth-content
    // existing children become the content
    const content = document.createElement('div');
    content.className = 'smooth-content';
    const wrap = document.createElement('div');
    wrap.className = 'smooth-wrap';
    wrap.appendChild(content);

    // move all body children EXCEPT chrome into content
    const chromeSel = '.curtain,.preloader,.scrollbar,.hover-preview,.quote';
    const kids = Array.from(document.body.childNodes);
    document.body.appendChild(wrap);
    kids.forEach(k => {
      if (k.nodeType === 1 && k.matches && k.matches(chromeSel)) return;
      if (k === wrap) return;
      content.appendChild(k);
    });

    // Insert nav OUTSIDE smooth-wrap so position:fixed works (smooth-wrap transforms its descendants)
    // Skip if nav is already present in DOM (e.g. rendered server-side by Blade)
    if (!document.querySelector('header.nav')) {
      const navWrap = document.createElement('div');
      navWrap.innerHTML = navHTML();
      // navHTML returns header + menu — insert both at the top of body, outside smooth-wrap
      const navNodes = Array.from(navWrap.childNodes).toReversed();
      navNodes.forEach(n => document.body.insertBefore(n, document.body.firstChild));
    }

    const main = content.querySelector('main') || content;
    // Skip footer injection if already present in DOM (e.g. rendered server-side by Blade)
    if (!document.querySelector('footer.foot')) {
      const footWrap = document.createElement('div');
      footWrap.innerHTML = footerHTML();
      const footEl = footWrap.querySelector('footer');
      if (footEl) main.parentNode.insertBefore(footEl, main.nextSibling);
    }

    // Active state by data-page
    if (opts.active) {
      document.querySelectorAll('.nav__links a').forEach(a => {
        if (a.getAttribute('href').startsWith(opts.active)) a.classList.add('is-active');
      });
    }

    // Quote modal trigger + dialog focus management
    let quoteLastFocused = null;
    // Visible, focusable elements inside the dialog (getClientRects excludes
    // display:none steps but keeps the position:fixed panel, unlike offsetParent).
    const quoteFocusables = (q) => [...q.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')]
      .filter((x) => !x.disabled && x.getClientRects().length > 0);

    function openQuote(q, pref) {
      if (q.parentElement !== document.body) document.body.appendChild(q);
      if (pref) {
        let matched = false;
        q.querySelectorAll('.quote__chips[data-name="type"] .quote__chip').forEach((c) => {
          const on = c.dataset.value === pref;
          if (on) matched = true;
          c.classList.toggle('is-on', on);
        });
        // The chips are project types ("Wedding film"); an industry name
        // ("Automobile & Luxury") matches none of them. Rather than drop the
        // context on the floor, seed it into the brief where it's visible and
        // still editable, and where it reaches us on submit.
        if (!matched) {
          const msg = q.querySelector('#q-msg');
          if (msg && !msg.value) msg.value = `Industry: ${pref}\n`;
        }
      }
      quoteLastFocused = document.activeElement;
      q.classList.add('is-open');
      q.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      // Move focus into the dialog so keyboard/SR users land inside it.
      (q.querySelector('.quote__close') || quoteFocusables(q)[0])?.focus();
    }

    function closeQuote(q) {
      q.classList.remove('is-open');
      q.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      // Return focus to whatever opened the modal.
      if (quoteLastFocused && typeof quoteLastFocused.focus === 'function') quoteLastFocused.focus();
      quoteLastFocused = null;
    }

    document.body.addEventListener('click', (e) => {
      const t = e.target.closest('[data-quote-trigger]');
      if (t) {
        e.preventDefault();
        const q = document.querySelector('.quote');
        // Pre-select project type via [data-quote-prefill] or [data-quote-trigger].
        const pref = t.dataset.quotePrefill || (t.dataset.quoteTrigger && t.dataset.quoteTrigger !== '' ? t.dataset.quoteTrigger : '');
        if (q) openQuote(q, pref);
      }
      const c = e.target.closest('[data-quote-close]');
      if (c) {
        const q = document.querySelector('.quote');
        if (q) closeQuote(q);
      }
    });
    document.addEventListener('keydown', (e) => {
      const q = document.querySelector('.quote.is-open');
      if (!q) return;
      if (e.key === 'Escape') { closeQuote(q); return; }
      // Trap Tab within the dialog so focus can't reach the page behind it.
      if (e.key === 'Tab') {
        const f = quoteFocusables(q);
        if (!f.length) return;
        const first = f[0];
        const last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });

    // Quote modal step logic
    const quote = document.querySelector('.quote');
    if (quote) {
      let step = 1;
      const total = 3;
      const data = {};
      const stepEls = quote.querySelectorAll('.quote__step');
      const panels = quote.querySelectorAll('.quote__panel-step');
      const nextBtn = quote.querySelector('[data-quote-next]');
      const backBtn = quote.querySelector('[data-quote-back]');
      const fill = quote.querySelector('.quote__bar-fill');
      const nav = quote.querySelector('.quote__nav');
      const nextLabel = quote.querySelector('.quote__next-label');

      function render() {
        stepEls.forEach((s,i) => s.classList.toggle('is-on', i+1 <= step));
        panels.forEach(p => p.classList.toggle('is-on', +p.dataset.panel === step));
        if (fill) fill.style.width = ((step-1)/total)*100 + '%';
        if (backBtn) backBtn.style.visibility = step > 1 && step < 4 ? 'visible' : 'hidden';
        if (step === 4) {
          if (nav) nav.style.display = 'none';
          if (fill) fill.style.width = '100%';
          const nameOut = quote.querySelector('[data-out="name"]');
          const emailOut = quote.querySelector('[data-out="email"]');
          if (nameOut && data.name) nameOut.textContent = data.name;
          if (emailOut && data.email) emailOut.textContent = data.email;
        } else {
          if (nav) nav.style.display = '';
          // Label only — the arrow lives in its own .quote__next-arr span so it
          // can slide on hover. Baking one into this string rendered "Continue → →".
          if (nextLabel) nextLabel.textContent = step === 3 ? 'Send brief' : 'Continue';
        }
      }
      quote.querySelectorAll('.quote__chips').forEach(group => {
        const name = group.dataset.name;
        group.addEventListener('click', e => {
          const chip = e.target.closest('.quote__chip');
          if (!chip) return;
          group.querySelectorAll('.quote__chip').forEach(c => c.classList.remove('is-on'));
          chip.classList.add('is-on');
          data[name] = chip.dataset.value;
        });
      });
      function captureInputs() {
        quote.querySelectorAll('input, textarea').forEach(i => {
          if (i.name) data[i.name] = i.value;
        });
      }
      if (nextBtn) nextBtn.addEventListener('click', onNext);
      function onNext() {
        captureInputs();
        if (step === 3) {
          if (!data.name || !data.email) { alert('Please add your name and email.'); return; }
          submitQuote();
          return;
        }
        if (step < 4) { step++; render(); }
      }
      function submitQuote() {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const body = new URLSearchParams({
          _token: csrfMeta ? csrfMeta.getAttribute('content') : '',
          name: data.name || '',
          email: data.email || '',
          phone: data.phone || '',
          company: data.company || '',
          message: data.message || '',
          project_type: data.type || '',
          budget: data.budget || '',
          timeline: data.timeline || '',
          location: data.location || '',
          source_page: globalThis.location.pathname,
          website: '', // honeypot — must be empty
        });
        nextBtn.disabled = true;
        fetch('/contact', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: body.toString(),
          redirect: 'manual',
        })
        .then(onFetchResponse)
        .catch(onFetchError);
      }
      function onFetchResponse(res) {
        nextBtn.disabled = false;
        if (res.ok || res.status === 302 || res.type === 'opaqueredirect') {
          step++;
          render();
        } else {
          res.text().then(onErrorText);
        }
      }
      function onErrorText(text) {
        let msg = 'Something went wrong. Please try again.';
        try {
          const json = JSON.parse(text);
          if (json.message) msg = json.message;
          else if (json.errors) msg = Object.values(json.errors).flat().join(' ');
        } catch (parseErr) {
          console.warn('Could not parse error response:', parseErr);
        }
        alert(msg);
      }
      function onFetchError(networkErr) {
        nextBtn.disabled = false;
        console.error('Quote submit network error:', networkErr);
        alert('Network error — please check your connection and try again.');
      }
      if (backBtn) backBtn.addEventListener('click', () => { if (step > 1) { step--; render(); } });
      render();
    }

    // Cookie consent banner
    const cookies = document.querySelector('.cookies');
    if (cookies) {
      const choice = localStorage.getItem('tlc-cookies');
      if (!choice) {
        setTimeout(() => { cookies.classList.add('is-open'); cookies.setAttribute('aria-hidden','false'); }, 1200);
      }
      cookies.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-cookies]');
        if (!btn) return;
        const choice = btn.dataset.cookies;
        localStorage.setItem('tlc-cookies', choice);
        // The choice has to reach the analytics tag or the banner is decoration:
        // it was stored and never read, so "Only essential" changed nothing.
        // Consent Mode starts denied in the layout; this is the only grant.
        if (typeof window.gtag === 'function') {
          window.gtag('consent', 'update', {
            analytics_storage: choice === 'accepted' ? 'granted' : 'denied',
          });
        }
        cookies.classList.remove('is-open');
        cookies.setAttribute('aria-hidden','true');
      });
    }
  }

  return { mount };
})();

/* ------------------------------------------------------------
   Auto-mount. chrome.js is a deferred ES module, so the DOM is
   already parsed by the time it runs — call mount() directly.
   Guarding on readyState covers the rare 'loading' case, and the
   .curtain check makes a double-call (e.g. a leftover inline boot
   script) a no-op. This replaces the old inline DOMContentLoaded
   boot in the Blade layout, which raced this module and often ran
   before window.TLC was defined — leaving the quote modal uninjected.
   ------------------------------------------------------------ */
(function bootTLC() {
  function boot() {
    if (document.querySelector('.curtain')) return; // already mounted
    const active = location.pathname.replace(/^\//, '').replace(/\/$/, '') || 'home';
    window.TLC.mount({ active });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
