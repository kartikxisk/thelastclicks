import Link from "next/link";
import { Metadata } from "next";
import { COMPANY, EDITORS } from "@/lib/constants";
import CTASection from "@/components/CTASection";

export const metadata: Metadata = {
  title: `Our Editors | ${COMPANY.name}`,
  description: "Meet our talented team of professional editors specializing in video editing, photo retouching, color grading, motion graphics, and more.",
};

export default function EditorsListPage() {
  return (
    <main className="min-h-screen">
      {/* Hero Section */}
      <section className="relative pt-32 pb-16 px-4 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-10" />
        
        {/* Animated Background Elements */}
        <div className="absolute top-40 left-20 w-64 h-64 bg-purple-500 rounded-full blur-[100px] opacity-20" />
        <div className="absolute bottom-20 right-20 w-80 h-80 bg-pink-500 rounded-full blur-[100px] opacity-20" />

        <div className="relative max-w-7xl mx-auto text-center">
          <Link
            href="/services/editing"
            className="inline-flex items-center text-purple-300 hover:text-purple-200 transition-colors mb-6"
          >
            <svg className="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
            Back to Editing Services
          </Link>
          <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6">
            Meet Our
            <span className="block bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
              Expert Editors
            </span>
          </h1>
          <p className="text-xl text-gray-300 max-w-2xl mx-auto">
            Talented professionals with years of experience in transforming raw footage into stunning visual stories
          </p>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-12 px-4 bg-white border-b border-gray-100">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {[
              { value: "6+", label: "Expert Editors" },
              { value: "40+", label: "Years Combined Experience" },
              { value: "3200+", label: "Projects Completed" },
              { value: "98%", label: "Client Satisfaction" },
            ].map((stat) => (
              <div key={stat.label} className="text-center">
                <div className="text-3xl md:text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                  {stat.value}
                </div>
                <div className="text-gray-600 text-sm mt-1">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Editors Grid */}
      <section className="py-20 px-4 bg-gray-50">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {EDITORS.map((editor) => (
              <div
                key={editor.id}
                className="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100"
              >
                {/* Image/Avatar Section */}
                <div className="aspect-[4/3] bg-gradient-to-br from-purple-100 via-pink-50 to-purple-100 relative overflow-hidden">
                  <div className="absolute inset-0 flex items-center justify-center">
                    <div className="w-32 h-32 rounded-full bg-gradient-to-br from-purple-200 to-pink-200 flex items-center justify-center text-6xl shadow-xl group-hover:scale-110 transition-transform duration-500">
                      👤
                    </div>
                  </div>
                  {/* Decorative Elements */}
                  <div className="absolute top-4 right-4 px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-sm font-medium text-purple-600">
                    {editor.experience}
                  </div>
                  <div className="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-white to-transparent" />
                </div>

                {/* Content Section */}
                <div className="p-6 -mt-6 relative">
                  <div className="flex items-center justify-between mb-2">
                    <h3 className="text-xl font-bold text-gray-900">{editor.name}</h3>
                    <div className="flex items-center text-sm text-gray-500">
                      <svg className="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                      {editor.projects}+ projects
                    </div>
                  </div>
                  
                  <p className="text-purple-600 font-medium mb-3">{editor.role}</p>
                  <p className="text-gray-600 text-sm mb-4 line-clamp-2">{editor.bio}</p>

                  {/* Specializations */}
                  <div className="mb-5">
                    <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                      Specializations
                    </p>
                    <div className="flex flex-wrap gap-2">
                      {editor.specialization.map((spec) => (
                        <span
                          key={spec}
                          className="px-3 py-1 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-full text-xs font-medium border border-purple-100"
                        >
                          {spec}
                        </span>
                      ))}
                    </div>
                  </div>

                  {/* Action Buttons */}
                  <div className="flex gap-3">
                    <Link
                      href={`/services/editing/editors/${editor.id}`}
                      className="flex-1 text-center py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-medium hover:shadow-lg hover:shadow-purple-500/30 transition-all"
                    >
                      View Profile
                    </Link>
                    <button
                      className="px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors"
                      aria-label="Save editor"
                    >
                      <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Expertise Areas */}
      <section className="py-20 px-4 bg-white">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              Areas of Expertise
            </h2>
            <p className="text-gray-600 max-w-2xl mx-auto">
              Our team covers all aspects of professional editing
            </p>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {[
              { name: "Video Editing", icon: "🎬", color: "from-red-500 to-orange-500" },
              { name: "Color Grading", icon: "🎨", color: "from-purple-500 to-pink-500" },
              { name: "Photo Retouching", icon: "✨", color: "from-blue-500 to-cyan-500" },
              { name: "Motion Graphics", icon: "💫", color: "from-green-500 to-teal-500" },
              { name: "Audio Mixing", icon: "🎧", color: "from-yellow-500 to-orange-500" },
              { name: "VFX & Animation", icon: "🌟", color: "from-indigo-500 to-purple-500" },
            ].map((expertise) => (
              <div
                key={expertise.name}
                className="group relative p-6 rounded-2xl bg-gray-50 hover:bg-white hover:shadow-xl transition-all duration-300 text-center cursor-pointer"
              >
                <div className={`absolute inset-0 bg-gradient-to-br ${expertise.color} opacity-0 group-hover:opacity-5 rounded-2xl transition-opacity`} />
                <span className="text-4xl mb-3 block">{expertise.icon}</span>
                <p className="text-sm font-medium text-gray-900">{expertise.name}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Hiring CTA */}
      <section className="py-20 px-4 bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900">
        <div className="max-w-4xl mx-auto text-center">
          <span className="inline-block px-4 py-2 rounded-full bg-white/10 text-purple-300 text-sm font-medium mb-6">
            🚀 Join Our Team
          </span>
          <h2 className="text-3xl md:text-4xl font-bold text-white mb-6">
            Are You a Talented Editor?
          </h2>
          <p className="text-gray-300 text-lg mb-8 max-w-2xl mx-auto">
            We're always looking for skilled editors to join our team. If you have a passion for visual storytelling, we'd love to hear from you.
          </p>
          <Link
            href="/careers"
            className="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-full font-semibold hover:shadow-2xl hover:shadow-purple-500/30 hover:scale-105 transition-all"
          >
            Apply Now
            <svg className="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </Link>
        </div>
      </section>

      <CTASection
        title="Ready to Work with Our Editors?"
        description="Get in touch to discuss your project and find the perfect editor for your needs"
        primaryButton={{ text: "Start a Project", href: "/contact" }}
        secondaryButton={{ text: COMPANY.email, href: `mailto:${COMPANY.email}` }}
        variant="purple"
      />
    </main>
  );
}
