'use client' // owns open state, focus trap and body scroll lock

import { useEffect, useRef, useState, type ReactNode } from 'react'

/**
 * The small-screen navigation drawer.
 *
 * Deliberately a real dialog rather than a styled div: it closes on Escape,
 * traps focus while open, restores focus to the trigger on close, and locks
 * body scroll. Those four behaviours are what separate a menu a keyboard user
 * can leave from one that strands them.
 */
export function MobileMenu({ children }: { children: ReactNode }) {
  const [open, setOpen] = useState(false)
  const panel = useRef<HTMLDivElement>(null)
  const trigger = useRef<HTMLButtonElement>(null)

  useEffect(() => {
    if (!open) return

    const previouslyFocused = document.activeElement as HTMLElement | null
    const { overflow } = document.body.style
    document.body.style.overflow = 'hidden'

    const focusable = () =>
      Array.from(
        panel.current?.querySelectorAll<HTMLElement>(
          'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])'
        ) ?? []
      )

    focusable()[0]?.focus()

    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        setOpen(false)
        return
      }

      if (event.key !== 'Tab') return

      const items = focusable()
      if (items.length === 0) return

      const first = items[0]
      const last = items[items.length - 1]

      // Wrap at both ends so Tab can never reach the page behind the drawer.
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault()
        last.focus()
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault()
        first.focus()
      }
    }

    document.addEventListener('keydown', onKeyDown)

    return () => {
      document.removeEventListener('keydown', onKeyDown)
      document.body.style.overflow = overflow
      previouslyFocused?.focus()
    }
  }, [open])

  return (
    <>
      <button
        ref={trigger}
        type="button"
        aria-label="Menu"
        aria-expanded={open}
        className="md:hidden"
        onClick={() => setOpen(true)}
      >
        Menu
      </button>

      {open && (
        <div
          role="dialog"
          aria-modal="true"
          aria-label="Site navigation"
          className="fixed inset-0 z-50 bg-ink"
          onClick={(event) => {
            if (event.target === event.currentTarget) setOpen(false)
          }}
        >
          <div ref={panel} className="flex h-full flex-col gap-8 px-(--pad-x) py-10">
            <button
              type="button"
              aria-label="Close menu"
              className="self-end text-paper-dim"
              onClick={() => setOpen(false)}
            >
              Close
            </button>
            <nav
              aria-label="Mobile"
              className="flex flex-col gap-6 text-3xl"
              // Any link click dismisses the drawer; without this the panel
              // stays over the destination after client-side navigation.
              onClick={() => setOpen(false)}
            >
              {children}
            </nav>
          </div>
        </div>
      )}
    </>
  )
}
