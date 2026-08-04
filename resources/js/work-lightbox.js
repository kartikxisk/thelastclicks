/* Work lightbox: opens a carousel of a tile's media (image / video / YouTube). */
export function initWorkLightbox() {
  const box = document.querySelector('[data-work-lightbox]');
  const tiles = [...document.querySelectorAll('[data-work-tile]')];
  if (!box || !tiles.length) return;

  const stage = box.querySelector('[data-wlb-stage]');
  const caption = box.querySelector('[data-wlb-caption]');
  const closeBtn = box.querySelector('[data-wlb-close]');
  let items = [];
  let index = 0;
  let lastFocused = null;

  function focusableEls() {
    return [...box.querySelectorAll('button, [href], input, select, textarea, iframe, video, [tabindex]:not([tabindex="-1"])')]
      .filter((el) => !el.disabled);
  }

  function render() {
    const item = items[index];
    stage.innerHTML = '';
    if (!item) return;

    let el;
    if (item.type === 'youtube') {
      el = document.createElement('iframe');
      el.src = item.url;
      el.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; picture-in-picture; fullscreen';
      el.allowFullscreen = true;
      el.title = item.caption || 'YouTube video';
      el.referrerPolicy = 'strict-origin-when-cross-origin';
      el.loading = 'lazy';
    } else if (item.type === 'video') {
      el = document.createElement('video');
      el.src = item.url;
      el.controls = true;
      el.playsInline = true;
      // Deliberately NOT autoplay. The attribute makes the browser attempt
      // playback on insertion, judged against the autoplay policy without the
      // click that opened the lightbox counting for it — so it was refused, and
      // the fallback below muted the video for good. The explicit play() call
      // runs inside the gesture, where sound is allowed.
      el.muted = false;
    } else {
      el = document.createElement('img');
      el.src = item.url;
      // The opened image IS the dialog's content, so it must not be silent to a
      // screen reader — fall back to a generic label when there's no caption.
      el.alt = item.caption || 'Enlarged media';
    }
    stage.appendChild(el);
    caption.textContent = item.caption || '';

    // Both entry points into render() are a click (opening a tile, or prev /
    // next), so this runs inside a user gesture and plays with sound.
    //
    // The muted retry is a genuine last resort, not the normal path: a platform
    // that refuses sound here would otherwise leave the viewer looking at a
    // frozen first frame. Controls are on, so sound is one click away when it
    // does fire.
    if (el.tagName === 'VIDEO') {
      const started = el.play();
      if (started && typeof started.catch === 'function') {
        started.catch(() => {
          el.muted = true;
          const retry = el.play();
          if (retry && typeof retry.catch === 'function') retry.catch(() => {});
        });
      }
    }
  }

  function open(payload, start = 0) {
    items = payload;
    index = start >= 0 && start < payload.length ? start : 0;
    lastFocused = document.activeElement;
    // Portal to <body>, same as the quote modal. The lightbox is rendered inside a
    // <section>, so any ancestor creating a stacking context (transform, will-change,
    // isolation, opacity) would trap it however high its z-index is.
    if (box.parentElement !== document.body) document.body.appendChild(box);
    box.hidden = false;
    document.body.style.overflow = 'hidden';
    render();
    closeBtn.focus();
  }

  function close() {
    box.hidden = true;
    document.body.style.overflow = '';
    stage.innerHTML = ''; // stops video + unloads the iframe
    caption.textContent = '';
    if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    lastFocused = null;
  }

  const step = (n) => { if (items.length) { index = (index + n + items.length) % items.length; render(); } };

  tiles.forEach((tile) => {
    tile.addEventListener('click', () => {
      // The payload may sit on the tile or on an ancestor. A strip of tiles that
      // all share one payload puts it on the container and gives each tile only
      // its offset — otherwise every tile opens a one-item carousel and next/prev
      // wrap straight back to the item already on screen, which reads as broken.
      // closest() returns the tile itself when it carries its own payload, so
      // single-payload tiles elsewhere are unaffected.
      const holder = tile.closest('[data-work-media]') || tile;
      const start = Number.parseInt(tile.dataset.workIndex || '0', 10);
      try { open(JSON.parse(holder.dataset.workMedia || '[]'), start); } catch (e) { /* malformed payload: ignore */ }
    });
  });

  closeBtn.addEventListener('click', close);
  box.querySelector('[data-wlb-prev]').addEventListener('click', () => step(-1));
  box.querySelector('[data-wlb-next]').addEventListener('click', () => step(1));
  box.addEventListener('click', (e) => { if (e.target === box || e.target === stage) close(); });
  document.addEventListener('keydown', (e) => {
    if (box.hidden) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') step(-1);
    if (e.key === 'ArrowRight') step(1);
    if (e.key === 'Tab') {
      const focusable = focusableEls();
      if (!focusable.length) return;
      const first = focusable[0];
      const last = focusable.at(-1);
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }
  });
}
