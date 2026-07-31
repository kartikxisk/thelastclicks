# WebGL Layer and Production Cutover Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add the five WebGL moments to the Next application under enforced performance budgets, prove SEO parity against the live Blade site, switch production traffic to Next, and retire the Blade frontend.

**Architecture:** A single persistent `<Canvas>` in the root layout that never unmounts. Sections opt into 3D by rendering a drei `<View>` bound to the `data-section` anchors Plan 2 already placed. WebGPU with automatic WebGL2 fallback; shaders authored once in TSL. Every scene is dynamically imported so the initial bundle carries zero WebGL bytes.

**Tech Stack:** three (WebGPURenderer + TSL), @react-three/fiber, @react-three/drei, @react-three/postprocessing, GSAP ScrollTrigger, Lighthouse CI.

**Prerequisites:** Plan 1 and Plan 2 are complete. The staging site renders every route with real content.

> **Revised during Plan 2 execution — read before starting Task 1.**
>
> The app runs Next 16 with `cacheComponents: true`. That flag changes
> navigation semantics in a way this plan's canvas design depends on: Next uses
> React `<Activity>` to keep recently visited routes **mounted but hidden**
> rather than unmounting them, so effects are cleaned up and recreated as a
> route hides and shows.
>
> Consequences to verify before building on Task 1:
>
> - The "single canvas persists across navigation" test still holds, but for a
>   different reason than assumed — confirm the canvas lives in the root layout
>   above any `<Activity>` boundary, or it will be preserved per-route instead
>   of shared.
> - `<Scene>`'s IntersectionObserver must survive hide/show. An observer
>   attached in an effect is torn down when a route hides; on re-show it must
>   re-attach, or a returning visitor gets a dead scene.
> - Video textures on a hidden route must pause. `<Activity>` cleanup runs
>   effects, so the existing pause-on-unmount path should fire — assert it.
>
> See `node_modules/next/dist/docs/01-app/02-guides/preserving-ui-state.md`.

## Global Constraints

- **Zero WebGL bytes in the initial bundle.** Every scene is `next/dynamic` with `ssr: false` behind `<Suspense>`. A route's first paint is server-rendered HTML and CSS only. Task 8 enforces this with a bundle-size assertion.
- **One canvas for the whole site.** Mounted in the root layout, never unmounted across navigation. Sections use drei `<View track={ref}>`; no component creates its own `<Canvas>`.
- Renderer is `WebGPURenderer` from `three/webgpu`, which falls back to WebGL2 automatically. Materials are authored in **TSL**, never hand-written GLSL — TSL compiles to both backends from one source.
- **Performance budgets, enforced by Lighthouse CI as hard failures:** LCP ≤ **2.0s**, INP ≤ **200ms**, CLS ≤ **0.05**, on throttled mid-tier mobile.
- **Reduced tier** activates when `matchMedia('(pointer: coarse)')` matches or `navigator.deviceMemory < 4`: poster images instead of video textures, no post-processing, DPR capped at 1.
- `prefers-reduced-motion: reduce` renders every scene static — no scroll scrubbing, no idle animation.
- Never more than **two** video textures decoding at once. Offscreen textures pause via `IntersectionObserver`.
- Do not restructure the DOM Plan 2 built. WebGL attaches to existing `data-section` anchors. If a scene needs a DOM change, it is a wrapper, not a rewrite.
- The Blade site keeps serving production until Task 10. Do not delete anything under `resources/` before then.

---

### Task 1: Canvas foundation

The persistent canvas, the renderer with its fallback, the device-tier detection, and the `<Scene>` wrapper every later task uses.

**Files:**
- Create: `web/src/webgl/Canvas.tsx`
- Create: `web/src/webgl/Scene.tsx`
- Create: `web/src/webgl/useDeviceTier.ts`
- Modify: `web/src/app/layout.tsx`
- Test: `web/tests/e2e/webgl-foundation.spec.ts`

**Interfaces:**
- Consumes: the `data-section` anchors from Plan 2 Tasks 5–7.
- Produces:
  - `<WebGLCanvas />` — client, mounted once in the root layout. Renders `null` until first paint completes.
  - `<Scene section="hero">{children}</Scene>` — binds a drei `<View>` to `[data-section="hero"]`, dynamically imports its children.
  - `useDeviceTier(): 'full' | 'reduced' | 'off'` — `off` under `prefers-reduced-motion`, `reduced` on coarse pointer or low memory, `full` otherwise.
  - `window.__webglTier` — the resolved tier, exposed for Playwright assertions only.

- [ ] **Step 1: Write the failing test**

Create `web/tests/e2e/webgl-foundation.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('a single canvas mounts and persists across navigation', async ({ page }) => {
  await page.goto('/')
  await expect(page.locator('canvas')).toHaveCount(1)

  const id = await page.evaluate(() => {
    const c = document.querySelector('canvas') as HTMLCanvasElement & { __id?: string }
    c.__id = 'marked'
    return c.__id
  })
  expect(id).toBe('marked')

  await page.getByRole('link', { name: /portfolio/i }).first().click()
  await page.waitForURL('**/portfolio')

  // Same element survived the route change — the canvas was not remounted.
  const stillMarked = await page.evaluate(
    () => (document.querySelector('canvas') as HTMLCanvasElement & { __id?: string }).__id
  )
  expect(stillMarked).toBe('marked')
  await expect(page.locator('canvas')).toHaveCount(1)
})

test('resolves the full tier on desktop', async ({ page }) => {
  await page.goto('/')
  await page.waitForFunction(() => (window as never as { __webglTier?: string }).__webglTier !== undefined)
  expect(await page.evaluate(() => (window as never as { __webglTier: string }).__webglTier)).toBe('full')
})

test('resolves the reduced tier on a coarse pointer', async ({ browser }) => {
  const context = await browser.newContext({ ...({ hasTouch: true, isMobile: true } as const) })
  const page = await context.newPage()
  await page.goto('/')
  await page.waitForFunction(() => (window as never as { __webglTier?: string }).__webglTier !== undefined)
  expect(await page.evaluate(() => (window as never as { __webglTier: string }).__webglTier)).toBe('reduced')
  await context.close()
})

test('resolves the off tier under reduced motion', async ({ browser }) => {
  const context = await browser.newContext({ reducedMotion: 'reduce' })
  const page = await context.newPage()
  await page.goto('/')
  await page.waitForFunction(() => (window as never as { __webglTier?: string }).__webglTier !== undefined)
  expect(await page.evaluate(() => (window as never as { __webglTier: string }).__webglTier)).toBe('off')
  await context.close()
})

test('page content is server-rendered without the canvas', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false })
  const page = await context.newPage()
  await page.goto('/')

  // Every word of content must be present with JS off. The canvas is decoration.
  await expect(page.locator('h1')).toBeVisible()
  await expect(page.locator('[data-section="hero"]')).toBeVisible()
  await expect(page.locator('canvas')).toHaveCount(0)
  await context.close()
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd web && npm run test:e2e -- webgl-foundation`
Expected: FAIL — no canvas exists.

- [ ] **Step 3: Install the 3D dependencies**

```bash
cd web && npm install three @react-three/fiber @react-three/drei
npm install -D @types/three
```

Verify `three` is at r171 or later — `npm ls three` must report `>= 0.171.0`. WebGPU zero-config imports (`three/webgpu`) landed in r171; earlier versions require an addons path that this plan does not use.

- [ ] **Step 4: Implement device tier detection**

Create `web/src/webgl/useDeviceTier.ts`:

