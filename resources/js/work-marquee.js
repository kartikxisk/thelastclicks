/* Work marquee — autoplaying previews on a looping strip.
 *
 * The scroll itself is pure CSS (see .wmq__track); hover and :focus-within
 * pause it without JS. This file owns only the video side plus the explicit
 * stop control.
 *
 * Two rules shape it:
 *
 *   Nothing polls. Playback is driven by one IntersectionObserver on the strip,
 *   not by a timer watching scroll position.
 *
 *   A dozen <video> elements decoding at once is real CPU even when muted and
 *   off screen, so they ship as preload="none" and only load and play while the
 *   strip is actually in view. Scrolling past releases them again.
 *
 *   "In view" is per tile, not per strip. Gating on the strip alone still ran
 *   every film in the track — twenty-four of them for twelve works, nineteen of
 *   which sat outside the clip with nobody able to see them.
 */

const REDUCED = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function play(video) {
  if (video.dataset.wmqReady !== '1') {
    // preload="none" means there is nothing buffered until we ask.
    video.preload = 'auto';
    video.dataset.wmqReady = '1';
  }
  // Autoplay can still be refused (power saving, platform policy). The poster
  // stays up in that case, which is a fine still — so the rejection is ignored
  // rather than reported.
  const started = video.play();
  if (started && typeof started.catch === 'function') started.catch(() => {});
}

export function initWorkMarquee() {
  const roots = document.querySelectorAll('[data-work-marquee]');
  if (!roots.length) return;

  roots.forEach(root => {
    const videos = [...root.querySelectorAll('[data-wmq-video]')];
    const toggle = root.querySelector('[data-wmq-toggle]');
    let stopped = false;
    let onScreen = false;

    // Which tiles are actually within the strip's own window. The set is what
    // keeps the rule at the top of this file true: the track holds the works
    // twice so it can loop, so a twelve-work strip ships twenty-four <video>
    // elements while only about seven are ever on screen. Playing the whole
    // track meant nineteen films decoding behind a clip that hides them, which
    // is what made scrolling past this section stutter.
    const visible = new Set();

    const sync = () => {
      const allowed = onScreen && !stopped && !REDUCED();
      videos.forEach(v => (allowed && visible.has(v) ? play(v) : v.pause()));
    };

    if (toggle) {
      toggle.addEventListener('click', () => {
        stopped = !stopped;
        // One control for both, because WCAG 2.2.2 covers the scrolling and the
        // auto-playing video alike, and two separate stop buttons for one strip
        // is worse than none.
        root.classList.toggle('is-paused', stopped);
        toggle.setAttribute('aria-pressed', String(stopped));
        toggle.textContent = stopped ? 'Play' : 'Pause';
        sync();
      });
    }

    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        onScreen = e.isIntersecting;
        sync();
      });
    }, { rootMargin: '200px 0px' });

    io.observe(root);

    // Second gate, per tile. Rooted on the strip's own viewport rather than the
    // page's: the track is clipped by .wmq__viewport, so a rootMargin measured
    // against the page would be swallowed by that clip and a tile would only
    // start once it was already visible. Against this root the margin buys a
    // tile enough lead-in to be running by the time it slides into view.
    const frame = root.querySelector('.wmq__viewport');
    const tileIO = new IntersectionObserver(entries => {
      entries.forEach(e => (e.isIntersecting ? visible.add(e.target) : visible.delete(e.target)));
      sync();
    }, { root: frame || null, rootMargin: '0px 240px' });

    videos.forEach(v => tileIO.observe(v));
  });
}
