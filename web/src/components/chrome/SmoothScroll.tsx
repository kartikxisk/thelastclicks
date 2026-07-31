'use client' // owns a Lenis instance and its rAF loop

import { ReactLenis } from 'lenis/react'
import { useEffect, useState, type ReactNode } from 'react'

/**
 * Momentum scroll for the whole document.
 *
 * Lenis drives native scroll rather than transform-virtualising the page, so
 * position:sticky, anchor links, and the browser's own scroll restoration all
 * keep working — which is the reason it beats the older wrapper-transform
 * approach the Blade site used.
 *
 * Users who ask for reduced motion get native scroll. Momentum is precisely
 * the kind of motion that request is about, so this is one of the few places
 * that opts out entirely rather than shortening a duration.
 */
export function SmoothScroll({ children }: { children: ReactNode }) {
  // Resolved after mount so server and first client render agree. Defaulting
  // to `true` means the very first paint is plain native scroll and Lenis only
  // takes over once we know it is wanted.
  const [reduced, setReduced] = useState(true)

  useEffect(() => {
    const query = window.matchMedia('(prefers-reduced-motion: reduce)')
    const apply = () => setReduced(query.matches)

    apply()
    query.addEventListener('change', apply)
    return () => query.removeEventListener('change', apply)
  }, [])

  if (reduced) return <>{children}</>

  return (
    <ReactLenis
      root
      options={{
        duration: 1.1,
        // Cubic ease-out: fast take-off, long settle. Matches --ease.
        easing: (t: number) => 1 - Math.pow(1 - t, 3),
        smoothWheel: true,
      }}
    >
      {children}
    </ReactLenis>
  )
}
