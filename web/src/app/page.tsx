import type { Metadata } from 'next'
import { getHome } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { JsonLd } from '@/components/JsonLd'

export async function generateMetadata(): Promise<Metadata> {
  const { seo } = await getHome()
  return toMetadata(seo)
}

/**
 * Temporary smoke page. Proves the data path, tokens and metadata pipeline
 * work end to end against the live API; replaced by the real homepage in the
 * next task.
 */
export default async function HomePage() {
  const { data, seo } = await getHome()

  return (
    <>
      <JsonLd data={seo.json_ld} />
      <div className="mx-auto max-w-(--maxw) px-(--pad-x) py-(--section-y)">
        <h1 className="text-5xl font-semibold tracking-tight text-paper">
          {seo.title ?? 'TheLastClicks'}
        </h1>
        <p className="mt-4 text-muted-2">{seo.description}</p>

        <ul className="mt-10 space-y-1 text-paper-dim">
          <li>hero slides: {data.hero_slides.length}</li>
          <li>services: {data.services.length}</li>
          <li>featured works: {data.featured_works.length}</li>
          <li>industries: {data.industries.length}</li>
          <li>testimonials: {data.testimonials.length}</li>
          <li>clients: {data.clients.length}</li>
        </ul>

        <p className="mt-10 text-red" data-first-service>
          {data.services[0]?.title ?? 'no services'}
        </p>
      </div>
    </>
  )
}
