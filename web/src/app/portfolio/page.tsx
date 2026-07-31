import type { Metadata } from 'next'
import { Suspense } from 'react'
import { getSettings, getWorks } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { Section } from '@/components/Section'
import { Pagination } from '@/components/Pagination'
import { WorkFilters } from '@/components/work/WorkFilters'
import { WorkGallery } from '@/components/work/WorkGallery'

type SearchParams = Promise<{ category?: string; page?: string }>

/** Coerce once so metadata and the page agree on what was requested. */
function parse(params: { category?: string; page?: string }) {
  const page = Number(params.page)
  return {
    category: params.category || undefined,
    page: Number.isInteger(page) && page > 1 ? page : undefined,
  }
}

export async function generateMetadata({
  searchParams,
}: {
  searchParams: SearchParams
}): Promise<Metadata> {
  const { seo } = await getWorks(parse(await searchParams))
  return toMetadata(seo)
}

/**
 * The filtered result set.
 *
 * Split out and suspended because reading searchParams makes this dynamic:
 * under Partial Prerendering the surrounding shell is still served as static
 * HTML immediately, and only this part waits on the request. Awaiting
 * searchParams in the page body instead would block the whole route.
 */
async function Results({ searchParams }: { searchParams: SearchParams }) {
  const params = parse(await searchParams)

  const [{ data, meta, filters }, settings] = await Promise.all([
    getWorks(params),
    getSettings(),
  ])

  return (
    <>
      <WorkFilters categories={filters.categories} active={params.category} />
      <WorkGallery works={data} ratio={settings.work_tile_ratio} />
      <Pagination meta={meta} basePath="/portfolio" params={{ category: params.category }} />
    </>
  )
}

/** Matches the grid's shape so the shell does not collapse then jump. */
function ResultsSkeleton() {
  return (
    <div aria-hidden="true" className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      {Array.from({ length: 6 }, (_, i) => (
        <div key={i} className="aspect-4/3 animate-pulse bg-ink-2" />
      ))}
    </div>
  )
}

export default function PortfolioPage({ searchParams }: { searchParams: SearchParams }) {
  return (
    <Section name="portfolio" eyebrow="Work" title="Selected films & photography." as="h1">
      <Suspense fallback={<ResultsSkeleton />}>
        <Results searchParams={searchParams} />
      </Suspense>
    </Section>
  )
}
