import Link from 'next/link'
import type { Service } from '@/lib/types'
import { Section } from '@/components/Section'

export function ServicesSection({ services }: { services: Service[] }) {
  if (services.length === 0) return null

  return (
    <Section name="services" eyebrow="03 — Services" title="What we make.">
      <ul className="grid gap-px border border-line bg-line md:grid-cols-3">
        {services.map((service) => (
          <li key={service.id} className="bg-ink">
            <Link
              href={`/services/${service.slug}`}
              data-magnetic
              className="group flex h-full flex-col gap-4 p-8 transition-colors duration-(--dur-base) ease-(--ease-brand) hover:bg-ink-2"
            >
              <h3 className="text-2xl font-semibold tracking-tight">{service.title}</h3>

              {service.hero_copy && (
                <p className="text-paper-dim">{service.hero_copy}</p>
              )}

              {service.tags.length > 0 && (
                <ul className="mt-auto flex flex-wrap gap-2 pt-6 text-sm text-muted-2">
                  {service.tags.map((tag) => (
                    <li key={tag} className="border border-line px-2 py-1">
                      {tag}
                    </li>
                  ))}
                </ul>
              )}

              <span
                aria-hidden="true"
                className="text-red transition-transform duration-(--dur-base) ease-(--ease-brand) group-hover:translate-x-1"
              >
                →
              </span>
            </Link>
          </li>
        ))}
      </ul>
    </Section>
  )
}
