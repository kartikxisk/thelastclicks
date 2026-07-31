import Link from 'next/link'
import type { FilterOption } from '@/lib/types'

/**
 * Category filters as links, not client state.
 *
 * Filter state lives in the URL so a filtered view is server-rendered,
 * linkable, crawlable, and survives a refresh. A React-state version would be
 * none of those, and would need JavaScript to show anything at all.
 */
export function WorkFilters({
  categories,
  active,
}: {
  categories: FilterOption[]
  active?: string
}) {
  if (categories.length === 0) return null

  const base =
    'border px-4 py-2 text-sm transition-colors duration-(--dur-fast) ease-(--ease-brand)'
  const on = 'border-red bg-red text-white'
  const off = 'border-line text-paper-dim hover:border-paper-dim'

  return (
    <nav aria-label="Filter by category" className="mb-10 flex flex-wrap gap-2">
      <Link href="/portfolio" className={`${base} ${!active ? on : off}`}>
        All
      </Link>

      {categories.map((category) => {
        const selected = active === category.value
        return (
          <Link
            key={category.value}
            href={`/portfolio?category=${category.value}`}
            aria-current={selected ? 'page' : undefined}
            className={`${base} ${selected ? on : off}`}
          >
            {category.label}
          </Link>
        )
      })}
    </nav>
  )
}
