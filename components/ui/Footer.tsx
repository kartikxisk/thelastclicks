import Link from "next/link";
import { COMPANY, SERVICES } from "@/lib/constants";
import Logo from "@/components/ui/Logo";

export default function Footer() {
  return (
    <footer className="relative bg-gradient-to-b from-zinc-950 via-brand-deep/50 to-zinc-950 text-zinc-50 border-t border-brand-dark/50">
      {/* Decorative gradient */}
      <div className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-primary/50 to-transparent" />
      
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
          {/* Brand */}
          <div className="space-y-4">
            <Logo size="lg" />
            <p className="text-zinc-400">{COMPANY.tagline}</p>
            <div className="flex space-x-4">
              <a
                href="#"
                className="w-10 h-10 rounded-full bg-brand-deep border border-brand-dark/50 flex items-center justify-center hover:bg-gradient-to-br hover:from-brand-primary hover:to-brand-accent hover:border-brand-primary transition-all duration-300"
                aria-label="Facebook"
              >
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M18.77,7.46H14.5v-1.9c0-.9.6-1.1,1-1.1h3V.5h-4.33C10.24.5,9.5,3.44,9.5,5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4Z" />
                </svg>
              </a>
              <a
                href="#"
                className="w-10 h-10 rounded-full bg-brand-deep border border-brand-dark/50 flex items-center justify-center hover:bg-gradient-to-br hover:from-brand-primary hover:to-brand-accent hover:border-brand-primary transition-all duration-300"
                aria-label="Instagram"
              >
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12,2.16c3.2,0,3.58,0,4.85.07,3.25.15,4.77,1.69,4.92,4.92.06,1.27.07,1.65.07,4.85s0,3.58-.07,4.85c-.15,3.23-1.66,4.77-4.92,4.92-1.27.06-1.65.07-4.85.07s-3.58,0-4.85-.07c-3.26-.15-4.77-1.7-4.92-4.92-.06-1.27-.07-1.65-.07-4.85s0-3.58.07-4.85C2.38,3.92,3.9,2.38,7.15,2.23,8.42,2.18,8.8,2.16,12,2.16ZM12,0C8.74,0,8.33,0,7.05.07c-4.27.2-6.78,2.71-7,7C0,8.33,0,8.74,0,12s0,3.67.07,4.95c.2,4.27,2.71,6.78,7,7C8.33,24,8.74,24,12,24s3.67,0,4.95-.07c4.27-.2,6.78-2.71,7-7C24,15.67,24,15.26,24,12s0-3.67-.07-4.95c-.2-4.27-2.71-6.78-7-7C15.67,0,15.26,0,12,0Zm0,5.84A6.16,6.16,0,1,0,18.16,12,6.16,6.16,0,0,0,12,5.84ZM12,16a4,4,0,1,1,4-4A4,4,0,0,1,12,16ZM18.41,4.15a1.44,1.44,0,1,0,1.44,1.44A1.44,1.44,0,0,0,18.41,4.15Z" />
                </svg>
              </a>
              <a
                href="#"
                className="w-10 h-10 rounded-full bg-brand-deep border border-brand-dark/50 flex items-center justify-center hover:bg-gradient-to-br hover:from-brand-primary hover:to-brand-accent hover:border-brand-primary transition-all duration-300"
                aria-label="YouTube"
              >
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M23.5,6.19a3,3,0,0,0-2.11-2.14C19.53,3.5,12,3.5,12,3.5s-7.53,0-9.39.55A3,3,0,0,0,.5,6.19,31.45,31.45,0,0,0,0,12a31.45,31.45,0,0,0,.5,5.81,3,3,0,0,0,2.11,2.14c1.86.55,9.39.55,9.39.55s7.53,0,9.39-.55a3,3,0,0,0,2.11-2.14A31.45,31.45,0,0,0,24,12,31.45,31.45,0,0,0,23.5,6.19ZM9.6,15.6V8.4L15.84,12Z" />
                </svg>
              </a>
            </div>
          </div>

          {/* Services */}
          <div>
            <h4 className="text-lg font-semibold mb-4 text-zinc-50">Services</h4>
            <ul className="space-y-3">
              {Object.values(SERVICES).map((service) => (
                <li key={service.href}>
                  <Link href={service.href} className="text-zinc-400 hover:text-brand-accent transition-colors">
                    {service.name}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="text-lg font-semibold mb-4 text-zinc-50">Quick Links</h4>
            <ul className="space-y-3">
              <li>
                <Link href="/about" className="text-zinc-400 hover:text-brand-accent transition-colors">
                  About Us
                </Link>
              </li>
              <li>
                <Link href="/portfolio" className="text-zinc-400 hover:text-brand-accent transition-colors">
                  Portfolio
                </Link>
              </li>
              <li>
                <Link href="/services/editing/editors" className="text-zinc-400 hover:text-brand-accent transition-colors">
                  Our Editors
                </Link>
              </li>
              <li>
                <Link href="/contact" className="text-zinc-400 hover:text-brand-accent transition-colors">
                  Contact
                </Link>
              </li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h4 className="text-lg font-semibold mb-4 text-zinc-50">Contact Us</h4>
            <ul className="space-y-3">
              <li className="flex items-center space-x-3 text-zinc-400">
                <svg className="w-5 h-5 text-brand-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                  />
                </svg>
                <a href={`mailto:${COMPANY.email}`} className="hover:text-brand-accent transition-colors">
                  {COMPANY.email}
                </a>
              </li>
              <li className="flex items-center space-x-3 text-zinc-400">
                <svg className="w-5 h-5 text-brand-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                  />
                </svg>
                <a href={`tel:${COMPANY.mobile}`} className="hover:text-brand-accent transition-colors">
                  {COMPANY.mobile}
                </a>
              </li>
            </ul>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="border-t border-brand-dark/30 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
          <p className="text-zinc-500 text-sm">
            © {new Date().getFullYear()} {COMPANY.name}. All rights reserved.
          </p>
          <div className="flex space-x-6 mt-4 md:mt-0">
            <Link href="/privacy" className="text-zinc-500 hover:text-brand-accent text-sm transition-colors">
              Privacy Policy
            </Link>
            <Link href="/terms" className="text-zinc-500 hover:text-brand-accent text-sm transition-colors">
              Terms of Service
            </Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
