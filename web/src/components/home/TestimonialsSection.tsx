import type { Testimonial } from '@/lib/types'
import { Section } from '@/components/Section'

export function TestimonialsSection({ testimonials }: { testimonials: Testimonial[] }) {
  if (testimonials.length === 0) return null

  return (
    <Section name="testimonials" eyebrow="06 — Testimonials" title="What clients say.">
      <ul className="grid gap-px border border-line bg-line md:grid-cols-2">
        {testimonials.map((testimonial) => (
          <li key={testimonial.id} className="bg-ink p-8">
            <figure className="flex h-full flex-col gap-6">
              <blockquote className="text-xl leading-relaxed text-balance">
                “{testimonial.quote}”
              </blockquote>

              {(testimonial.client_name || testimonial.role_company) && (
                <figcaption className="mt-auto text-sm text-muted-2">
                  {testimonial.client_name}
                  {testimonial.client_name && testimonial.role_company && ' — '}
                  {testimonial.role_company}
                </figcaption>
              )}
            </figure>
          </li>
        ))}
      </ul>
    </Section>
  )
}
