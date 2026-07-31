'use client'

import { OrthographicCamera } from '@react-three/drei'
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
  const { invalidate } = useThree()
  const [rects, setRects] = useState<TileRect[]>([])
  const [bounds, setBounds] = useState({ width: 0, height: 0 })

  // Measure the DOM tiles, and re-measure on resize. Never per frame: a rect
  // read forces layout, and doing it every frame is how a scene turns a
  // scroll into a jank fest.
  useEffect(() => {
    const measure = () => {
      const root = document.querySelector('[data-grid-root]')
      if (!root) return

      // Relative to the grid root, not the viewport: the View is scissored to
      // that element, so its origin is the box the planes must be placed in.
      const origin = root.getBoundingClientRect()
      const tiles = Array.from(document.querySelectorAll('[data-work-tile]'))

      setRects(
        tiles.map((tile) => {
          const r = tile.getBoundingClientRect()
          return {
            width: r.width,
            height: r.height,
            left: r.left - origin.left,
            top: r.top - origin.top,
          }
        })
      )
      setBounds({ width: origin.width, height: origin.height })
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

  // Nothing renders until the grid has been measured. The orthographic
  // frustum is built from these bounds, and a camera constructed with a
  // zero-width frustum projects everything to a degenerate point — from which
  // it never recovers, because the projection matrix is only rebuilt when the
  // camera itself remounts.
  if (tier === 'off' || rects.length === 0 || bounds.width === 0 || bounds.height === 0) {
    return null
  }

  return (
    <>
      {/*
       * Orthographic, in pixel units, centred on the view. That is what makes
       * a DOM rect convert to a world position by simple subtraction — with a
       * perspective camera the mapping depends on distance and every plane
       * would need solving separately.
       */}
      <OrthographicCamera
        makeDefault
        position={[0, 0, 1000]}
        left={-bounds.width / 2}
        right={bounds.width / 2}
        top={bounds.height / 2}
        bottom={-bounds.height / 2}
        near={0.1}
        far={3000}
      />

      <group ref={group}>
        {works.slice(0, rects.length).map((work, i) => (
          <GalleryTile
            key={work.id}
            work={work}
            rect={rects[i]}
            viewport={bounds}
            tier={tier}
            sharedUniforms={uniforms}
          />
        ))}
      </group>
    </>
  )
}

export type TileRect = { width: number; height: number; left: number; top: number }

/**
 * Maps a rect measured against the view root into world units.
 *
 * The camera looks down -Z at a plane in pixel units, so a rect converts by
 * moving its centre into a coordinate space whose origin is the middle of the
 * view rather than its top-left.
 */
export function rectToWorld(rect: TileRect, size: { width: number; height: number }) {
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
