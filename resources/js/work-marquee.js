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

    const sync = () => {
      const shouldPlay = onScreen && !stopped && !REDUCED();
      videos.forEach(v => (shouldPlay ? play(v) : v.pause()));
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
  });
}
