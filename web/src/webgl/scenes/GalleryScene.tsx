'use client'

import { useFrame, useThree } from '@react-three/fiber'
import { useEffect, useMemo, useRef, useState } from 'react'
import { Color, Group, ShaderMaterial, Vector2 } from 'three'
import type { Work } from '@/lib/types'
import { useDeviceTier } from '../useDeviceTier'
import { useScrollVelocity } from '../useScrollVelocity'
import { GalleryTile } from './GalleryTile'

/**
 * Moment 2 — the portfolio grid as curved planes.
 *
 * Each tile gets a plane positioned over its DOM rect, bent around a cylinder
 * and stretched by scroll velocity. The DOM tiles stay in place and keep every
 * bit of their behaviour — they are the buttons, they carry the text, they are
 * what a screen reader and a crawler see. Only their cover image is hidden,
 * and only once the scene has actually painted, so a failure anywhere in here
 * degrades to the plain grid rather than to an empty page.
 */
export function GalleryScene({ works }: { works: Work[] }) {
  const tier = useDeviceTier()
  const velocity = useScrollVelocity()
  const group = useRef<Group>(null)
  const { size, invalidate } = useThree()
  const [rects, setRects] = useState<DOMRect[]>([])

  // Measure the DOM tiles, and re-measure on resize. Never per frame: a rect
  // read forces layout, and doing it every frame is how a scene turns a
  // scroll into a jank fest.
  useEffect(() => {
    const measure = () => {
      const tiles = Array.from(document.querySelectorAll('[data-work-tile]'))
      setRects(tiles.map((tile) => tile.getBoundingClientRect()))
      invalidate()
    }

    measure()

    const observer = new ResizeObserver(measure)
    observer.observe(document.body)

    window.addEventListener('resize', measure)
    return () => {
      observer.disconnect()
      window.removeEventListener('resize', measure)
    }
  }, [invalidate])

  const uniforms = useMemo(
    () => ({
      uVelocity: { value: 0 },
      uCurvature: { value: 0.6 },
      uTint: {
        value: new Color(
          typeof window === 'undefined'
            ? '#e80f03'
            : getComputedStyle(document.documentElement).getPropertyValue('--red').trim() ||
                '#e80f03'
        ),
      },
    }),
    []
  )

  useFrame(() => {
    if (!group.current) return

    let animating = false

    for (const child of group.current.children) {
      const material = (child as unknown as { material?: ShaderMaterial }).material
      if (!material?.uniforms?.uVelocity) continue

      material.uniforms.uVelocity.value = velocity.current
      if (velocity.current !== 0) animating = true
    }

    if (animating) invalidate()
  })

  if (tier === 'off' || rects.length === 0) return null

  return (
    <group ref={group}>
      {works.slice(0, rects.length).map((work, i) => (
        <GalleryTile
          key={work.id}
          work={work}
          rect={rects[i]}
          viewport={size}
          tier={tier}
          sharedUniforms={uniforms}
        />
      ))}
    </group>
  )
}

/** Maps a DOM rect to world units for a camera looking down -Z at the plane. */
export function rectToWorld(rect: DOMRect, size: { width: number; height: number }) {
  return {
    width: rect.width,
    height: rect.height,
    x: rect.left + rect.width / 2 - size.width / 2,
    y: -(rect.top + rect.height / 2 - size.height / 2),
  }
}

export type SharedUniforms = {
  uVelocity: { value: number }
  uCurvature: { value: number }
  uTint: { value: Color }
}

export type { Vector2 }
