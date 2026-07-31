/**
 * Browser-safe API calls.
 *
 * Deliberately a separate module from `api.ts`: that file holds `use cache`
 * server reads, and Next rejects any module reachable from a Client Component
 * that defines them. Splitting makes it structurally impossible to call a
 * cached server read from the browser rather than relying on discipline.
 *
 * These post to a **relative** path. In production nginx serves Laravel and
 * Next from one origin, so /api/v1 is same-origin; in development
 * next.config.ts rewrites /api/v1/* to the PHP server. Either way the browser
 * never learns an API host, and CORS never enters the picture.
 */

export interface SubmitResult {
  ok: boolean
  status: number
  body: {
    message?: string
    errors?: Record<string, string[]>
    data?: { id: number | null }
  }
}

async function submit(path: string, body: Record<string, unknown>): Promise<SubmitResult> {
  try {
    const response = await fetch(`/api/v1${path}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(body),
      cache: 'no-store',
    })

    return {
      ok: response.ok,
      status: response.status,
      body: await response.json().catch(() => ({})),
    }
  } catch {
    // A dropped connection is not a validation failure; surface it as its own
    // status so the form can say something truthful rather than blaming input.
    return { ok: false, status: 0, body: {} }
  }
}

export const submitContact = (body: Record<string, unknown>) => submit('/contact', body)
export const submitQuote = (body: Record<string, unknown>) => submit('/quotes', body)
export const submitNewsletter = (body: { email: string; website?: string }) =>
  submit('/newsletter', body)
