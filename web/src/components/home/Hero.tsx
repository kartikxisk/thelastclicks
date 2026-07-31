'use client' // advances slides on a timer

import Image from 'next/image'
import dynamic from 'next/dynamic'
import { useEffect, useState } from 'react'
import type { HeroSlide } from '@/lib/types'
import { Scene } from '@/webgl/Scene'

const SLIDE_MS = 6000

/**
 * Loaded separately from the canvas so the scene's own code is fetched only on
 * routes that mount it, rather than riding along with the shared context.
 */
const HeroScene = dynamic(() => import('@/webgl/scenes/HeroScene').then((m) => m.HeroScene), {
  ssr: false,
})

/**
 * The opening frame.
 *
 * `data-section="hero"` and `data-nav-transparent` are contracts, not styling:
 * Plan 3 binds a WebGL view to the first, and NavScrollState measures the
 * second to decide when the header goes solid.
 *
 * Slide one's poster is the LCP element, so it is the only image marked
 * priority and it is never lazy. Later slides load normally.
 */
export function Hero({
  slides,
  fallbackImage,
}: {
  slides: HeroSlide[]
  fallbackImage?: string | null
}) {
  const [index, setIndex] = useState(0)

  useEffect(() => {
    if (slides.length < 2) return

    // Respect reduced motion: no auto-advance, the first slide simply stays.
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return

    const id = setInterval(() => setIndex((i) => (i + 1) % slides.length), SLIDE_MS)
    return () => clearInterval(id)
  }, [slides.length])

  return (
    <section
      data-section="hero"
      data-nav-transparent
      // -mt-24 cancels the layout padding that clears the fixed header: the
      // hero is designed to sit *under* a transparent nav, full bleed.
      className="relative -mt-24 flex min-h-[92vh] items-end overflow-hidden"
    >
      {slides.map((slide, i) => {
        const poster = slide.poster?.url ?? slide.asset?.url
        if (!poster) return null

        return (
          <div
            key={slide.id}
            aria-hidden={i !== index}
            className="absolute inset-0 transition-opacity duration-(--dur-slow) ease-(--ease-brand)"
            style={{ opacity: i === index ? 1 : 0 }}
          >
            {slide.is_video && slide.asset ? (
              <video
                src={slide.asset.url}
                poster={slide.poster?.url ?? undefined}
                muted
                playsInline
                loop
                // Only the visible slide should be decoding.
                autoPlay={i === index}
                preload={i === 0 ? 'metadata' : 'none'}
                className="h-full w-full object-cover"
              />
            ) : (
              <Image
                src={poster}
                alt={slide.asset?.alt ?? ''}
                fill
                priority={i === 0}
                sizes="100vw"
                className="object-cover"
              />
            )}
          </div>
        )
      })}

      {slides.length === 0 && fallbackImage && (
        // No hero slides configured. Rather than a black void, lead with the
        // most recent featured work — it is real content and it is what the
        // section is for.
        <Image
          src={fallbackImage}
          alt=""
          fill
          priority
          sizes="100vw"
          className="object-cover"
        />
      )}

      {/* WebGL sits above the poster and below the scrim, so the DOM image
          stays the LCP element and the headline keeps its contrast whether or
          not the scene ever loads. */}
      <Scene section="hero">
        <HeroScene slides={slides} />
      </Scene>

      {/* Top scrim for the nav. The header is transparent over the hero, so
          without this the links sit on whatever the frame happens to be and
          contrast becomes a coin flip per slide. */}
      <div className="pointer-events-none absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-ink/80 to-transparent" />

      {/* Legibility scrim. Without it the headline sits on whatever the frame
          happens to be, and contrast becomes a coin flip per slide. */}
      <div className="absolute inset-0 bg-gradient-to-t from-ink via-ink/40 to-transparent" />

      <div className="relative mx-auto w-full max-w-(--maxw) px-(--pad-x) pb-(--section-y)">
        <h1 className="max-w-4xl text-balance text-5xl font-semibold leading-[1.05] tracking-tight md:text-7xl">
          Cinematic photography <em className="not-italic text-red">&</em> film production
        </h1>

        {slides[index]?.label && (
          <p className="mt-6 text-paper-dim" aria-live="polite">
            {slides[index].label}
          </p>
        )}
      </div>
    </section>
  )
}
