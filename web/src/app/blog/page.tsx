import type { Metadata } from 'next'
import Link from 'next/link'
import Image from 'next/image'
import { Suspense } from 'react'
import { getPosts } from '@/lib/api'
import { toMetadata } from '@/lib/metadata'
import { Section } from '@/components/Section'
import { Pagination } from '@/components/Pagination'

type SearchParams = Promise<{ category?: string; tag?: string; page?: string }>

function parse(params: { category?: string; tag?: string; page?: string }) {
  const page = Number(params.page)
  return {
    category: params.category || undefined,
    tag: params.tag || undefined,
    page: Number.isInteger(page) && page > 1 ? page : undefined,
  }
}

export async function generateMetadata({
  searchParams,
}: {
  searchParams: SearchParams
}): Promise<Metadata> {
  const { seo } = await getPosts(parse(await searchParams))
  return toMetadata(seo)
}

/** Suspended because searchParams makes it dynamic; the shell stays static. */
async function Posts({ searchParams }: { searchParams: SearchParams }) {
  const params = parse(await searchParams)
  const { data, meta } = await getPosts(params)

  if (data.length === 0) {
    return <p className="text-muted-2">Nothing published under that filter yet.</p>
  }

  return (
    <>
      <ul className="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
        {data.map((post) => (
          <li key={post.id} data-post-card>
            <Link href={`/blog/${post.slug}`} data-magnetic className="group block">
              <div className="relative aspect-16/10 overflow-hidden bg-ink-2">
                {post.cover && (
                  <Image
                    src={post.cover}
                    alt=""
                    fill
                    sizes="(min-width: 1024px) 33vw, (min-width: 768px) 50vw, 100vw"
                    className="object-cover transition-transform duration-(--dur-slow) ease-(--ease-brand) group-hover:scale-105"
                  />
                )}
              </div>

              <div className="mt-4 flex items-center gap-3 text-sm text-muted-2">
                {post.category && <span className="text-red">{post.category.label}</span>}
                <span data-reading-time>{post.reading_minutes} min read</span>
              </div>

              <h2 className="mt-2 text-xl font-medium leading-snug">{post.title}</h2>
              {post.excerpt && <p className="mt-2 text-paper-dim">{post.excerpt}</p>}
            </Link>
          </li>
        ))}
      </ul>

      <Pagination
        meta={meta}
        basePath="/blog"
        params={{ category: params.category, tag: params.tag }}
      />
    </>
  )
}

function PostsSkeleton() {
  return (
    <div aria-hidden="true" className="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
      {Array.from({ length: 6 }, (_, i) => (
        <div key={i} className="aspect-16/10 animate-pulse bg-ink-2" />
      ))}
    </div>
  )
}

export default function BlogPage({ searchParams }: { searchParams: SearchParams }) {
  return (
    <Section name="blog" eyebrow="Journal" title="Notes from the studio.">
      <Suspense fallback={<PostsSkeleton />}>
        <Posts searchParams={searchParams} />
      </Suspense>
    </Section>
  )
}