```ts
'use client' // reads matchMedia and navigator

import { useEffect, useState } from 'react'

export type DeviceTier = 'full' | 'reduced' | 'off'

/**
 * How much WebGL this device gets.
 *
 * - `off`   — the user asked for reduced motion. Scenes render one static frame.
 * - `reduced` — coarse pointer or under 4GB. Poster images instead of video
 *   textures, no post-processing, DPR capped at 1.
 * - `full`  — everything.
 *
 * Resolves after mount, so the server and the first client render agree on
 * `off` and no hydration mismatch is possible.
 */
export function useDeviceTier(): DeviceTier {
  const [tier, setTier] = useState<DeviceTier>('off')

  useEffect(() => {
    const resolve = (): DeviceTier => {
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return 'off'

      const coarse = window.matchMedia('(pointer: coarse)').matches
      const memory = (navigator as Navigator & { deviceMemory?: number }).deviceMemory
      if (coarse || (memory !== undefined && memory < 4)) return 'reduced'

      return 'full'
    }

    const apply = () => {
      const next = resolve()
      setTier(next)
      // Exposed for Playwright assertions only. Nothing in the app reads it.
      ;(window as never as { __webglTier: DeviceTier }).__webglTier = next
    }

    apply()

    const motion = window.matchMedia('(prefers-reduced-motion: reduce)')
    motion.addEventListener('change', apply)
    return () => motion.removeEventListener('change', apply)
  }, [])

  return tier
}
```

- [ ] **Step 5: Implement the canvas**

Create `web/src/webgl/Canvas.tsx`:

```tsx
'use client' // owns the WebGL context and a rAF loop

import { Canvas } from '@react-three/fiber'
import { View, Preload } from '@react-three/drei'
import { useEffect, useState } from 'react'
import { WebGPURenderer } from 'three/webgpu'
import { useDeviceTier } from './useDeviceTier'

/**
 * The site's single WebGL context. Mounted once in the root layout and never
 * unmounted, so navigating between routes reuses the same context, the same
 * compiled shaders, and the same GPU-resident textures.
 *
 * `eventSource={document.body}` lets pointer events reach meshes inside
 * scissored views even though the canvas itself sits behind the DOM.
 *
 * Mounting is deferred to after first paint: the canvas must never be on the
 * critical path to LCP.
 */
export function WebGLCanvas() {
  const tier = useDeviceTier()
  const [painted, setPainted] = useState(false)

  useEffect(() => {
    // Two frames after mount, the DOM content has painted and we can afford
    // to start compiling shaders.
    const id = requestAnimationFrame(() => requestAnimationFrame(() => setPainted(true)))
    return () => cancelAnimationFrame(id)
  }, [])

  if (!painted || tier === 'off') return null

  return (
    <Canvas
      eventSource={typeof document !== 'undefined' ? document.body : undefined}
      eventPrefix="client"
      dpr={tier === 'reduced' ? 1 : [1, 2]}
      gl={(props) => {
        // WebGPU with automatic WebGL2 fallback. Production-ready since r171
        // and supported on Chrome, Edge, Firefox, and Safari 26 incl. iOS.
        const renderer = new WebGPURenderer({ ...(props as object), antialias: tier === 'full' })
        return renderer.init().then(() => renderer)
      }}
      style={{
        position: 'fixed',
        inset: 0,
        pointerEvents: 'none',
        zIndex: 0,
      }}
      frameloop="demand"
    >
      <View.Port />
      <Preload all />
    </Canvas>
  )
}
```

If `WebGPURenderer` construction throws on the target browser, R3F falls back only if the promise rejects cleanly — wrap the `init()` in a `.catch()` that constructs a `WebGLRenderer` instead, and log which path was taken so Task 8's fallback test can assert it.

- [ ] **Step 6: Implement the Scene wrapper**

Create `web/src/webgl/Scene.tsx`:

```tsx
'use client' // binds a View to a DOM rect

import { View } from '@react-three/drei'
import { Suspense, useEffect, useRef, useState, type ReactNode } from 'react'

/**
 * Binds a WebGL view to a DOM section. drei's View uses gl.scissor to cut the
 * shared canvas down to the tracked element's rect and follows it through
 * scroll and resize — which is what lets real SSR'd HTML and WebGL coexist
 * without a second context.
 *
 * Children mount only once the section is near the viewport, so a scene three
 * screens down never costs anything on first load.
 */
export function Scene({ section, children }: { section: string; children: ReactNode }) {
  const ref = useRef<HTMLDivElement>(null)
  const [near, setNear] = useState(false)

  useEffect(() => {
    const target = document.querySelector(`[data-section="${section}"]`)
    if (!target) return

    const observer = new IntersectionObserver(
      ([entry]) => entry.isIntersecting && setNear(true),
      { rootMargin: '200% 0px' }
    )
    observer.observe(target)
    return () => observer.disconnect()
  }, [section])

  return (
    <div ref={ref} data-scene={section} style={{ position: 'absolute', inset: 0 }}>
      {near && (
        <View track={ref as React.RefObject<HTMLElement>}>
          <Suspense fallback={null}>{children}</Suspense>
        </View>
      )}
    </div>
  )
}
```

- [ ] **Step 7: Mount the canvas in the layout**

In `web/src/app/layout.tsx`, add a dynamic import and render it inside `<SmoothScroll>`, before `<Nav>`:

```tsx
import dynamic from 'next/dynamic'

const WebGLCanvas = dynamic(
  () => import('@/webgl/Canvas').then((m) => m.WebGLCanvas),
  { ssr: false } // WebGL has no server rendering; ssr:false keeps it out of the RSC payload
)
```

- [ ] **Step 8: Run test to verify it passes**

Run: `cd web && npm run test:e2e -- webgl-foundation`
Expected: PASS, 10 tests.

- [ ] **Step 9: Commit**

```bash
git add web/src/webgl web/src/app/layout.tsx web/tests/e2e/webgl-foundation.spec.ts web/package.json web/package-lock.json
git commit -m "feat(webgl): add persistent canvas with WebGPU renderer and device tiers"
```

---

### Task 2: Video texture manager

The shared machinery behind moments 1 and 2. Getting the decode budget right once means neither scene has to think about it.

**Files:**
- Create: `web/src/webgl/useVideoTexture.ts`
- Create: `web/src/webgl/videoBudget.ts`
- Test: `web/tests/e2e/video-budget.spec.ts`

**Interfaces:**
- Consumes: `useDeviceTier` (Task 1).
- Produces:
  - `useSceneVideoTexture(src, poster, opts): {texture, isVideo}` — returns a `VideoTexture` on the full tier, a poster `Texture` on the reduced tier.
  - `videoBudget.acquire(id): boolean` / `videoBudget.release(id)` — at most 2 concurrent decodes, LRU eviction.
  - `window.__activeVideoTextures` — count, exposed for Playwright assertions only.

- [ ] **Step 1: Write the failing test**

Create `web/tests/e2e/video-budget.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('never decodes more than two videos at once', async ({ page }) => {
  await page.goto('/portfolio')
  await page.waitForFunction(() => (window as never as { __activeVideoTextures?: number }).__activeVideoTextures !== undefined)

  // Scroll the full grid; every tile wants a video texture.
  for (let i = 0; i < 10; i++) {
    await page.mouse.wheel(0, 800)
    await page.waitForTimeout(200)

    const active = await page.evaluate(
      () => (window as never as { __activeVideoTextures: number }).__activeVideoTextures
    )
    expect(active, `after scroll step ${i}`).toBeLessThanOrEqual(2)
  }
})

test('pauses videos that scroll out of view', async ({ page }) => {
  await page.goto('/portfolio')
  await page.waitForTimeout(1000)

  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight))
  await page.waitForTimeout(1000)

  const playingOffscreen = await page.evaluate(() =>
    Array.from(document.querySelectorAll('video')).filter((v) => {
      const rect = v.getBoundingClientRect()
      const offscreen = rect.bottom < 0 || rect.top > window.innerHeight
      return offscreen && !v.paused
    }).length
  )

  expect(playingOffscreen).toBe(0)
})

test('uses poster images instead of video on the reduced tier', async ({ browser }) => {
  const context = await browser.newContext({ hasTouch: true, isMobile: true })
  const page = await context.newPage()
  await page.goto('/portfolio')
  await page.waitForTimeout(1500)

  const active = await page.evaluate(
    () => (window as never as { __activeVideoTextures?: number }).__activeVideoTextures ?? 0
  )
  expect(active).toBe(0)
  await context.close()
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd web && npm run test:e2e -- video-budget`
Expected: FAIL — `__activeVideoTextures` is never defined.

