/* Work globe — featured tiles orbiting on a rotating sphere.
 *
 * three.js does the sphere maths and its CSS3DRenderer keeps every tile a real
 * DOM <button>, so the existing lightbox delegation, lazy <img> loading, focus
 * outlines and hover styles all keep working — nothing is redrawn to a canvas.
 *
 * three is ~170kb gzipped, so it is dynamically imported the first time a globe
 * comes near the viewport. The section sits well below the fold, and the grid
 * in the markup is a complete layout on its own, so the download never blocks
 * first paint and a failed import just leaves the grid in place.
 */

const IDLE_SPIN = 0.0018;   // radians/frame when nobody is touching it
const TILT_LIMIT = 0.42;    // how far up/down the arc can be dragged
// Deliberately small. The fit below has to reserve width for the worst yaw —
// a tile swung toward the camera is magnified — so a generous limit costs span.
// The arc should reach the container edges; the sway only needs to be felt.
const YAW_LIMIT  = 0.16;
// Idle drift, as a fraction of the arc's half-span. Each tile floats on its own
// phase so the arc breathes instead of moving as one rigid body.
const FLOAT_AMP = 0.05;
const DRAG_STALE = 120;     // ms — a pointer that stopped this long ago doesn't fling

const clamp = (v, lo, hi) => Math.min(hi, Math.max(lo, v));

export function initWorkGlobe() {
  const roots = document.querySelectorAll('[data-work-globe]');
  if (!roots.length) return;

  // Reduced motion gets the static grid. A sphere that only moves on drag would
  // still be a large unexpected motion the moment it's touched.
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (!e.isIntersecting) return;
      io.unobserve(e.target);
      mount(e.target);
    });
  }, { rootMargin: '300px 0px' });

  roots.forEach((r) => io.observe(r));
}

