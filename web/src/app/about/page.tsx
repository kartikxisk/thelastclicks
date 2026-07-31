import type { Metadata } from 'next'
import { getAbout } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { Section } from '@/components/Section'
import { ClientLogos } from '@/components/home/ClientLogos'
import { TestimonialsSection } from '@/components/home/TestimonialsSection'

export async function generateMetadata(): Promise<Metadata> {
  const { seo } = await getAbout()
  return toMetadata(seo)
}

export default async function AboutPage() {
  const { data } = await getAbout()

  return (
    <>
      <Section
        name="about"
        eyebrow="Studio"
        title="A studio at the intersection of cinema, brand and craft."
        as="h1"
      >
        <p className="max-w-2xl text-lg text-paper-dim">
          We plan, shoot and finish in one place — a single crew from director to editor, with
          colour and sound handled in house rather than passed on.
        </p>

        <dl className="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
          <div className="border-t border-line pt-4">
            <dt className="text-sm text-muted-2">Projects delivered</dt>
            <dd className="mt-1 text-4xl font-semibold tracking-tight">{data.stats.works}</dd>
          </div>
          <div className="border-t border-line pt-4">
            <dt className="text-sm text-muted-2">Clients served</dt>
            <dd className="mt-1 text-4xl font-semibold tracking-tight">{data.stats.clients}</dd>
          </div>
        </dl>
      </Section>

      <ClientLogos clients={data.clients} />
      <TestimonialsSection testimonials={data.testimonials} />
    </>
  )
}
