/**
 * Serialise structured data for embedding in a <script> tag.
 *
 * JSON.stringify does not escape `<`, so an admin-entered title containing
 * `</script>` would close the tag early and everything after it would be
 * parsed as markup — stored XSS, authored through the Filament admin.
 *
 * Escaping `<` as the < JSON escape is safe inside any JSON string
 * literal, parses back to the identical value, and makes the breakout
 * impossible. The ampersand and line-separator escapes guard the same class of
 * problem for older parsers and for JSONP-style contexts.
 */
function serialise(entry: Record<string, unknown>): string {
  return JSON.stringify(entry)
    .replace(/</g, '\\u003c')
    .replace(/>/g, '\\u003e')
    .replace(/&/g, '\\u0026')
    .replace(/\\u2028/g, '\\u2028')
    .replace(/\\u2029/g, '\\u2029')
}

/**
 * Renders each structured-data entry as its own script tag.
 *
 * One combined @graph would also be valid, but separate tags are what the
 * Blade site emitted, and keeping the shape identical means the pre-cutover
 * SEO parity crawl diffs cleanly instead of flagging every page.
 */
export function JsonLd({ data }: { data: Record<string, unknown>[] }) {
  if (!data?.length) return null

  return (
    <>
      {data.map((entry, i) => (
        <script
          // Entries are positional and static per route; index is stable here.
          key={i}
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: serialise(entry) }}
        />
      ))}
    </>
  )
}
