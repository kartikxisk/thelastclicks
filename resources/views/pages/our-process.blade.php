<x-layouts.app
    title="Our Process — TheLastClicks"
    description="Four phases. One standard. How we work from brief to final cut — written guarantees included."
    :canonical="url('/our-process')"
>
<x-slot name="head">
<style>
  /* ============================================================
     OUR PROCESS — editorial page-scoped styles
     ============================================================ */

  /* --- Editorial hero --- */
  .proc-hero { max-width: var(--maxw); margin-inline: auto; padding: 130px var(--pad-x) 0; position: relative; }
  .proc-hero__crumb { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--paper-dim); display: flex; gap: 10px; margin-bottom: 28px; }
  .proc-hero__crumb a { color: var(--paper-dim); }
  .proc-hero__crumb a:hover { color: var(--red); }
  .proc-hero__row { display: grid; grid-template-columns: 1.3fr 1fr; gap: 80px; align-items: end; padding-bottom: 56px; border-bottom: 1px solid var(--line); }
  .proc-hero h1 { font-family: var(--f-display); font-weight: 700; font-size: clamp(56px, 9.5vw, 168px); letter-spacing: -0.05em; line-height: 0.9; }
  .proc-hero h1 em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .proc-hero__lead { font-size: 18px; line-height: 1.55; color: var(--paper-dim); max-width: 38ch; }
  .proc-hero__stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; padding: 36px 0; border-bottom: 1px solid var(--line); }
  .proc-hero__stats .stat { padding-right: 16px; }
  .proc-hero__stats dt { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); margin-bottom: 12px; }
  .proc-hero__stats dd { font-family: var(--f-display); font-weight: 700; font-size: clamp(28px, 3.6vw, 44px); letter-spacing: -0.03em; line-height: 1; font-variant-numeric: tabular-nums; }
  .proc-hero__stats dd em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }

  /* --- Sticky phase scroll (different from homepage one) --- */
  .phases { padding: 100px 0; }
  .phase {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    border-top: 1px solid var(--line);
    padding: 80px 0;
    position: relative;
  }
  .phase:last-child { border-bottom: 1px solid var(--line); }
  .phase__sticky {
    position: sticky;
    top: 14vh;
    align-self: start;
    display: grid;
    gap: 24px;
    align-content: start;
  }
  .phase__num {
    display: inline-flex;
    align-items: baseline;
    gap: 14px;
    font-family: var(--f-mono);
    font-size: 11px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--red);
  }
  .phase__num strong {
    font-family: var(--f-display);
    font-weight: 700;
    font-size: clamp(64px, 8vw, 124px);
    letter-spacing: -0.05em;
    line-height: 0.85;
    color: var(--paper);
    font-variant-numeric: tabular-nums;
  }
  .phase__title {
    font-family: var(--f-display);
    font-weight: 600;
    font-size: clamp(40px, 5.5vw, 72px);
    letter-spacing: -0.04em;
    line-height: 0.96;
    text-wrap: balance;
  }
  .phase__title em {
    font-family: 'Instrument Serif', serif;
    font-style: italic;
    font-weight: 400;
    color: var(--red);
  }
  .phase__lead {
    color: var(--paper-dim);
    font-size: 17px;
    line-height: 1.6;
    max-width: 42ch;
  }
  .phase__meta {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 6px;
  }
  .phase__pill {
    padding: 6px 12px;
    border: 1px solid var(--line);
    border-radius: 100px;
    font-family: var(--f-mono);
    font-size: 10.5px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--paper-dim);
  }
  .phase__pill .dot { width: 5px; height: 5px; background: var(--red); border-radius: 50%; display: inline-block; margin-right: 8px; vertical-align: middle; }

  /* RIGHT side checklist */
  .phase__body { display: grid; gap: 24px; align-content: start; }
  .phase__media {
    position: relative;
    aspect-ratio: 4/3;
    overflow: hidden;
    background: var(--ink-2);
  }
  .phase__media img {
    width: 100%; height: 100%; object-fit: cover;
    transform: scale(1.04);
    transition: transform 1s var(--ease-soft);
    filter: grayscale(0.15) brightness(0.85);
  }
  .phase:hover .phase__media img { transform: scale(1.08); filter: none; }
  .phase__media-tag {
    position: absolute;
    top: 16px; left: 16px;
    padding: 6px 12px;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 100px;
    font-family: var(--f-mono);
    font-size: 10px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #fff;
  }

  .checklist {
    list-style: none;
    display: grid;
    gap: 0;
    border-top: 1px solid var(--line);
  }
  .checklist li {
    display: grid;
    grid-template-columns: 32px 1fr;
    gap: 16px;
    align-items: start;
    padding: 18px 0;
    border-bottom: 1px solid var(--line);
    font-size: 15.5px;
    color: var(--paper);
    line-height: 1.5;
    opacity: 0;
    transform: translateY(12px);
    transition: opacity 0.5s var(--ease), transform 0.5s var(--ease),
                padding-left 0.4s var(--ease-soft), color 0.4s var(--ease);
  }
  /* Items cascade in once the list scrolls into view (.reveal → .is-in). */
  .checklist.is-in li { opacity: 1; transform: none; }
  .checklist.is-in li:nth-child(1) { transition-delay: 0.1s; }
  .checklist.is-in li:nth-child(2) { transition-delay: 0.22s; }
  .checklist.is-in li:nth-child(3) { transition-delay: 0.34s; }
  .checklist.is-in li:nth-child(4) { transition-delay: 0.46s; }
  .checklist li:hover { padding-left: 8px; color: var(--red); }
  .checklist li .check {
    width: 20px; height: 20px;
    border-radius: 50%;
    border: 1.5px solid var(--red);
    display: grid; place-items: center;
    flex-shrink: 0;
    margin-top: 1px;
    transition: background 0.4s var(--ease-soft), transform 0.4s var(--ease-spring);
  }
  .checklist li .check::after {
    content: '';
    width: 8px; height: 4px;
    border-left: 1.5px solid var(--red);
    border-bottom: 1.5px solid var(--red);
    transform: rotate(-45deg) translate(1px, -1px);
    transition: border-color 0.3s var(--ease);
  }
  .checklist li:hover .check { background: var(--red); transform: scale(1.1); }
  .checklist li:hover .check::after { border-color: #fff; }
  .checklist li strong { font-family: var(--f-display); font-weight: 500; display: block; }

  /* Phase glyphs reuse the homepage sproc set; drawn in when revealed. */
  .phase .sproc__glyph { width: 48px; height: 48px; }
  .sproc__glyph.reveal.is-in .g { stroke-dashoffset: 0; }
  .sproc__glyph.reveal.is-in .f { opacity: 1; transform: scale(1); }
  .sproc__glyph.reveal.is-in .pulse { animation: sproc-rec 1.8s ease-in-out 1.2s infinite; }

  @media (prefers-reduced-motion: reduce) {
    .checklist li { opacity: 1; transform: none; transition: padding-left 0.4s var(--ease-soft), color 0.4s var(--ease); }
  }

  /* Output card */
  .output {
    border: 1px solid var(--line);
    background: linear-gradient(180deg, rgba(232,15,3,0.03), transparent 60%);
    padding: 24px 28px;
    display: grid;
    gap: 12px;
  }
  .output__h {
    font-family: var(--f-mono);
    font-size: 10.5px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--red);
    display: inline-flex;
    align-items: center;
    gap: 10px;
  }
  .output__h::before {
    content: '';
    width: 18px; height: 1px; background: var(--red);
  }
  .output__list {
    display: flex; flex-wrap: wrap; gap: 8px;
  }
  .output__list span {
    padding: 5px 11px;
    background: var(--ink-2);
    border: 1px solid var(--line);
    border-radius: 100px;
    font-family: var(--f-mono);
    font-size: 10.5px;
    letter-spacing: 0.14em;
    color: var(--paper);
  }

  /* --- Timeline ribbon --- */
  .ribbon {
    padding: 100px 0;
    border-top: 1px solid var(--line);
    position: relative;
    overflow: hidden;
  }
  .ribbon__head { margin-bottom: 56px; }
  .ribbon__bar {
    position: relative;
    height: 78px;
    margin-bottom: 36px;
  }
  .ribbon__line {
    position: absolute;
    top: 39px; left: 0; right: 0;
    height: 1px;
    background: var(--line);
  }
  .ribbon__fill {
    position: absolute;
    top: 39px; left: 0;
    height: 1px;
    background: var(--red);
    width: 0;
    animation: ribbonFill 2.5s var(--ease-soft) 0.4s forwards;
  }
  @keyframes ribbonFill { to { width: 100%; } }
  .ribbon__stops {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    position: relative;
    height: 78px;
  }
  .ribbon__stop {
    position: relative;
    text-align: center;
  }
  .ribbon__stop::before {
    content: '';
    position: absolute;
    top: 33px; left: 50%;
    width: 12px; height: 12px;
    border-radius: 50%;
    background: var(--ink);
    border: 2px solid var(--line);
    transform: translateX(-50%);
    transition: border-color 0.4s var(--ease), background 0.4s var(--ease), transform 0.4s var(--ease-spring);
  }
  .ribbon__stop:hover::before { border-color: var(--red); background: var(--red); transform: translateX(-50%) scale(1.4); }
  .ribbon__stop.is-now::before { border-color: var(--red); background: var(--red); box-shadow: 0 0 0 4px rgba(232,15,3,0.18); }
  .ribbon__day {
    position: absolute; top: 0; left: 0; right: 0;
    font-family: var(--f-mono); font-size: 10.5px;
    letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--paper-dim);
  }
  .ribbon__label {
    position: absolute; bottom: 0; left: 0; right: 0;
    font-family: var(--f-display); font-weight: 500;
    font-size: 14px; letter-spacing: -0.01em;
  }

  .ribbon__detail {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 16px;
    margin-top: 8px;
  }
  .ribbon__cell { padding: 0; }
  .ribbon__cell p {
    font-size: 13px;
    line-height: 1.5;
    color: var(--paper-dim);
    border-top: 1px solid var(--line);
    padding-top: 14px;
  }

  /* --- Principles strip --- */
  .principles {
    padding: 100px 0;
    border-top: 1px solid var(--line);
  }
  .principles__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    border-top: 1px solid var(--line);
  }
  .principle {
    padding: 36px 28px;
    border-right: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
    transition: background 0.4s var(--ease-soft);
    position: relative;
    overflow: hidden;
  }
  .principle:nth-child(3n) { border-right: 0; }
  .principle::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(232,15,3,0.06), transparent 70%);
    opacity: 0;
    transition: opacity 0.5s var(--ease);
    pointer-events: none;
  }
  .principle:hover::before { opacity: 1; }
  .principle__icon {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: rgba(232,15,3,0.1);
    border: 1px solid rgba(232,15,3,0.2);
    display: grid; place-items: center;
    color: var(--red);
    font-family: var(--f-mono); font-size: 14px;
    margin-bottom: 22px;
    transition: background 0.4s var(--ease-soft), color 0.4s var(--ease), transform 0.4s var(--ease-spring);
  }
  .principle:hover .principle__icon { background: var(--red); color: #fff; transform: rotate(15deg) scale(1.1); }
  .principle h3 {
    font-family: var(--f-display); font-weight: 500;
    font-size: 22px;
    letter-spacing: -0.025em;
    line-height: 1.1;
    margin-bottom: 12px;
    text-wrap: balance;
  }
  .principle h3 em {
    font-family: 'Instrument Serif', serif;
    font-style: italic; font-weight: 400; color: var(--red);
  }
  .principle p {
    color: var(--paper-dim);
    font-size: 14.5px;
    line-height: 1.55;
    text-wrap: pretty;
  }

  /* --- Guarantees --- */
  .guarantees {
    padding: 100px 0;
    border-top: 1px solid var(--line);
  }
  .guarantees__list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  .guarantee {
    padding: 28px;
    border: 1px solid var(--line);
    background: var(--ink-2);
    display: grid;
    grid-template-columns: 56px 1fr auto;
    gap: 20px;
    align-items: center;
    transition: border-color 0.4s var(--ease), transform 0.4s var(--ease-spring);
  }
  .guarantee:hover { border-color: var(--red); transform: translateX(4px); }
  .guarantee__num {
    font-family: var(--f-display);
    font-weight: 700;
    font-size: 28px;
    color: var(--red);
    letter-spacing: -0.03em;
    line-height: 1;
    font-variant-numeric: tabular-nums;
  }
  .guarantee__num em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; }
  .guarantee__h {
    font-family: var(--f-display);
    font-weight: 500;
    font-size: 18px;
    letter-spacing: -0.015em;
    line-height: 1.2;
  }
  .guarantee__p { color: var(--paper-dim); font-size: 13px; line-height: 1.45; margin-top: 4px; }
  .guarantee__arrow {
    width: 32px; height: 32px;
    border-radius: 50%;
    border: 1px solid var(--line);
    display: grid; place-items: center;
    color: var(--paper-dim);
    transition: border-color 0.4s var(--ease), color 0.4s var(--ease), transform 0.4s var(--ease-spring);
  }
  .guarantee:hover .guarantee__arrow { border-color: var(--red); color: var(--red); transform: rotate(-45deg); }
  .guarantee__arrow svg { width: 14px; height: 14px; }

  /* --- Kit grid (refined) --- */
  .kit {
    padding: 100px 0;
    border-top: 1px solid var(--line);
  }
  .kit__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
  }
  .kit__card {
    aspect-ratio: 4/3;
    background: var(--ink-2);
    border: 1px solid var(--line);
    padding: 28px;
    display: grid;
    grid-template-rows: auto 1fr auto;
    gap: 14px;
    transition: border-color 0.4s var(--ease), background 0.4s var(--ease);
    position: relative;
    overflow: hidden;
  }
  .kit__card:hover { border-color: var(--red); }
  .kit__cat {
    font-family: var(--f-mono); font-size: 10.5px;
    letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--paper-dim);
  }
  .kit__name {
    font-family: var(--f-display); font-weight: 500;
    font-size: clamp(26px, 3vw, 38px);
    letter-spacing: -0.025em; line-height: 0.95;
    text-wrap: balance;
  }
  .kit__name em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .kit__list {
    display: flex; flex-wrap: wrap; gap: 6px;
    font-family: var(--f-mono); font-size: 10.5px;
    letter-spacing: 0.12em; color: var(--paper-dim);
  }
  .kit__list span { padding: 4px 9px; border: 1px solid var(--line); border-radius: 100px; }

  /* --- FAQ block --- */
  .pfaq {
    padding: 100px 0;
    border-top: 1px solid var(--line);
  }

  /* --- Responsive --- */
  @media (max-width: 980px) {
    .proc-hero__row { grid-template-columns: 1fr; gap: 28px; padding-bottom: 36px; }
    .proc-hero__stats { grid-template-columns: 1fr 1fr; gap: 22px; }
    .phase { grid-template-columns: 1fr; gap: 32px; padding: 56px 0; }
    .phase__sticky { position: static; }
    .ribbon__bar { display: none; }
    .ribbon__stops { display: grid; grid-template-columns: 1fr; gap: 12px; height: auto; margin-top: 0; }
    .ribbon__stop {
      text-align: left;
      padding: 18px 0;
      border-bottom: 1px solid var(--line);
      display: grid;
      grid-template-columns: 80px 1fr;
      gap: 16px;
      align-items: center;
    }
    .ribbon__stop::before { display: none; }
    .ribbon__day { position: static; }
    .ribbon__label { position: static; font-size: 18px; }
    .ribbon__detail { grid-template-columns: 1fr; gap: 0; }
    .ribbon__cell { padding: 0 0 16px; }
    .ribbon__cell p { padding-top: 0; border-top: 0; padding-bottom: 14px; border-bottom: 1px solid var(--line); }
    .principles__grid { grid-template-columns: 1fr; }
    .principle { border-right: 0; }
    .guarantees__list { grid-template-columns: 1fr; }
    .guarantee { grid-template-columns: 48px 1fr auto; padding: 22px; gap: 14px; }
    .kit__grid { grid-template-columns: 1fr; }
    .kit__card { aspect-ratio: auto; padding: 22px; gap: 12px; }
  }
  @media (max-width: 540px) {
    .proc-hero__stats { grid-template-columns: 1fr 1fr; }
  }
