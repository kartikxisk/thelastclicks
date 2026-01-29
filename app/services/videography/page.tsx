import Link from "next/link";
import { Metadata } from "next";
import { COMPANY, SERVICES } from "@/lib/constants";
import CTASection from "@/components/CTASection";

export const metadata: Metadata = {
  title: `Videography Services | ${COMPANY.name}`,
  description: "Cinematic video production for weddings, corporate events, music videos, documentaries, and commercials. Creating visual stories that captivate.",
};

export default function VideographyServicePage() {
  const videographyService = SERVICES.videography;

  return (
    <main className="min-h-screen">
      {/* Hero Section */}
      <section className="relative pt-32 pb-20 px-4 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-blue-900 via-gray-900 to-indigo-900" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-20" />
        
        <div className="absolute top-20 left-10 w-72 h-72 bg-blue-500 rounded-full blur-[128px] opacity-30 animate-pulse" />
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-indigo-500 rounded-full blur-[128px] opacity-30 animate-pulse" />

        <div className="relative max-w-7xl mx-auto text-center">
          <span className="inline-block px-4 py-2 rounded-full bg-white/10 text-blue-300 text-sm font-medium mb-6 backdrop-blur-sm">
            🎬 Cinematic Videography
          </span>
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
            Stories That
            <br />
            <span className="bg-gradient-to-r from-blue-400 via-indigo-400 to-blue-400 bg-clip-text text-transparent">
              Move People
            </span>
          </h1>
          <p className="text-xl text-gray-300 max-w-2xl mx-auto mb-10">
            {videographyService.description}. Our cinematographers craft visual narratives that leave lasting impressions.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              href="/contact"
              className="px-8 py-4 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-full font-semibold hover:shadow-2xl hover:shadow-blue-500/30 hover:scale-105 transition-all"
            >
              Start Your Project
            </Link>
            <Link
              href="/portfolio"
              className="px-8 py-4 bg-white/10 text-white rounded-full font-semibold backdrop-blur-sm hover:bg-white/20 transition-all"
            >
              Watch Showreel
            </Link>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="py-20 px-4 bg-gray-50">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              Videography Services
            </h2>
            <p className="text-gray-600 max-w-2xl mx-auto">
              From wedding films to commercial productions, we bring your vision to life
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {videographyService.categories.map((category) => (
              <div
                key={category.slug}
                className="group relative bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100"
              >
                <div className="aspect-video bg-gradient-to-br from-blue-100 via-indigo-50 to-blue-100 flex items-center justify-center">
                  <span className="text-6xl group-hover:scale-110 transition-transform duration-300">
                    {category.icon}
                  </span>
                </div>
                <div className="p-6">
                  <h3 className="text-xl font-semibold text-gray-900 mb-2">{category.name}</h3>
                  <p className="text-gray-600 mb-4">
                    Cinematic {category.name.toLowerCase()} that capture the essence of your story.
                  </p>
                  <Link
                    href={`/services/videography/${category.slug}`}
                    className="inline-flex items-center text-blue-600 font-medium hover:text-blue-700 transition-colors"
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

      {/* Process Section */}
      <section className="py-20 px-4">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              Our Process
            </h2>
            <p className="text-gray-600 max-w-2xl mx-auto">
              How we bring your vision to the screen
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            {[
              { step: "01", title: "Consultation", desc: "Understanding your vision and requirements" },
              { step: "02", title: "Pre-Production", desc: "Planning, scripting, and storyboarding" },
              { step: "03", title: "Production", desc: "Professional filming with latest equipment" },
              { step: "04", title: "Post-Production", desc: "Editing, color grading, and final delivery" },
            ].map((item, index) => (
              <div key={item.step} className="relative">
                <div className="text-center p-6 bg-white rounded-2xl shadow-sm border border-gray-100">
                  <span className="text-5xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                    {item.step}
                  </span>
                  <h3 className="text-lg font-semibold text-gray-900 mt-4 mb-2">{item.title}</h3>
                  <p className="text-gray-600 text-sm">{item.desc}</p>
                </div>
                {index < 3 && (
                  <div className="hidden md:block absolute top-1/2 -right-4 transform -translate-y-1/2 text-gray-300 text-2xl">
                    →
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Equipment Section */}
      <section className="py-20 px-4 bg-gray-50">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              Professional Equipment
            </h2>
            <p className="text-gray-600 max-w-2xl mx-auto">
              We use industry-leading gear for exceptional quality
            </p>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            {[
              { icon: "🎥", name: "Cinema Cameras" },
              { icon: "🎤", name: "Pro Audio Gear" },
              { icon: "💡", name: "Studio Lighting" },
              { icon: "🚁", name: "Drone Footage" },
              { icon: "🎞️", name: "Gimbal Stabilizers" },
              { icon: "🖥️", name: "4K/8K Editing" },
              { icon: "🎨", name: "Color Suite" },
              { icon: "🔊", name: "Sound Studio" },
            ].map((item) => (
              <div key={item.name} className="text-center p-6 bg-white rounded-xl shadow-sm border border-gray-100">
                <span className="text-4xl mb-3 block">{item.icon}</span>
                <p className="text-gray-700 font-medium">{item.name}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <CTASection
        title="Ready to Tell Your Story?"
        description="Let's create something cinematic together"
        primaryButton={{ text: "Get Started", href: "/contact" }}
        variant="blue"
      />
    </main>
  );
}
