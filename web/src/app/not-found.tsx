import Link from 'next/link'
import { Section } from '@/components/Section'

/**
 * 404. Deliberately offers routes rather than apologising at length — someone
 * who landed here from a stale link wants a way onward, not an explanation.
 */
export default function NotFound() {
  return (
    <Section name="not-found" eyebrow="404" title="That page has moved on." as="h1">
      <p className="max-w-xl text-paper-dim">
        The link may be old, or the page retired. The work is all still here.
      </p>

      <div className="mt-10 flex flex-wrap gap-4">
        <Link href="/portfolio" className="border border-line px-6 py-3">
          See the work
        </Link>
        <Link href="/contact" className="border border-line px-6 py-3">
          Start a project
        </Link>
        <Link href="/" className="border border-line px-6 py-3">
          Back home
        </Link>
      </div>
    </Section>
  )
}
