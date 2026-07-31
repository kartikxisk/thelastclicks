'use client'

import { EffectComposer, Noise, Vignette } from '@react-three/postprocessing'
import { BlendFunction } from 'postprocessing'
import { useEffect } from 'react'
import type { DeviceTier } from './useDeviceTier'

/**
 * Moment 5 — film grain and vignette.
 *
 * Photography grammar: grain and a falloff at the edges are what a lens does,
 * and they are the cheapest thing that separates a shader site from a CSS one.
 *
 * Full tier only. On `reduced` this is the first thing to go — it is a
 * full-screen pass every frame, which is exactly the cost a low-end device
 * cannot absorb, and its absence costs nothing legible.
 *
 * Deliberately no chromatic aberration. The spec allows it, but it shifts the
 * colour channels of anything behind it, and the DOM text sits above the
 * canvas where it cannot be corrected — measuring contrast per-page against a
 * moving aberration is not a trade worth making for the effect it adds.
 */
export function PostProcessing({ tier }: { tier: DeviceTier }) {
  const active = tier === 'full'

  useEffect(() => {
    // Exposed for end-to-end assertions only.
    ;(window as unknown as { __postProcessing: boolean }).__postProcessing = active

    return () => {
      ;(window as unknown as { __postProcessing: boolean }).__postProcessing = false
    }
  }, [active])

  if (!active) return null

  return (
    <EffectComposer>
      {/* Low opacity and OVERLAY rather than NORMAL: grain should sit in the
          image, not as a scrim over it. Anything heavier reads as noise on a
          broken video rather than as film. */}
      <Noise premultiply blendFunction={BlendFunction.OVERLAY} opacity={0.28} />
      <Vignette eskil={false} offset={0.25} darkness={0.55} />
    </EffectComposer>
  )
}
