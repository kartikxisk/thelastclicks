'use client'

import { usePathname } from 'next/navigation'
import { useState } from 'react'

/**
 * A wipe across the viewport on route change.
 *
 * Implemented as a DOM overlay rather than a pass over the WebGL canvas. The
 * spec allows either; this is the version that cannot fail badly. A wipe is a
 * solid shape moving across the screen — a shader buys nothing legible here,
 * and routing it through the canvas would make navigation depend on WebGL
 * being alive, which is the one thing a transition must never do.
 *
 * Three rules it obeys, all from plans/001:
 *
 *   - 600ms end to end. A curtain that outlasts the navigation makes the site
 *     feel broken rather than considered.
 *   - `pointer-events: none` at every moment. A transition that eats a click
 *     is worse than no transition.
 *   - Nothing under reduced motion, via motion-safe.
 *
 * State is adjusted during render rather than in an effect, and the overlay
 * removes itself on animationend, so there is no timer to drift out of sync
 * with the animation it is supposed to match.
 */
export function RouteTransition() {
  const pathname = usePathname()

  const [seen, setSeen] = useState(pathname)
  const [wipeKey, setWipeKey] = useState<string | null>(null)

  // React's sanctioned "adjust state when a prop changes" pattern: setting
  // during render of the same component re-renders immediately, before the
  // browser paints, rather than scheduling a second pass from an effect.
  if (seen !== pathname) {
    setSeen(pathname)
    setWipeKey(pathname)
  }

  if (!wipeKey) return null

  return (
    <div
      key={wipeKey}
      aria-hidden="true"
      onAnimationEnd={() => setWipeKey(null)}
      className="pointer-events-none fixed inset-0 z-[70] bg-red motion-safe:animate-[route-wipe_600ms_var(--ease-brand-3)_both] motion-reduce:hidden"
    >
      <style>{`
        @keyframes route-wipe {
          0%   { transform: scaleX(0); transform-origin: left; }
          45%  { transform: scaleX(1); transform-origin: left; }
          55%  { transform: scaleX(1); transform-origin: right; }
          100% { transform: scaleX(0); transform-origin: right; }
        }
      `}</style>
    </div>
  )
}
