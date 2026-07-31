import { revalidateTag } from 'next/cache'
import { NextResponse } from 'next/server'
import { revalidateSecret } from '@/lib/env'

/**
 * Called by Laravel's RevalidateFrontend job when admin content changes.
 *
 * Only reachable from localhost in production — nginx does not proxy
 * /api/revalidate from the public internet — but the shared secret is checked
 * anyway, because "unreachable" is one config edit away from being wrong.
 *
 * revalidateTag uses stale-while-revalidate: visitors keep getting the cached
 * page while the fresh one builds behind them. That is the right trade for an
 * editor saving in Filament, where a few seconds of staleness is invisible and
 * a blocking rebuild would not be.
 */
export async function POST(request: Request) {
  let body: { tags?: unknown; secret?: unknown }

  try {
    body = await request.json()
  } catch {
    return NextResponse.json({ message: 'Invalid JSON' }, { status: 400 })
  }

  if (typeof body.secret !== 'string' || body.secret !== revalidateSecret()) {
    return NextResponse.json({ message: 'Invalid secret' }, { status: 401 })
  }

  const tags = Array.isArray(body.tags)
    ? body.tags.filter((tag): tag is string => typeof tag === 'string')
    : []

  for (const tag of tags) {
    revalidateTag(tag, 'max')
  }

  return NextResponse.json({ revalidated: tags })
}
