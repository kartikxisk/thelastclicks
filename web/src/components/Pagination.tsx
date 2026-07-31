import Link from 'next/link'
import type { PageMeta } from '@/lib/types'

/**
 * Prev/next pagination built from the API's meta block.
 *
 * Links rather than buttons, and rendered server-side, so pagination works
 * without JavaScript and each page is separately addressable — which is also
 * what lets the API's page-aware canonical mean anything.
 */
export function Pagination({
  meta,
  basePath,
  params = {},
}: {
  meta: PageMeta
  basePath: string
  /** Filters to carry across pages so paging never silently drops them. */
  params?: Record<string, string | undefined>
}) {
  if (meta.last_page <= 1) return null

  const href = (page: number) => {
    const query = new URLSearchParams()

    for (const [key, value] of Object.entries(params)) {
      if (value) query.set(key, value)
    }
    if (page > 1) query.set('page', String(page))

    const suffix = query.toString()
    return suffix ? `${basePath}?${suffix}` : basePath
  }

  return (
    <nav aria-label="Pagination" className="mt-16 flex items-center justify-between text-sm">
      {meta.current_page > 1 ? (
        <Link href={href(meta.current_page - 1)} rel="prev">
          ← Previous
        </Link>
      ) : (
        <span />
      )}

      <span aria-current="page" className="text-muted-2">
        Page {meta.current_page} of {meta.last_page}
      </span>

      {meta.current_page < meta.last_page ? (
        <Link href={href(meta.current_page + 1)} rel="next">
          Next →
        </Link>
      ) : (
        <span />
      )}
    </nav>
  )
}
