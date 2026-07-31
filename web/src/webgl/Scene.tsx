'use client' // binds a View to a DOM rect

import { View } from '@react-three/drei'
import { Suspense, useEffect, useRef, useState, type ReactNode } from 'react'

/**
 * Binds a WebGL view to a DOM section.
 *
 * drei's View uses gl.scissor to cut the shared canvas down to the tracked
 * element's rect and follows it through scroll and resize — which is what lets
 * real server-rendered HTML and WebGL coexist without a second context.
 *
 * Children mount only once the section is near the viewport, so a scene three
 * screens down costs nothing on first load. The observer lives in an effect,
 * which Cache Components tears down when a route is hidden and recreates when
 * it is shown again — so a returning visitor gets a live scene rather than a
 * dead one.
 */
export function Scene({ section, children }: { section: string; children: ReactNode }) {
  const track = useRef<HTMLDivElement>(null)
  const [near, setNear] = useState(false)

  useEffect(() => {
    const target = document.querySelector(`[data-section="${section}"]`)
    if (!target) return

    const observer = new IntersectionObserver(
      ([entry]) => {
        // Latch on: once a scene has been near the viewport it stays mounted,
        // so scrolling back and forth does not recompile its shaders.
        if (entry.isIntersecting) setNear(true)
      },
      { rootMargin: '200% 0px' }
    )

    observer.observe(target)
    return () => observer.disconnect()
  }, [section])

  return (
    <div
      ref={track}
      data-scene={section}
      aria-hidden="true"
      className="pointer-events-none absolute inset-0"
    >
      {near && (
        <View track={track as React.RefObject<HTMLElement>}>
          <Suspense fallback={null}>{children}</Suspense>
        </View>
      )}
    </div>
  )
}
