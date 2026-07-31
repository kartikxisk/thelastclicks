'use client'

import { useFrame } from '@react-three/fiber'
import { useMemo, useRef } from 'react'
import type { Mesh, ShaderMaterial } from 'three'
import type { Work } from '@/lib/types'
import type { DeviceTier } from '../useDeviceTier'
import { useSceneTexture } from '../useSceneTexture'
import { rectToWorld, type SharedUniforms } from './GalleryScene'

const vertexShader = /* glsl */ `
  uniform float uVelocity;
  uniform float uCurvature;
  uniform float uHover;

  varying vec2 vUv;

  void main() {
    vUv = uv;

    vec3 p = position;

    // Bend around a cylinder: the tile's edges fall away from the viewer, so
    // the grid reads as a surface rather than a set of flat cards.
    p.z -= (1.0 - cos(uv.x * 3.14159 - 1.5708)) * uCurvature * 40.0;

    // Scroll velocity stretches along Y, which is what makes a fast flick feel
    // like motion through the grid instead of a jump between two states.
    p.y += sin(uv.y * 3.14159) * uVelocity * 30.0;

    // Hover bulges the centre toward the viewer.
    float centre = 1.0 - clamp(distance(uv, vec2(0.5)) * 2.0, 0.0, 1.0);
    p.z += centre * uHover * 24.0;

    gl_Position = projectionMatrix * modelViewMatrix * vec4(p, 1.0);
  }
`

const fragmentShader = /* glsl */ `
  uniform sampler2D uTexture;
  uniform float     uHasTexture;
  uniform float     uHover;
  uniform vec3      uTint;

  varying vec2 vUv;

  void main() {
    if (uHasTexture < 0.5) discard;

    // Sample with the same displacement direction as the geometry, so the
    // image travels with the surface instead of sliding across it.
    vec2 uv = vUv;
    vec3 base = texture2D(uTexture, uv).rgb;

    gl_FragColor = vec4(base + uTint * uHover * 0.12, 1.0);
  }
`

/**
 * One work tile as a curved plane, positioned over its DOM counterpart.
 *
 * Hover is eased toward its target rather than set, so the bulge reads as
 * attraction rather than a snap (plans/004).
 */
export function GalleryTile({
  work,
  rect,
  viewport,
  tier,
  sharedUniforms,
}: {
  work: Work
  rect: DOMRect
  viewport: { width: number; height: number }
  tier: DeviceTier
  sharedUniforms: SharedUniforms
}) {
  const mesh = useRef<Mesh>(null)
  const hover = useRef(0)
  const target = useRef(0)

  const { texture } = useSceneTexture({
    id: `work-${work.slug}`,
    videoUrl: work.preview_video_url,
    posterUrl: work.cover,
    tier,
  })

  const placement = useMemo(() => rectToWorld(rect, viewport), [rect, viewport])

  const uniforms = useMemo(
    () => ({
      uTexture: { value: null as unknown },
      uHasTexture: { value: 0 },
      uHover: { value: 0 },
      uVelocity: sharedUniforms.uVelocity,
      uCurvature: sharedUniforms.uCurvature,
      uTint: sharedUniforms.uTint,
    }),
    [sharedUniforms]
  )

  useFrame(() => {
    const material = mesh.current?.material as ShaderMaterial | undefined
    if (!material) return

    if (texture && material.uniforms.uTexture.value !== texture) {
      material.uniforms.uTexture.value = texture
      material.uniforms.uHasTexture.value = 1
    }

    hover.current += (target.current - hover.current) * 0.12
    material.uniforms.uHover.value = hover.current
  })

  return (
    <mesh
      ref={mesh}
      position={[placement.x, placement.y, 0]}
      onPointerOver={() => {
        target.current = 1
      }}
      onPointerOut={() => {
        target.current = 0
      }}
    >
      {/* Segmented across X for the cylinder bend, less across Y where the
          stretch is a single sine and does not need the vertices. */}
      <planeGeometry args={[placement.width, placement.height, 32, 8]} />
      <shaderMaterial
        vertexShader={vertexShader}
        fragmentShader={fragmentShader}
        uniforms={uniforms}
        transparent
      />
    </mesh>
  )
}
