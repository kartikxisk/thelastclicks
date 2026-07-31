import type { Metadata } from 'next'
import { siteUrl } from './env'
import type { Seo } from './types'

/**
 * Maps an API `seo` object onto Next's Metadata.
 *
 * Every route goes through here and no route hardcodes a title — the admin
 * owns them through SeoPage, and a hardcoded string would silently win over an
 * editor's change.
 */
export function toMetadata(seo: Seo): Metadata {
  const title = seo.title ?? undefined
  const description = seo.description ?? undefined
  const ogTitle = seo.og.title ?? title
  const ogDescription = seo.og.description ?? description
  const images = seo.og.image ? [seo.og.image] : undefined

  return {
    metadataBase: new URL(siteUrl()),
    title,
    description,
    alternates: { canonical: seo.canonical },
    robots: { index: !seo.noindex, follow: !seo.nofollow },
    openGraph: {
      title: ogTitle,
      description: ogDescription,
      url: seo.canonical,
      images,
      type: 'website',
      siteName: 'TheLastClicks',
    },
    twitter: {
      card: 'summary_large_image',
      title: ogTitle,
      description: ogDescription,
      images,
    },
  }
}
