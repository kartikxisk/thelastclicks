import type { Metadata } from 'next'
import Link from 'next/link'
import Image from 'next/image'
import { notFound } from 'next/navigation'
import { getPost, getPosts } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { JsonLd } from '@/components/JsonLd'
import { Section } from '@/components/Section'

type Params = Promise<{ slug: string }>

/** Pre-render the first page of posts; the rest render on demand and cache. */
export async function generateStaticParams() {
  const { data } = await getPosts()
  return data.map((post) => ({ slug: post.slug }))
}

export async function generateMetadata({ params }: { params: Params }): Promise<Metadata> {
  const { slug } = await params

  const payload = await getPost(slug)

  return payload ? toMetadata(payload.seo) : {}
}

export default async function PostPage({ params }: { params: Params }) {
  const { slug } = await params

  const payload = await getPost(slug)
  if (!payload) notFound()

  const { data, seo } = payload

  return (
    <>
      <JsonLd data={seo.json_ld} />

      <article>
        <Section name="post-header" eyebrow={data.category?.label} title={data.title} as="h1">
          <div className="flex flex-wrap items-center gap-4 text-sm text-muted-2">
            {data.published_at && (
              <time dateTime={data.published_at}>
                {new Intl.DateTimeFormat('en-GB', {
                  day: 'numeric',
                  month: 'long',
                  year: 'numeric',
                  timeZone: 'UTC',
                }).format(new Date(data.published_at))}
              </time>
            )}
            <span data-reading-time>{data.reading_minutes} min read</span>
          </div>

          {data.cover && (
            <div className="relative mt-10 aspect-16/9 overflow-hidden bg-ink-2">
              <Image
                src={data.cover}
                alt=""
                fill
                priority
                sizes="(min-width: 1560px) 1560px, 100vw"
                className="object-cover"
              />
            </div>
          )}
        </Section>

        {data.body && (
          <section className="px-(--pad-x) pb-(--section-y)">
            <div
              className="prose-invert mx-auto max-w-3xl space-y-4 text-lg leading-relaxed text-paper-dim [&_a]:text-red [&_a]:underline [&_h2]:mt-10 [&_h2]:text-2xl [&_h2]:font-semibold [&_h2]:text-paper [&_li]:ml-5 [&_ul]:list-disc"
              /*
               * Raw admin-authored HTML.
               *
               * The trust boundary here is the authenticated Filament admin,
               * NOT sanitisation: Filament's RichEditor stores what Trix sends
               * without server-side filtering, and the project has no HTML
               * purifier. The Blade site renders this identically
               * (`{!! $post->body !!}` in blog/show.blade.php), so this
               * matches existing behaviour rather than loosening it.
               *
               * Consequence: anyone who can edit a post can execute script on
               * this page. That is acceptable only while post-edit permission
               * is limited to fully trusted staff. If that ever widens, or as
               * defence against a compromised admin session, sanitise on write
               * in Laravel — mews/purifier on the Post model's body — rather
               * than here, so every consumer of the API benefits.
               */
              dangerouslySetInnerHTML={{ __html: data.body }}
            />
          </section>
        )}

        {data.tags.length > 0 && (
          <Section name="post-tags">
            <ul className="flex flex-wrap gap-2 text-sm">
              {data.tags.map((tag) => (
                <li key={tag.value}>
                  <Link
                    href={`/blog?tag=${tag.value}`}
                    className="border border-line px-3 py-1 text-muted-2"
                  >
                    {tag.label}
                  </Link>
                </li>
              ))}
            </ul>
          </Section>
        )}
      </article>

      {data.related.length > 0 && (
        <Section name="related" eyebrow="More" title="Keep reading.">
          <ul data-related className="grid gap-8 md:grid-cols-3">
            {data.related.map((post) => (
              <li key={post.id}>
                <Link href={`/blog/${post.slug}`} className="group block">
                  <h3 className="font-medium">{post.title}</h3>
                  <p className="mt-1 text-sm text-muted-2">{post.reading_minutes} min read</p>
                </Link>
              </li>
            ))}
          </ul>
        </Section>
      )}
    </>
  )
}
