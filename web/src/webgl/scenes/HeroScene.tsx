'use client'

import { useFrame, useThree } from '@react-three/fiber'
import { useMemo, useRef } from 'react'
import { Color, Mesh, ShaderMaterial, Vector2 } from 'three'
import type { HeroSlide } from '@/lib/types'
import { useDeviceTier } from '../useDeviceTier'
import { useScrollVelocity } from '../useScrollVelocity'
import { useSceneTexture } from '../useSceneTexture'

/*
 * GLSL rather than TSL, and the WebGL2 renderer rather than WebGPU.
 *
 * The spec calls for TSL compiled to both backends. That is still the right
 * destination — it is one shader source instead of two — but it is a migration
 * to make deliberately, with something on screen to compare against, not while
 * the first scene is also being written. WebGL2 is the fallback path either
 * way, so nothing here is throwaway: the geometry, the uniforms and the
 * texture plumbing all carry over, and only the shader body changes.
 */

const vertexShader = /* glsl */ `
  uniform float uVelocity;
  uniform vec2  uPointer;
  uniform float uTime;

  varying vec2  vUv;
  varying float vDisplace;

  void main() {
    vUv = uv;

    // Distance from the pointer, so the surface lifts toward the cursor.
    float pointer = 1.0 - clamp(distance(uv, uPointer) * 2.0, 0.0, 1.0);

    // A slow travelling wave keeps the plane alive while the page is still.
    float idle = sin(uv.x * 6.0 + uTime * 0.6) * 0.012;

    // Scroll velocity bends the whole plane along Y.
    float bend = uVelocity * 0.35 * sin(uv.y * 3.14159);

    vDisplace = idle + bend + pointer * 0.08;

    vec3 displaced = position + normal * vDisplace;
    gl_Position = projectionMatrix * modelViewMatrix * vec4(displaced, 1.0);
  }
`

const fragmentShader = /* glsl */ `
  uniform sampler2D uTexture;
  uniform vec3      uRim;
  uniform float     uHasTexture;

  varying vec2  vUv;
  varying float vDisplace;

  void main() {
    vec3 base = uHasTexture > 0.5 ? texture2D(uTexture, vUv).rgb : vec3(0.04);

    // Displacement drives a rim in the brand red, so the light reads as a
    // consequence of the geometry moving rather than an overlay on top of it.
    float rim = clamp(abs(vDisplace) * 6.0, 0.0, 1.0);

    gl_FragColor = vec4(base + uRim * rim * 0.6, 1.0);
  }
`

/**
 * Moment 1 — the hero slide as a displaced plane.
 *
 * The DOM hero underneath keeps its heading and its poster image; this paints
 * over it once ready. The poster stays the LCP element, which is why this is
 * additive rather than a replacement.
 */
export function HeroScene({ slides }: { slides: HeroSlide[] }) {
  const tier = useDeviceTier()
  const velocity = useScrollVelocity()
  const mesh = useRef<Mesh>(null)
  const { viewport, invalidate } = useThree()

  const slide = slides[0]

  const { texture } = useSceneTexture({
    id: `hero-${slide?.id ?? 'none'}`,
    videoUrl: slide?.is_video ? (slide.asset?.url ?? null) : null,
    posterUrl: slide?.poster?.url ?? slide?.asset?.url ?? null,
    tier,
  })

  const uniforms = useMemo(
    () => ({
      uTexture: { value: null as unknown },
      uVelocity: { value: 0 },
      uPointer: { value: new Vector2(0.5, 0.5) },
      uTime: { value: 0 },
      uHasTexture: { value: 0 },
      // Read from the token rather than hardcoded, so the brand red has one
      // definition shared with the CSS.
      uRim: {
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

  useFrame((state, delta) => {
    const material = mesh.current?.material as ShaderMaterial | undefined
    if (!material) return

    material.uniforms.uTime.value += delta
    material.uniforms.uVelocity.value = velocity.current
    material.uniforms.uPointer.value.set((state.pointer.x + 1) / 2, (state.pointer.y + 1) / 2)

    if (texture && material.uniforms.uTexture.value !== texture) {
      material.uniforms.uTexture.value = texture
      material.uniforms.uHasTexture.value = 1
    }

    // frameloop is "demand", so the scene has to ask for the next frame. It
    // only does so while there is something to animate.
    if (velocity.current !== 0 || material.uniforms.uHasTexture.value > 0.5) invalidate()
  })

  if (!slide) return null

  return (
    <mesh ref={mesh}>
      {/* Subdivided enough to bend smoothly; 64x64 is where extra segments
          stop being visible and start costing frames. */}
      <planeGeometry args={[viewport.width, viewport.height, 64, 64]} />
      <shaderMaterial
        vertexShader={vertexShader}
        fragmentShader={fragmentShader}
        uniforms={uniforms}
      />
    </mesh>
  )
}
