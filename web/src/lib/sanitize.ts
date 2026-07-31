/**
 * Strip an admin-authored string down to the two inline tags a headline needs.
 *
 * `hero_headline` reaches the page through `dangerouslySetInnerHTML`, and the
 * Filament RichEditor stores whatever the client sent — there is no
 * server-side filtering anywhere in the stack. That makes an editor account
 * (or a stolen session) enough to execute script on a public page.
 *
 * An allowlist rather than a blocklist: everything that is not a bare `<br>`,
 * `<em>` or `</em>` is escaped, so tags, attributes, event handlers and
 * protocol tricks all become visible text rather than markup. Two tags is the
 * whole vocabulary a headline needs, so nothing legitimate is lost.
 */
const ALLOWED = /^(?:br\s*\/?|\/?em)$/i

export function sanitizeHeadline(html: string): string {
  return html.replace(/<([^>]*)>/g, (match, inner: string) =>
    ALLOWED.test(inner.trim()) ? `<${inner.trim().toLowerCase()}>` : escapeTag(match)
  )
}

function escapeTag(value: string): string {
  return value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
}
