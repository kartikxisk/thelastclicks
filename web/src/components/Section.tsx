import type { ReactNode } from 'react'
import { RevealText } from '@/components/RevealText'
import { sanitizeHeadline } from '@/lib/sanitize'

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
  as = 'h2',
  titleHtml,
}: {
  name: string
  eyebrow?: string
  title?: ReactNode
  /**
   * Render the title as HTML, reduced to <br> and <em>.
   *
   * Service headlines are admin-authored and contain <br> and <em> — the Blade
   * site renders them raw, and escaping them printed the tags on screen. Same
   * trust boundary as the post body: an authenticated editor wrote it.
   */
  titleHtml?: string
  children: ReactNode
  className?: string
  /**
   * Heading level. Every page needs exactly one h1, so the lead section of
   * each route passes `as="h1"` and the rest stay h2 — otherwise a page has
   * no document heading at all, which breaks both screen-reader navigation
   * and the heading structure crawlers read.
   */
  as?: 'h1' | 'h2'
}) {
  const Heading = as

  return (
    <section data-section={name} className={`px-(--pad-x) py-(--section-y) ${className}`}>
      <div className="mx-auto max-w-(--maxw)">
        {(eyebrow || title) && (
          <header className="mb-12">
            {eyebrow && (
              <RevealText
                as="p"
                className="mb-3 text-sm uppercase tracking-[0.2em] text-muted-2"
              >
                {eyebrow}
              </RevealText>
            )}
            {titleHtml ? (
              <Heading
                className={
                  as === 'h1'
                    ? 'text-balance text-4xl font-semibold tracking-tight md:text-6xl'
                    : 'text-balance text-3xl font-semibold tracking-tight md:text-5xl'
                }
                // Allowlisted to <br> and <em>; anything else becomes visible text.
                dangerouslySetInnerHTML={{ __html: sanitizeHeadline(titleHtml) }}
              />
            ) : (
              title && (
                <RevealText
                  as={as}
                  className={
                    as === 'h1'
                      ? 'text-balance text-4xl font-semibold tracking-tight md:text-6xl'
                      : 'text-balance text-3xl font-semibold tracking-tight md:text-5xl'
                  }
                >
                  {title}
                </RevealText>
              )
            )}
          </header>
        )}
        {children}
      </div>
    </section>
  )
}
