'use client' // owns lightbox open state

import Image from 'next/image'
import dynamic from 'next/dynamic'
import { useEffect, useLayoutEffect, useState } from 'react'
import { Scene } from '@/webgl/Scene'
import type { Work } from '@/lib/types'
import { WorkLightbox } from './WorkLightbox'

const GalleryScene = dynamic(
  () => import('@/webgl/scenes/GalleryScene').then((m) => m.GalleryScene),
  { ssr: false }
)

/**
 * Tiles that open a lightbox. Shared by the homepage collage and the portfolio
 * grid — the layout differs, the interaction does not.
 *
 * Tiles are buttons rather than links: work detail pages are retired, so there
 * is nowhere to navigate to. A link to `#` would be a lie to assistive tech.
 *
 * `data-section="work-grid"` and `data-work-tile` are contracts Plan 3's WebGL
 * gallery binds to.
 */
export function WorkGallery({
  works,
  ratio,
  layout = 'grid',
}: {
  works: Work[]
  /** CSS aspect-ratio from admin settings, e.g. "4 / 3". */
  ratio: string
  layout?: 'grid' | 'collage'
}) {
  const [open, setOpen] = useState<Work | null>(null)

  // Cache Components hides a route with <Activity> rather than unmounting it,
  // so component state survives navigation. That is right for a filters panel
  // and wrong for a modal: without this, leaving the page with the lightbox
  // open and coming back reopens it over the grid. Closing in the cleanup
  // resets it whenever this route is hidden.
  useLayoutEffect(() => () => setOpen(null), [])

  // Hide the DOM cover images only once a canvas actually exists and the
  // device is on the full tier. Anything that goes wrong upstream — no WebGL,
  // reduced motion, a chunk that never loads — leaves this false and the plain
  // grid on screen, which is the behaviour a failure should have.
  const [webglActive, setWebglActive] = useState(false)

  useEffect(() => {
    const check = () => {
      const tier = (window as unknown as { __webglTier?: string }).__webglTier
      setWebglActive(tier === 'full' && document.querySelector('canvas') !== null)
    }

    const id = window.setInterval(check, 500)
    check()

    return () => window.clearInterval(id)
  }, [])

  if (works.length === 0) {
    return <p className="text-muted-2">No published work yet.</p>
  }

  return (
    <>
      <Scene section="work-grid">
        <GalleryScene works={works} />
      </Scene>

      <ul
        data-section="work-grid"
        data-webgl={webglActive || undefined}
        className={
          layout === 'collage'
            ? 'grid grid-cols-2 gap-4 md:grid-cols-4'
            : 'grid gap-6 sm:grid-cols-2 lg:grid-cols-3'
        }
      >
        {works.map((work, i) => (
          <li
            key={work.id}
            data-work-tile
            // The collage staggers every third tile downward so the grid reads
            // as a cluster rather than a strict lattice.
            className={layout === 'collage' && i % 3 === 1 ? 'md:mt-12' : undefined}
          >
            <button
              type="button"
              data-magnetic
              onClick={() => setOpen(work)}
              className="group block w-full text-left"
              aria-haspopup="dialog"
            >
              <div
                className="relative overflow-hidden bg-ink-2"
                // Reserved from the admin-set ratio, so nothing shifts as
                // images decode (plans/012).
                style={{ aspectRatio: ratio }}
              >
                {work.cover && !webglActive && (
                  <Image
                    src={work.cover}
                    alt=""
                    fill
                    sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
                    // Grid only. On /portfolio the first row is the LCP
                    // element, and lazy-loading it makes LCP wait for a
                    // request that starts after layout. On the homepage
                    // collage the hero already owns LCP, and priority here
                    // just competes with it for bandwidth.
                    priority={layout === 'grid' && i < 3}
                    className="object-cover transition-transform duration-(--dur-slow) ease-(--ease-brand) group-hover:scale-105"
                  />
                )}
              </div>

              <div className="mt-3 flex items-baseline justify-between gap-4">
                <h3 className="font-medium">{work.title}</h3>
                {work.category_label && (
                  <span className="text-sm text-muted-2">{work.category_label}</span>
                )}
              </div>

              {work.client && <p className="text-sm text-muted-2">{work.client}</p>}
            </button>
          </li>
        ))}
      </ul>

      {open && <WorkLightbox work={open} onClose={() => setOpen(null)} />}
    </>
  )
}