- [ ] **Step 3: Implement the budget**

Create `web/src/webgl/videoBudget.ts`:

```ts
/**
 * At most two videos decode at once, site-wide.
 *
 * This is the single largest lever on jank in a video-heavy WebGL site: each
 * decode is a separate hardware pipeline, and a phone that handles two
 * smoothly will drop frames on four. LRU eviction means the tiles nearest the
 * viewport keep their streams.
 */
const MAX_CONCURRENT = 2

const active = new Map<string, number>() // id -> last touched timestamp counter
let tick = 0

function publish() {
  if (typeof window !== 'undefined') {
    ;(window as never as { __activeVideoTextures: number }).__activeVideoTextures = active.size
  }
}

/** Returns true when this id may decode. Evicts the least recently touched. */
export function acquire(id: string): boolean {
  if (active.has(id)) {
    active.set(id, ++tick)
    return true
  }

  if (active.size >= MAX_CONCURRENT) {
    let oldest: string | null = null
    let oldestTick = Infinity

    for (const [key, value] of active) {
      if (value < oldestTick) {
        oldestTick = value
        oldest = key
      }
    }

    if (oldest === null) return false
    active.delete(oldest)
    evictionListeners.get(oldest)?.()
  }

  active.set(id, ++tick)
  publish()
  return true
}

export function release(id: string): void {
  active.delete(id)
  evictionListeners.delete(id)
  publish()
}

const evictionListeners = new Map<string, () => void>()

/** Called when this id loses its slot to a nearer tile. */
export function onEvicted(id: string, callback: () => void): void {
  evictionListeners.set(id, callback)
}

export function reset(): void {
  active.clear()
  evictionListeners.clear()
  publish()
}
```

- [ ] **Step 4: Implement the texture hook**

Create `web/src/webgl/useVideoTexture.ts`. Requirements:

- On tier `reduced` or `off`, load the poster as a plain `Texture` and never create a `<video>` element at all. Return `isVideo: false`.
- On tier `full`, create a `<video>` with `muted playsInline loop preload="none" crossOrigin="anonymous"`, wrap it in a `VideoTexture`, and gate `play()` on `videoBudget.acquire(id)`.
- Use an `IntersectionObserver` on the tracked DOM section: entering calls `acquire` then `play()`, leaving calls `pause()` then `release`.
- Register `onEvicted(id, () => video.pause())` so losing a slot actually stops the decode.
- On unmount: pause, `release(id)`, `video.removeAttribute('src')`, `video.load()`, and `texture.dispose()`. Skipping the `removeAttribute` + `load` pair leaves the decoder running on some browsers even after the element is detached.
- Publish `window.__activeVideoTextures` through `videoBudget` only — never write it from the hook.

- [ ] **Step 5: Run test to verify it passes**

Run: `cd web && npm run test:e2e -- video-budget`
Expected: FAIL still — nothing consumes the hook yet. This test goes green in Task 4. Note that here and move on; do not weaken the test to make it pass early.

- [ ] **Step 6: Commit**

```bash
git add web/src/webgl/videoBudget.ts web/src/webgl/useVideoTexture.ts web/tests/e2e/video-budget.spec.ts
git commit -m "feat(webgl): add video texture budget with LRU eviction"
```

---

### Task 3: Moment 1 — hero

Hero slide videos as `VideoTexture` on a subdivided plane, displaced by scroll velocity and pointer, lit with the brand red.

**Files:**
- Create: `web/src/webgl/scenes/HeroScene.tsx`
- Create: `web/src/webgl/materials/displacedVideo.ts`
- Create: `web/src/webgl/useScrollVelocity.ts`
- Modify: `web/src/components/home/Hero.tsx`
- Test: `web/tests/e2e/hero-scene.spec.ts`

**Interfaces:**
- Consumes: `Scene` (Task 1), `useSceneVideoTexture` (Task 2), `HeroSlide[]` from Plan 2.
- Produces:
  - `useScrollVelocity(): MutableRefObject<number>` — normalized −1..1, read in `useFrame`, never in React state.
  - `displacedVideoMaterial(opts)` — TSL node material.

- [ ] **Step 1: Write the failing test**

Create `web/tests/e2e/hero-scene.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('the hero renders its DOM copy regardless of WebGL', async ({ page }) => {
  await page.goto('/')
  // The heading is DOM text, not a mesh — it must be selectable and readable.
  await expect(page.locator('[data-section="hero"] h1')).toBeVisible()
})

test('a webgl view attaches to the hero section', async ({ page }) => {
  await page.goto('/')
  await expect(page.locator('[data-scene="hero"]')).toBeAttached()
})

test('the hero poster is the LCP element, not the canvas', async ({ page }) => {
  await page.goto('/')

  const lcpTag = await page.evaluate(
    () =>
      new Promise<string>((resolve) => {
        new PerformanceObserver((list) => {
          const entries = list.getEntries()
          const last = entries[entries.length - 1] as PerformanceEntry & { element?: Element }
          resolve(last.element?.tagName ?? 'none')
        }).observe({ type: 'largest-contentful-paint', buffered: true })
        setTimeout(() => resolve('timeout'), 5000)
      })
  )

  expect(lcpTag).not.toBe('CANVAS')
})

test('the hero renders one static frame under reduced motion', async ({ browser }) => {
  const context = await browser.newContext({ reducedMotion: 'reduce' })
  const page = await context.newPage()
  await page.goto('/')
  await page.waitForTimeout(500)

  const before = await page.locator('[data-section="hero"]').screenshot()
  await page.mouse.wheel(0, 300)
  await page.waitForTimeout(600)
  const after = await page.locator('[data-section="hero"]').screenshot()

  expect(Buffer.compare(before, after)).toBe(0)
  await context.close()
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd web && npm run test:e2e -- hero-scene`
Expected: FAIL — no `[data-scene="hero"]`.

- [ ] **Step 3: Implement scroll velocity**

Create `web/src/webgl/useScrollVelocity.ts`. It must return a **ref**, not state — writing scroll velocity into React state re-renders on every frame and is the most common way a WebGL site ends up at 20fps. Read `window.scrollY` in a passive listener, difference it against the previous frame, damp toward zero, and clamp to −1..1.

- [ ] **Step 4: Implement the TSL material**

Create `web/src/webgl/materials/displacedVideo.ts`. Requirements:

- Authored in TSL (`import { ... } from 'three/tsl'`), never hand-written GLSL. TSL compiles to both WebGPU and WebGL2 from one source; GLSL would mean maintaining two.
- Vertex displacement is a function of the video texture's luminance, a scroll-velocity uniform, and a pointer-position uniform.
- Fragment stage samples the video texture and adds a rim term in `#e80f03` — read the value from the `--red` CSS custom property at material construction rather than hardcoding it, so the token stays the single source of truth.
- Expose uniforms `uVelocity`, `uPointer`, `uTime`, and `uRimColor`.

- [ ] **Step 5: Implement the scene and wire the section**

Create `web/src/webgl/scenes/HeroScene.tsx` rendering a `<planeGeometry args={[w, h, 64, 64]} />` with the material, driven in `useFrame` from the velocity ref.

In `web/src/components/home/Hero.tsx`, keep every existing DOM element exactly as Plan 2 built it, and add the dynamically imported scene as a sibling:

```tsx
const HeroScene = dynamic(() => import('@/webgl/scenes/HeroScene').then((m) => m.HeroScene), {
  ssr: false,
})
```

Render `<Scene section="hero"><HeroScene slides={slides} /></Scene>` alongside the existing markup. The DOM `<h1>` and the poster `<img>` stay — the poster is the LCP element and the canvas paints over it once ready.

- [ ] **Step 6: Run test to verify it passes**

