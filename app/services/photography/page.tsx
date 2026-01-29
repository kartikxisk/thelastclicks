import Link from "next/link";
import { Metadata } from "next";
import { COMPANY, SERVICES } from "@/lib/constants";
import CTASection from "@/components/CTASection";

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
        <div className="absolute inset-0 bg-gradient-to-br from-amber-900 via-gray-900 to-orange-900" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-20" />
        
        <div className="absolute top-20 left-10 w-72 h-72 bg-amber-500 rounded-full blur-[128px] opacity-30 animate-pulse" />
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-orange-500 rounded-full blur-[128px] opacity-30 animate-pulse" />

        <div className="relative max-w-7xl mx-auto text-center">
          <span className="inline-block px-4 py-2 rounded-full bg-white/10 text-amber-300 text-sm font-medium mb-6 backdrop-blur-sm">
            📸 Professional Photography
          </span>
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
            Capture Every
            <br />
            <span className="bg-gradient-to-r from-amber-400 via-orange-400 to-amber-400 bg-clip-text text-transparent">
              Beautiful Moment
            </span>
          </h1>
          <p className="text-xl text-gray-300 max-w-2xl mx-auto mb-10">
            {photographyService.description}. Our expert photographers bring artistry and precision to every shot.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              href="/contact"
              className="px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-full font-semibold hover:shadow-2xl hover:shadow-amber-500/30 hover:scale-105 transition-all"
            >
              Book a Session
            </Link>
            <Link
              href="/portfolio"
              className="px-8 py-4 bg-white/10 text-white rounded-full font-semibold backdrop-blur-sm hover:bg-white/20 transition-all"
            >
              View Portfolio
            </Link>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="py-20 px-4 bg-gray-50">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              Photography Services
            </h2>
            <p className="text-gray-600 max-w-2xl mx-auto">
              From intimate portraits to grand celebrations, we cover it all
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {photographyService.categories.map((category) => (
              <div
                key={category.slug}
                className="group relative bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100"
              >
                <div className="aspect-[4/3] bg-gradient-to-br from-amber-100 via-orange-50 to-amber-100 flex items-center justify-center">
                  <span className="text-6xl group-hover:scale-110 transition-transform duration-300">
                    {category.icon}
                  </span>
                </div>
                <div className="p-6">
                  <h3 className="text-xl font-semibold text-gray-900 mb-2">{category.name}</h3>
                  <p className="text-gray-600 mb-4">
                    Professional {category.name.toLowerCase()} tailored to your unique needs and style.
                  </p>
                  <Link
                    href={`/services/photography/${category.slug}`}
                    className="inline-flex items-center text-amber-600 font-medium hover:text-amber-700 transition-colors"
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
      <section className="py-20 px-4">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              Why Choose Us?
            </h2>
            <p className="text-gray-600 max-w-2xl mx-auto">
              What sets our photography apart
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {[
              { icon: "🎨", title: "Artistic Vision", desc: "Creative compositions that tell your story" },
              { icon: "📷", title: "Pro Equipment", desc: "Latest cameras and lighting gear" },
              { icon: "⚡", title: "Quick Delivery", desc: "Get your photos within 48-72 hours" },
              { icon: "💯", title: "Satisfaction", desc: "100% satisfaction guaranteed" },
            ].map((item) => (
              <div key={item.title} className="text-center p-6">
                <span className="text-5xl mb-4 block">{item.icon}</span>
                <h3 className="text-lg font-semibold text-gray-900 mb-2">{item.title}</h3>
                <p className="text-gray-600">{item.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <CTASection
        title="Ready to Capture Your Moments?"
        description="Let's discuss your photography needs"
        primaryButton={{ text: "Get a Quote", href: "/contact" }}
        variant="amber"
      />
    </main>
  );
}
