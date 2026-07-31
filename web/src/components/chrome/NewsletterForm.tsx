'use client' // form state and submission

import { useState, type FormEvent } from 'react'
import { submitNewsletter } from '@/lib/api-client'

type Status = 'idle' | 'sending' | 'done' | 'error'

/**
 * Footer subscribe form.
 *
 * Mirrors the API's guards: a `website` honeypot the server drops silently,
 * and a 429 the user is told about plainly. The honeypot is positioned
 * offscreen rather than `display:none` — some bots skip fields they can tell
 * are undisplayed, and offscreen still keeps it out of the tab order and the
 * accessibility tree.
 */
export function NewsletterForm() {
  const [status, setStatus] = useState<Status>('idle')
  const [message, setMessage] = useState('')

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setStatus('sending')

    const form = new FormData(event.currentTarget)
    const result = await submitNewsletter({
      email: String(form.get('email') ?? ''),
      website: String(form.get('website') ?? ''),
    })

    if (result.ok) {
      setStatus('done')
      setMessage(result.body.message ?? 'Thanks — you are on the list.')
      return
    }

    setStatus('error')
    setMessage(
      result.status === 429
        ? 'Too many requests — try again in a minute.'
        : (result.body.errors?.email?.[0] ?? 'That address did not look right.')
    )
  }

  if (status === 'done') {
    return (
      <p role="status" className="text-paper-dim">
        {message}
      </p>
    )
  }

  return (
    <form onSubmit={onSubmit} className="flex flex-col gap-3">
      <label htmlFor="newsletter-email" className="text-sm text-muted-2">
        Studio dispatches, occasionally.
      </label>

      <div className="flex gap-2">
        <input
          id="newsletter-email"
          name="email"
          type="email"
          required
          autoComplete="email"
          placeholder="you@studio.com"
          aria-invalid={status === 'error'}
          aria-describedby={status === 'error' ? 'newsletter-error' : undefined}
          className="min-w-0 flex-1 border border-line bg-ink-2 px-3 py-2"
        />
        <button
          type="submit"
          disabled={status === 'sending'}
          className="border border-line px-4 py-2 disabled:opacity-50"
        >
          {status === 'sending' ? 'Sending' : 'Subscribe'}
        </button>
      </div>

      {status === 'error' && (
        <small id="newsletter-error" role="alert" className="text-red">
          {message}
        </small>
      )}

      {/* Honeypot. Offscreen rather than display:none, and hidden from
          assistive tech so a real user never lands on it. */}
      <input
        type="text"
        name="website"
        tabIndex={-1}
        autoComplete="off"
        aria-hidden="true"
        className="absolute left-[-9999px]"
      />
    </form>
  )
}
