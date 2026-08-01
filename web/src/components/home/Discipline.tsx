'use client'

import { useEffect, useRef, useState } from 'react'
import { Section } from '@/components/Section'

/**
 * Ported from the Blade homepage's "02 Discipline" block, which was dropped in
 * the first pass because it is hardcoded copy rather than API content — the
 * port followed the data and missed everything the templates carried
 * themselves.
 *
 * The figures live here for the same reason they lived in the template: they
 * are positioning claims, not records. Moving them into Manage SEO or a
 * settings key would be an improvement, and is deliberately not bundled into
 * this fix.
 */
const STATS = [
  { value: 5, suffix: '+', label: 'Years of experience' },
  { value: 20, suffix: '+', label: 'Cities covered across India' },
  { value: 1000, suffix: '+', label: 'Events & activations over the last decade' },
]

/**
 * Counts up when it first scrolls into view, then stops.
 *
 * Reduced motion gets the final figure immediately — the number is the
 * information, the count is decoration.
 */
function Counter({ to, suffix }: { to: number; suffix: string }) {
  const [value, setValue] = useState(0)
  const ref = useRef<HTMLSpanElement>(null)

  useEffect(() => {
    const node = ref.current
    if (!node) return

    let frame = 0
    let start = 0

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return
        observer.disconnect()

        // The number is the information; the count is decoration. Reduced
        // motion gets the figure straight away.
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
          setValue(to)
          return
        }

        const step = (now: number) => {
          if (!start) start = now
          const progress = Math.min((now - start) / 1600, 1)
          // Ease-out: the number arrives fast and settles, matching --ease.
          setValue(Math.round(to * (1 - Math.pow(1 - progress, 3))))
          if (progress < 1) frame = requestAnimationFrame(step)
        }

        frame = requestAnimationFrame(step)
      },
      { rootMargin: '-10% 0px' }
    )

    observer.observe(node)

    return () => {
      observer.disconnect()
      if (frame) cancelAnimationFrame(frame)
    }
  }, [to])

  return (
    <span ref={ref} className="tabular-nums">
      {value.toLocaleString('en-IN')}
      <em className="not-italic text-red">{suffix}</em>
    </span>
  )
}

export function Discipline() {
  return (
    <Section
      name="discipline"
      eyebrow="Why TheLastClicks"
      title={
        <>
          Built on the discipline of <em className="not-italic text-red">premium brands.</em>
        </>
      }
    >
      <div className="grid gap-12 lg:grid-cols-2">
        <p className="text-xl leading-relaxed text-paper">
          Not a vendor — a long-term partner that scales with your story.
        </p>

        <div className="space-y-5 text-paper-dim">
          <p>
            Brands choose us because we deliver trust, not just footage. Every shoot — wedding,
            brand, commercial, or corporate — is run with the same discipline: show up prepared,
            protect the brief, deliver work that holds up under scrutiny.
          </p>
          <p>
            That discipline is why our client list spans far beyond weddings and product
            launches — we have delivered for some of the country&rsquo;s most demanding
            organisations, from{' '}
            <strong className="text-paper">national institutions and defence forces</strong> to{' '}
            <strong className="text-paper">global enterprise brands</strong> and leading
            automotive names.
          </p>
          <p>
            We don&rsquo;t chase &ldquo;good enough.&rdquo; Every project is a chance to be
            better than the last one — sharper frames, tighter edits, stronger stories.
          </p>
        </div>
      </div>

      <dl className="mt-20 grid gap-10 border-t border-line pt-10 sm:grid-cols-3">
        {STATS.map((stat) => (
          <div key={stat.label}>
            <dd className="text-6xl font-semibold tracking-tight md:text-7xl">
              <Counter to={stat.value} suffix={stat.suffix} />
            </dd>
            <dt className="mt-3 text-sm text-muted-2">{stat.label}</dt>
          </div>
        ))}
      </dl>
    </Section>
  )
}
