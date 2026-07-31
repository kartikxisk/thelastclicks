'use client' // pointer tracking

import { useEffect, useState } from 'react'

/**
 * The camera-shutter cursor, carried over from the Blade site.
 *
 * Mounted only on devices with a fine pointer — a coarse-pointer device has no
 * cursor to replace, and rendering one would leave a stray element chasing
 * taps. Position is written straight to the element's transform inside a rAF
 * rather than through React state, because a state update per pointermove
 * would re-render the tree at input frequency.
 *
 * Elements marked [data-magnetic] pull the cursor toward their centre.
 */
export function Cursor() {
  const [enabled, setEnabled] = useState(false)

  useEffect(() => {
    const fine = window.matchMedia('(hover: hover) and (pointer: fine)')
    const apply = () => setEnabled(fine.matches)

    apply()
    fine.addEventListener('change', apply)
    return () => fine.removeEventListener('change', apply)
  }, [])

  useEffect(() => {
    if (!enabled) return

    const dot = document.querySelector<HTMLElement>('[data-cursor-root]')
    const trail = document.querySelector<HTMLElement>('[data-cursor-trail]')
    if (!dot) return

    let x = window.innerWidth / 2
    let y = window.innerHeight / 2
    // The trail chases the glyph rather than the pointer, so it lags by
    // however far the cursor moved this frame — fast motion stretches it,
    // stillness collapses it back.
    let tx = x
    let ty = y
    let frame = 0

    const draw = () => {
      tx += (x - tx) * 0.18
      ty += (y - ty) * 0.18

      dot.style.transform = `translate3d(${x}px, ${y}px, 0) translate(-50%, -50%)`

      if (trail) {
        const stretch = Math.min(Math.hypot(x - tx, y - ty) / 40, 1)
        trail.style.transform = `translate3d(${tx}px, ${ty}px, 0) translate(-50%, -50%) scale(${1 + stretch * 1.6})`
        trail.style.opacity = String(0.15 + stretch * 0.35)
      }

      // Keep animating while the trail is still catching up, so it settles
      // instead of freezing mid-chase when the pointer stops.
      frame = Math.abs(x - tx) > 0.5 || Math.abs(y - ty) > 0.5 ? requestAnimationFrame(draw) : 0
    }

    const onMove = (event: PointerEvent) => {
      const target = (event.target as Element | null)?.closest?.('[data-magnetic]')

      if (target) {
        // Ease toward the centre of a magnetic target rather than snapping, so
        // the pull reads as attraction instead of a jump.
        const rect = target.getBoundingClientRect()
        const cx = rect.left + rect.width / 2
        const cy = rect.top + rect.height / 2
        x = event.clientX + (cx - event.clientX) * 0.35
        y = event.clientY + (cy - event.clientY) * 0.35
      } else {
        x = event.clientX
        y = event.clientY
      }

      if (!frame) frame = requestAnimationFrame(draw)
    }

    window.addEventListener('pointermove', onMove, { passive: true })

    return () => {
      if (frame) cancelAnimationFrame(frame)
      window.removeEventListener('pointermove', onMove)
    }
  }, [enabled])

  if (!enabled) return null

  return (
    <>
      <div
        data-cursor-trail
        aria-hidden="true"
        className="pointer-events-none fixed left-0 top-0 z-[59] h-6 w-6 rounded-full bg-red opacity-0 will-change-transform"
      />
      <div
        data-cursor-root
        aria-hidden="true"
        className="pointer-events-none fixed left-0 top-0 z-[60] will-change-transform"
      >
        <svg width="28" height="28" viewBox="0 0 24 24">
          <path
            d="M9 3 7.5 5H4a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3.5L15 3H9z"
            fill="#fff"
            stroke="#000"
            strokeWidth="1.2"
          />
          <circle cx="12" cy="12.5" r="4" fill="none" stroke="#000" strokeWidth="1.2" />
          <circle cx="12" cy="12.5" r="1.6" fill="var(--red)" />
        </svg>
      </div>
    </>
  )
}
