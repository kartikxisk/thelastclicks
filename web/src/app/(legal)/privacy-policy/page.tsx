import type { Metadata } from 'next'
import { LegalPage, legalMetadata } from '@/components/LegalPage'

export const generateMetadata = (): Promise<Metadata> => legalMetadata('privacy-policy')

export default function Page() {
  return <LegalPage slug="privacy-policy" heading="Privacy policy." />
}
