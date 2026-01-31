"use client";

import Link from "next/link";
import { useState } from "react";
import { NAV_LINKS, SERVICES } from "@/lib/constants";
import Logo from "@/components/ui/Logo";

export default function Navbar() {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isServicesOpen, setIsServicesOpen] = useState(false);

  return (
    <nav className="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-[#280905]/95 via-zinc-950/95 to-[#280905]/95 backdrop-blur-md border-b border-[#740A03]/50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <Logo size="md" />

          {/* Desktop Navigation */}
          <div className="hidden md:flex items-center space-x-8">
            {/* Services Dropdown */}
            <div className="relative">
              <button
                onMouseEnter={() => setIsServicesOpen(true)}
                onMouseLeave={() => setIsServicesOpen(false)}
                className="flex items-center space-x-1 text-zinc-300 hover:text-[#E6501B] transition-colors font-medium"
              >
                <span>Services</span>
                <svg
                  className={`w-4 h-4 transition-transform ${isServicesOpen ? "rotate-180" : ""}`}
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              {/* Dropdown Menu */}
              {isServicesOpen && (
                <div
                  onMouseEnter={() => setIsServicesOpen(true)}
                  onMouseLeave={() => setIsServicesOpen(false)}
                  className="absolute top-full left-0 mt-2 w-64 bg-gradient-to-b from-zinc-950 to-[#280905] rounded-xl shadow-2xl shadow-[#C3110C]/10 border border-[#740A03]/50 py-2 animate-fadeIn"
                >
                  {Object.values(SERVICES).map((service) => (
                    <Link
                      key={service.href}
                      href={service.href}
                      className="flex items-center px-4 py-3 hover:bg-[#740A03]/30 transition-colors"
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
                className="text-zinc-300 hover:text-[#E6501B] transition-colors font-medium"
              >
                {link.name}
              </Link>
            ))}

            <Link
              href="/contact"
              className="bg-gradient-to-r from-[#C3110C] to-[#E6501B] text-zinc-50 px-6 py-2 rounded-full font-medium hover:shadow-lg hover:shadow-[#C3110C]/30 hover:scale-105 transition-all"
            >
              Get Quote
            </Link>
          </div>

          {/* Mobile Menu Button */}
          <button
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            className="md:hidden p-2 rounded-lg hover:bg-[#280905] transition-colors"
            aria-label="Toggle menu"
          >
            <svg className="w-6 h-6 text-zinc-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              {isMobileMenuOpen ? (
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              ) : (
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
              )}
            </svg>
          </button>
        </div>

        {/* Mobile Menu */}
        {isMobileMenuOpen && (
          <div className="md:hidden py-4 border-t border-[#740A03]/30 animate-fadeIn">
            <div className="space-y-2">
              <p className="px-4 py-2 text-sm font-semibold text-zinc-400 uppercase">Services</p>
              {Object.values(SERVICES).map((service) => (
                <Link
                  key={service.href}
                  href={service.href}
                  className="block px-4 py-2 text-zinc-300 hover:bg-[#280905] hover:text-[#E6501B] transition-colors"
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  {service.name}
                </Link>
              ))}
              <hr className="my-2 border-[#740A03]/30" />
              {NAV_LINKS.filter((link) => link.name !== "Services").map((link) => (
                <Link
                  key={link.href}
                  href={link.href}
                  className="block px-4 py-2 text-zinc-300 hover:bg-[#280905] hover:text-[#E6501B] transition-colors"
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  {link.name}
                </Link>
              ))}
              <div className="px-4 pt-2">
                <Link
                  href="/contact"
                  className="block text-center bg-gradient-to-r from-[#C3110C] to-[#E6501B] text-zinc-50 px-6 py-3 rounded-full font-medium"
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
