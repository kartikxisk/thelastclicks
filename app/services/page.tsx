import Link from "next/link";
import { Metadata } from "next";
import { COMPANY, SERVICES } from "@/lib/constants";
import CTASection from "@/components/CTASection";

export const metadata: Metadata = {
  title: `Our Services | ${COMPANY.name}`,
  description: "Explore our professional photography, videography, and editing services. We capture and create stunning visual content for all occasions.",
};

export default function ServicesPage() {
  const services = Object.values(SERVICES);

  return (
    <main className="min-h-screen">
      {/* Hero Section */}
      <section className="relative pt-32 pb-20 px-4 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-purple-900 via-gray-900 to-pink-900" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-20" />
        
        <div className="absolute top-20 left-10 w-72 h-72 bg-purple-500 rounded-full blur-[128px] opacity-30 animate-pulse" />
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-pink-500 rounded-full blur-[128px] opacity-30 animate-pulse" />

        <div className="relative max-w-7xl mx-auto text-center">
          <span className="inline-block px-4 py-2 rounded-full bg-white/10 text-purple-300 text-sm font-medium mb-6 backdrop-blur-sm">
            🎯 Professional Services
          </span>
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
            Our Creative
            <br />
            <span className="bg-gradient-to-r from-purple-400 via-pink-400 to-purple-400 bg-clip-text text-transparent">
              Services
            </span>
          </h1>
          <p className="text-xl text-gray-300 max-w-2xl mx-auto mb-10">
            From capturing your precious moments to transforming them into stunning visual stories, we offer comprehensive creative services.
          </p>
        </div>
      </section>

      {/* Services Grid */}
      <section className="py-20 px-4 bg-gray-50">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {services.map((service, index) => (
              <div
                key={service.href}
                className="group relative bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100"
              >
                {/* Card Header */}
                <div className="relative h-48 bg-gradient-to-br from-purple-600 to-pink-600 overflow-hidden">
                  <div className="absolute inset-0 bg-black/20" />
                  <div className="absolute inset-0 flex items-center justify-center">
                    <span className="text-7xl opacity-50 group-hover:scale-110 transition-transform duration-500">
                      {index === 0 ? "📸" : index === 1 ? "🎬" : "✨"}
                    </span>
                  </div>
                  <div className="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-white to-transparent" />
                </div>

                {/* Card Content */}
                <div className="p-8 -mt-8 relative">
                  <h2 className="text-2xl font-bold text-gray-900 mb-3">{service.name}</h2>
                  <p className="text-gray-600 mb-6">{service.description}</p>

                  {/* Categories Preview */}
                  <div className="flex flex-wrap gap-2 mb-6">
                    {service.categories.slice(0, 3).map((category) => (
                      <span
                        key={category.slug}
                        className="px-3 py-1 bg-purple-50 text-purple-600 rounded-full text-sm font-medium"
                      >
                        {category.icon} {category.name}
                      </span>
                    ))}
                    {service.categories.length > 3 && (
                      <span className="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">
                        +{service.categories.length - 3} more
                      </span>
                    )}
                  </div>

                  <Link
                    href={service.href}
                    className="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-full font-medium hover:shadow-lg hover:scale-105 transition-all"
                  >
                    Explore {service.name}
                    <svg className="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                  </Link>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <CTASection
        title="Ready to Get Started?"
        description="Let's discuss your project and bring your vision to life"
        primaryButton={{ text: "Contact Us Today", href: "/contact" }}
        variant="light"
      />
    </main>
  );
}
