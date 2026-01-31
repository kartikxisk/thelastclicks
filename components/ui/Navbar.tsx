"use client";

import Link from "next/link";
import { useState } from "react";
import { NAV_LINKS, SERVICES } from "@/lib/constants";
import Logo from "@/components/ui/Logo";

export default function Navbar() {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isServicesOpen, setIsServicesOpen] = useState(false);

  return (
    <nav className="from-brand-deep/95 to-brand-deep/95 border-brand-dark/50 fixed top-0 right-0 left-0 z-50 border-b bg-linear-to-r via-zinc-950/95 backdrop-blur-md">
      <div className="container">
        <div className="flex h-16 items-center justify-between">
          {/* Logo */}
          <Logo size="md" />

          {/* Desktop Navigation */}
          <div className="hidden items-center space-x-8 md:flex">
            {/* Services Dropdown */}
            <div className="relative">
              <button
                onMouseEnter={() => setIsServicesOpen(true)}
                onMouseLeave={() => setIsServicesOpen(false)}
                className="hover:text-brand-accent flex items-center space-x-1 font-medium text-zinc-300 transition-colors"
              >
                <span>Services</span>
                <svg
                  className={`h-4 w-4 transition-transform ${isServicesOpen ? "rotate-180" : ""}`}
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </button>

              {/* Dropdown Menu */}
              {isServicesOpen && (
                <div
                  onMouseEnter={() => setIsServicesOpen(true)}
                  onMouseLeave={() => setIsServicesOpen(false)}
                  className="to-brand-deep shadow-brand-primary/10 border-brand-dark/50 animate-fadeIn absolute top-full left-0 mt-2 w-64 rounded-xl border bg-linear-to-b from-zinc-950 py-2 shadow-2xl"
                >
                  {Object.values(SERVICES).map((service) => (
                    <Link
                      key={service.href}
                      href={service.href}
                      className="hover:bg-brand-dark/30 flex items-center px-4 py-3 transition-colors"
                    >
                      <div>
                        <p className="font-medium text-zinc-50">{service.name}</p>
                        <p className="text-sm text-zinc-400">{service.description}</p>
                      </div>
                    </Link>
                  ))}
                </div>
              )}
            </div>

            {NAV_LINKS.filter((link) => link.name !== "Services").map((link) => (
              <Link
                key={link.href}
                href={link.href}
                className="hover:text-brand-accent font-medium text-zinc-300 transition-colors"
              >
                {link.name}
              </Link>
            ))}

            <Link
              href="/contact"
              className="from-brand-primary to-brand-accent hover:shadow-brand-primary/30 rounded-full bg-linear-to-r px-6 py-2 font-medium text-zinc-50 transition-all hover:scale-105 hover:shadow-lg"
            >
              Get Quote
            </Link>
          </div>

          {/* Mobile Menu Button */}
          <button
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            className="hover:bg-brand-deep rounded-lg p-2 transition-colors md:hidden"
            aria-label="Toggle menu"
          >
            <svg
              className="h-6 w-6 text-zinc-50"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              {isMobileMenuOpen ? (
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M6 18L18 6M6 6l12 12"
                />
              ) : (
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M4 6h16M4 12h16M4 18h16"
                />
              )}
            </svg>
          </button>
        </div>

        {/* Mobile Menu */}
        {isMobileMenuOpen && (
          <div className="border-brand-dark/30 animate-fadeIn border-t py-4 md:hidden">
            <div className="space-y-2">
              <p className="px-4 py-2 text-sm font-semibold text-zinc-400 uppercase">Services</p>
              {Object.values(SERVICES).map((service) => (
                <Link
                  key={service.href}
                  href={service.href}
                  className="hover:bg-brand-deep hover:text-brand-accent block px-4 py-2 text-zinc-300 transition-colors"
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  {service.name}
                </Link>
              ))}
              <hr className="border-brand-dark/30 my-2" />
              {NAV_LINKS.filter((link) => link.name !== "Services").map((link) => (
                <Link
                  key={link.href}
                  href={link.href}
                  className="hover:bg-brand-deep hover:text-brand-accent block px-4 py-2 text-zinc-300 transition-colors"
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  {link.name}
                </Link>
              ))}
              <div className="px-4 pt-2">
                <Link
                  href="/contact"
                  className="from-brand-primary to-brand-accent block rounded-full bg-linear-to-r px-6 py-3 text-center font-medium text-zinc-50"
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  Get Quote
                </Link>
              </div>
            </div>
          </div>
        )}
      </div>
    </nav>
  );
}
