import Link from "next/link";
import { COMPANY, SERVICES } from "@/lib/constants";
import CTASection from "@/components/CTASection";
import CreativeProcess from "@/components/CreativeProcess";
import TrustedBrands from "@/components/TrustedBrands";
import Testimonials from "@/components/Testimonials";
import FAQSection from "@/components/FAQSection";

export default function Homepage() {
  return (
    <main className="min-h-screen bg-zinc-950">
      {/* Hero Section */}
      <section className="relative min-h-screen flex items-center justify-center px-4 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-[#280905] via-zinc-950 to-[#740A03]" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-10" />
        
        {/* Animated Gradient Orbs */}
        <div className="absolute top-20 left-10 w-72 h-72 bg-[#C3110C] rounded-full blur-[128px] opacity-20 animate-pulse" />
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-[#E6501B] rounded-full blur-[128px] opacity-20 animate-pulse" />
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#740A03] rounded-full blur-[200px] opacity-15" />

        <div className="relative max-w-7xl mx-auto text-center z-10">
          <span className="inline-block px-4 py-2 rounded-full bg-zinc-50/10 text-[#E6501B] text-sm font-medium mb-6 backdrop-blur-sm border border-[#C3110C]/30">
            ✨ {COMPANY.tagline}
          </span>
          <h1 className="text-5xl md:text-7xl lg:text-8xl font-display text-zinc-50 mb-6 leading-tight">
            We Create
            <br />
            <span className="bg-gradient-to-r from-[#C3110C] via-[#E6501B] to-[#C3110C] bg-clip-text text-transparent">
              Visual Magic
            </span>
          </h1>
          <p className="text-xl md:text-2xl text-zinc-400 max-w-3xl mx-auto mb-10">
            Professional photography, cinematic videography, and expert editing services that transform your moments into timeless memories.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              href="/services"
              className="px-8 py-4 bg-gradient-to-r from-[#C3110C] to-[#E6501B] text-zinc-50 rounded-full font-semibold text-lg hover:shadow-2xl hover:shadow-[#C3110C]/30 hover:scale-105 transition-all"
            >
              Explore Services
            </Link>
            <Link
              href="/portfolio"
              className="px-8 py-4 bg-zinc-50/10 text-zinc-50 rounded-full font-semibold text-lg backdrop-blur-sm hover:bg-zinc-50/20 transition-all border border-[#C3110C]/30"
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
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <span className="inline-block px-4 py-2 rounded-full bg-[#C3110C]/20 text-[#E6501B] text-sm font-medium mb-4 border border-[#C3110C]/30">
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
                className="group relative bg-gradient-to-br from-[#280905] to-zinc-950 rounded-3xl p-8 shadow-sm hover:shadow-2xl hover:shadow-[#C3110C]/20 transition-all duration-500 border border-[#740A03]/30 overflow-hidden"
              >
                <div className="absolute inset-0 bg-gradient-to-br from-[#C3110C]/10 to-[#E6501B]/10 opacity-0 group-hover:opacity-100 transition-opacity" />
                <div className="relative">
                  <span className="text-5xl mb-6 block">
                    {index === 0 ? "📸" : index === 1 ? "🎬" : "✨"}
                  </span>
                  <h3 className="text-2xl font-bold text-zinc-50 mb-3">{service.name}</h3>
                  <p className="text-zinc-400 mb-6">{service.description}</p>
                  <span className="inline-flex items-center text-[#E6501B] font-medium group-hover:text-[#C3110C]">
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
      <section className="py-20 px-4 bg-gradient-to-r from-[#740A03] via-[#C3110C] to-[#E6501B]">
        <div className="max-w-7xl mx-auto">
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

      {/* Why Choose Us */}
      <section className="py-24 px-4 bg-zinc-950">
        <div className="max-w-7xl mx-auto">
          <div className="grid lg:grid-cols-2 gap-16 items-center">
            <div>
              <span className="inline-block px-4 py-2 rounded-full bg-[#C3110C]/20 text-[#E6501B] text-sm font-medium mb-4 border border-[#C3110C]/30">
                Why Choose Us
              </span>
              <h2 className="text-4xl md:text-5xl font-display italic text-zinc-50 mb-6">
                We Bring Your Vision to Life
              </h2>
              <p className="text-xl text-zinc-400 mb-8">
                With years of experience and a passion for creativity, we deliver exceptional results that exceed expectations.
              </p>
              <div className="space-y-6">
                {[
                  { icon: "🎯", title: "Expert Team", desc: "Skilled professionals with years of industry experience" },
                  { icon: "⚡", title: "Fast Delivery", desc: "Quick turnaround without compromising quality" },
                  { icon: "💎", title: "Premium Quality", desc: "4K/8K support with professional-grade output" },
                  { icon: "🤝", title: "Dedicated Support", desc: "Personalized attention throughout your project" },
                ].map((item) => (
                  <div key={item.title} className="flex items-start space-x-4">
                    <span className="text-3xl">{item.icon}</span>
                    <div>
                      <h4 className="text-lg font-semibold text-zinc-50">{item.title}</h4>
                      <p className="text-zinc-400">{item.desc}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
            <div className="relative">
              <div className="aspect-square rounded-3xl bg-gradient-to-br from-[#280905] via-[#740A03] to-[#280905] flex items-center justify-center border border-[#740A03]/30">
                <span className="text-[150px]">🎥</span>
              </div>
              <div className="absolute -bottom-6 -left-6 w-32 h-32 bg-gradient-to-br from-[#C3110C] to-[#E6501B] rounded-2xl flex items-center justify-center text-zinc-50 text-4xl shadow-2xl shadow-[#C3110C]/30">
                📸
              </div>
              <div className="absolute -top-6 -right-6 w-24 h-24 bg-zinc-950 rounded-2xl flex items-center justify-center text-3xl shadow-xl border border-[#740A03]/30">
                ✨
              </div>
            </div>
          </div>
        </div>
      </section>

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