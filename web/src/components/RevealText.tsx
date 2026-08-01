'use client'

import { useEffect, useRef, type ReactNode } from 'react'

/**
 * Masked line reveal for headings.
 *
 * Each line sits in its own overflow-hidden box and rises into it, staggered.
 * That is the effect that reads as "this was designed" rather than "this
 * faded in" — the mask edge gives the motion something to travel against.
 *
 * Splitting happens on a clone measured off-screen, so the real heading is
 * never mid-split while the browser paints. Server-rendered markup is
 * untouched until the effect runs, which keeps the text selectable, readable
 * with JavaScript off, and identical for a crawler.
 *
 * Reduced motion skips the split entirely — the heading simply is there.
 */
/**
 * Escape a word before it is interpolated back into markup.
 *
 * textContent returns the *unescaped* string, so a heading that safely
 * displayed a literal `<img onerror=...>` as text would become live markup the
 * moment it was written back through innerHTML. Round-tripping text through
 * innerHTML is exactly how DOM-based XSS gets introduced.
 */
function escapeWord(word: string): string {
  return word
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
}

export function RevealText({
  children,
  as: Tag = 'span',
  className = '',
  delay = 0,
}: {
  children: ReactNode
  as?: 'h1' | 'h2' | 'h3' | 'span' | 'p'
  className?: string
  delay?: number
}) {
  const ref = useRef<HTMLElement>(null)

  useEffect(() => {
    const node = ref.current
    if (!node) return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return

    // Split into words wrapped in line-masks. Words rather than characters:
    // per-character splitting on a long headline creates hundreds of nodes for
    // an effect nobody can distinguish at reading distance.
    const original = node.innerHTML
    const words = (node.textContent ?? '').split(/(\s+)/)

    node.innerHTML = words
      .map((word) =>
        word.trim()
          ? `<span class="inline-block overflow-hidden align-bottom"><span class="reveal-word inline-block translate-y-full">${escapeWord(word)}</span></span>`
          : word
      )
      .join('')

    const parts = Array.from(node.querySelectorAll<HTMLElement>('.reveal-word'))

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return
        observer.disconnect()

        parts.forEach((part, i) => {
          part.style.transition =
            'transform var(--dur-slow) var(--ease-brand), opacity var(--dur-base) var(--ease-brand)'
          part.style.transitionDelay = `${delay + i * 45}ms`
          part.style.transform = 'translateY(0)'

          // Release the compositor layer once the word has arrived; holding
          // will-change forever is what exhausts GPU memory on a long page.
          part.addEventListener(
            'transitionend',
            () => {
              part.style.willChange = 'auto'
            },
            { once: true }
          )
        })
      },
      { rootMargin: '-8% 0px' }
    )

    observer.observe(node)

    return () => {
      observer.disconnect()
      node.innerHTML = original
    }
  }, [delay])

  return (
    <Tag ref={ref as never} className={className}>
      {children}
    </Tag>
  )
}
