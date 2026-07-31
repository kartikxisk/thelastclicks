import Link from 'next/link'

/**
 * Closing call to action over an admin-managed background video.
 *
 * The video is decorative: muted, looping, `preload="none"` so it never
 * competes with content for bandwidth, and aria-hidden so it is not announced.
 * A poster is deliberately absent — the gradient carries the frame until the
 * video arrives, and a poster would be a second full-size download for two
 * seconds of coverage.
 */
export function CtaBand({ videoUrl }: { videoUrl: string }) {
  return (
    <section data-section="cta" className="relative overflow-hidden">
      <video
        src={videoUrl}
        muted
        playsInline
        loop
        autoPlay
        preload="none"
        aria-hidden="true"
        tabIndex={-1}
        className="absolute inset-0 h-full w-full object-cover opacity-35 motion-reduce:hidden"
      />

      <div className="absolute inset-0 bg-gradient-to-t from-ink via-ink/70 to-ink/40" />

      <div className="relative mx-auto max-w-(--maxw) px-(--pad-x) py-(--section-y) text-center">
        <h2 className="mx-auto max-w-3xl text-balance text-4xl font-semibold tracking-tight md:text-6xl">
          Bring us a brief.
        </h2>
        <p className="mx-auto mt-6 max-w-xl text-paper-dim">
          Photography, videography or post-production — we reply within four working hours.
        </p>

        <Link
          href="/contact"
          data-magnetic
          className="mt-10 inline-block bg-red px-8 py-4 font-medium text-white transition-transform duration-(--dur-base) ease-(--ease-brand) hover:scale-105"
        >
          Start a project
        </Link>
      </div>
    </section>
  )
}