async function mount(root) {
  const stage = root.querySelector('[data-wglobe-stage]');
  const cells = [...root.querySelectorAll('[data-wglobe-cell]')];
  // Fewer than four tiles isn't a sphere, it's a wobble. Keep the grid.
  if (!stage || cells.length < 4) return;

  let THREE, CSS3DRenderer, CSS3DObject;
  try {
    const [core, css3d] = await Promise.all([
      import('three'),
      import('three/addons/renderers/CSS3DRenderer.js'),
    ]);
    THREE = core;
    ({ CSS3DRenderer, CSS3DObject } = css3d);
  } catch {
    return; // grid fallback stays on the page
  }

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(42, 1, 1, 8000);
  const renderer = new CSS3DRenderer();
  const group = new THREE.Object3D();
  scene.add(group);

  /* Scattered field. A pure low-discrepancy sequence still left visible bands
     and bald corners at this tile count, so the field is stratified instead:
     one tile per cell of a coarse grid, jittered well off centre. That
     guarantees the whole frame is used while keeping the placement unplanned.
     Halton supplies the jitter so it stays deterministic — a resize must not
     reshuffle the field under the reader. */
  const halton = (i, base) => {
    let f = 1, r = 0, n = i + 1;
    while (n > 0) { f /= base; r += f * (n % base); n = Math.floor(n / base); }
    return r;
  };

  const n = cells.length;

  const objects = cells.map((cell, i) => {
    // ±0.5 of a cell — enough to break the grid up completely, not enough to
    // let neighbours trade places and re-open a gap.
    const jx = (halton(i, 3) - 0.5) * 0.4;
    const jy = (halton(i, 5) - 0.5) * 0.4;
    const obj = new CSS3DObject(cell);
    // x/y are assigned in layout(), which is where the grid is decided.
    obj.userData.jit = [jx, jy];
    obj.userData.dir = new THREE.Vector3(0, 0, halton(i, 7) * 2 - 1);
    obj.userData.front = true;
    // Irrational multipliers keep the three axes from ever re-synchronising,
    // so no two tiles fall into a visible shared rhythm.
    obj.userData.ph = [i * 2.399, i * 1.618 + 1.1, i * 0.937 + 2.3];
    obj.userData.sp = [0.000115, 0.000149, 0.000093];
    obj.userData.base = new THREE.Vector3();
    group.add(obj);
    return obj;
  });

  root.classList.add('is-3d');
  stage.appendChild(renderer.domElement);

  /* -------------------- Layout -------------------- */
  let arcSpan = 300;   // half-width of the arc in world units, set by layout()

  function layout() {
    const w = stage.clientWidth;
    const h = stage.clientHeight;
    if (!w || !h) return;
    renderer.setSize(w, h);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();

    // Tiles are a fixed pixel size in 3D space, so they have to be scaled by
    // hand — otherwise a phone-sized sphere is nothing but overlapping tiles.
    // offsetWidth is the CSS box and ignores the transform the renderer writes,
    // so it stays the true unscaled size across relayouts.
    const cellW = cells[0].offsetWidth || 190;
    const cellH = cells[0].offsetHeight || cellW * 0.75;
    // Declared here because the scale budget below needs it: CSS3DRenderer
    // projects with `perspective: fovPx`, so this sets the magnification.
    const fovPx = (0.5 * h) / Math.tan((camera.fov * Math.PI) / 360);
    /* Size the tile to its grid cell rather than to the viewport. A viewport
       formula can't know whether the tiles actually fit: at 15 tiles the total
       tile area can exceed the stage, and then overlap is geometry, not tuning.
       Solving against the cell makes them as large as the layout allows and no
       larger — so a bigger screen genuinely gets bigger tiles. The cell has to
       hold the tile plus its drift on both sides. */
    const driftPx0 = Math.min(w, h) * FLOAT_AMP;

    /* Pick the column count that makes the tiles biggest, rather than deriving
       it from the tile count alone. The best split depends on the stage's
       aspect AND the tile's — a 16:9 tile wants fewer, wider columns than a
       9:16 one on the same stage, and a fixed guess leaves size on the table
       (which is what kept desktop tiles small). Cheap: it runs on resize. */
    let cols = 2, rows = n, best = -1;
    for (let c = 1; c <= n; c++) {
      const r = Math.ceil(n / c);
      const fit = Math.min((w / c) / cellW, (h / r) / cellH);
      if (fit > best) { best = fit; cols = c; rows = r; }
    }
    cols = Math.max(1, cols);
    rows = Math.max(1, rows);

    const cellPxW = w / cols;
    const cellPxH = h / rows;
    /* A tile's on-screen size is scale × magnification, and magnification runs
       up to ~1.3 for the nearest tiles. Budgeting the cell against the base
       scale alone lets those near tiles render a third larger than their cell
       allows — which is what was still putting them in contact. Divide the
       budget by the worst magnification so even the closest tile fits. */
    /* Depth is the main tax on tile size: the size budget is divided by the
       worst-case magnification, which depth drives. Keep it shallow enough that
       tiles stay large — the near/far contrast survives at this range. */
    const depthPre = Math.min(fovPx * 0.16, Math.min(w, h) * 0.22);
    const swingPre = Math.sin(YAW_LIMIT) * w * 0.5 * Math.sin(YAW_LIMIT);
    const maxMag = fovPx / Math.max(1, fovPx - depthPre - swingPre);
    const scale = clamp(
      Math.min(
        (cellPxW - driftPx0 * 2) * 0.98 / ((cells[0].offsetWidth || 190) * maxMag),
        (cellPxH - driftPx0 * 2) * 0.98 / ((cells[0].offsetHeight || 142) * maxMag),
      ),
      0.5,
      2.4,
    );
    const halfW = (cellW * scale) / 2;
    const halfH = (cellH * scale) / 2;

    /* Park the camera exactly one focal length out, so world units map 1:1 to
       pixels at z = 0 and the arc's span can be set directly from the stage
       width. Tiles pushed off that plane still scale naturally with distance,
       which is what gives the overlaps their depth. */
    camera.position.z = fovPx;

    /* Depth drives magnification: CSS3DRenderer scales by fovPx/dist, so a tile
       nearer the camera is a wider one. Budgeting every tile against the single
       worst case wastes the frame — a tile at the BACK is minified and can sit
       far closer to the edge than one at the front. So each tile gets its own
       span, computed from its own depth. */
    const depth = depthPre;
    const swing = swingPre;
    const driftPx = Math.min(w, h) * FLOAT_AMP;
    /* No inset. The field is meant to occupy the whole stage — pulling it in
       from the edges just leaves dead margin around a cluster in the middle.
       Tiles are already held inside the frame by their own half-size and the
       drift reserve, so the outermost ones sit near-flush with the edge. */
    const fieldPad = 0;

    arcSpan = w / 2;

    /* Position in SCREEN space, then divide out the magnification to get the
       world point that projects there. Scaling world coords by a per-tile span
       instead — which is what this did first — means a far tile (big span) and a
       near tile (small span) in neighbouring cells can land on top of each
       other: the grid guarantees separation in normalised units, but those units
       mean different pixel distances at different depths. Placing by pixels
       keeps the scatter exactly as laid out, whatever the depth. */
    objects.forEach((o, i) => {
      const col = i % cols;
      const row = Math.floor(i / cols);
      const j = o.userData.jit;
      const d = o.userData.dir;
      // Edge-to-edge mapping: cell centres would cap the outer tiles short of
      // the frame and leave a permanent dead margin.
      d.x = cols === 1 ? 0 : clamp((col + j[0]) / (cols - 1), 0, 1) * 2 - 1;
      d.y = rows === 1 ? 0 : clamp((row + j[1]) / (rows - 1), 0, 1) * 2 - 1;
      const z = d.z * depth;
      const mag = fovPx / Math.max(1, fovPx - z - swing);
      // Usable half-extent for THIS tile, at its rendered size.
      const safeX = Math.max(20, w / 2 - halfW * mag - driftPx - fieldPad);
      const safeY = Math.max(20, h / 2 - halfH * mag - driftPx - fieldPad);
      o.userData.base.set((d.x * safeX) / mag, (d.y * safeY) / mag, z);
      o.position.copy(o.userData.base);
      o.scale.setScalar(scale);
    });
  }

  layout();
  new ResizeObserver(layout).observe(stage);

  /* -------------------- Frame loop -------------------- */
  let velY = IDLE_SPIN, velX = 0;
  let dragging = false, hovering = false, paused = false;
  let lastMove = 0;
  let snapTo = null;           // yaw to ease to when a tile takes keyboard focus
  let raf = 0, visible = false;
  const world = new THREE.Vector3();

  function frame() {
    if (snapTo !== null) {
      const d = snapTo - group.rotation.y;
      group.rotation.y += d * 0.12;
      group.rotation.x += (0 - group.rotation.x) * 0.12;
      if (Math.abs(d) < 0.002) { group.rotation.y = snapTo; snapTo = null; }
    } else if (!dragging) {
      // Ease back to the idle drift rather than snapping — a fling decays into
      // the ambient spin instead of stopping dead.
      // A face-on arc must not spin like a globe: a full turn swings it
      // edge-on and the parabola collapses to a line. It sways instead.
      velY *= 0.86;
      velX *= 0.9;
      const sway = (paused || hovering) ? 0 : Math.sin(performance.now() * 0.00035) * YAW_LIMIT * 0.5;
      group.rotation.y = clamp(group.rotation.y + velY + (sway - group.rotation.y) * 0.018, -YAW_LIMIT, YAW_LIMIT);
      group.rotation.x = clamp(group.rotation.x + velX, -TILT_LIMIT, TILT_LIMIT);
    }

    /* Zero-g drift. Each tile orbits its own point on the arc on three
       independent sine axes, so the arrangement reads as suspended rather than
       pinned to a wire. Frozen while paused or dragged — the user's input
       should be the only thing moving then. */
    const now = performance.now();
    const drifting = !paused && !dragging;
    const amp = Math.min(arcSpan, stage.clientHeight / 2) * FLOAT_AMP;

    // Billboard every tile: cancelling the group's rotation leaves each one
    // square to the camera, so photos stay readable instead of turning edge-on
    // or mirroring across the back of the sphere. The camera is axis-aligned,
    // so "facing the camera" is just the inverse of the parent rotation.
    for (const o of objects) {
      if (drifting) {
        const [p0, p1, p2] = o.userData.ph;
        const [s0, s1, s2] = o.userData.sp;
        const b = o.userData.base;
        o.position.set(
          b.x + Math.sin(now * s0 + p0) * amp,
          b.y + Math.cos(now * s1 + p1) * amp * 1.15,
          b.z + Math.sin(now * s2 + p2) * amp * 0.9,
        );
      }

      o.quaternion.copy(group.quaternion).invert();
      // A degree or two of roll, phase-shifted per tile — nothing in free fall
      // holds a perfectly level horizon.
      o.rotateZ(Math.sin(now * 0.00013 + o.userData.ph[0]) * 0.035);

      // Depth, 0 at the back of the sphere to 1 at the front. Derived from the
      // tile's own direction rather than a world matrix so it doesn't depend on
      // where in the frame the renderer last updated its matrices.
      world.copy(o.position).applyQuaternion(group.quaternion);
      const depth = clamp(0.5 + world.z / (arcSpan * 1.1), 0, 1);
      // Quantised to 1/40ths, and only written when the step actually changes.
      // --d feeds an opacity calc(), so every write invalidates that element's
      // style — writing all 15 every frame measured ~16ms/frame on its own. The
      // step is far finer than the eye can see in an opacity ramp.
      const step = Math.round(depth * 40);
      if (step !== o.userData.step) {
        o.userData.step = step;
        o.element.style.setProperty('--d', (step / 40).toFixed(3));
      }

      // Tiles on the far side sit behind the near ones — they must not swallow
      // the click meant for whatever is in front of them.
      const front = depth > 0.5;
      if (front !== o.userData.front) {
        o.userData.front = front;
        o.element.style.pointerEvents = front ? 'auto' : 'none';
      }
    }

    renderer.render(scene, camera);
    raf = requestAnimationFrame(frame);
  }

  function start() {
    if (raf) return;
    raf = requestAnimationFrame(frame);
  }
  function stop() {
    cancelAnimationFrame(raf);
    raf = 0;
  }

  // Spinning off-screen, or behind a hidden tab, is pure battery.
  const runIO = new IntersectionObserver(([e]) => {
    visible = e.isIntersecting;
    if (visible && !document.hidden) start(); else stop();
  }, { rootMargin: '100px 0px' });
  runIO.observe(root);
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) stop(); else if (visible) start();
  });

  /* -------------------- Drag to spin -------------------- */
  let px = 0, py = 0, moved = 0;

  stage.addEventListener('pointerdown', (e) => {
    if (e.pointerType === 'mouse' && e.button !== 0) return;
    dragging = true;
    moved = 0;
    px = e.clientX;
    py = e.clientY;
    lastMove = e.timeStamp;
    snapTo = null;
    root.classList.add('is-dragging');
  });

  stage.addEventListener('pointermove', (e) => {
    if (!dragging) return;
    const dx = e.clientX - px;
    const dy = e.clientY - py;
    px = e.clientX;
    py = e.clientY;
    moved += Math.abs(dx) + Math.abs(dy);
    // Capture only once the gesture is clearly a drag, so a plain click on a
    // tile still lands on the tile.
    if (moved > 6 && !stage.hasPointerCapture(e.pointerId)) stage.setPointerCapture(e.pointerId);
    velY = dx * 0.005;
    velX = dy * 0.004;
    group.rotation.y += velY;
    group.rotation.x = clamp(group.rotation.x + velX, -TILT_LIMIT, TILT_LIMIT);
    lastMove = e.timeStamp;
  });

  function endDrag(e) {
    if (!dragging) return;
    dragging = false;
    if (e && stage.hasPointerCapture(e.pointerId)) stage.releasePointerCapture(e.pointerId);
    root.classList.remove('is-dragging');
    // A pointer that came to rest before release shouldn't fling the sphere on
    // the stale velocity from wherever it last moved.
    if (e && e.timeStamp - lastMove > DRAG_STALE) { velY = 0; velX = 0; }
  }
  stage.addEventListener('pointerup', endDrag);
  stage.addEventListener('pointercancel', endDrag);

  // Swallow the click that terminates a drag, or releasing a spin would also
  // open the lightbox for whatever tile happened to be under the cursor.
  stage.addEventListener('click', (e) => {
    if (moved > 6) { e.preventDefault(); e.stopPropagation(); moved = 0; }
  }, true);

  root.addEventListener('pointerenter', () => { hovering = true; });
  root.addEventListener('pointerleave', () => { hovering = false; endDrag(); });

  /* -------------------- Keyboard -------------------- */
  // Tabbing to a tile on the far side would otherwise focus something the user
  // can't see; bring it round instead.
  objects.forEach((o) => {
    o.element.addEventListener('focusin', () => {
      const d = o.userData.dir;
      // Yaw that rotates this tile's direction onto +z (straight at the camera),
      // picked in whichever direction is the shorter way round from here.
      const want = -Math.atan2(d.x, d.z);
      const turns = Math.round((group.rotation.y - want) / (Math.PI * 2));
      snapTo = want + turns * Math.PI * 2;
    });
  });

  stage.addEventListener('keydown', (e) => {
    if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
    e.preventDefault();
    snapTo = null;
    velY = e.key === 'ArrowRight' ? -0.05 : 0.05;
  });

  /* -------------------- Pause control -------------------- */
  // WCAG 2.2.2: the spin runs indefinitely, so it needs a stop that works
  // without a pointer. Swapping the label (rather than aria-pressed) is what
  // conveys the state — the button's accessible name always says what the next
  // press will do. Dragging still works while paused; that's user-initiated.
  const controls = root.querySelector('[data-wglobe-controls]');
  const toggle = root.querySelector('[data-wglobe-toggle]');
  if (controls) controls.hidden = false;
  if (toggle) {
    toggle.addEventListener('click', () => {
      paused = !paused;
      toggle.textContent = paused ? 'Resume rotation' : 'Pause rotation';
      if (paused) { velY = 0; velX = 0; snapTo = null; }
    });
  }
}
