'use client'

import dynamic from 'next/dynamic'
import { Scene } from './Scene'

const ServicesScene = dynamic(
  () => import('./scenes/ServicesScene').then((m) => m.ServicesScene),
  { ssr: false }
)

/**
 * Client boundary for the pillars scene. Exists because `ssr: false` is only
 * legal inside a Client Component and the service page is a server one.
 */
export function ServicesSceneMount({ count }: { count: number }) {
  if (count === 0) return null

  return (
    <Scene section="pillars">
      <ServicesScene count={count} />
    </Scene>
  )
}
