'use client' // `ssr: false` is only legal inside a Client Component

import dynamic from 'next/dynamic'

/**
 * Loads the WebGL canvas lazily, on the client only.
 *
 * This wrapper exists because `next/dynamic` with `ssr: false` is rejected in a
 * Server Component, and the root layout is one. Keeping the boundary here means
 * the layout stays a server component while three, fiber and drei are still
 * excluded from both the RSC payload and the initial browser bundle — which is
 * what holds the "zero WebGL bytes on first load" promise.
 */
const WebGLCanvas = dynamic(() => import('./Canvas').then((m) => m.WebGLCanvas), {
  ssr: false,
})

export function CanvasMount() {
  return <WebGLCanvas />
}
