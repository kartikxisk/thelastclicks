/**
 * At most two videos decode at once, site-wide.
 *
 * This is the single largest lever on jank in a video-heavy WebGL site: each
 * decode is its own hardware pipeline, and a phone that handles two smoothly
 * will drop frames on four. LRU eviction means the tiles nearest the viewport
 * keep their streams and the ones scrolled past give theirs up.
 *
 * A module-level registry rather than React state on purpose — the hero and
 * the portfolio gallery are separate trees, and the limit has to hold across
 * both.
 */
const MAX_CONCURRENT = 2

/** id -> monotonic touch counter, so the smallest value is least recent. */
const active = new Map<string, number>()
const onEvict = new Map<string, () => void>()
let tick = 0

function publish() {
  if (typeof window !== 'undefined') {
    ;(window as unknown as { __activeVideoTextures: number }).__activeVideoTextures = active.size
  }
}

/**
 * Claim a decode slot. Returns false only if the caller somehow cannot be
 * accommodated; otherwise the least recently touched holder is evicted and
 * told to pause.
 */
export function acquire(id: string): boolean {
  if (active.has(id)) {
    active.set(id, ++tick)
    return true
  }

  if (active.size >= MAX_CONCURRENT) {
    let oldest: string | null = null
    let oldestTick = Infinity

    for (const [key, touched] of active) {
      if (touched < oldestTick) {
        oldestTick = touched
        oldest = key
      }
    }

    if (oldest === null) return false

    active.delete(oldest)
    // Losing the slot has to actually stop the decode, or the limit is
    // bookkeeping that changes nothing.
    onEvict.get(oldest)?.()
  }

  active.set(id, ++tick)
  publish()
  return true
}

export function release(id: string): void {
  active.delete(id)
  onEvict.delete(id)
  publish()
}

/** Called when this id loses its slot to a nearer tile. */
export function whenEvicted(id: string, callback: () => void): void {
  onEvict.set(id, callback)
}

/** Test seam — resets the registry between runs. */
export function reset(): void {
  active.clear()
  onEvict.clear()
  publish()
}
