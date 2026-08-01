import type { Metadata } from 'next'
import { getHome, getSettings } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { JsonLd } from '@/components/JsonLd'
import { Section } from '@/components/Section'
import { Hero } from '@/components/home/Hero'
import { ClientLogos } from '@/components/home/ClientLogos'
import { Discipline } from '@/components/home/Discipline'
import { IndustriesDeck } from '@/components/home/IndustriesDeck'
import { ServicesSection } from '@/components/home/ServicesSection'
import { TestimonialsSection } from '@/components/home/TestimonialsSection'
import { CtaBand } from '@/components/home/CtaBand'
import { WorkGallery } from '@/components/work/WorkGallery'

export async function generateMetadata(): Promise<Metadata> {
  const { seo } = await getHome()
  return toMetadata(seo)
}

/**
 * Section order matches the Blade homepage: hero, clients, industries,
 * services, testimonials, work, CTA. Keeping the order identical is what lets
 * the pre-cutover SEO parity crawl compare heading structure cleanly.
 */
export default async function HomePage() {
  const [{ data, seo }, settings] = await Promise.all([getHome(), getSettings()])

  return (
    <>
      <JsonLd data={seo.json_ld} />

      <Hero slides={data.hero_slides} fallbackImage={data.featured_works[0]?.cover} />
      <ClientLogos clients={data.clients} />
      <Discipline />
      <IndustriesDeck industries={data.industries} />
      <ServicesSection services={data.services} />
      <TestimonialsSection testimonials={data.testimonials} />

      <Section name="work" eyebrow="07 — Work" title="Selected projects.">
        <WorkGallery
          works={data.featured_works}
          ratio={settings.work_tile_ratio}
          layout="collage"
        />
      </Section>

      <CtaBand videoUrl={settings.cta_video_url} />
    </>
  )
}
