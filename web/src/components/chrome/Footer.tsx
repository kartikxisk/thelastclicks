import Link from 'next/link'
import type { Settings } from '@/lib/types'
import { currentYear } from '@/lib/year'
import { NewsletterForm } from './NewsletterForm'

/** Platform key to display label, in the order the footer lists them. */
const SOCIAL_LABELS: Record<keyof Settings['socials'], string> = {
  instagram: 'Instagram',
  youtube: 'YouTube',
  behance: 'Behance',
  linkedin: 'LinkedIn',
  facebook: 'Facebook',
  x: 'X',
  pinterest: 'Pinterest',
}

export async function Footer({ settings }: { settings: Settings }) {
  const year = await currentYear()

  // Unset platforms come back as null rather than absent, so filtering here
  // keeps the markup free of empty links.
  const socials = (
    Object.entries(SOCIAL_LABELS) as [keyof Settings['socials'], string][]
  ).flatMap(([key, label]) => {
    const url = settings.socials[key]
    return url ? [{ key, label, url }] : []
  })

  return (
    <footer className="border-t border-line px-(--pad-x) py-(--section-y)">
      <div className="mx-auto grid max-w-(--maxw) gap-12 md:grid-cols-3">
        <div className="flex flex-col gap-2">
          <p className="text-lg font-semibold tracking-tight">TheLastClicks</p>
          {settings.contact_email && (
            <a href={`mailto:${settings.contact_email}`}>{settings.contact_email}</a>
          )}
          {settings.contact_phone && (
            <a href={`tel:${settings.contact_phone.replace(/\s+/g, '')}`}>
              {settings.contact_phone}
            </a>
          )}
          {settings.whatsapp_url && (
            <a href={settings.whatsapp_url} rel="noopener noreferrer" target="_blank">
              WhatsApp
            </a>
          )}
        </div>

        <nav aria-label="Footer" className="flex flex-col gap-2 text-paper-dim">
          <Link href="/portfolio">Portfolio</Link>
          <Link href="/industries">Industries</Link>
          <Link href="/blog">Journal</Link>
          <Link href="/about">About</Link>
          <Link href="/contact">Contact</Link>
        </nav>

        <div className="flex flex-col gap-6">
          <NewsletterForm />

          {socials.length > 0 && (
            <ul className="flex flex-wrap gap-4 text-sm text-muted-2">
              {socials.map(({ key, label, url }) => (
                <li key={key}>
                  <a href={url} rel="noopener noreferrer" target="_blank">
                    {label}
                  </a>
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>

      <div className="mx-auto mt-16 flex max-w-(--maxw) flex-wrap gap-x-6 gap-y-2 text-sm text-muted-2">
        <span>&copy; {year} TheLastClicks. All rights reserved.</span>
        <Link href="/privacy-policy">Privacy</Link>
        <Link href="/terms-of-service">Terms</Link>
        <Link href="/cookie-policy">Cookies</Link>
        <Link href="/disclaimer">Disclaimer</Link>
      </div>
    </footer>
  )
}