Run: `cd web && npm run test:e2e -- hero-scene`
Expected: PASS, 8 tests.

- [ ] **Step 7: Commit**

```bash
git add web/src/webgl web/src/components/home/Hero.tsx web/tests/e2e/hero-scene.spec.ts
git commit -m "feat(webgl): add displaced video hero scene"
```

---

### Task 4: Moment 2 — curved portfolio gallery

The centerpiece. A cylindrical plane gallery where each work's preview video is a texture that distorts on scroll velocity and bulges on hover.

**Files:**
- Create: `web/src/webgl/scenes/GalleryScene.tsx`
- Create: `web/src/webgl/materials/curvedTile.ts`
- Modify: `web/src/components/work/WorkGrid.tsx`
- Test: `web/tests/e2e/gallery-scene.spec.ts`

**Interfaces:**
- Consumes: `Scene` (Task 1), `useSceneVideoTexture` + `videoBudget` (Task 2), `useScrollVelocity` (Task 3), `Work[]` from Plan 2.
- Produces: `GalleryScene` bound to `[data-section="work-grid"]`.

- [ ] **Step 1: Write the failing test**

Create `web/tests/e2e/gallery-scene.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('every work tile stays a real DOM link target', async ({ page, request }) => {
  const body = await (await request.get(`${process.env.API_BASE_URL}/api/v1/works`)).json()

  await page.goto('/portfolio')
  // WebGL is decoration. The tiles, their titles, and the lightbox trigger
  // must all still exist in the DOM for crawlers and keyboard users.
  await expect(page.locator('[data-work-tile]')).toHaveCount(body.data.length)
})

test('tile titles are readable text, not textures', async ({ page, request }) => {
  const body = await (await request.get(`${process.env.API_BASE_URL}/api/v1/works`)).json()
  await page.goto('/portfolio')
  await expect(page.locator('[data-work-tile]').first()).toContainText(body.data[0].title)
})

test('a webgl view attaches to the work grid', async ({ page }) => {
  await page.goto('/portfolio')
  await expect(page.locator('[data-scene="work-grid"]')).toBeAttached()
})

test('the grid is keyboard navigable with WebGL active', async ({ page }) => {
  await page.goto('/portfolio')
  await page.waitForTimeout(800)

  await page.keyboard.press('Tab')
  await page.keyboard.press('Tab')

  const focused = await page.evaluate(() => document.activeElement?.tagName)
  expect(['A', 'BUTTON']).toContain(focused)
})

test('holds 30fps or better while scrolling the grid', async ({ page }) => {
  await page.goto('/portfolio')
  await page.waitForTimeout(1200)

  const fps = await page.evaluate(
    () =>
      new Promise<number>((resolve) => {
        let frames = 0
        const start = performance.now()
        const count = () => {
          frames++
          if (performance.now() - start < 2000) requestAnimationFrame(count)
          else resolve(frames / 2)
        }
        requestAnimationFrame(count)
        const scroll = setInterval(() => window.scrollBy(0, 40), 16)
        setTimeout(() => clearInterval(scroll), 2000)
      })
  )

  expect(fps).toBeGreaterThanOrEqual(30)
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd web && npm run test:e2e -- gallery-scene`
Expected: FAIL — no `[data-scene="work-grid"]`.

- [ ] **Step 3: Implement the curved tile material**

Create `web/src/webgl/materials/curvedTile.ts` in TSL. Requirements:

- Vertex stage bends each plane around a cylinder — `z = cos(x * curvature) * radius` — with `uCurvature` as a uniform so the effect can be dialed per breakpoint.
- Scroll velocity adds a sine-wave stretch along the plane's Y axis. Codrops' infinite circular gallery and its "distortion and grain on scroll" tutorial are the reference implementations; read them before writing the shader rather than deriving it.
- A `uHover` uniform in 0..1 drives a bulge centered on `uPointerLocal`.
- Fragment stage samples the video-or-poster texture with the same UV displacement, so the image and the geometry distort together rather than sliding against each other.

- [ ] **Step 4: Implement the scene**

Create `web/src/webgl/scenes/GalleryScene.tsx`. Requirements:

- One instanced or individually-meshed plane per work, positioned to sit exactly over its `[data-work-tile]` rect. Read the rects once on mount and on resize, not per frame.
- Each tile calls `useSceneVideoTexture(work.preview_video_url, work.cover, {id: work.slug})`. The budget from Task 2 does the throttling — this scene must not implement its own.
- Hover is raycast against the meshes and animated with GSAP into `uHover`, not by setting the uniform directly, so it eases rather than snapping.
- The DOM tiles get `opacity: 0` **only on the full tier and only after the scene reports ready**. On reduced, off, or any error path, the DOM tiles stay fully visible — a failed scene must degrade to the working Plan 2 grid, never to a blank page.

- [ ] **Step 5: Run both test suites**

Run: `cd web && npm run test:e2e -- gallery-scene video-budget`
Expected: PASS. The `video-budget` spec from Task 2 goes green here — this is its first real consumer.

- [ ] **Step 6: Commit**

```bash
git add web/src/webgl web/src/components/work/WorkGrid.tsx web/tests/e2e/gallery-scene.spec.ts
git commit -m "feat(webgl): add curved plane portfolio gallery with video textures"
```

---

### Task 5: Moments 3, 4, 5 — services depth, cursor, post-processing

**Files:**
- Create: `web/src/webgl/scenes/ServicesScene.tsx`
- Create: `web/src/webgl/scenes/CursorScene.tsx`
- Create: `web/src/webgl/PostProcessing.tsx`
- Create: `web/src/webgl/materials/grain.ts`
- Modify: `web/src/components/chrome/Cursor.tsx`, `web/src/webgl/Canvas.tsx`
- Test: `web/tests/e2e/services-scene.spec.ts`
- Test: `web/tests/e2e/post-processing.spec.ts`

**Interfaces:**
- Consumes: `Scene`, `useDeviceTier` (Task 1), `useScrollVelocity` (Task 3).
- Produces:
  - `ServicesScene` — scroll-scrubbed camera through one depth layer per service, bound to `[data-section="pillars"]`.
  - `CursorScene` — replaces the CSS cursor's visual on the full tier only.
  - `<PostProcessing />` — grain, chromatic aberration, vignette. Full tier only.

- [ ] **Step 1: Write the failing tests**

Create `web/tests/e2e/services-scene.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('service copy stays DOM text through the scrub', async ({ page, request }) => {
  const { data } = await (await request.get(`${process.env.API_BASE_URL}/api/v1/services`)).json()
  await page.goto(`/services/${data[0].slug}`)

  await expect(page.locator('[data-section="pillars"]')).toContainText(data[0].pillars[0].title)
})

test('scrubbing does not hijack the scrollbar position', async ({ page, request }) => {
  const { data } = await (await request.get(`${process.env.API_BASE_URL}/api/v1/services`)).json()
  await page.goto(`/services/${data[0].slug}`)
  await page.waitForTimeout(800)

  const before = await page.evaluate(() => window.scrollY)
  await page.mouse.wheel(0, 600)
  await page.waitForTimeout(500)
  const after = await page.evaluate(() => window.scrollY)

  // The hero scroll lock was deliberately removed — see plans/002.
  // A scrub must never pin the page.
  expect(after).toBeGreaterThan(before)
})

test('reaches the footer by scrolling', async ({ page, request }) => {
  const { data } = await (await request.get(`${process.env.API_BASE_URL}/api/v1/services`)).json()
  await page.goto(`/services/${data[0].slug}`)

  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight))
  await page.waitForTimeout(800)
  await expect(page.locator('footer')).toBeInViewport()
})
```

