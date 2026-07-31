'use client'

import { useFrame, useThree } from '@react-three/fiber'
import { useEffect, useMemo, useRef } from 'react'
import { Color, Group } from 'three'
import { useDeviceTier } from '../useDeviceTier'

/**
 * Moment 3 — depth layers scrubbed by scroll.
 *
 * One plane per pillar, stacked away from the camera. Scrolling the section
 * pulls the stack toward the viewer, so the page reads as travelling through
 * the process rather than past a list of it.
 *
 * Deliberately not pinned. GSAP ScrollTrigger's `pin` was the obvious way to
 * do this and is the wrong one here: the hero scroll lock was removed in
 * plans/002 precisely because pinning breaks the relationship between how far
 * you scroll and how far the page moves. Progress is read from the section's
 * own rect instead, so the scrollbar keeps telling the truth.
 */
export function ServicesScene({ count }: { count: number }) {
  const tier = useDeviceTier()
  const group = useRef<Group>(null)
  const progress = useRef(0)
  const { viewport, invalidate } = useThree()

  useEffect(() => {
    const section = document.querySelector('[data-section="pillars"]')
    if (!section) return

    let frame = 0

    const measure = () => {
      frame = 0
      const rect = section.getBoundingClientRect()
      const span = rect.height + window.innerHeight

      // 0 as the section enters from below, 1 as it leaves past the top.
      progress.current = span > 0 ? 1 - rect.bottom / span : 0
      invalidate()
    }

    const onScroll = () => {
      if (!frame) frame = requestAnimationFrame(measure)
    }

    measure()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onScroll)

    return () => {
      if (frame) cancelAnimationFrame(frame)
      window.removeEventListener('scroll', onScroll)
      window.removeEventListener('resize', onScroll)
    }
  }, [invalidate])

  const tint = useMemo(
    () =>
      new Color(
        typeof window === 'undefined'
          ? '#e80f03'
          : getComputedStyle(document.documentElement).getPropertyValue('--red').trim() ||
              '#e80f03'
      ),
    []
  )

  useFrame(() => {
    if (!group.current) return

    group.current.children.forEach((child, i) => {
      const depth = i / Math.max(count - 1, 1)

      // Each layer starts further back and arrives at its own point in the
      // scrub, so they resolve in sequence rather than all at once.
      child.position.z = -600 + (progress.current - depth * 0.25) * 900
      child.rotation.z = (depth - 0.5) * 0.25 + progress.current * 0.1

      const material = (child as unknown as { material?: { opacity: number } }).material
      if (material) {
        material.opacity = Math.max(0, 0.22 - Math.abs(depth - progress.current) * 0.3)
      }
    })
  })

  // Reduced devices skip this entirely: it is a full-viewport stack of
  // translucent planes composited every frame, and it adds atmosphere rather
  // than information.
  if (tier !== 'full') return null

  return (
    <group ref={group}>
      {Array.from({ length: count }, (_, i) => (
        <mesh key={i}>
          <planeGeometry args={[viewport.width * 0.8, viewport.height * 0.6]} />
          <meshBasicMaterial color={tint} transparent opacity={0} depthWrite={false} />
        </mesh>
      ))}
    </group>
  )
}
