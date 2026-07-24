<x-layouts.app title="Page not found — TheLastClicks" description="This page is not in our archive.">

<style>
  .nf { min-height: calc(100vh - 90px); display: grid; place-items: center; padding: 130px var(--pad-x) 80px; position: relative; overflow: hidden; }
  .nf::before { content: '404'; position: absolute; inset: 50% auto auto 50%; transform: translate(-50%, -50%); font-family: var(--f-display); font-weight: 800; font-size: clamp(280px, 50vw, 720px); letter-spacing: -0.08em; color: rgba(232,15,3,0.06); line-height: 0.85; pointer-events: none; z-index: 0; user-select: none; }
  .nf__inner { position: relative; z-index: 1; text-align: center; max-width: 720px; }
  .nf__kicker { font-family: var(--f-mono); font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--red); display: inline-flex; align-items: center; gap: 12px; margin-bottom: 32px; }
  .nf__kicker::before, .nf__kicker::after { content: ''; width: 28px; height: 1px; background: var(--red); }
  .nf h1 { font-family: var(--f-display); font-weight: 700; font-size: clamp(56px, 9vw, 144px); letter-spacing: -0.05em; line-height: 0.9; text-wrap: balance; }
  .nf h1 em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .nf p { margin-top: 28px; font-size: clamp(16px, 1.4vw, 19px); line-height: 1.6; color: var(--paper-dim); max-width: 44ch; margin-left: auto; margin-right: auto; }
  .nf__buttons { margin-top: 40px; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
  .nf__links { margin-top: 64px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; border-top: 1px solid var(--line); }
  .nf__link { padding: 32px 24px; border-right: 1px solid var(--line); text-align: left; transition: background 0.4s var(--ease-soft); }
  .nf__link:last-child { border-right: 0; }
  .nf__link:hover { background: rgba(232,15,3,0.04); }
  .nf__link span { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); display: block; margin-bottom: 8px; }
  .nf__link strong { font-family: var(--f-display); font-weight: 500; font-size: 22px; letter-spacing: -0.025em; line-height: 1.1; display: flex; justify-content: space-between; align-items: center; gap: 12px; }
  .nf__link strong::after { content: '→'; color: var(--red); transition: transform 0.3s var(--ease-spring); }
  .nf__link:hover strong::after { transform: translateX(6px); }
  @media (max-width: 760px) {
    .nf__links { grid-template-columns: 1fr; }
    .nf__link { border-right: 0; border-bottom: 1px solid var(--line); padding: 22px 0; }
  }
</style>

<section class="nf" data-screen-label="01 404">
    <div class="nf__inner">
        <span class="nf__kicker">Lost in post</span>
        <h1 data-split>This frame <em>didn't make</em><br>the final cut.</h1>
        <p>This page didn't make the cut. Here are a few worth a watch.</p>
        <div class="nf__buttons">
            <a class="btn btn--red" href="{{ url('/') }}" data-magnetic data-cursor="HOME">Back to home <span class="arr"></span></a>
            <a class="btn btn--ghost" href="{{ url('/') }}" data-cursor="VIEW">See the reel <span class="arr"></span></a>
        </div>
        <div class="nf__links">
            <a class="nf__link" href="{{ url('/services/photography') }}"><span>Our craft</span><strong>Services</strong></a>
            <a class="nf__link" href="{{ url('/our-process') }}"><span>How we work</span><strong>Our process</strong></a>
            <a class="nf__link" href="{{ url('/contact') }}"><span>Bring a brief</span><strong>Contact us</strong></a>
        </div>
    </div>
</section>

</x-layouts.app>
