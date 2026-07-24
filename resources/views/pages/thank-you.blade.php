<x-layouts.app
    title="Brief received — TheLastClicks"
    description="Thanks for your brief — we will be in touch within 4 working hours."
    :canonical="url('/thank-you')"
>
  <x-slot name="head">
<style>
  .ty { min-height: calc(100vh - 90px); display: grid; place-items: center; padding: 130px var(--pad-x) 80px; }
  .ty__inner { text-align: center; max-width: 760px; }
  .ty__check {
    width: 120px; height: 120px;
    border-radius: 50%;
    background: rgba(232,15,3,0.08);
    border: 1.5px solid var(--red);
    display: grid; place-items: center;
    margin: 0 auto 40px;
    animation: tyPulse 2.4s var(--ease-soft) infinite;
    position: relative;
  }
  .ty__check::before {
    content: '';
    position: absolute;
    inset: -8px;
    border: 1px solid rgba(232,15,3,0.18);
    border-radius: 50%;
    animation: tyRing 2.4s var(--ease-soft) infinite;
  }
  @keyframes tyPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.04); }
  }
  @keyframes tyRing {
    0% { transform: scale(1); opacity: 0.6; }
    100% { transform: scale(1.5); opacity: 0; }
  }
  .ty__check svg { width: 60px; height: 60px; color: var(--red); }
  .ty__kicker { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--red); margin-bottom: 24px; display: inline-block; }
  .ty h1 { font-family: var(--f-display); font-weight: 700; font-size: clamp(48px, 7.5vw, 112px); letter-spacing: -0.045em; line-height: 0.94; text-wrap: balance; }
  .ty h1 em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .ty p { margin-top: 26px; font-size: 17px; line-height: 1.6; color: var(--paper-dim); max-width: 50ch; margin-left: auto; margin-right: auto; text-wrap: pretty; }
  .ty__next { margin-top: 48px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; padding-top: 48px; border-top: 1px solid var(--line); }
  .ty__step { padding: 28px 22px; border: 1px solid var(--line); text-align: left; transition: border-color 0.4s var(--ease-soft); }
  .ty__step:hover { border-color: var(--red); }
  .ty__step-n { font-family: var(--f-display); font-weight: 700; font-size: 36px; color: var(--red); letter-spacing: -0.03em; line-height: 1; margin-bottom: 16px; }
  .ty__step h3 { font-family: var(--f-display); font-weight: 500; font-size: 18px; letter-spacing: -0.02em; margin-bottom: 8px; }
  .ty__step p { margin: 0; font-size: 13.5px; color: var(--paper-dim); line-height: 1.5; max-width: 100%; }
  .ty__cta { margin-top: 48px; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
  @media (max-width: 760px) {
    .ty__next { grid-template-columns: 1fr; }
  }
</style>
  </x-slot>
  <section class="ty" data-screen-label="01 Thank you">
    <div class="ty__inner">
      <div class="ty__check">
        <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 32 L28 44 L48 22"/></svg>
      </div>
      <span class="ty__kicker">Brief received</span>
      <h1 data-split>Thanks — we're on <em>your brief.</em></h1>
      <p>We'll read every line and reply with next steps, a direction, and a number — within 4 working hours. Here's what happens next.</p>
      <div class="ty__next">
        <div class="ty__step">
          <div class="ty__step-n">01</div>
          <h3>We read it carefully</h3>
          <p>Director and producer review it together. No templates.</p>
        </div>
        <div class="ty__step">
          <div class="ty__step-n">02</div>
          <h3>We respond within 4h</h3>
          <p>A clear yes/no, a few questions, and a tentative scope.</p>
        </div>
        <div class="ty__step">
          <div class="ty__step-n">03</div>
          <h3>We meet, we treat</h3>
          <p>A 30-min align call, then a treatment within 5 working days.</p>
        </div>
      </div>
      <div class="ty__cta">
        <a class="btn btn--ghost" href="{{ url('/') }}" data-cursor="WORK">See the reel <span class="arr"></span></a>
        <a class="btn btn--ghost" href="{{ url('/blog') }}" data-cursor="READ">Read the journal <span class="arr"></span></a>
      </div>
    </div>
  </section>
</x-layouts.app>
