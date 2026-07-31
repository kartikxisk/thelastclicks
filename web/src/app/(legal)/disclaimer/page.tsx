import type { Metadata } from 'next'
import { LegalPage, legalMetadata } from '@/components/LegalPage'

export const generateMetadata = (): Promise<Metadata> => legalMetadata('disclaimer')

export default function Page() {
  return <LegalPage slug="disclaimer" heading="Disclaimer." />
}
