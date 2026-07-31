import type { ReactNode } from 'react'
import { Outfit } from 'next/font/google'
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
export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="en" className={outfit.variable}>
      <body className="min-h-dvh">
        <a className="skip-link" href="#main">
          Skip to content
        </a>
        <main id="main">{children}</main>
      </body>
    </html>
  )
}