Create `web/tests/e2e/post-processing.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('post-processing is off on the reduced tier', async ({ browser }) => {
  const context = await browser.newContext({ hasTouch: true, isMobile: true })
  const page = await context.newPage()
  await page.goto('/')
  await page.waitForTimeout(1200)

  expect(
    await page.evaluate(() => (window as never as { __postProcessing?: boolean }).__postProcessing ?? false)
  ).toBe(false)
  await context.close()
})

test('post-processing is on for the full tier', async ({ page }) => {
  await page.goto('/')
  await page.waitForTimeout(1200)
  expect(await page.evaluate(() => (window as never as { __postProcessing: boolean }).__postProcessing)).toBe(true)
})

test('text contrast survives the grain pass', async ({ page }) => {
  await page.goto('/')
  await page.waitForTimeout(1200)

  // Grain over text is the fastest way to fail WCAG without noticing.
  // The pass must sit behind DOM content, never over it.
  const canvasZ = await page.evaluate(() => getComputedStyle(document.querySelector('canvas')!).zIndex)
  const mainZ = await page.evaluate(() => getComputedStyle(document.querySelector('main')!).zIndex)
  expect(Number(canvasZ)).toBeLessThan(Number(mainZ) || 1)
})
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `cd web && npm run test:e2e -- services-scene post-processing`
Expected: FAIL.

- [ ] **Step 3: Implement ServicesScene**

Scroll-scrubbed camera moving through depth layers, one per pillar. Use GSAP ScrollTrigger with `scrub: 1` writing into a ref that `useFrame` reads. Do **not** use `pin: true` — the hero scroll lock was deliberately removed in `plans/002-remove-hero-scroll-lock.md` and pinning reintroduces the same problem.

Particle-dispersion on the display type between sections is the Shopify Editions technique: render the section heading to an offscreen canvas, sample it into a point cloud, and disperse on scroll. The DOM heading stays in place and visible — the particles are an overlay.

- [ ] **Step 4: Implement CursorScene and post-processing**

- `CursorScene` renders on the **full tier only**. `Cursor.tsx` from Plan 2 keeps its CSS-cursor behavior as the base layer; the WebGL version adds the trail and magnetic snap on top. Coarse pointers still mount neither.
- `PostProcessing` composes grain, subtle chromatic aberration, and vignette in TSL. Set `window.__postProcessing = true` when the pass is active, for the test above.
- Chromatic aberration must be **subtle** — measure text contrast after enabling it, and if any body text drops below 4.5:1 against `--ink`, reduce the offset until it does not.

```bash
cd web && npm install @react-three/postprocessing
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `cd web && npm run test:e2e -- services-scene post-processing`
Expected: PASS, 12 tests.

- [ ] **Step 6: Commit**

```bash
git add web/src/webgl web/src/components/chrome/Cursor.tsx web/tests/e2e/services-scene.spec.ts web/tests/e2e/post-processing.spec.ts web/package.json web/package-lock.json
git commit -m "feat(webgl): add services depth scrub, webgl cursor and post-processing"
```

---

### Task 6: Page transitions

A shader wipe over the persistent canvas, replacing the default route change.

**Files:**
- Create: `web/src/webgl/Transition.tsx`
- Create: `web/src/webgl/materials/wipe.ts`
- Modify: `web/src/app/layout.tsx`
- Test: `web/tests/e2e/transitions.spec.ts`

**Interfaces:**
- Consumes: `useDeviceTier` (Task 1).
- Produces: `<Transition />` — listens to `usePathname()` and runs the wipe on change.

- [ ] **Step 1: Write the failing test**

Create `web/tests/e2e/transitions.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('navigation completes and content is readable after the wipe', async ({ page }) => {
  await page.goto('/')
  await page.getByRole('link', { name: /portfolio/i }).first().click()

  await expect(page).toHaveURL(/\/portfolio/)
  await expect(page.locator('[data-work-tile]').first()).toBeVisible({ timeout: 3000 })
})

test('the wipe never blocks interaction for more than a second', async ({ page }) => {
  await page.goto('/')

  const start = Date.now()
  await page.getByRole('link', { name: /blog/i }).first().click()
  await page.locator('[data-post-card]').first().waitFor({ state: 'visible' })

  expect(Date.now() - start).toBeLessThan(3000)
})

test('back navigation works through the transition', async ({ page }) => {
  await page.goto('/')
  await page.getByRole('link', { name: /portfolio/i }).first().click()
  await expect(page).toHaveURL(/\/portfolio/)

  await page.goBack()
  await expect(page).toHaveURL(/\/$/)
  await expect(page.locator('[data-section="hero"]')).toBeVisible()
})

test('no transition under reduced motion', async ({ browser }) => {
  const context = await browser.newContext({ reducedMotion: 'reduce' })
  const page = await context.newPage()
  await page.goto('/')
  await page.getByRole('link', { name: /portfolio/i }).first().click()

  await expect(page.locator('[data-work-tile]').first()).toBeVisible({ timeout: 1500 })
  await context.close()
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd web && npm run test:e2e -- transitions`
Expected: the first three may pass with default navigation. That is fine — they are regression guards. Implement the wipe and keep them green.

- [ ] **Step 3: Implement the wipe**

Requirements:

- A full-viewport `<View>` on its own layer, above the section views.
- The wipe covers, the route swaps, the wipe uncovers. Total duration under 900ms — `plans/001-unblock-navigation-curtain-preloader.md` documents that a curtain which outlasts the navigation makes the site feel broken, not premium.
- On tier `off`, render nothing and let navigation happen instantly.
- The wipe element is `pointer-events: none` at every moment. A transition that eats a click is worse than no transition.

- [ ] **Step 4: Run test to verify it passes**

Run: `cd web && npm run test:e2e -- transitions`
Expected: PASS, 8 tests.

- [ ] **Step 5: Commit**

```bash
git add web/src/webgl/Transition.tsx web/src/webgl/materials/wipe.ts web/src/app/layout.tsx web/tests/e2e/transitions.spec.ts
git commit -m "feat(webgl): add shader wipe page transitions"
```

---

### Task 7: Port the motion specification

The 15 plans in `plans/` become the motion system rather than being discarded.

**Files:**
- Create: `web/src/styles/motion.css`
- Create: `web/src/lib/motion.ts`
- Modify: every component with an animation
- Create: `docs/motion-spec.md`
- Test: `web/tests/e2e/motion.spec.ts`

**Interfaces:**
- Consumes: `plans/001` through `plans/015`.
- Produces:
  - Duration and easing tokens in `motion.css`, consolidated per `plans/011`.
  - `useGsapContext(scope)` — wraps `useGSAP` so every animation is scoped and auto-reverted.
  - `docs/motion-spec.md` — the ported rules, so `plans/` can be archived.

- [ ] **Step 1: Read all fifteen plans**

Read every file in `plans/`. They are short. Extract the rule each one establishes into a table: what it mandates, and which component it applies to. That table is the skeleton of `docs/motion-spec.md`.

- [ ] **Step 2: Write the failing test**

