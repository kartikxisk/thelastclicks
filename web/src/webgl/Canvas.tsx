'use client' // owns the WebGL context and a rAF loop

import { Canvas } from '@react-three/fiber'
import { Preload, View } from '@react-three/drei'
import { useEffect, useState } from 'react'
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
    // Two frames after mount the DOM content has painted, and we can afford to
    // start compiling shaders.
    let inner = 0
    const outer = requestAnimationFrame(() => {
      inner = requestAnimationFrame(() => setPainted(true))
    })

    return () => {
      cancelAnimationFrame(outer)
      if (inner) cancelAnimationFrame(inner)
    }
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
      <Preload all />
    </Canvas>
  )
}
