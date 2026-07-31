import type { Metadata } from 'next'
import Image from 'next/image'
import { notFound } from 'next/navigation'
import { getService, getServices, getSettings } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { JsonLd } from '@/components/JsonLd'
import { Section } from '@/components/Section'
import { ServicesSceneMount } from '@/webgl/ServicesSceneMount'
import { WorkGallery } from '@/components/work/WorkGallery'

type Params = Promise<{ slug: string }>

/** Pre-render every service at build; there are three and they change rarely. */
export async function generateStaticParams() {
  const { data } = await getServices()
  return data.map((service) => ({ slug: service.slug }))
}

export async function generateMetadata({ params }: { params: Params }): Promise<Metadata> {
  const { slug } = await params

  const payload = await getService(slug)

  // An unknown slug renders notFound() in the page; empty metadata here keeps
  // the two paths from both reporting the same missing record.
  return payload ? toMetadata(payload.seo) : {}
}

export default async function ServicePage({ params }: { params: Params }) {
  const { slug } = await params

  const payload = await getService(slug)
  if (!payload) notFound()

  const { data, seo } = payload
  const settings = await getSettings()

  return (
    <>
      <JsonLd data={seo.json_ld} />

      <Section
        name="service-hero"
        eyebrow={data.title}
        // The headline is admin-authored and carries <br> and <em>; escaping
        // it printed the tags on screen.
        titleHtml={data.hero_headline ?? undefined}
        title={data.hero_headline ? undefined : data.title}
        as="h1"
      >
        {data.hero_copy && <p className="max-w-2xl text-lg text-paper-dim">{data.hero_copy}</p>}

        {/* The hero meta strip was deliberately retired from the Blade service
            pages (commit 517ac53). hero_meta is still served by the API for
            other consumers, but is not rendered here. */}

        {data.hero && (
          <div className="relative mt-12 aspect-video overflow-hidden bg-ink-2">
            <Image
              src={data.hero.url}
              alt={data.hero.alt ?? ''}
              fill
              priority
              sizes="(min-width: 1560px) 1560px, 100vw"
              className="object-cover"
            />
          </div>
        )}
      </Section>

      {data.pillars.length > 0 && (
        <Section name="pillars" eyebrow="Approach" title="How we work." className="relative">
          <ServicesSceneMount count={data.pillars.length} />
          <ul className="grid gap-px border border-line bg-line md:grid-cols-3">
            {data.pillars.map((pillar) => (
              <li key={pillar.title} className="bg-ink p-8">
                <h3 className="text-xl font-semibold tracking-tight">{pillar.title}</h3>
                <p className="mt-3 text-paper-dim">{pillar.desc}</p>
              </li>
            ))}
          </ul>
        </Section>
      )}

      {data.phases.length > 0 && (
        <Section name="phases" eyebrow="Process" title="What happens, in order.">
          <ol className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
            {data.phases.map((phase) => (
              <li key={phase.num} className="border-t border-line pt-6">
                <p className="text-sm text-red">{phase.num}</p>
                <h3 className="mt-2 text-lg font-medium">{phase.title}</h3>
                <p className="mt-2 text-sm text-paper-dim">{phase.desc}</p>
                {phase.time && <p className="mt-3 text-sm text-muted-2">{phase.time}</p>}
              </li>
            ))}
          </ol>
        </Section>
      )}

      {data.kit.length > 0 && (
        <Section name="kit" eyebrow="Kit" title="What we bring.">
          <div className="grid gap-8 md:grid-cols-3">
            {data.kit.map((group) => (
              <div key={group.title}>
                <h3 className="text-lg font-medium">{group.title}</h3>
                <ul className="mt-3 space-y-1 text-paper-dim">
                  {group.items.map((item) => (
                    <li key={item}>{item}</li>
                  ))}
                </ul>
              </div>
            ))}
          </div>
        </Section>
      )}

      {data.related_works.length > 0 && (
        <Section name="related-work" eyebrow="Work" title="Recent projects.">
          <WorkGallery works={data.related_works} ratio={settings.work_tile_ratio} />
        </Section>
      )}

      {data.faqs.length > 0 && (
        <Section name="faqs" eyebrow="FAQ" title="Common questions.">
          <div className="max-w-3xl">
            {data.faqs.map((faq) => (
              // Native <details>: works without JavaScript, accessible by
              // default. Style the disclosure, do not reimplement it.
              <details key={faq.q} className="group border-b border-line py-5">
                <summary className="cursor-pointer list-none text-lg font-medium">
                  {faq.q}
                </summary>
                {/* grid-template-rows rather than max-height, so the animation
                    eases to the content's real height (plans/006). */}
                <div className="grid grid-rows-[0fr] transition-[grid-template-rows] duration-(--dur-base) ease-(--ease-brand) group-open:grid-rows-[1fr]">
                  <div className="overflow-hidden">
                    <p className="pt-3 text-paper-dim">{faq.a}</p>
                  </div>
                </div>
              </details>
            ))}
          </div>
        </Section>
      )}

      <Section name="service-cta" title={data.cta?.title ?? 'Start a project.'}>
        {data.cta?.copy && <p className="max-w-xl text-paper-dim">{data.cta.copy}</p>}
        <a
          href={`/contact?service=${data.slug}`}
          data-magnetic
          className="mt-8 inline-block bg-red px-8 py-4 font-medium text-white transition-transform duration-(--dur-base) ease-(--ease-brand) hover:scale-105"
        >
          Brief us
        </a>
      </Section>
    </>
  )
}