Create `web/tests/e2e/motion.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('duration and easing tokens are defined once', async ({ page }) => {
  await page.goto('/')

  const tokens = await page.evaluate(() => {
    const s = getComputedStyle(document.documentElement)
    return {
      fast: s.getPropertyValue('--dur-fast').trim(),
      base: s.getPropertyValue('--dur-base').trim(),
      slow: s.getPropertyValue('--dur-slow').trim(),
      ease: s.getPropertyValue('--ease').trim(),
    }
  })

  expect(tokens.fast).not.toBe('')
  expect(tokens.base).not.toBe('')
  expect(tokens.slow).not.toBe('')
  expect(tokens.ease).toBe('cubic-bezier(0.16, 1, 0.3, 1)')
})

test('no element is left with a permanent will-change', async ({ page }) => {
  await page.goto('/')
  await page.evaluate(() => window.scrollTo(0, 1200))
  await page.waitForTimeout(1500)

  // plans/007: will-change held forever forces a layer per element and
  // exhausts GPU memory on long pages.
  const stuck = await page.evaluate(
    () =>
      Array.from(document.querySelectorAll('*')).filter(
        (el) => getComputedStyle(el).willChange !== 'auto'
      ).length
  )

  expect(stuck).toBeLessThanOrEqual(3)
})

test('hover states animate transform, not layout properties', async ({ page }) => {
  await page.goto('/portfolio')
  const tile = page.locator('[data-work-tile]').first()

  const before = await tile.boundingBox()
  await tile.hover()
  await page.waitForTimeout(400)
  const after = await tile.boundingBox()

  // plans/005: hovering must not reflow. Transform-only means the layout
  // box is unchanged even as the tile visibly moves.
  expect(after?.width).toBeCloseTo(before?.width ?? 0, 0)
  expect(after?.height).toBeCloseTo(before?.height ?? 0, 0)
})

test('reduced motion is gentler, not zero', async ({ browser }) => {
  const context = await browser.newContext({ reducedMotion: 'reduce' })
  const page = await context.newPage()
  await page.goto('/')

  // plans/008: removing all motion loses the spatial cues that make
  // navigation legible. Transitions shorten; they do not vanish.
  const duration = await page.evaluate(() =>
    getComputedStyle(document.querySelector('a')!).transitionDuration
  )
  expect(duration).not.toBe('0s')
  await context.close()
})

test('collapsibles animate with grid-rows, not max-height', async ({ page, request }) => {
  const { data } = await (await request.get(`${process.env.API_BASE_URL}/api/v1/services`)).json()
  await page.goto(`/services/${data[0].slug}`)

  const faq = page.locator('[data-section="faqs"] details').first()
  test.skip((await faq.count()) === 0, 'no faqs seeded')

  // plans/006: max-height animation eases toward a guessed value, so the
  // motion is wrong whenever the content is shorter than the guess.
  const usesMaxHeight = await faq.evaluate((el) => {
    const inner = el.querySelector('div')
    return inner ? getComputedStyle(inner).maxHeight !== 'none' : false
  })
  expect(usesMaxHeight).toBe(false)
})
```

- [ ] **Step 3: Run test to verify it fails**

Run: `cd web && npm run test:e2e -- motion`
Expected: FAIL — duration tokens undefined.

- [ ] **Step 4: Write the motion tokens**

Create `web/src/styles/motion.css` with `--dur-fast`, `--dur-base`, `--dur-slow`, and the three existing easings. Use the values `plans/011-consolidate-easing-and-duration-tokens.md` settled on. Import it from `globals.css`.

- [ ] **Step 5: Apply each plan's rule**

Work through the table from Step 1 and apply each rule to the components Plan 2 built:

- `plans/004` — entrances ease out, never ease in-out.
- `plans/005` — hover moves via `transform`, never `width`/`height`/`margin`/`top`.
- `plans/006` — collapsibles use `grid-template-rows: 0fr → 1fr`.
- `plans/007` — set `will-change` on animation start, clear it in the completion callback.
- `plans/008` — reduced motion shortens durations to ~150ms; it does not set them to zero.
- `plans/010` — quote modal entrance timing.
- `plans/012` — no `scale(0)` starts; hero zoom bounds.
- `plans/013` — grid filtering animates items out rather than removing them instantly.
- `plans/014` — lightbox open motion.
- `plans/015` — wizard step continuity.

- [ ] **Step 6: Run test to verify it passes**

Run: `cd web && npm run test:e2e -- motion`
Expected: PASS, 10 tests.

- [ ] **Step 7: Write the motion spec and retire the plans**

Create `docs/motion-spec.md` from the Step 1 table, with a header noting it supersedes `plans/001`–`plans/015` and that those files described the Blade implementation.

Move `plans/` to `docs/archive/blade-motion-plans/` rather than deleting — they record why each rule exists, and that reasoning outlives the code.

- [ ] **Step 8: Commit**

```bash
git add web/src/styles/motion.css web/src/lib/motion.ts web/src/components docs/motion-spec.md docs/archive
git rm -r --cached plans
git commit -m "feat(web): port the motion specification from the Blade plans"
```

---

### Task 8: Performance gate

Makes the budgets real. A PR that regresses LCP fails.

**Files:**
- Create: `web/lighthouserc.json`
- Create: `.github/workflows/frontend.yml`
- Create: `web/tests/e2e/bundle-budget.spec.ts`
- Test: the workflow itself

**Interfaces:**
- Consumes: every route from Plan 2.
- Produces: a CI job that fails on LCP > 2.0s, INP > 200ms, CLS > 0.05, or any WebGL bytes in the initial bundle.

- [ ] **Step 1: Write the failing bundle test**

Create `web/tests/e2e/bundle-budget.spec.ts`:

```ts
import { expect, test } from '@playwright/test'

test('no webgl bytes on the initial load', async ({ page }) => {
  const scripts: string[] = []
  page.on('request', (r) => {
    if (r.resourceType() === 'script') scripts.push(r.url())
  })

  await page.goto('/', { waitUntil: 'domcontentloaded' })

  // three is ~600KB. Shipping it in the initial chunk is the single fastest
  // way to blow the LCP budget on mobile.
  const webglChunks = scripts.filter((u) => /three|fiber|drei|postprocessing/i.test(u))
  expect(webglChunks, `WebGL loaded eagerly: ${webglChunks.join(', ')}`).toEqual([])
})

test('webgl loads after first paint, not before', async ({ page }) => {
  await page.goto('/')
  await page.waitForTimeout(3000)

  // It must eventually arrive — otherwise this test passes on a broken build.
  const loaded = await page.evaluate(() => document.querySelector('canvas') !== null)
  expect(loaded).toBe(true)
})

test('falls back to WebGL2 when WebGPU is unavailable', async ({ page }) => {
  await page.addInitScript(() => {
    // @ts-expect-error deliberately removing the API
    delete navigator.gpu
  })

  await page.goto('/')
  await page.waitForTimeout(2500)
  await expect(page.locator('canvas')).toHaveCount(1)

  const errors: string[] = []
  page.on('console', (m) => m.type() === 'error' && errors.push(m.text()))
  expect(errors.filter((e) => /webgpu|renderer/i.test(e))).toEqual([])
})
```

- [ ] **Step 2: Run the bundle test**

Run: `cd web && npm run build && npm run test:e2e -- bundle-budget`
Expected: it either passes, or fails by naming the chunk that leaked. If it fails, the cause is a static import of a WebGL module somewhere in a server component — find it and make it `next/dynamic` with `ssr: false`.

- [ ] **Step 3: Configure Lighthouse CI**

```bash
cd web && npm install -D @lhci/cli
```

Create `web/lighthouserc.json`:

```json
{
  "ci": {
    "collect": {
      "startServerCommand": "npm run start",
      "url": [
        "http://127.0.0.1:3000/",
        "http://127.0.0.1:3000/portfolio",
        "http://127.0.0.1:3000/services/photography",
        "http://127.0.0.1:3000/blog",
        "http://127.0.0.1:3000/contact"
      ],
      "numberOfRuns": 3,
      "settings": {
        "preset": "desktop",
        "formFactor": "mobile",
        "throttling": {
          "cpuSlowdownMultiplier": 4
        }
      }
    },
    "assert": {
      "assertions": {
        "largest-contentful-paint": ["error", { "maxNumericValue": 2000 }],
        "cumulative-layout-shift": ["error", { "maxNumericValue": 0.05 }],
        "interaction-to-next-paint": ["error", { "maxNumericValue": 200 }],
        "total-blocking-time": ["error", { "maxNumericValue": 300 }],
        "categories:accessibility": ["error", { "minScore": 0.95 }],
        "categories:seo": ["error", { "minScore": 1 }]
      }
    },
    "upload": { "target": "filesystem", "outputDir": "./.lighthouseci" }
  }
}
```

- [ ] **Step 4: Wire the CI workflow**

Create `.github/workflows/frontend.yml` running on pull requests that touch `web/**`: checkout, Node 22, `npm ci`, `npm run typecheck`, `npm run lint`, `npm run build`, `npx lhci autorun`, `npm run test:e2e`.

