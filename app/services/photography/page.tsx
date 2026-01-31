import Link from "next/link";
import { Metadata } from "next";
import { COMPANY, SERVICES } from "@/lib/constants";
import CTASection from "@/components/CTASection";
import Icon from "@/components/ui/IconMap";
import { FiCamera } from "react-icons/fi";
import { IoColorPaletteOutline } from "react-icons/io5";
import { BsCamera, BsLightning } from "react-icons/bs";
import { MdCheckCircle } from "react-icons/md";

export const metadata: Metadata = {
  title: `Photography Services | ${COMPANY.name}`,
  description: "Professional photography services for weddings, portraits, events, products, fashion, and corporate needs. Capturing your moments beautifully.",
};

export default function PhotographyServicePage() {
  const photographyService = SERVICES.photography;

  return (
    <main className="min-h-screen">
      {/* Hero Section */}
      <section className="relative pt-32 pb-20 px-4 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-20" />
        
        <div className="absolute top-20 left-10 w-72 h-72 bg-brand-primary rounded-full blur-[128px] opacity-30 animate-pulse" />
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-brand-accent rounded-full blur-[128px] opacity-30 animate-pulse" />

        <div className="relative max-w-7xl mx-auto text-center">
          <span className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-zinc-50/10 text-brand-accent text-sm font-medium mb-6 backdrop-blur-sm border border-brand-primary/30">
            <FiCamera className="w-4 h-4" />
            Professional Photography
          </span>
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-zinc-50 mb-6 leading-tight">
            Capture Every
            <br />
            <span className="bg-gradient-to-r from-brand-primary via-brand-accent to-brand-primary bg-clip-text text-transparent">
              Beautiful Moment
            </span>
          </h1>
          <p className="text-xl text-zinc-400 max-w-2xl mx-auto mb-10">
            {photographyService.description}. Our expert photographers bring artistry and precision to every shot.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              href="/contact"
              className="px-8 py-4 bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 rounded-full font-semibold hover:shadow-2xl hover:shadow-brand-primary/30 hover:scale-105 transition-all"
            >
              Book a Session
            </Link>
            <Link
              href="/portfolio"
              className="px-8 py-4 bg-zinc-50/10 text-zinc-50 rounded-full font-semibold backdrop-blur-sm hover:bg-zinc-50/20 transition-all border border-brand-primary/30"
            >
              View Portfolio
            </Link>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="py-20 px-4 bg-zinc-950">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-zinc-50 mb-4">
              Photography Services
            </h2>
            <p className="text-zinc-400 max-w-2xl mx-auto">
              From intimate portraits to grand celebrations, we cover it all
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {photographyService.categories.map((category) => (
              <div
                key={category.slug}
                className="group relative bg-gradient-to-br from-brand-deep to-zinc-950 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:shadow-brand-primary/20 transition-all duration-300 border border-brand-dark/30"
              >
                <div className="aspect-[4/3] bg-gradient-to-br from-brand-dark/30 via-brand-deep to-brand-primary/30 flex items-center justify-center">
                  <span className="text-brand-accent group-hover:scale-110 transition-transform duration-300">
                    <Icon name={category.icon} className="w-16 h-16" />
                  </span>
                </div>
                <div className="p-6">
                  <h3 className="text-xl font-semibold text-zinc-50 mb-2">{category.name}</h3>
                  <p className="text-zinc-400 mb-4">
                    Professional {category.name.toLowerCase()} tailored to your unique needs and style.
                  </p>
                  <Link
                    href={`/services/photography/${category.slug}`}
                    className="inline-flex items-center text-brand-accent font-medium hover:text-brand-primary transition-colors"
                  >
                    Learn more
                    <svg className="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                  </Link>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Why Choose Us */}
      <section className="py-20 px-4 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-zinc-50 mb-4">
              Why Choose Us?
            </h2>
            <p className="text-zinc-400 max-w-2xl mx-auto">
              What sets our photography apart
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {[
              { icon: IoColorPaletteOutline, title: "Artistic Vision", desc: "Creative compositions that tell your story" },
              { icon: BsCamera, title: "Pro Equipment", desc: "Latest cameras and lighting gear" },
              { icon: BsLightning, title: "Quick Delivery", desc: "Get your photos within 48-72 hours" },
              { icon: MdCheckCircle, title: "Satisfaction", desc: "100% satisfaction guaranteed" },
            ].map((item) => (
              <div key={item.title} className="text-center p-6 bg-zinc-950/50 rounded-2xl border border-brand-dark/30">
                <span className="text-brand-accent mb-4 block flex justify-center">
                  <item.icon className="w-12 h-12" />
                </span>
                <h3 className="text-lg font-semibold text-zinc-50 mb-2">{item.title}</h3>
                <p className="text-zinc-400">{item.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <CTASection
        title="Ready to Capture Your Moments?"
        description="Let's discuss your photography needs"
        primaryButton={{ text: "Get a Quote", href: "/contact" }}
        variant="dark"
      />
    </main>
  );
}
