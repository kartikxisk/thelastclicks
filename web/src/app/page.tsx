import { getHome } from '@/lib/api'

/**
 * Temporary smoke page. Proves the Cache Components data path works end to end
 * against the live Laravel API; replaced by the real homepage in the next task.
 */
export default async function HomePage() {
  const { data, seo } = await getHome()

  return (
    <main style={{ padding: '2rem', fontFamily: 'monospace' }}>
      <h1>{seo.title ?? 'TheLastClicks'}</h1>
      <ul>
        <li>hero slides: {data.hero_slides.length}</li>
        <li>services: {data.services.length}</li>
        <li>featured works: {data.featured_works.length}</li>
        <li>industries: {data.industries.length}</li>
        <li>testimonials: {data.testimonials.length}</li>
        <li>clients: {data.clients.length}</li>
      </ul>
      <p data-first-service>{data.services[0]?.title ?? 'no services'}</p>
    </main>
  )
}
