import Link from 'next/link'
import Image from 'next/image'
import type { Industry, Service, Settings } from '@/lib/types'
import { MobileMenu } from './MobileMenu'
import { NavScrollState } from './NavScrollState'

/**
 * Site header. A server component — only the scroll-state listener and the
 * mobile drawer need the client, and they are isolated into their own files so
 * the nav's markup and its data stay on the server.
 *
 * Services and industries populate the dropdowns, matching the Blade nav.
 */
export function Nav({
  settings,
  services,
  industries,
}: {
  settings: Settings
  services: Service[]
  industries: Industry[]
}) {
  const links = (
    <>
      <Link href="/portfolio">Portfolio</Link>
      <Link href="/blog">Journal</Link>
      <Link href="/about">About</Link>
      <Link href="/contact">Contact</Link>
    </>
  )

  return (
    <>
      <NavScrollState />

      <header
        data-nav
        data-scrolled="false"
        className="fixed inset-x-0 top-0 z-40 transition-colors duration-(--dur-base) ease-(--ease-brand) data-[scrolled=true]:bg-ink/90 data-[scrolled=true]:backdrop-blur"
      >
        <div className="mx-auto flex max-w-(--maxw) items-center justify-between gap-8 px-(--pad-x) py-5">
          <Link href="/" aria-label="TheLastClicks — home">
            {settings.brand_logo_url ? (
              <Image
                src={settings.brand_logo_url}
                alt="TheLastClicks"
                width={140}
                height={28}
                priority
              />
            ) : (
              // No logo uploaded: render the wordmark rather than substituting
              // a bundled file, matching SiteSetting::brandLogoUrl()'s contract.
              <span className="text-lg font-semibold tracking-tight">TheLastClicks</span>
            )}
          </Link>

          <nav aria-label="Primary" className="hidden items-center gap-8 text-sm md:flex">
            <details className="relative">
              <summary className="cursor-pointer list-none">Services</summary>
              <div className="absolute left-0 top-full mt-3 flex min-w-56 flex-col gap-2 border border-line bg-ink-2 p-4">
                {services.map((service) => (
                  <Link key={service.slug} href={`/services/${service.slug}`}>
                    {service.title}
                  </Link>
                ))}
              </div>
            </details>

            <details className="relative">
              <summary className="cursor-pointer list-none">Industries</summary>
              <div className="absolute left-0 top-full mt-3 flex min-w-56 flex-col gap-2 border border-line bg-ink-2 p-4">
                {industries.map((industry) => (
                  <Link key={industry.slug} href={`/contact?industry=${industry.slug}`}>
                    {industry.title}
                  </Link>
                ))}
              </div>
            </details>

            {links}
          </nav>

          <MobileMenu>{links}</MobileMenu>
        </div>
      </header>
    </>
  )
}
