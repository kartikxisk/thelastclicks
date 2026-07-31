import Image from 'next/image'
import type { Client } from '@/lib/types'

/**
 * The logo marquee.
 *
 * The track is duplicated so the loop has no visible seam, and the duplicate
 * is aria-hidden so screen readers hear each client once rather than twice.
 * Animation is a transform on a single element — cheap, and it stops entirely
 * under reduced motion via the global rule in motion.css.
 */
export function ClientLogos({ clients }: { clients: Client[] }) {
  const withLogos = clients.filter((client) => client.logo)
  if (withLogos.length === 0) return null

  const track = (hidden: boolean) => (
    <ul aria-hidden={hidden || undefined} className="flex shrink-0 items-center gap-16 pr-16">
      {withLogos.map((client) => (
        <li key={client.id} className="shrink-0 opacity-60">
          <Image
            src={client.logo as string}
            alt={client.name}
            width={120}
            height={40}
            className="h-8 w-auto object-contain"
          />
        </li>
      ))}
    </ul>
  )

  return (
    <section data-section="clients" aria-label="Clients" className="overflow-hidden py-12">
      <div className="flex w-max animate-[marquee_40s_linear_infinite] motion-reduce:animate-none">
        {track(false)}
        {track(true)}
      </div>

      <style>{`
        @keyframes marquee {
          from { transform: translate3d(0, 0, 0); }
          to   { transform: translate3d(-50%, 0, 0); }
        }
      `}</style>
    </section>
  )
}
