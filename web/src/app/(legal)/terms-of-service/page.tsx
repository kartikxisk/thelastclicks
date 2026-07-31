import type { Metadata } from 'next'
import { LegalPage, legalMetadata } from '@/components/LegalPage'

export const generateMetadata = (): Promise<Metadata> => legalMetadata('terms-of-service')

export default function Page() {
  return <LegalPage slug="terms-of-service" heading="Terms of service." />
}
