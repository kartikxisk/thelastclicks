'use client'

import { useEffect, useRef, type RefObject } from 'react'

/**
 * Normalised scroll velocity, -1..1, damped toward zero.
 *
 * Returns a ref, never state. Writing scroll velocity into React state
 * re-renders the tree at input frequency and is the most common way a WebGL
 * site ends up at 20fps — scenes read this inside useFrame instead.
 *
 * Damping happens on a rAF that only runs while there is velocity to bleed
 * off, so an idle page schedules nothing (plans/003).
 */
export function useScrollVelocity(): RefObject<number> {
  const velocity = useRef(0)

  useEffect(() => {
    let last = window.scrollY
    let frame = 0

    const decay = () => {
      velocity.current *= 0.9

      if (Math.abs(velocity.current) < 0.001) {
        velocity.current = 0
        frame = 0
        return
      }

      frame = requestAnimationFrame(decay)
    }

    const onScroll = () => {
      const current = window.scrollY
      const delta = current - last
      last = current

      // Normalised against a viewport-ish distance, then clamped, so the value
      // means the same thing on a laptop and a tall phone.
      const next = velocity.current + delta / Math.max(window.innerHeight, 1)
      velocity.current = Math.max(-1, Math.min(1, next))

      if (!frame) frame = requestAnimationFrame(decay)
    }

    window.addEventListener('scroll', onScroll, { passive: true })

    return () => {
      window.removeEventListener('scroll', onScroll)
      if (frame) cancelAnimationFrame(frame)
    }
  }, [])

  return velocity
}
