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
    } else {
      el = document.createElement('img');
      el.src = item.url;
      el.alt = item.caption || '';
    }
    stage.appendChild(el);
    caption.textContent = item.caption || '';
  }

  function open(payload, start = 0) {
    items = payload;
    index = start >= 0 && start < payload.length ? start : 0;
    lastFocused = document.activeElement;
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
      // Gallery tiles carry a shared payload + their own index so the lightbox
      // opens on the clicked item; Our Work tiles omit the index and start at 0.
      const start = Number.parseInt(tile.dataset.workIndex || '0', 10);
      try { open(JSON.parse(tile.dataset.workMedia || '[]'), start); } catch (e) { /* malformed payload: ignore */ }
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
