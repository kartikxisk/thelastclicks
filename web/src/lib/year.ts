import { cacheLife } from 'next/cache'

/**
 * The current year, for the copyright line.
 *
 * Reading the clock directly in a prerendered Server Component is rejected
 * under Cache Components: the static shell would bake in whatever the build
 * machine's date happened to be, and never move. Wrapping it in a `use cache`
 * scope makes the staleness explicit and bounded — at the turn of the year the
 * footer is wrong for at most a day, which is the right trade against making
 * the whole layout dynamic for four digits.
 */
export async function currentYear(): Promise<number> {
  'use cache'
  cacheLife('days')

  return new Date().getFullYear()
}