The job needs the Laravel API reachable. Either start PHP's built-in server against a seeded SQLite database in a prior step, or point `API_BASE_URL` at the staging host with credentials from repository secrets. Pick one and document it in the workflow file's header comment.

- [ ] **Step 5: Run Lighthouse locally and fix what it finds**

Run: `cd web && npm run build && npx lhci autorun`
Expected: budgets met on all five URLs.

If LCP fails, the usual causes in that order: the hero poster is not `priority`, a font is render-blocking, or a WebGL chunk leaked into the initial bundle. If CLS fails, an image is missing explicit dimensions. If INP fails, something is doing layout work in a scroll or pointer handler — find it and move the work into `useFrame` reading a ref.

Do not raise a budget to make the gate pass.

- [ ] **Step 6: Commit**

```bash
git add web/lighthouserc.json .github/workflows/frontend.yml web/tests/e2e/bundle-budget.spec.ts web/package.json web/package-lock.json
git commit -m "ci(web): gate pull requests on core web vitals and bundle budget"
```

---

### Task 9: SEO parity verification

Proves the Next site is at least as good as the Blade site on every SEO-visible attribute, before any traffic moves. This task **blocks cutover**.

**Files:**
- Create: `web/scripts/seo-parity.ts`
- Create: `docs/seo-parity-report.md`
- Test: the script is the test

**Interfaces:**
- Consumes: the live Blade site and the staging Next site.
- Produces: `docs/seo-parity-report.md` — a per-URL diff. Cutover requires zero unexplained differences.

- [ ] **Step 1: Write the parity script**

Create `web/scripts/seo-parity.ts`. For every URL in the route inventory it must fetch both origins and compare:

- HTTP status
- `<title>`
- `<meta name="description">`
- `<link rel="canonical">` (normalizing the host, since the origins differ)
- `<meta name="robots">`
- OG title, description, image
- The `@type` of every JSON-LD block
- The text of the single `<h1>`
- The count of `<h2>` elements

URL inventory: `/`, `/about`, `/portfolio`, `/industries`, `/blog`, `/contact`, `/privacy-policy`, `/terms-of-service`, `/cookie-policy`, `/disclaimer`, `/thank-you`, every `/services/{slug}` from the API, and every `/blog/{slug}` from the API.

It must also request every source in `web/src/redirects.ts` against **both** origins and assert both return 301 to the same destination.

Output a markdown table to `docs/seo-parity-report.md` and exit non-zero if any row differs.

- [ ] **Step 2: Run the script against staging**

```bash
cd web && npx tsx scripts/seo-parity.ts \
  --blade https://thelastclicks.com \
  --next https://next.thelastclicks.com
```

Expected: initially some differences. That is the point of the task.

- [ ] **Step 3: Resolve every difference**

For each row that differs, decide and record which of these applies:

1. **A bug in the Next app** — fix it.
2. **A deliberate improvement** — record the reason in the report.
3. **Missing content in the admin** — add it through Filament, not through code.

Re-run until the script exits zero or every remaining difference is a documented case 2.

- [ ] **Step 4: Verify the sitemap still resolves**

`sitemap.xml` and `robots.txt` stay owned by Laravel, served by nginx from `public/`. Confirm on staging:

```bash
curl -sI https://next.thelastclicks.com/sitemap.xml | head -1   # 200
curl -sI https://next.thelastclicks.com/robots.txt  | head -1   # 200
```

Then confirm every URL the sitemap lists returns 200 on the Next site:

```bash
curl -s https://thelastclicks.com/sitemap.xml \
  | grep -oP '(?<=<loc>)[^<]+' \
  | sed 's|https://thelastclicks.com||' \
  | while read -r path; do
      code=$(curl -so /dev/null -w '%{http_code}' "https://next.thelastclicks.com${path}")
      [ "$code" = "200" ] || echo "FAIL ${code} ${path}"
    done
```

Expected: no output. Any line is a URL that would 404 after cutover.

- [ ] **Step 5: Commit**

```bash
git add web/scripts/seo-parity.ts docs/seo-parity-report.md
git commit -m "test(seo): verify metadata parity between Blade and Next"
```

---

### Task 10: Production cutover

Switch traffic. This is the irreversible-feeling step, so it is built to be reversible.

**Files:**
- Create: `docs/deploy/nginx-production.conf`
- Create: `docs/deploy/cutover-runbook.md`
- Modify: `docs/DEPLOYMENT.md`

**Interfaces:**
- Consumes: everything above.
- Produces: production traffic served by Next, with a 30-second rollback.

**Do not begin this task until every item in its Step 1 gate is confirmed.**

- [ ] **Step 1: Confirm the cutover gate**

Every one of these must be true. Record the evidence for each — a passing test name, a command's output, or a URL you loaded.

- [ ] `docs/seo-parity-report.md` shows zero unexplained differences.
- [ ] `cd web && npm run test:e2e` is fully green against staging.
- [ ] `./bin/php vendor/bin/pest` is fully green.
- [ ] `npx lhci autorun` meets every budget.
- [ ] Every sitemap URL returns 200 on staging (Task 9 Step 4).
- [ ] Editing content in Filament updates staging within 10 seconds.
- [ ] A contact submission on staging creates a Quote and queues both emails.
- [ ] `systemctl restart tlc-web` recovers within 10 seconds.
- [ ] A database backup taken within the last hour (`spatie/laravel-backup` is already configured).

- [ ] **Step 2: Write the production nginx config**

Create `docs/deploy/nginx-production.conf`. It is the current config with `location /` changed to proxy to Node, and explicit locations keeping PHP for everything Laravel still owns:

```nginx
server {
    listen 443 ssl http2;
    server_name thelastclicks.com www.thelastclicks.com;

    root /home/forge/thelastclicks.com/public;
    index index.php;

    # --- Laravel keeps these ---
    location ^~ /api/     { try_files $uri /index.php?$query_string; }
    location ^~ /admin/   { try_files $uri /index.php?$query_string; }
    location ^~ /livewire/ { try_files $uri /index.php?$query_string; }
    location ^~ /storage/ { try_files $uri =404; }

    # Generated weekly by `sitemap:generate` into public/. Serve from disk.
    location = /sitemap.xml { try_files $uri =404; }
    location = /robots.txt  { try_files $uri =404; }
    location = /favicon.ico { try_files $uri =404; access_log off; }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # --- Next.js takes everything else ---
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 60s;
    }

    # Immutable build assets — long cache, never revalidated.
    location /_next/static/ {
        proxy_pass http://127.0.0.1:3000;
        proxy_cache_valid 200 365d;
        add_header Cache-Control "public, max-age=31536000, immutable";
    }
}
```

Verify against the server's **actual** current config before writing this file — the PHP-FPM socket path, the SSL block, and any existing security headers must carry over unchanged. Read the live file first; do not assume it matches this template.

- [ ] **Step 3: Disable the response cache for API routes**

`spatie/laravel-responsecache` is applied per-route in `routes/web.php` and never touches `routes/api.php`, so no change is needed. Confirm rather than assume:

```bash
./bin/php artisan route:list --path=api --json | grep -c cacheResponse
```

Expected: `0`.

- [ ] **Step 4: Deploy the production build**

```bash
cd /home/forge/thelastclicks.com
git pull
cd web
npm ci
npm run build
cp -r .next/static .next/standalone/.next/static
cp -r public .next/standalone/public
sudo systemctl restart tlc-web

# Confirm Node is serving before touching nginx.
curl -sI http://127.0.0.1:3000/ | head -1   # must be 200
```

- [ ] **Step 5: Switch nginx**

```bash
sudo cp /etc/nginx/sites-available/thelastclicks.com \
        /etc/nginx/sites-available/thelastclicks.com.blade-backup

sudo cp /home/forge/thelastclicks.com/docs/deploy/nginx-production.conf \
        /etc/nginx/sites-available/thelastclicks.com

sudo nginx -t && sudo systemctl reload nginx
```

