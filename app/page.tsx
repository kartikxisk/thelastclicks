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
      <section className="relative min-h-screen flex items-center justify-center px-4 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-10" />
        
        {/* Animated Gradient Orbs */}
        <div className="absolute top-20 left-10 w-72 h-72 bg-brand-primary rounded-full blur-[128px] opacity-20 animate-pulse" />
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-brand-accent rounded-full blur-[128px] opacity-20 animate-pulse" />
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-brand-dark rounded-full blur-[200px] opacity-15" />

        <div className="container relative text-center z-10">
          <span className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-zinc-50/10 text-brand-accent text-sm font-medium mb-6 backdrop-blur-sm border border-brand-primary/30">
            <Icon name="HiSparkles" className="w-4 h-4" />
            {COMPANY.tagline}
          </span>
          <h1 className="text-5xl md:text-7xl lg:text-8xl font-display text-zinc-50 mb-6 leading-tight">
            We Create
            <br />
            <span className="bg-gradient-to-r from-brand-primary via-brand-accent to-brand-primary bg-clip-text text-transparent">
              Visual Magic
            </span>
          </h1>
          <p className="text-xl md:text-2xl text-zinc-400 max-w-3xl mx-auto mb-10">
            Professional photography, cinematic videography, and expert editing services that transform your moments into timeless memories.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              href="/services"
              className="px-8 py-4 bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 rounded-full font-semibold text-lg hover:shadow-2xl hover:shadow-brand-primary/30 hover:scale-105 transition-all"
            >
              Explore Services
            </Link>
            <Link
              href="/portfolio"
              className="px-8 py-4 bg-zinc-50/10 text-zinc-50 rounded-full font-semibold text-lg backdrop-blur-sm hover:bg-zinc-50/20 transition-all border border-brand-primary/30"
            >
              View Our Work
            </Link>
          </div>
        </div>

        {/* Scroll Indicator */}
        <div className="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
          <svg className="w-6 h-6 text-zinc-50/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
        </div>
      </section>

      {/* Services Section */}
      <section className="py-24 px-4 bg-zinc-950">
        <div className="container">
          <div className="text-center mb-16">
            <span className="inline-block px-4 py-2 rounded-full bg-brand-primary/20 text-brand-accent text-sm font-medium mb-4 border border-brand-primary/30">
              Our Services
            </span>
            <h2 className="text-4xl md:text-5xl font-display italic text-zinc-50 mb-4">
              What We Offer
            </h2>
            <p className="text-xl text-zinc-400 max-w-2xl mx-auto">
              Comprehensive creative services to bring your vision to life
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {Object.values(SERVICES).map((service, index) => (
              <Link
                key={service.href}
                href={service.href}
                className="group relative bg-gradient-to-br from-brand-deep to-zinc-950 rounded-3xl p-8 shadow-sm hover:shadow-2xl hover:shadow-brand-primary/20 transition-all duration-500 border border-brand-dark/30 overflow-hidden"
              >
                <div className="absolute inset-0 bg-gradient-to-br from-brand-primary/10 to-brand-accent/10 opacity-0 group-hover:opacity-100 transition-opacity" />
                <div className="relative">
                  <span className="text-5xl mb-6 block text-brand-accent">
                    {index === 0 ? <FiCamera className="w-12 h-12" /> : index === 1 ? <FiVideo className="w-12 h-12" /> : <FiEdit3 className="w-12 h-12" />}
                  </span>
                  <h3 className="text-2xl font-bold text-zinc-50 mb-3">{service.name}</h3>
                  <p className="text-zinc-400 mb-6">{service.description}</p>
                  <span className="inline-flex items-center text-brand-accent font-medium group-hover:text-brand-primary">
                    Learn more
                    <svg className="w-4 h-4 ml-2 group-hover:translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                  </span>
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-20 px-4 bg-gradient-to-r from-brand-dark via-brand-primary to-brand-accent">
        <div className="container">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {[
              { value: "10+", label: "Years Experience" },
              { value: "5000+", label: "Projects Completed" },
              { value: "500+", label: "Happy Clients" },
              { value: "50+", label: "Team Members" },
            ].map((stat) => (
              <div key={stat.label} className="text-center">
                <div className="text-4xl md:text-5xl font-bold text-zinc-50 mb-2">
                  {stat.value}
                </div>
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
        variant="dark"
      />
    </main>
  );
}