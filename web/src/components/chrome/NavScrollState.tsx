'use client' // needs a scroll listener

import { useEffect } from 'react'

/**
 * Sets `data-scrolled` on the header once the page has scrolled past the hero.
 *
 * Implements plans/009. The Blade version polled on a 100ms setInterval, which
 * had three problems this avoids:
 *
 *   1. State could lag scroll by a tenth of a second. The header swap is a
 *      400ms transition, so on a fast flick the nav visibly changed *after*
 *      the hero had left and the causal link was broken.
 *   2. It read offsetHeight ten times a second forever, forcing a layout flush
 *      on every tick whether or not the page had moved.
 *   3. It never stopped — background tabs, unscrollable documents, all of it.
 *
 * Here the threshold is measured once and on resize; scroll only compares a
 * number, and the comparison is coalesced into a frame so a burst of scroll
 * events costs one update.
 */
export function NavScrollState() {
  useEffect(() => {
    const header = document.querySelector('header[data-nav]')
    if (!header) return

    // Transparent over a hero or a full-media page header; solid past it.
    const overMedia = document.querySelector('[data-nav-transparent]')
    let threshold = 30
    let frame = 0
    let applied: boolean | null = null

    const measure = () => {
      threshold = overMedia
        ? Math.max(overMedia.getBoundingClientRect().height - 80, 120)
        : 30
    }

    const evaluate = () => {
      frame = 0
      const scrolled = window.scrollY > threshold
      // Only touch the DOM when the answer actually changed.
      if (scrolled === applied) return
      applied = scrolled
      header.setAttribute('data-scrolled', String(scrolled))
    }

    const onScroll = () => {
      if (frame) return
      frame = requestAnimationFrame(evaluate)
    }

    const onResize = () => {
      measure()
      evaluate()
    }

    measure()
    evaluate()

    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onResize)

    return () => {
      if (frame) cancelAnimationFrame(frame)
      window.removeEventListener('scroll', onScroll)
      window.removeEventListener('resize', onResize)
    }
  }, [])

  return null
}
