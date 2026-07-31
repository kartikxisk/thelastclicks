import type { Metadata } from 'next'
import Link from 'next/link'
import { getStaticPage } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { Section } from '@/components/Section'

export async function generateMetadata(): Promise<Metadata> {
  const { seo } = await getStaticPage('thank-you')
  return toMetadata(seo)
}

/**
 * The API returns a null body for this route on purpose — it is a designed
 * confirmation screen rather than an article, so the markup lives here and
 * only the metadata comes from the admin.
 */
export default function ThankYouPage() {
  return (
    <Section name="thank-you" eyebrow="Received" title="Brief received.">
      <p className="max-w-xl text-lg text-paper-dim">
        Thanks — we will be in touch within four working hours.
      </p>

      <div className="mt-10 flex flex-wrap gap-4">
        <Link href="/portfolio" className="border border-line px-6 py-3">
          See the work
        </Link>
        <Link href="/" className="border border-line px-6 py-3">
          Back home
        </Link>
      </div>
    </Section>
  )
}
