import { Metadata } from "next";
import { COMPANY } from "@/lib/constants";
import CTASection from "@/components/CTASection";

export const metadata: Metadata = {
  title: `About Us | ${COMPANY.name}`,
  description: `Learn about ${COMPANY.name} - our story, mission, and the passionate team behind capturing your most precious moments.`,
};

const stats = [
  { value: "10+", label: "Years Experience" },
  { value: "5000+", label: "Projects Completed" },
  { value: "500+", label: "Happy Clients" },
  { value: "50+", label: "Team Members" },
];

const values = [
  {
    icon: "🎯",
    title: "Excellence",
    description: "We strive for perfection in every frame we capture and every edit we make.",
  },
  {
    icon: "💡",
    title: "Creativity",
    description: "Pushing boundaries to create unique and memorable visual experiences.",
  },
  {
    icon: "🤝",
    title: "Trust",
    description: "Building lasting relationships through transparency and reliability.",
  },
  {
    icon: "⚡",
    title: "Innovation",
    description: "Embracing latest technology to deliver cutting-edge results.",
  },
];

const team = [
  {
    name: "Rajesh Kumar",
    role: "Founder & Lead Photographer",
    bio: "With 15+ years of experience, Rajesh founded The Last Clicks to bring professional photography to everyone.",
  },
  {
    name: "Meera Sharma",
    role: "Creative Director",
    bio: "Meera leads our creative vision, ensuring every project tells a compelling visual story.",
  },
  {
    name: "Arjun Patel",
    role: "Head of Videography",
    bio: "Award-winning cinematographer with expertise in wedding and commercial films.",
  },
];

export default function AboutPage() {
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
            ✨ Our Story
          </span>
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
            About
            <br />
            <span className="bg-gradient-to-r from-purple-400 via-pink-400 to-purple-400 bg-clip-text text-transparent">
              {COMPANY.name}
            </span>
          </h1>
          <p className="text-xl text-gray-300 max-w-2xl mx-auto">
            {COMPANY.tagline}. We are passionate about preserving your precious moments through stunning visual storytelling.
          </p>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-16 px-4 bg-white border-b border-gray-100">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {stats.map((stat) => (
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

      {/* Our Story */}
      <section className="py-20 px-4 bg-gray-50">
        <div className="max-w-7xl mx-auto">
          <div className="grid lg:grid-cols-2 gap-12 items-center">
            <div>
              <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                Our Journey
              </h2>
              <div className="space-y-4 text-gray-600">
                <p>
                  Founded in 2015, {COMPANY.name} began as a small passion project by a group of photography enthusiasts who believed that every moment deserves to be captured beautifully.
                </p>
                <p>
                  Over the years, we have grown into a full-service creative agency, offering photography, videography, and professional editing services. Our team has expanded to include some of the most talented artists in the industry.
                </p>
                <p>
                  Today, we are proud to have served thousands of clients, from intimate personal events to large corporate projects. Our commitment to quality and creativity remains at the heart of everything we do.
                </p>
              </div>
            </div>
            <div className="relative">
              <div className="aspect-square rounded-3xl bg-gradient-to-br from-purple-200 via-pink-100 to-purple-200 flex items-center justify-center">
                <span className="text-9xl">📸</span>
              </div>
              <div className="absolute -bottom-6 -right-6 w-32 h-32 bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center text-white text-4xl shadow-2xl">
                🎬
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Our Values */}
      <section className="py-20 px-4">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              Our Values
            </h2>
            <p className="text-gray-600 max-w-2xl mx-auto">
              The principles that guide us in delivering exceptional results
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {values.map((value) => (
              <div
                key={value.title}
                className="text-center p-8 bg-white rounded-2xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100"
              >
                <span className="text-5xl mb-4 block">{value.icon}</span>
                <h3 className="text-xl font-semibold text-gray-900 mb-2">{value.title}</h3>
                <p className="text-gray-600">{value.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Team Section */}
      <section className="py-20 px-4 bg-gray-50">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              Meet Our Team
            </h2>
            <p className="text-gray-600 max-w-2xl mx-auto">
              The creative minds behind your stunning visuals
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {team.map((member) => (
              <div
                key={member.name}
                className="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow border border-gray-100"
              >
                <div className="aspect-[4/3] bg-gradient-to-br from-purple-100 via-pink-50 to-purple-100 flex items-center justify-center">
                  <div className="w-24 h-24 rounded-full bg-gradient-to-br from-purple-200 to-pink-200 flex items-center justify-center text-5xl">
                    👤
                  </div>
                </div>
                <div className="p-6">
                  <h3 className="text-xl font-semibold text-gray-900 mb-1">{member.name}</h3>
                  <p className="text-purple-600 font-medium mb-3">{member.role}</p>
                  <p className="text-gray-600 text-sm">{member.bio}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <CTASection
        title="Let's Create Something Beautiful Together"
        description="Ready to capture your next memorable moment?"
        primaryButton={{ text: "Get in Touch", href: "/contact" }}
        variant="gradient"
      />
    </main>
  );
}
