'use client' // owns the WebGL context and a rAF loop

import { Canvas } from '@react-three/fiber'
import { Preload, View } from '@react-three/drei'
import { useEffect, useState } from 'react'
import { PostProcessing } from './PostProcessing'
import { useDeviceTier } from './useDeviceTier'

/**
 * The site's single WebGL context.
 *
 * Mounted once in the root layout, which sits above the <Activity> boundary
 * Cache Components wraps routes in — so navigating reuses the same context,
 * the same compiled shaders and the same GPU-resident textures rather than
 * tearing them down. Putting this inside a page would give each route its own
 * context and defeat the point.
 *
 * `eventSource={document.body}` lets pointer events reach meshes inside
 * scissored views even though the canvas sits behind the DOM at z-index 0.
 *
 * Mounting is deferred until after first paint: the canvas must never be on
 * the critical path to LCP, and the DOM content is the content.
 */
export function WebGLCanvas() {
  const tier = useDeviceTier()
  const [painted, setPainted] = useState(false)

  useEffect(() => {
    /*
     * Wait for the main thread to go idle, not merely for a paint.
     *
     * Two frames after mount was enough to keep WebGL off the critical path to
     * LCP, but not enough to keep it off Total Blocking Time: parsing and
     * compiling ~600KB of three, fiber and drei still landed while the browser
     * was hydrating, and TBT measured 450ms against a 300ms budget.
     *
     * requestIdleCallback waits until there is genuinely nothing else to do.
     * The timeout is a floor so the canvas still arrives on a page that never
     * goes idle, and the rAF fallback covers Safari, which has no rIC.
     */
    const start = () => setPainted(true)

    const idle =
      typeof window.requestIdleCallback === 'function' ? window.requestIdleCallback : null

    if (idle) {
      const id = idle(start, { timeout: 2500 })
      return () => window.cancelIdleCallback(id)
    }

    // Safari has no requestIdleCallback.
    const id = window.setTimeout(start, 1200)
    return () => window.clearTimeout(id)
  }, [])

  if (!painted || tier === 'off') return null

  return (
    <Canvas
      eventSource={typeof document === 'undefined' ? undefined : document.body}
      eventPrefix="client"
      dpr={tier === 'reduced' ? 1 : [1, 2]}
      // Render only when something asks for it. An always-on loop burns battery
      // on a page whose scenes are all offscreen (plans/003).
      frameloop="demand"
      style={{
        position: 'fixed',
        inset: 0,
        // The canvas is decoration behind the DOM. Pointer events reach it via
        // eventSource, so it must not intercept them itself.
        pointerEvents: 'none',
        zIndex: 0,
      }}
    >
      <View.Port />
      <PostProcessing tier={tier} />
      <Preload all />
    </Canvas>
  )
}
