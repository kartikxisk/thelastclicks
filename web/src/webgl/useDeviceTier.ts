'use client' // reads matchMedia and navigator

import { useEffect, useState } from 'react'

export type DeviceTier = 'full' | 'reduced' | 'off'

/**
 * How much WebGL this device gets.
 *
 * - `off`     — the user asked for reduced motion. Nothing mounts.
 * - `reduced` — coarse pointer or under 4GB. Poster images instead of video
 *               textures, no post-processing, DPR capped at 1.
 * - `full`    — everything.
 *
 * Starts at `off` and resolves after mount, so the server render and the first
 * client render agree and there is no hydration mismatch. That also means the
 * first paint never carries a canvas, which is what keeps WebGL off the
 * critical path to LCP.
 */
export function useDeviceTier(): DeviceTier {
  const [tier, setTier] = useState<DeviceTier>('off')

  useEffect(() => {
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)')

    const resolve = (): DeviceTier => {
      if (motion.matches) return 'off'

      const coarse = window.matchMedia('(pointer: coarse)').matches
      const memory = (navigator as Navigator & { deviceMemory?: number }).deviceMemory

      return coarse || (memory !== undefined && memory < 4) ? 'reduced' : 'full'
    }

    const apply = () => {
      const next = resolve()
      setTier(next)
      // Exposed for end-to-end assertions only; nothing in the app reads it.
      ;(window as unknown as { __webglTier: DeviceTier }).__webglTier = next
    }

    apply()
    motion.addEventListener('change', apply)
    return () => motion.removeEventListener('change', apply)
  }, [])

  return tier
}
