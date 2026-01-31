import Link from "next/link";
import { COMPANY, SERVICES } from "@/lib/constants";
import CTASection from "@/components/CTASection";
import CreativeProcess from "@/components/CreativeProcess";
import TrustedBrands from "@/components/TrustedBrands";
import Testimonials from "@/components/Testimonials";
import FAQSection from "@/components/FAQSection";
import Icon from "@/components/ui/IconMap";
import { FiCamera, FiVideo, FiEdit3 } from "react-icons/fi";
import { BiTargetLock } from "react-icons/bi";
import { BsLightning } from "react-icons/bs";
import { IoDiamondOutline } from "react-icons/io5";
import { MdOutlineHandshake } from "react-icons/md";

export default function Homepage() {
  return (
    <main className="min-h-screen bg-zinc-950">
      {/* Hero Section */}
      <section className="relative flex min-h-screen items-center justify-center overflow-hidden px-4">
        <div className="from-brand-deep to-brand-dark absolute inset-0 bg-linear-to-br via-zinc-950" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-10" />

        {/* Animated Gradient Orbs */}
        <div className="bg-brand-primary absolute top-20 left-10 h-72 w-72 animate-pulse rounded-full opacity-20 blur-[128px]" />
        <div className="bg-brand-accent absolute right-10 bottom-10 h-96 w-96 animate-pulse rounded-full opacity-20 blur-[128px]" />
        <div className="bg-brand-dark absolute top-1/2 left-1/2 h-[600px] w-[600px] -translate-x-1/2 -translate-y-1/2 rounded-full opacity-15 blur-[200px]" />

        <div className="relative z-10 container text-center">
          <span className="text-brand-accent border-brand-primary/30 mb-6 inline-flex items-center gap-2 rounded-full border bg-zinc-50/10 px-4 py-2 text-sm font-medium backdrop-blur-sm">
            <Icon name="HiSparkles" className="h-4 w-4" />
            {COMPANY.tagline}
          </span>
          <h1 className="font-display mb-6 text-5xl leading-tight text-zinc-50 md:text-7xl lg:text-8xl">
            We Create
            <br />
            <span className="from-brand-primary via-brand-accent to-brand-primary bg-linear-to-r bg-clip-text text-transparent">
              Visual Magic
            </span>
          </h1>
          <p className="mx-auto mb-10 max-w-3xl text-xl text-zinc-400 md:text-2xl">
            Professional photography, cinematic videography, and expert editing services that
            transform your moments into timeless memories.
          </p>
          <div className="flex flex-col justify-center gap-4 sm:flex-row">
            <Link
              href="/services"
              className="from-brand-primary to-brand-accent hover:shadow-brand-primary/30 rounded-full bg-linear-to-r px-8 py-4 text-lg font-semibold text-zinc-50 transition-all hover:scale-105 hover:shadow-2xl"
            >
              Explore Services
            </Link>
            <Link
              href="/portfolio"
              className="border-brand-primary/30 rounded-full border bg-zinc-50/10 px-8 py-4 text-lg font-semibold text-zinc-50 backdrop-blur-sm transition-all hover:bg-zinc-50/20"
            >
              View Our Work
            </Link>
          </div>
        </div>

        {/* Scroll Indicator */}
        <div className="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
          <svg
            className="h-6 w-6 text-zinc-50/50"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M19 14l-7 7m0 0l-7-7m7 7V3"
            />
          </svg>
        </div>
      </section>

      {/* Services Section */}
      <section className="bg-zinc-950 px-4 py-24">
        <div className="container">
          <div className="mb-16 text-center">
            <span className="bg-brand-primary/20 text-brand-accent border-brand-primary/30 mb-4 inline-block rounded-full border px-4 py-2 text-sm font-medium">
              Our Services
            </span>
            <h2 className="font-display mb-4 text-4xl text-zinc-50 italic md:text-5xl">
              What We Offer
            </h2>
            <p className="mx-auto max-w-2xl text-xl text-zinc-400">
              Comprehensive creative services to bring your vision to life
            </p>
          </div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
            {Object.values(SERVICES).map((service, index) => (
              <Link
                key={service.href}
                href={service.href}
                className="group from-brand-deep hover:shadow-brand-primary/20 border-brand-dark/30 relative overflow-hidden rounded-3xl border bg-linear-to-br to-zinc-950 p-8 shadow-sm transition-all duration-500 hover:shadow-2xl"
              >
                <div className="from-brand-primary/10 to-brand-accent/10 absolute inset-0 bg-linear-to-br opacity-0 transition-opacity group-hover:opacity-100" />
                <div className="relative">
                  <span className="text-brand-accent mb-6 block text-5xl">
                    {index === 0 ? (
                      <FiCamera className="h-12 w-12" />
                    ) : index === 1 ? (
                      <FiVideo className="h-12 w-12" />
                    ) : (
                      <FiEdit3 className="h-12 w-12" />
                    )}
                  </span>
                  <h3 className="mb-3 text-2xl font-bold text-zinc-50">{service.name}</h3>
                  <p className="mb-6 text-zinc-400">{service.description}</p>
                  <span className="text-brand-accent group-hover:text-brand-primary inline-flex items-center font-medium">
                    Learn more
                    <svg
                      className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-2"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M9 5l7 7-7 7"
                      />
                    </svg>
                  </span>
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="from-brand-dark via-brand-primary to-brand-accent bg-linear-to-r px-4 py-20">
        <div className="container">
          <div className="grid grid-cols-2 gap-8 md:grid-cols-4">
            {[
              { value: "10+", label: "Years Experience" },
              { value: "5000+", label: "Projects Completed" },
              { value: "500+", label: "Happy Clients" },
              { value: "50+", label: "Team Members" },
            ].map((stat) => (
              <div key={stat.label} className="text-center">
                <div className="mb-2 text-4xl font-bold text-zinc-50 md:text-5xl">{stat.value}</div>
                <div className="text-zinc-50/80">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Creative Process Section */}
      <CreativeProcess />

      {/* Trusted Brands Section */}
      <TrustedBrands />

      {/* Testimonials Section */}
      <Testimonials />

      {/* FAQ Section */}
      <FAQSection />

      <CTASection
        title="Ready to Create Something Amazing?"
        description="Let's discuss your project and bring your vision to life"
        primaryButton={{ text: "Get a Free Quote", href: "/contact" }}
        secondaryButton={{ text: "Call Us Now", href: `tel:${COMPANY.mobile}` }}
      />
    </main>
  );
}
