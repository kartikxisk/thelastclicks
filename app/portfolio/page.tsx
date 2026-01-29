import Link from "next/link";
import { Metadata } from "next";
import { COMPANY } from "@/lib/constants";
import CTASection from "@/components/CTASection";

export const metadata: Metadata = {
  title: `Portfolio | ${COMPANY.name}`,
  description: `Explore our portfolio of stunning photography, videography, and editing work. See the quality and creativity we bring to every project.`,
};

const portfolioItems = [
  { id: 1, title: "Wedding in Udaipur", category: "Wedding Photography", icon: "💒" },
  { id: 2, title: "Corporate Brand Film", category: "Videography", icon: "🎬" },
  { id: 3, title: "Fashion Editorial", category: "Fashion Photography", icon: "👗" },
  { id: 4, title: "Product Launch Video", category: "Commercial", icon: "📺" },
  { id: 5, title: "Portrait Series", category: "Portrait Photography", icon: "👤" },
  { id: 6, title: "Music Video", category: "Videography", icon: "🎵" },
  { id: 7, title: "E-commerce Shoot", category: "Product Photography", icon: "📦" },
  { id: 8, title: "Documentary Film", category: "Videography", icon: "📽️" },
  { id: 9, title: "Event Coverage", category: "Event Photography", icon: "🎉" },
];

const categories = ["All", "Photography", "Videography", "Commercial", "Wedding"];

export default function PortfolioPage() {
  return (
    <main className="min-h-screen">
      {/* Hero Section */}
      <section className="relative pt-32 pb-20 px-4 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-20" />
        
        <div className="absolute top-20 left-10 w-72 h-72 bg-purple-500 rounded-full blur-[128px] opacity-30 animate-pulse" />
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-pink-500 rounded-full blur-[128px] opacity-30 animate-pulse" />

        <div className="relative max-w-7xl mx-auto text-center">
          <span className="inline-block px-4 py-2 rounded-full bg-white/10 text-purple-300 text-sm font-medium mb-6 backdrop-blur-sm">
            🖼️ Our Work
          </span>
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
            Our
            <br />
            <span className="bg-gradient-to-r from-purple-400 via-pink-400 to-purple-400 bg-clip-text text-transparent">
              Portfolio
            </span>
          </h1>
          <p className="text-xl text-gray-300 max-w-2xl mx-auto">
            A showcase of our best work across photography, videography, and creative editing
          </p>
        </div>
      </section>

      {/* Filter Tabs */}
      <section className="py-8 px-4 bg-white border-b border-gray-100 sticky top-16 z-40">
        <div className="max-w-7xl mx-auto">
          <div className="flex flex-wrap gap-3 justify-center">
            {categories.map((category) => (
              <button
                key={category}
                className={`px-6 py-2 rounded-full font-medium transition-all ${
                  category === "All"
                    ? "bg-gradient-to-r from-purple-600 to-pink-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-purple-100 hover:text-purple-600"
                }`}
              >
                {category}
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* Portfolio Grid */}
      <section className="py-20 px-4 bg-gray-50">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {portfolioItems.map((item) => (
              <div
                key={item.id}
                className="group relative bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100"
              >
                <div className="aspect-[4/3] bg-gradient-to-br from-purple-100 via-pink-50 to-purple-100 flex items-center justify-center relative overflow-hidden">
                  <span className="text-7xl group-hover:scale-110 transition-transform duration-500">
                    {item.icon}
                  </span>
                  <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                  <div className="absolute bottom-4 left-4 right-4 translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                    <span className="px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-sm font-medium text-purple-600">
                      View Project
                    </span>
                  </div>
                </div>
                <div className="p-6">
                  <span className="text-sm text-purple-600 font-medium">{item.category}</span>
                  <h3 className="text-xl font-semibold text-gray-900 mt-1">{item.title}</h3>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-16 px-4 bg-white">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {[
              { value: "500+", label: "Projects Completed" },
              { value: "200+", label: "Happy Clients" },
              { value: "15+", label: "Industry Awards" },
              { value: "10+", label: "Years Experience" },
            ].map((stat) => (
              <div key={stat.label} className="text-center">
                <div className="text-4xl md:text-5xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                  {stat.value}
                </div>
                <div className="text-gray-600 mt-2">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <CTASection
        title="Want to See Your Project Here?"
        description="Let's create something amazing together"
        primaryButton={{ text: "Start Your Project", href: "/contact" }}
        variant="gradient"
      />
    </main>
  );
}
