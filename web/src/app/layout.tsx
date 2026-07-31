import type { ReactNode } from 'react'
import { Outfit } from 'next/font/google'
import { getIndustries, getServices, getSettings } from '@/lib/api'
import { Cursor } from '@/components/chrome/Cursor'
import { Footer } from '@/components/chrome/Footer'
import { Nav } from '@/components/chrome/Nav'
import { SmoothScroll } from '@/components/chrome/SmoothScroll'
import './globals.css'

/**
 * One typeface site-wide, matching the Blade site — the only stylistic
 * variants are filled and outlined. Self-hosted by next/font, so it costs no
 * render-blocking request and no layout shift on swap.
 */
const outfit = Outfit({
  subsets: ['latin'],
  display: 'swap',
  variable: '--font-outfit',
})

/**
 * No `metadata` export here on purpose. Every route builds its own from the
 * admin-managed SeoPage row via toMetadata(); a layout-level title would sit
 * underneath those as a default nobody maintains.
 */
export default async function RootLayout({ children }: { children: ReactNode }) {
  // Three cached reads, each tagged separately, so an edit to one does not
  // invalidate the chrome wholesale.
  const [settings, services, industries] = await Promise.all([
    getSettings(),
    getServices(),
    getIndustries(),
  ])

  return (
    <html lang="en" className={outfit.variable}>
      <body className="min-h-dvh">
        <a className="skip-link" href="#main">
          Skip to content
        </a>

        <SmoothScroll>
          <Cursor />
          <Nav settings={settings} services={services.data} industries={industries.data} />
          <main id="main">{children}</main>
          <Footer settings={settings} />
        </SmoothScroll>
      </body>
    </html>
  )
}
