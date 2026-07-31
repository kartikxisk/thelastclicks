import type { Metadata } from 'next'
import Link from 'next/link'
import Image from 'next/image'
import { getIndustries } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { Section } from '@/components/Section'

export async function generateMetadata(): Promise<Metadata> {
  const { seo } = await getIndustries()
  return toMetadata(seo)
}

/**
 * Index only — industry detail pages were retired, /industries/{slug} 301s
 * here, and each card opens a pre-filled quote instead. Any card that linked
 * to a slug would send the visitor through that redirect for nothing.
 */
export default async function IndustriesPage() {
  const { data } = await getIndustries()

  return (
    <Section name="industries" eyebrow="Sectors" title="Industries we know.">
      <ul className="grid gap-10 md:grid-cols-2">
        {data.map((industry) => (
          <li key={industry.id} data-industry-card className="border-t border-line pt-8">
            <div className="grid gap-6 sm:grid-cols-[200px_1fr]">
              {industry.cover && (
                <div className="relative aspect-4/3 overflow-hidden bg-ink-2">
                  <Image
                    src={industry.cover}
                    alt=""
                    fill
                    sizes="200px"
                    className="object-cover"
                  />
                </div>
              )}

              <div>
                <h2 className="text-2xl font-semibold tracking-tight">{industry.title}</h2>
                {industry.summary && (
                  <p className="mt-2 text-paper-dim">{industry.summary}</p>
                )}

                {industry.testimonials.length > 0 && (
                  <figure className="mt-4 border-l-2 border-red pl-4">
                    <blockquote className="text-sm text-paper-dim">
                      “{industry.testimonials[0].quote}”
                    </blockquote>
                    {industry.testimonials[0].client_name && (
                      <figcaption className="mt-1 text-sm text-muted-2">
                        {industry.testimonials[0].client_name}
                      </figcaption>
                    )}
                  </figure>
                )}

                <Link
                  href={`/contact?industry=${industry.slug}`}
                  data-magnetic
                  className="mt-6 inline-block text-red"
                >
                  Brief us for {industry.title} →
                </Link>
              </div>
            </div>
          </li>
        ))}
      </ul>
    </Section>
  )
}
