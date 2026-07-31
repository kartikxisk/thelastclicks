import Link from 'next/link'
import Image from 'next/image'
import type { Industry } from '@/lib/types'
import { Section } from '@/components/Section'

/**
 * Industry cards.
 *
 * Each links to a pre-filled quote rather than a detail page — those were
 * retired, and /industries/{slug} now 301s to the index. Linking to a slug
 * here would send visitors through a redirect for no reason.
 */
export function IndustriesDeck({ industries }: { industries: Industry[] }) {
  if (industries.length === 0) return null

  return (
    <Section name="industries" eyebrow="02 — Industries" title="Sectors we know.">
      <ul className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {industries.map((industry) => (
          <li key={industry.id} data-industry-card>
            <Link
              href={`/contact?industry=${industry.slug}`}
              data-magnetic
              className="group block"
            >
              <div className="relative aspect-4/5 overflow-hidden bg-ink-2">
                {industry.cover && (
                  <Image
                    src={industry.cover}
                    alt=""
                    fill
                    sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw"
                    // Transform-only hover: the layout box never changes, so
                    // nothing reflows (plans/005).
                    className="object-cover transition-transform duration-(--dur-slow) ease-(--ease-brand) group-hover:scale-105"
                  />
                )}
              </div>

              <h3 className="mt-4 text-lg font-medium">{industry.title}</h3>
              {industry.summary && (
                <p className="mt-1 text-sm text-muted-2">{industry.summary}</p>
              )}
            </Link>
          </li>
        ))}
      </ul>
    </Section>
  )
}
