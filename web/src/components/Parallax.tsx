'use client'

import { useEffect, useRef, type ReactNode } from 'react'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

/**
 * Scrubbed parallax for imagery.
 *
 * Deliberately scoped to decorative layers — never text, never anything
 * interactive. Parallaxed body copy hurts reading comfort and can provoke
 * motion sickness, which is why the effect stops at the image.
 *
 * The shift is small on purpose. Beyond about 15% the foreground and
 * background visibly desync and the illusion reads as a bug rather than depth.
 * The wrapper clips, so the oversized child never escapes its box.
 */
export function Parallax({
  children,
  /** Percentage of its own height the layer travels. Keep it 5–15. */
  amount = 12,
  className = '',
  style,
}: {
  children: ReactNode
  amount?: number
  className?: string
  style?: React.CSSProperties
}) {
  const wrapper = useRef<HTMLDivElement>(null)
  const layer = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const box = wrapper.current
    const inner = layer.current
    if (!box || !inner) return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return

    const ctx = gsap.context(() => {
      gsap.fromTo(
        inner,
        { yPercent: -amount / 2 },
        {
          yPercent: amount / 2,
          ease: 'none',
          scrollTrigger: {
            trigger: box,
            scrub: 0.5,
            start: 'top bottom',
            end: 'bottom top',
            // Hold the compositor layer only while the trigger is live, and
            // give it back afterwards — a permanent will-change on every image
            // exhausts GPU memory on a long grid (plans/007).
            onToggle: ({ isActive }) => {
              inner.style.willChange = isActive ? 'transform' : 'auto'
            },
          },
        }
      )
    }, box)

    return () => ctx.revert()
  }, [amount])

  return (
    <div ref={wrapper} className={`overflow-hidden ${className}`} style={style}>
      {/* Oversized so the travel never exposes an edge. */}
      <div ref={layer} className="h-[115%] w-full">
        {children}
      </div>
    </div>
  )
}