</style>
</x-slot>
  <!-- 1. EDITORIAL HERO -->
  <section class="proc-hero" data-screen-label="01 Header">
    <div class="proc-hero__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Our Process</span></div>
    <div class="proc-hero__row">
      <h1 data-split>From <em>brief</em><br>to <em>final cut.</em></h1>
      <p class="proc-hero__lead reveal">A clear process is how we protect the craft. Four phases, written guarantees.</p>
    </div>
    <dl class="proc-hero__stats">
      <div class="stat reveal"><dt>Phases</dt><dd>04<em>·</em></dd></div>
      <div class="stat reveal" data-delay="1"><dt>Avg. timeline</dt><dd>2–6<em> wk</em></dd></div>
      <div class="stat reveal" data-delay="2"><dt>Review cycles</dt><dd>3<em> incl.</em></dd></div>
      <div class="stat reveal" data-delay="3"><dt>Same-day reels</dt><dd>48<em>h</em></dd></div>
    </dl>
  </section>

  <!-- 2. PHASES — Each phase is a full sticky section with detail -->
  <section class="phases" data-screen-label="02 Phases">
    <div class="wrap">
      <div class="services__head" style="margin-bottom: 56px">
        <div>
          <span class="section__eyebrow" data-scramble>The four phases</span>
          <h2 class="section__title" data-split>Phase by <em>phase.</em></h2>
        </div>
        <p class="section__lead reveal">Every phase has an owner, a deliverable and a review gate — no drift, no silent timelines.</p>
      </div>

      <!-- PHASE 01 - BRIEF -->
      <article class="phase">
        <div class="phase__sticky">
          <span class="phase__num"><strong>01</strong>Discovery &amp; Brief</span>
          <h3 class="phase__title">Understanding the <em>real</em> brief.</h3>
          <svg class="sproc__glyph reveal" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <circle class="g" cx="32" cy="32" r="18" pathLength="1"/>
            <path class="g" d="M32 6v8M32 50v8M6 32h8M50 32h8" pathLength="1" style="--d:.5s"/>
            <circle class="f" cx="32" cy="32" r="3.5" fill="var(--red)" stroke="none"/>
          </svg>
          <p class="phase__lead">Goals, audience, guardrails and metrics — locked before any camera moves.</p>
          <div class="phase__meta">
            <span class="phase__pill"><span class="dot"></span>Day 0–2</span>
            <span class="phase__pill">2-hour kickoff</span>
            <span class="phase__pill">No-cost</span>
          </div>
        </div>
        <div class="phase__body">
          <div class="phase__media spotlight">
            <span class="phase__media-tag">Kickoff</span>
            <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1400&q=85" alt="Discovery session" decoding="async">
          </div>
          <ul class="checklist reveal">
            <li><span class="check"></span><div><strong>Audience &amp; channel mapping</strong></div></li>
            <li><span class="check"></span><div><strong>Brand guardrails inventory</strong></div></li>
            <li><span class="check"></span><div><strong>Success metrics, in writing</strong></div></li>
            <li><span class="check"></span><div><strong>One-sentence creative thesis</strong></div></li>
          </ul>
          <div class="output">
            <span class="output__h">You receive</span>
            <div class="output__list">
              <span>Creative thesis doc</span><span>Deliverables list</span><span>Shared metrics</span><span>Risk register</span>
            </div>
          </div>
        </div>
      </article>

      <!-- PHASE 02 - PLAN -->
      <article class="phase">
        <div class="phase__sticky">
          <span class="phase__num"><strong>02</strong>Pre-production</span>
          <h3 class="phase__title">Plan it like a <em>shoot day.</em></h3>
          <svg class="sproc__glyph reveal" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect class="g" x="6" y="20" width="14" height="24" pathLength="1"/>
            <rect class="g" x="25" y="20" width="14" height="24" pathLength="1" style="--d:.35s"/>
            <rect class="g" x="44" y="20" width="14" height="24" pathLength="1" style="--d:.55s"/>
            <path class="f" d="M30 28.5l5.5 3.5-5.5 3.5z" fill="var(--red)" stroke="none"/>
          </svg>
          <p class="phase__lead">Every minute accounted for before we step on set.</p>
          <div class="phase__meta">
            <span class="phase__pill"><span class="dot"></span>Day 3–10</span>
            <span class="phase__pill">Treatment locked</span>
            <span class="phase__pill">Crew confirmed</span>
          </div>
        </div>
        <div class="phase__body">
          <div class="phase__media spotlight">
            <span class="phase__media-tag">Treatment</span>
            <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=1400&q=85" alt="Planning a shoot" decoding="async">
          </div>
          <ul class="checklist reveal">
            <li><span class="check"></span><div><strong>Director-written treatment</strong></div></li>
            <li><span class="check"></span><div><strong>Shot list &amp; storyboard</strong></div></li>
            <li><span class="check"></span><div><strong>Casting, locations, permits</strong></div></li>
            <li><span class="check"></span><div><strong>Crew, kit &amp; call sheet locked</strong></div></li>
          </ul>
          <div class="output">
            <span class="output__h">You receive</span>
            <div class="output__list">
              <span>Treatment deck</span><span>Shot list</span><span>Call sheet</span><span>Schedule</span><span>Risk plan</span>
            </div>
          </div>
        </div>
      </article>

      <!-- PHASE 03 - SHOOT -->
      <article class="phase">
        <div class="phase__sticky">
          <span class="phase__num"><strong>03</strong>Production</span>
          <h3 class="phase__title">On-ground <em>execution.</em></h3>
          <svg class="sproc__glyph reveal" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path class="g" d="M10 22V12a2 2 0 0 1 2-2h10" pathLength="1"/>
            <path class="g" d="M42 10h10a2 2 0 0 1 2 2v10" pathLength="1" style="--d:.2s"/>
            <path class="g" d="M54 42v10a2 2 0 0 1-2 2H42" pathLength="1" style="--d:.4s"/>
            <path class="g" d="M22 54H12a2 2 0 0 1-2-2V42" pathLength="1" style="--d:.6s"/>
            <circle class="f pulse" cx="32" cy="32" r="4" fill="var(--red)" stroke="none"/>
          </svg>
          <p class="phase__lead">The director who wrote the treatment runs the floor — daily rushes, zero drift.</p>
          <div class="phase__meta">
            <span class="phase__pill"><span class="dot"></span>Shoot days</span>
            <span class="phase__pill">Director-led</span>
            <span class="phase__pill">Daily selects</span>
          </div>
        </div>
        <div class="phase__body">
          <div class="phase__media spotlight">
            <span class="phase__media-tag">On set</span>
            <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1400&q=85" alt="On-set production" decoding="async">
          </div>
          <ul class="checklist reveal">
            <li><span class="check"></span><div><strong>Cinema-grade capture</strong></div></li>
            <li><span class="check"></span><div><strong>On-set look development</strong></div></li>
            <li><span class="check"></span><div><strong>Daily rushes &amp; selects</strong></div></li>
            <li><span class="check"></span><div><strong>Continuity &amp; supervision</strong></div></li>
          </ul>
          <div class="output">
            <span class="output__h">You receive</span>
            <div class="output__list">
              <span>Daily rushes</span><span>BTS stills</span><span>Selects</span><span>Same-day reel*</span>
            </div>
          </div>
        </div>
      </article>

      <!-- PHASE 04 - POST -->
      <article class="phase">
        <div class="phase__sticky">
          <span class="phase__num"><strong>04</strong>Post &amp; Delivery</span>
          <h3 class="phase__title">Finish like <em>you mean it.</em></h3>
          <svg class="sproc__glyph reveal" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path class="g" d="M12 27v10" pathLength="1"/>
            <path class="g" d="M22 20v24" pathLength="1" style="--d:.15s"/>
            <path class="g" d="M32 13v38" pathLength="1" style="--d:.3s" stroke="var(--red)"/>
            <path class="g" d="M42 22v20" pathLength="1" style="--d:.45s"/>
            <path class="g" d="M52 28v8" pathLength="1" style="--d:.6s"/>
          </svg>
          <p class="phase__lead">Grade, sound and conform in-house — never outsourced. The USP in action.</p>
          <div class="phase__meta">
            <span class="phase__pill"><span class="dot"></span>Week 2–4</span>
            <span class="phase__pill">3 review cycles</span>
            <span class="phase__pill">In-house grade</span>
          </div>
        </div>
        <div class="phase__body">
          <div class="phase__media spotlight">
            <span class="phase__media-tag">Post</span>
            <img src="https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=1400&q=85" alt="Post production" loading="lazy" decoding="async">
          </div>
          <ul class="checklist reveal">
            <li><span class="check"></span><div><strong>Story-led edit, 3 review cycles</strong></div></li>
            <li><span class="check"></span><div><strong>ACES grade &amp; sound in-house</strong></div></li>
            <li><span class="check"></span><div><strong>Platform-tuned masters</strong></div></li>
            <li><span class="check"></span><div><strong>Cloud archive &amp; debrief</strong></div></li>
          </ul>
          <div class="output">
            <span class="output__h">You receive</span>
            <div class="output__list">
              <span>Hero film</span><span>Platform cuts</span><span>Masters</span><span>Subtitles</span><span>Cloud archive</span><span>Debrief</span>
            </div>
          </div>
        </div>
      </article>
    </div>
  </section>

  <!-- 3. SAMPLE TIMELINE RIBBON -->
  <section class="ribbon" data-screen-label="03 Sample Timeline">
    <div class="wrap">
      <div class="services__head ribbon__head">
        <div>
          <span class="section__eyebrow" data-scramble>A real schedule</span>
          <h2 class="section__title" data-split>Here's what <em>4 weeks</em> actually looks like.</h2>
        </div>
        <p class="section__lead reveal">Sample timeline for a typical brand film, one shoot day. A reference, not a template — every project gets its own.</p>
      </div>
      <div class="ribbon__bar">
        <div class="ribbon__line"></div>
        <div class="ribbon__fill"></div>
        <div class="ribbon__stops">
          <div class="ribbon__stop is-now">
            <span class="ribbon__day">Day 1</span>
            <span class="ribbon__label">Kickoff</span>
          </div>
          <div class="ribbon__stop">
            <span class="ribbon__day">Day 5</span>
            <span class="ribbon__label">Treatment</span>
          </div>
          <div class="ribbon__stop">
            <span class="ribbon__day">Day 10</span>
            <span class="ribbon__label">Pre-pro lock</span>
          </div>
          <div class="ribbon__stop">
            <span class="ribbon__day">Day 14</span>
            <span class="ribbon__label">Shoot</span>
          </div>
          <div class="ribbon__stop">
            <span class="ribbon__day">Day 21</span>
            <span class="ribbon__label">Fine cut</span>
          </div>
          <div class="ribbon__stop">
            <span class="ribbon__day">Day 28</span>
            <span class="ribbon__label">Delivery</span>
          </div>
        </div>
      </div>
      <div class="ribbon__detail">
        <div class="ribbon__cell reveal"><p>2-hour kickoff — brief mapped, metrics locked, thesis signed off.</p></div>
        <div class="ribbon__cell reveal" data-delay="1"><p>Treatment deck for review. Mood, palette, type, music — all visualised.</p></div>
        <div class="ribbon__cell reveal" data-delay="2"><p>Call sheet, shot list, kit locked. Crew confirmed, contracts signed.</p></div>
        <div class="ribbon__cell reveal" data-delay="3"><p>Production day. Rushes backed up on-site, selects to editor by 9 PM.</p></div>
        <div class="ribbon__cell reveal" data-delay="4"><p>Fine cut after two feedback rounds. Sound and grade in parallel.</p></div>
        <div class="ribbon__cell reveal" data-delay="5"><p>Masters delivered with platform cuts, subtitles and a written debrief.</p></div>
      </div>
    </div>
  </section>

  <!-- 4. SIX PRINCIPLES -->
  <section class="principles" data-screen-label="04 Principles">
    <div class="wrap">
      <div class="services__head">
        <div>
          <span class="section__eyebrow" data-scramble>Working principles</span>
          <h2 class="section__title" data-split>The six rules we <em>never bend.</em></h2>
        </div>
        <p class="section__lead reveal">Ask us to break one and we re-scope or walk. The standard is the standard.</p>
      </div>

      <div class="principles__grid">
        <div class="principle reveal">
          <div class="principle__icon">◐</div>
          <h3>Director on every shoot. <em>No exceptions.</em></h3>
          <p>The director who wrote the treatment runs the floor. No hand-off mid-project.</p>
        </div>
        <div class="principle reveal" data-delay="1">
          <div class="principle__icon">▲</div>
          <h3>Grade is <em>never</em> outsourced.</h3>
          <p>The hand that lit the film grades it. Authorship survives to delivery.</p>
        </div>
        <div class="principle reveal" data-delay="2">
          <div class="principle__icon">●</div>
          <h3>Three review cycles. <em>Written feedback.</em></h3>
          <p>Structured rounds, never email chains. Past three, we re-scope — not spiral.</p>
        </div>
        <div class="principle reveal">
          <div class="principle__icon">★</div>
          <h3>Daily rushes during shoot.</h3>
          <p>You see everything we shoot, every evening. No rough-cut surprises a week later.</p>
        </div>
        <div class="principle reveal" data-delay="1">
          <div class="principle__icon">◇</div>
          <h3>Brand guidelines are <em>law.</em></h3>
          <p>Tone, palette, type, compliance — premium-brand discipline at every scale.</p>
        </div>
        <div class="principle reveal" data-delay="2">
          <div class="principle__icon">◢</div>
          <h3>Two-year archive. <em>Always.</em></h3>
          <p>Searchable cloud archive of every project. Re-cut, re-export, re-purpose — no re-fee.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- 5. GUARANTEES -->
  <section class="guarantees" data-screen-label="05 Guarantees">
    <div class="wrap">
      <div class="services__head">
        <div>
          <span class="section__eyebrow" data-scramble>What we promise</span>
          <h2 class="section__title" data-split>Four <em>guarantees,</em> in writing.</h2>
        </div>
        <p class="section__lead reveal">Every brief comes with these built in. Not bullet points — contractual.</p>
      </div>

      <div class="guarantees__list">
        <div class="guarantee reveal">
          <div class="guarantee__num">04<em>h</em></div>
          <div>
            <div class="guarantee__h">Reply within 4 working hours.</div>
            <p class="guarantee__p">Brief to first response — every weekday, every time zone we work in.</p>
          </div>
          <div class="guarantee__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></div>
        </div>
        <div class="guarantee reveal" data-delay="1">
          <div class="guarantee__num">48<em>h</em></div>
          <div>
            <div class="guarantee__h">Same-day reel for events.</div>
            <p class="guarantee__p">Cut on-site, delivered before guests leave or the show ends — guaranteed.</p>
          </div>
          <div class="guarantee__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></div>
        </div>
        <div class="guarantee reveal" data-delay="2">
          <div class="guarantee__num">02<em>yr</em></div>
          <div>
            <div class="guarantee__h">Cloud archive included.</div>
            <p class="guarantee__p">Searchable archive of every asset, two years. Re-cuts, re-purposes — no re-fee.</p>
          </div>
          <div class="guarantee__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></div>
        </div>
        <div class="guarantee reveal" data-delay="3">
          <div class="guarantee__num">03<em>×</em></div>
          <div>
            <div class="guarantee__h">Review cycles, written.</div>
            <p class="guarantee__p">Three structured rounds per project — written feedback templates, never an email thread.</p>
          </div>
          <div class="guarantee__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></div>
        </div>
      </div>
    </div>
  </section>

  <!-- 6. KIT -->
  <section class="kit" data-screen-label="06 Kit">
    <div class="wrap">
      <div class="services__head">
        <div>
          <span class="section__eyebrow" data-scramble>Tools &amp; kit</span>
          <h2 class="section__title" data-split>Cinema-grade <em>by default.</em></h2>
        </div>
        <p class="section__lead reveal">A shortlist of what's in our trucks — extended per-brief when a project needs a specific look.</p>
      </div>
      <div class="kit__grid">
        <div class="kit__card reveal">
          <span class="kit__cat">Cameras</span>
          <h3 class="kit__name">Cinema <em>bodies.</em></h3>
          <div class="kit__list"><span>ARRI Alexa Mini</span><span>RED Komodo X</span><span>Sony FX6</span><span>Phantom Flex 4K</span></div>
        </div>
        <div class="kit__card reveal" data-delay="1">
          <span class="kit__cat">Lenses</span>
          <h3 class="kit__name">Glass we <em>trust.</em></h3>
          <div class="kit__list"><span>Cooke S4 Mini</span><span>Zeiss Supreme</span><span>Sigma Cine Primes</span><span>Atlas Orion</span></div>
        </div>
        <div class="kit__card reveal" data-delay="2">
          <span class="kit__cat">Lighting</span>
          <h3 class="kit__name">Light by <em>design.</em></h3>
          <div class="kit__list"><span>ARRI SkyPanel</span><span>Aputure 1200x</span><span>Astera Titan</span><span>Practicals</span></div>
        </div>
        <div class="kit__card reveal">
          <span class="kit__cat">Movement</span>
          <h3 class="kit__name">Smooth like <em>silk.</em></h3>
          <div class="kit__list"><span>DJI Ronin 4D</span><span>MoVI Pro</span><span>Phantom Flex</span><span>DJI Inspire 3</span></div>
        </div>
        <div class="kit__card reveal" data-delay="1">
          <span class="kit__cat">Sound</span>
          <h3 class="kit__name">Heard, <em>not seen.</em></h3>
          <div class="kit__list"><span>Sennheiser MKH</span><span>DPA 4017</span><span>Sound Devices MixPre</span><span>Lectrosonics</span></div>
        </div>
        <div class="kit__card reveal" data-delay="2">
          <span class="kit__cat">Post</span>
          <h3 class="kit__name">In-house <em>finish.</em></h3>
          <div class="kit__list"><span>DaVinci Resolve</span><span>Avid Media Composer</span><span>Pro Tools</span><span>Adobe Suite</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- 7. FAQ -->
  <section class="pfaq" data-screen-label="07 FAQ">
    <div class="wrap">
      <div class="services__head">
        <div>
          <span class="section__eyebrow" data-scramble>Common questions</span>
          <h2 class="section__title" data-split>Things people <em>ask.</em></h2>
        </div>
      </div>
      <div class="acc" data-acc>
        <div class="acc__item is-open">
          <button class="acc__head"><h3>Can you compress the timeline if we need it faster?</h3><span class="acc__plus"></span></button>
          <div class="acc__body"><div class="acc__body-inner">Yes — we collapse weeks 2–3 by parallelising edit and grade or adding a second editor. Never at the cost of quality; we'll re-scope or hold the shoot date instead.</div></div>
        </div>
        <div class="acc__item">
          <button class="acc__head"><h3>What if we don't like the rough cut?</h3><span class="acc__plus"></span></button>
          <div class="acc__body"><div class="acc__body-inner">We go back to the phase-one creative thesis — the brief we're protecting. Changing the thesis is a re-scope, not a revision; if the cut drifts from a sound thesis, that's on us and we re-edit at no charge.</div></div>
        </div>
        <div class="acc__item">
          <button class="acc__head"><h3>Do you work with our in-house team?</h3><span class="acc__plus"></span></button>
          <div class="acc__body"><div class="acc__body-inner">Often. We integrate cleanly with brand marketing teams, agencies of record and internal video teams — treatment-shoot-handoff or full end-to-end. Both are common.</div></div>
        </div>
        <div class="acc__item">
          <button class="acc__head"><h3>What about IP, licensing, and usage?</h3><span class="acc__plus"></span></button>
          <div class="acc__body"><div class="acc__body-inner">Default is a 3-year worldwide license on final films and stills. Buyouts and extensions negotiate easily per project. Raw footage stays with us, archived for two years.</div></div>
        </div>
        <div class="acc__item">
          <button class="acc__head"><h3>Do you travel for shoots?</h3><span class="acc__plus"></span></button>
          <div class="acc__body"><div class="acc__body-inner">Yes — pan-India and select international. Travel, stay and per-diems billed at cost, no markup. We've shot everywhere from Noida studios to the Himalayan foothills.</div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- 8. CTA -->
  <section class="cta-strip">
    <div class="wrap">
      <h2 class="cta-strip__title" data-split>Bring the brief.<br><em>We'll bring the cut.</em></h2>
      <div class="cta-strip__row reveal">
        <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">A treatment, a timeline and a number — back to you within 4 working hours.</p>
        <a class="btn btn--red" href="#quote" data-quote-trigger data-magnetic data-cursor="START">Start a brief <span class="arr"></span></a>
      </div>
    </div>
  </section>
</x-layouts.app>
