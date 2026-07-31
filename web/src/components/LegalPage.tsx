import type { Metadata } from 'next'
import { getStaticPage } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { Section } from '@/components/Section'
import type { StaticPageSlug } from '@/lib/types'

/**
 * The four legal routes differ only by slug and heading, so the shell lives
 * here and each route is a four-line adapter.
 */
export async function legalMetadata(slug: StaticPageSlug): Promise<Metadata> {
  const { seo } = await getStaticPage(slug)
  return toMetadata(seo)
}

export async function LegalPage({
  slug,
  heading,
}: {
  slug: StaticPageSlug
  heading: string
}) {
  const { data } = await getStaticPage(slug)

  return (
    <Section name="legal" eyebrow="Legal" title={heading}>
      {data.body && (
        <div
          className="max-w-3xl space-y-4 text-paper-dim [&_a]:text-red [&_a]:underline [&_h2]:mt-10 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:text-paper [&_li]:ml-5 [&_ul]:list-disc"
          /*
           * Rendered Blade partial from resources/views/pages/legal/. The
           * content is authored in the repo by developers, not by user input,
           * and the same partial backs the Blade page — so this is the same
           * trust boundary the live site already has, not a new one.
           *
           * When this copy moves into the admin, revisit: at that point it
           * becomes editor-authored and should be sanitised on write in
           * Laravel, where every API consumer benefits.
           */
          dangerouslySetInnerHTML={{ __html: data.body }}
        />
      )}
    </Section>
  )
}
