'use client'

import Image from 'next/image'
import { useCallback, useEffect, useRef, useState } from 'react'
import type { Work } from '@/lib/types'

/**
 * Fullscreen media viewer for a single project.
 *
 * Implements plans/014's open motion: the panel scales from 0.96 rather than
 * from 0, because a zero-scale start reads as materialising out of nothing
 * instead of moving (plans/012).
 *
 * Focus is trapped while open and restored to the tile that opened it, so a
 * keyboard user is returned to where they were rather than to the top of the
 * document.
 */
export function WorkLightbox({ work, onClose }: { work: Work; onClose: () => void }) {
  const [index, setIndex] = useState(0)
  const panel = useRef<HTMLDivElement>(null)

  const media = work.media
  const count = media.length

  const next = useCallback(() => setIndex((i) => (i + 1) % Math.max(count, 1)), [count])
  const prev = useCallback(
    () => setIndex((i) => (i - 1 + Math.max(count, 1)) % Math.max(count, 1)),
    [count]
  )

  useEffect(() => {
    const opener = document.activeElement as HTMLElement | null
    const { overflow } = document.body.style
    document.body.style.overflow = 'hidden'

    panel.current?.focus()

    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') onClose()
      if (event.key === 'ArrowRight') next()
      if (event.key === 'ArrowLeft') prev()

      if (event.key === 'Tab') {
        const items = Array.from(panel.current?.querySelectorAll<HTMLElement>('button') ?? [])
        if (items.length === 0) return

        const first = items[0]
        const last = items[items.length - 1]

        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault()
          last.focus()
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault()
          first.focus()
        }
      }
    }

    document.addEventListener('keydown', onKeyDown)

    return () => {
      document.removeEventListener('keydown', onKeyDown)
      document.body.style.overflow = overflow
      opener?.focus()
    }
  }, [next, prev, onClose])

  const current = media[index]

  return (
    <div
      role="dialog"
      aria-modal="true"
      aria-label={work.title}
      className="fixed inset-0 z-50 flex items-center justify-center bg-ink/95 p-6"
      onClick={(event) => {
        if (event.target === event.currentTarget) onClose()
      }}
    >
      <div
        ref={panel}
        tabIndex={-1}
        className="flex max-h-full w-full max-w-5xl flex-col gap-4 motion-safe:animate-[lightbox-in_var(--dur-base)_var(--ease-brand)_both]"
      >
        <div className="flex items-start justify-between gap-6">
          <div>
            <h2 className="text-2xl font-semibold tracking-tight">{work.title}</h2>
            {work.client && <p className="text-sm text-muted-2">{work.client}</p>}
          </div>
          <button type="button" onClick={onClose} aria-label="Close">
            Close
          </button>
        </div>

        {current && (
          <div className="relative flex min-h-0 flex-1 items-center justify-center bg-ink-2">
            {current.type === 'youtube' ? (
              <iframe
                src={current.url}
                title={current.caption ?? work.title}
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowFullScreen
                className="aspect-video w-full"
              />
            ) : current.type === 'video' ? (
              <video src={current.url} controls playsInline className="max-h-[70vh] w-full" />
            ) : (
              <Image
                src={current.url}
                alt={current.caption ?? ''}
                width={current.width ?? 1600}
                height={current.height ?? 900}
                sizes="(min-width: 1024px) 64rem, 100vw"
                className="max-h-[70vh] w-auto object-contain"
              />
            )}
          </div>
        )}

        {count > 1 && (
          <div className="flex items-center justify-between text-sm text-muted-2">
            <button type="button" onClick={prev} aria-label="Previous">
              ← Prev
            </button>
            <span aria-live="polite">
              {index + 1} / {count}
            </span>
            <button type="button" onClick={next} aria-label="Next">
              Next →
            </button>
          </div>
        )}

        {current?.caption && <p className="text-sm text-muted-2">{current.caption}</p>}
      </div>

      <style>{`
        @keyframes lightbox-in {
          from { opacity: 0; transform: scale(0.96) translate3d(0, 8px, 0); }
          to   { opacity: 1; transform: none; }
        }
      `}</style>
    </div>
  )
}