`nginx -t` must pass before the reload. If it fails, nothing has changed yet — fix the config and retry.

- [ ] **Step 6: Verify production immediately**

Within two minutes of the reload, confirm:

- [ ] `https://thelastclicks.com` loads and is the Next site.
- [ ] `https://thelastclicks.com/admin` loads Filament and you can log in.
- [ ] `https://thelastclicks.com/sitemap.xml` returns 200.
- [ ] `https://thelastclicks.com/robots.txt` returns 200 and does **not** contain `Disallow: /`.
- [ ] No response carries `X-Robots-Tag: noindex` — that header belongs to staging only.
- [ ] A redirect resolves: `curl -sI https://thelastclicks.com/our-works | head -1` returns `301`.
- [ ] A contact submission creates a Quote in the admin.
- [ ] `https://thelastclicks.com/storage/...` serves a media file.

**Rollback if any check fails:**

```bash
sudo cp /etc/nginx/sites-available/thelastclicks.com.blade-backup \
        /etc/nginx/sites-available/thelastclicks.com
sudo nginx -t && sudo systemctl reload nginx
```

The Blade site is untouched on disk and comes back immediately.

- [ ] **Step 7: Resubmit to Search Console**

Submit `sitemap.xml` for recrawl. Use URL Inspection on `/`, `/portfolio`, and one service page to confirm Google renders the Next version correctly. Watch Coverage daily for a week — a spike in 404s means a redirect was missed, and Task 9's report is where to look first.

- [ ] **Step 8: Write the runbook and commit**

Create `docs/deploy/cutover-runbook.md` containing the gate checklist, the switch commands, the verification list, and the rollback procedure, all as executable steps. Add a "Frontend cutover" section to `docs/DEPLOYMENT.md` linking to it.

```bash
git add docs/deploy/nginx-production.conf docs/deploy/cutover-runbook.md docs/DEPLOYMENT.md
git commit -m "docs(deploy): add production cutover runbook and nginx config"
```

---

### Task 11: Retire the Blade frontend

Only after production has been stable on Next for **seven days**.

**Files:**
- Delete: `resources/views/` except `emails/` and `filament/`
- Delete: `resources/css/core.css`, `resources/css/pages.css`
- Delete: `resources/js/core.js`, `chrome.js`, `scene.js`, `work-globe.js`, `work-lightbox.js`
- Delete: `app/Http/Controllers/Public/` except what the API still uses
- Delete: `tests/Feature/Public/`
- Modify: `routes/web.php`, `vite.config.js`, `package.json`, `app/Http/Controllers/Api/V1/PageController.php`

**Interfaces:**
- Consumes: a stable production Next site.
- Produces: a Laravel application that serves only the API and the admin.

- [ ] **Step 1: Confirm seven days of stability**

- [ ] Sentry shows no new frontend error classes since cutover.
- [ ] Search Console Coverage shows no increase in 404s.
- [ ] Organic sessions in GA4 are within 10% of the pre-cutover baseline.

If any of these fail, stop. The Blade site is still the rollback and deleting it removes that option.

- [ ] **Step 2: Move legal copy into the admin**

The API's `staticPage()` still renders Blade views. Before deleting them:

1. For each of `privacy`, `terms`, `cookies`, `disclaimer`, `thank-you`, copy the rendered HTML into a new `SiteSetting` key `legal_{slug}_body` through Filament, or add a dedicated `LegalPage` model if the admin needs richer editing. Pick one and note which in the commit message.
2. Change `PageController::staticPage()` to read from that source instead of `view()->render()`.
3. Update `tests/Feature/Api/V1/Pages/StaticPagesTest.php` to assert the admin-managed copy appears.

Run: `./bin/php vendor/bin/pest tests/Feature/Api/V1/Pages/StaticPagesTest.php`
Expected: PASS with the Blade views already deleted.

- [ ] **Step 3: Reduce routes/web.php**

`routes/web.php` keeps only what Laravel still serves. Every public page route and every redirect goes — Next owns both now.

```php
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| The public site is served by the Next.js application (see web/). Laravel
| serves the API (routes/api.php) and the Filament admin, which registers its
| own routes. Public page routes and their 301s moved to web/src/redirects.ts
| and nginx.
|
*/
```

The 301 redirects now live only in `web/src/redirects.ts`. `RedirectParityTest` compares against `routes/web.php`, so it must be deleted in the same commit — leaving it would assert against an empty list and pass vacuously, which is worse than not having it.

- [ ] **Step 4: Delete the Blade frontend**

```bash
git rm -r resources/views/pages resources/views/blog resources/views/works \
          resources/views/services resources/views/industries \
          resources/views/components resources/views/errors
git rm resources/views/home.blade.php resources/views/contact.blade.php
git rm resources/css/core.css resources/css/pages.css
git rm resources/js/core.js resources/js/chrome.js resources/js/scene.js \
       resources/js/work-globe.js resources/js/work-lightbox.js
git rm -r tests/Feature/Public
git rm tests/Feature/Api/V1/RedirectParityTest.php
```

Keep `resources/views/emails/` — the mailables render from it. Keep `resources/views/filament/` — the admin panel uses it.

- [ ] **Step 5: Delete the public controllers**

Remove `app/Http/Controllers/Public/` entirely. Before deleting, grep for each class name across `app/` and `routes/` to confirm nothing outside the deleted views references it:

```bash
grep -rn "Public\\\\" app/ routes/ --include="*.php"
```

Expected: no results. If any remain, resolve them before deleting.

- [ ] **Step 6: Reduce the root build**

Root `package.json` keeps only what Filament's theme build needs — Tailwind, PostCSS, autoprefixer, Vite, and `laravel-vite-plugin`. Remove `three`, `axios`, `concurrently`, and the typography/forms plugins if the admin theme does not use them.

Root `vite.config.js` builds only `resources/css/filament/admin/theme.css`. Remove the public-site entries.

Run: `npm ci && npm run build`
Expected: builds clean, and `/admin` still renders with its custom theme after `./bin/php artisan filament:assets`.

- [ ] **Step 7: Run everything**

Run: `./bin/php vendor/bin/pest && ./bin/php vendor/bin/phpstan analyse && ./bin/php vendor/bin/pint`
Expected: green. The suite is smaller now — `tests/Feature/Public/` is gone and that is intended.

Run: `cd web && npm run test:e2e`
Expected: green against production.

- [ ] **Step 8: Verify production once more**

- [ ] Every route still loads.
- [ ] `/admin` loads and every Filament resource saves.
- [ ] `sitemap.xml` and `robots.txt` still return 200.
- [ ] A contact submission still creates a Quote.

- [ ] **Step 9: Commit**

```bash
git add -A
git commit -m "chore: retire the Blade frontend

The public site is now served entirely by the Next.js application in web/.
Laravel keeps the /api/v1 layer and the Filament admin.

Removed: public Blade views, the public stylesheet and script bundles, the
public controllers, and their feature tests. Legal page copy moved into the
admin. The 301 redirect map now lives only in web/src/redirects.ts."
```

---

## Definition of Done

- [ ] All five WebGL moments render on WebGPU, with a verified WebGL2 fallback.
- [ ] `npx lhci autorun` meets LCP ≤ 2.0s, INP ≤ 200ms, CLS ≤ 0.05 on every gated URL.
- [ ] Zero WebGL bytes in the initial bundle, asserted by `bundle-budget.spec.ts`.
- [ ] Every page renders its full content with JavaScript disabled.
- [ ] The reduced tier and `prefers-reduced-motion` both behave as specified.
- [ ] `docs/seo-parity-report.md` shows zero unexplained differences.
- [ ] Production serves the Next site; `/admin`, `/api`, `sitemap.xml`, and `robots.txt` still work.
- [ ] Every 301 redirect resolves in production.
- [ ] The Blade frontend is deleted and the full test suite is green.
- [ ] `docs/motion-spec.md` supersedes `plans/`, which is archived rather than deleted.
