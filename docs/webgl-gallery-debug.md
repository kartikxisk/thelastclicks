# The curved-plane gallery does not draw

Open bug. Everything else in the WebGL layer renders; this one scene produces
nothing visible. Written down because it was guessed at three times and the
guessing is what wasted the time.

## Symptom

`/portfolio` mounts the canvas, `[data-scene="work-grid"]` attaches, the tier
resolves to `full`, textures load, and no plane is visible. Hiding the DOM
covers to "let the WebGL through" leaves an empty grid — which happened twice
and is why `WorkGallery` now renders covers unconditionally.

## Already fixed, and none of it was the cause

- The tracking div had no positioned ancestor, so drei's `View` scissored to
  the wrong box. Fixed: `[data-grid-root]` wraps it with `relative`.
- Tile rects were measured against the viewport while the View's origin is its
  own element. Fixed: rects are measured relative to `[data-grid-root]`.
- No camera in pixel space, so `rectToWorld` output meant nothing. Fixed: an
  `OrthographicCamera` sized to the view.
- Textures fetched cross-origin and were rejected by CORS. Fixed: they load
  through `/_next/image`, which is same-origin.

## The mistake to avoid repeating

Every attempt above added a fix *and* kept textures in play, so a failure at
any layer looked identical: nothing on screen. Debug in this order instead, and
confirm each step visually before moving on:

1. **Prove the view exists.** Replace `GalleryTile`'s material with
   `<meshBasicMaterial color="hotpink" />` and drop the custom shader
   entirely. If no pink rectangles appear, the problem is the `View`, the
   camera or the geometry — not the shader and not the textures.
2. **Prove the camera mapping.** With pink planes showing, check they land over
   the DOM tiles. If they are offset or the wrong size, `rectToWorld` or the
   orthographic frustum is wrong.
3. **Prove the shader.** Restore the custom material but return a constant
   colour from `main()`. If the pink disappears, the vertex displacement is
   pushing geometry outside the frustum, or `discard` is firing.
4. **Only then reintroduce the texture.** `uHasTexture` gates a `discard`, so a
   texture that never loads renders nothing at all — indistinguishable from a
   broken camera.

## Prime suspects, in order

- **`frameloop="demand"`.** The canvas only renders when something calls
  `invalidate()`. `GalleryScene` calls it on measure and while scroll velocity
  is non-zero — so a static page after the initial measure may simply never
  draw a frame. Try `frameloop="always"` temporarily; if the planes appear,
  this is it.
- **Plane z-position.** Tiles sit at `z = 0` while the camera is at `z = 1000`
  with `near = 0.1, far = 3000`. That should be in range, but the hover uniform
  pushes `z` by up to 24 and the curvature term subtracts up to `0.6 * 40`, so
  worth confirming nothing crosses the near plane.
- **`transparent` with `opacity` never set.** `GalleryTile`'s material is
  `transparent`, and the fragment shader writes `alpha = 1.0` — but if
  `uHasTexture` is 0 it `discard`s every fragment instead.

## Where the code is

| | |
|---|---|
| Scene | `web/src/webgl/scenes/GalleryScene.tsx` |
| Per-tile mesh + shader | `web/src/webgl/scenes/GalleryTile.tsx` |
| View binding | `web/src/webgl/Scene.tsx` |
| Canvas + frameloop | `web/src/webgl/Canvas.tsx` |
| DOM side | `web/src/components/work/WorkGallery.tsx` |

## The rule that must survive the fix

`WorkGallery` renders `work.cover` unconditionally. Whatever makes the gallery
draw, do not reintroduce a condition that hides the image when WebGL is
"active" — decoration must never be able to remove content. If the two layers
need to not overlap, fade the DOM image with opacity *after* the scene has
proven it drew a frame, never before.

## Debug session 2 — results

Ran steps 1 and 2 of the procedure above. Both suspects are now **ruled out**.

**Step 1 (solid colour, no shader, no texture): still nothing.** The material
was replaced with `<meshBasicMaterial color="hotpink" />` and no pink appeared.
That eliminates the shader, the textures and `uHasTexture`'s `discard` as
causes — the geometry is not reaching the screen at all.

**`frameloop="demand"` is not the cause.** Switching to `"always"` changed
nothing.

**The View is correctly sized.** `[data-scene="work-grid"]` measures
1328×1542, exactly matching `[data-grid-root]`. The tracking element and the
scissor box are right.

**No WebGL errors.** Only deprecation and GPU-performance warnings.

**A zero-width frustum guard did not fix it.** `GalleryScene` now returns null
until `bounds` is measured, on the theory that an `OrthographicCamera` built
with `left === right === 0` never recovers. Correct defensively, but not the
bug.

### What that leaves

The geometry exists and the view exists, but nothing rasterises. Remaining
candidates, in order:

1. **`makeDefault` inside a `<View>`.** drei's View manages its own camera.
   A child `OrthographicCamera` with `makeDefault` may be fighting it, or may
   not be applied to the View's render pass at all. Try removing the camera
   entirely and sizing planes in the View's default camera units instead —
   `useThree().viewport` inside a View reports that camera's world size.
2. **Plane scale versus camera units.** Without the ortho camera, planes sized
   in pixels (hundreds of units) against a default perspective camera at z=5
   are astronomically oversized and entirely off-screen. This would look
   exactly like "nothing renders".
3. **`<View.Port />` placement.** It is rendered as a sibling of
   `<PostProcessing />` inside the Canvas. Confirm the postprocessing pass is
   not consuming the render target the views draw into.

Suspect 3 is worth checking first and costs nothing: temporarily remove
`<PostProcessing />` from `Canvas.tsx` and see whether the planes appear. The
effect composer was added after the gallery, and nobody has verified the two
coexist.
