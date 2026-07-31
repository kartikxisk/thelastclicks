'use client'

import { useRouter, useSearchParams } from 'next/navigation'
import { useState, type FormEvent } from 'react'
import { submitQuote } from '@/lib/api-client'
import type { FilterOption, Service } from '@/lib/types'

type Errors = Record<string, string[]>

/**
 * The brief form.
 *
 * Pre-fills from `?service=` and `?industry=`, which is how service CTAs and
 * industry cards hand off — those links exist precisely because industry
 * detail pages were retired.
 *
 * On a 422 the form keeps every value the visitor typed and renders the API's
 * field errors inline. Clearing a long brief because one field failed is the
 * fastest way to lose the enquiry.
 */
export function QuoteForm({
  services,
  projectTypes,
  budgetRanges,
}: {
  services: Service[]
  projectTypes: FilterOption[]
  budgetRanges: FilterOption[]
}) {
  const router = useRouter()
  const search = useSearchParams()

  const [errors, setErrors] = useState<Errors>({})
  const [notice, setNotice] = useState('')
  const [sending, setSending] = useState(false)

  const presetService = search.get('service') ?? ''
  const presetIndustry = search.get('industry') ?? ''
  const presetType = search.get('type') ?? ''

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setSending(true)
    setErrors({})
    setNotice('')

    const form = new FormData(event.currentTarget)
    const payload = Object.fromEntries(form.entries())

    const result = await submitQuote({
      ...payload,
      // Where the brief came from, for the admin's lead desk.
      source_page: presetIndustry
        ? `/contact?industry=${presetIndustry}`
        : presetService
          ? `/contact?service=${presetService}`
          : '/contact',
    })

    setSending(false)

    if (result.ok) {
      router.push('/thank-you')
      return
    }

    if (result.status === 422) {
      setErrors(result.body.errors ?? {})
      setNotice('Please check the highlighted fields.')
      return
    }

    setNotice(
      result.status === 429
        ? 'Too many requests — try again in a minute.'
        : 'Something went wrong sending that. Please try again, or email us directly.'
    )
  }

  const fieldError = (name: string) =>
    errors[name] ? (
      <small data-field-error role="alert" className="text-red">
        {errors[name][0]}
      </small>
    ) : null

  const inputClass = 'border border-line bg-ink-2 px-3 py-2'

  return (
    <form onSubmit={onSubmit} className="grid max-w-2xl gap-5">
      {notice && (
        <p role="alert" className="border border-red px-4 py-3 text-red">
          {notice}
        </p>
      )}

      {presetIndustry && (
        <p data-prefilled-industry className="text-sm text-muted-2">
          Briefing for: <span className="text-paper">{presetIndustry.replace(/-/g, ' ')}</span>
          <input type="hidden" name="industry" value={presetIndustry} />
        </p>
      )}

      <div className="grid gap-5 sm:grid-cols-2">
        <label className="grid gap-2">
          <span>Name</span>
          <input
            name="name"
            required
            autoComplete="name"
            aria-invalid={!!errors.name}
            className={inputClass}
          />
          {fieldError('name')}
        </label>

        <label className="grid gap-2">
          <span>Company</span>
          <input name="company" autoComplete="organization" className={inputClass} />
          {fieldError('company')}
        </label>

        <label className="grid gap-2">
          <span>Email</span>
          <input
            name="email"
            type="email"
            required
            autoComplete="email"
            aria-invalid={!!errors.email}
            className={inputClass}
          />
          {fieldError('email')}
        </label>

        <label className="grid gap-2">
          <span>Phone</span>
          <input name="phone" type="tel" autoComplete="tel" className={inputClass} />
          {fieldError('phone')}
        </label>

        <label className="grid gap-2">
          <span>Service</span>
          <select name="service" defaultValue={presetService} className={inputClass}>
            <option value="">Select a service</option>
            {services.map((service) => (
              <option key={service.slug} value={service.slug}>
                {service.title}
              </option>
            ))}
          </select>
        </label>

        <label className="grid gap-2">
          <span>Project type</span>
          <select name="project_type" defaultValue={presetType} className={inputClass}>
            <option value="">Select a type</option>
            {projectTypes.map((type) => (
              <option key={type.value} value={type.value}>
                {type.label}
              </option>
            ))}
          </select>
          {fieldError('project_type')}
        </label>

        <label className="grid gap-2">
          <span>Budget</span>
          <select name="budget" className={inputClass}>
            <option value="">Select a range</option>
            {budgetRanges.map((range) => (
              <option key={range.value} value={range.value}>
                {range.label}
              </option>
            ))}
          </select>
        </label>

        <label className="grid gap-2">
          <span>Timeline</span>
          <input name="timeline" placeholder="e.g. March, or ASAP" className={inputClass} />
        </label>
      </div>

      <label className="grid gap-2">
        <span>Brief</span>
        <textarea
          name="message"
          rows={6}
          required
          aria-invalid={!!errors.message}
          className={inputClass}
        />
        {fieldError('message')}
      </label>

      <button
        type="submit"
        disabled={sending}
        data-magnetic
        className="justify-self-start bg-red px-8 py-4 font-medium text-white disabled:opacity-50"
      >
        {sending ? 'Sending…' : 'Send brief'}
      </button>

      {/* Honeypot: offscreen rather than display:none, since some bots skip
          fields they can tell are undisplayed. Out of the tab order and hidden
          from assistive tech either way. */}
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
