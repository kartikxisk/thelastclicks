/**
 * Client stories marquee — the stop control and the hover key light.
 *
 * The drift is pure CSS (.tsm__track), and hover and :has(:focus-visible)
 * pause it without script. What CSS cannot give is a way to stop it for good,
 * which WCAG 2.2.2 asks of any content that moves on its own for more than
 * five seconds — so the toggle flips one class, the same control the work
 * strip carries.
 *
 * The key light is a radial gradient on the hovered panel whose centre CSS
 * reads from --mx/--my. One delegated listener on the strip, writing at most
 * once a frame, and only to the panel under the pointer.
 */
export function initStoriesMarquee() {
  document.querySelectorAll('[data-stories-marquee]').forEach((root) => {
    const toggle = root.querySelector('[data-tsm-toggle]');

    toggle?.addEventListener('click', () => {
      const stopped = !root.classList.contains('is-paused');
      root.classList.toggle('is-paused', stopped);
      toggle.setAttribute('aria-pressed', String(stopped));
      toggle.textContent = stopped ? 'Play' : 'Pause';
    });

    let frame = 0;
    root.addEventListener('pointermove', (e) => {
      if (e.pointerType !== 'mouse') return;
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(() => {
        const card = e.target.closest?.('.tsm__card');
        if (!card) return;
        const box = card.getBoundingClientRect();
        card.style.setProperty('--mx', `${e.clientX - box.left}px`);
        card.style.setProperty('--my', `${e.clientY - box.top}px`);
      });
    }, { passive: true });
  });
}
