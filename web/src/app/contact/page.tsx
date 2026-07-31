import type { Metadata } from 'next'
import { Suspense } from 'react'
import { getContact } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { Section } from '@/components/Section'
import { QuoteForm } from '@/components/contact/QuoteForm'

export async function generateMetadata(): Promise<Metadata> {
  const { seo } = await getContact()
  return toMetadata(seo)
}

/**
 * The form reads searchParams via useSearchParams, so it must sit inside a
 * Suspense boundary — otherwise the whole route opts out of prerendering and
 * the shell stops being static.
 */
async function Form() {
  const { data } = await getContact()

  return (
    <QuoteForm
      services={data.services}
      projectTypes={data.project_types}
      budgetRanges={data.budget_ranges}
    />
  )
}

export default function ContactPage() {
  return (
    <Section
      name="contact"
      eyebrow="Contact"
      title="Bring us a brief."
    >
      <p className="mb-10 max-w-xl text-paper-dim">
        Photography, videography or post-production. We reply within four working hours.
      </p>

      <Suspense fallback={<div aria-hidden className="h-96 animate-pulse bg-ink-2" />}>
        <Form />
      </Suspense>
    </Section>
  )
}
