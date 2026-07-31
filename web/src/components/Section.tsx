import type { ReactNode } from 'react'

/**
 * The shared section shell: one vertical rhythm, one max width, one eyebrow
 * treatment. Declared once so a spacing change is a single edit rather than a
 * sweep through eight files.
 *
 * `name` becomes `data-section`, which Plan 3's WebGL views bind to. Renaming
 * one breaks a scene, so treat these as identifiers rather than labels.
 */
export function Section({
  name,
  eyebrow,
  title,
  children,
  className = '',
}: {
  name: string
  eyebrow?: string
  title?: ReactNode
  children: ReactNode
  className?: string
}) {
  return (
    <section data-section={name} className={`px-(--pad-x) py-(--section-y) ${className}`}>
      <div className="mx-auto max-w-(--maxw)">
        {(eyebrow || title) && (
          <header className="mb-12">
            {eyebrow && (
              <p className="mb-3 text-sm uppercase tracking-[0.2em] text-muted-2">{eyebrow}</p>
            )}
            {title && (
              <h2 className="text-balance text-3xl font-semibold tracking-tight md:text-5xl">
                {title}
              </h2>
            )}
          </header>
        )}
        {children}
      </div>
    </section>
  )
}
