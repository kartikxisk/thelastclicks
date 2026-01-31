import { Metadata } from "next";
import { COMPANY } from "@/lib/constants";
import CTASection from "@/components/CTASection";
import { FiCamera, FiVideo, FiUser } from "react-icons/fi";
import { BiTargetLock } from "react-icons/bi";
import { BsLightbulb, BsLightning } from "react-icons/bs";
import { MdOutlineHandshake } from "react-icons/md";
import { HiSparkles } from "react-icons/hi";

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
    icon: BiTargetLock,
    title: "Excellence",
    description: "We strive for perfection in every frame we capture and every edit we make.",
  },
  {
    icon: BsLightbulb,
    title: "Creativity",
    description: "Pushing boundaries to create unique and memorable visual experiences.",
  },
  {
    icon: MdOutlineHandshake,
    title: "Trust",
    description: "Building lasting relationships through transparency and reliability.",
  },
  {
    icon: BsLightning,
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
        <div className="absolute inset-0 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-20" />
        
        <div className="absolute top-20 left-10 w-72 h-72 bg-brand-primary rounded-full blur-[128px] opacity-30 animate-pulse" />
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-brand-accent rounded-full blur-[128px] opacity-30 animate-pulse" />

        <div className="relative max-w-7xl mx-auto text-center">
          <span className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-zinc-50/10 text-brand-accent text-sm font-medium mb-6 backdrop-blur-sm border border-brand-primary/30">
            <HiSparkles className="w-4 h-4" />
            Our Story
          </span>
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-zinc-50 mb-6 leading-tight">
            About
            <br />
            <span className="bg-gradient-to-r from-brand-primary via-brand-accent to-brand-primary bg-clip-text text-transparent">
              {COMPANY.name}
            </span>
          </h1>
          <p className="text-xl text-zinc-400 max-w-2xl mx-auto">
            {COMPANY.tagline}. We are passionate about preserving your precious moments through stunning visual storytelling.
          </p>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-16 px-4 bg-zinc-950 border-b border-brand-dark/30">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {stats.map((stat) => (
              <div key={stat.label} className="text-center">
                <div className="text-4xl md:text-5xl font-bold bg-gradient-to-r from-brand-primary to-brand-accent bg-clip-text text-transparent">
                  {stat.value}
                </div>
                <div className="text-zinc-400 mt-2">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Our Story */}
      <section className="py-20 px-4 bg-zinc-950">
        <div className="max-w-7xl mx-auto">
          <div className="grid lg:grid-cols-2 gap-12 items-center">
            <div>
              <h2 className="text-3xl md:text-4xl font-bold text-zinc-50 mb-6">
                Our Journey
              </h2>
              <div className="space-y-4 text-zinc-400">
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
              <div className="aspect-square rounded-3xl bg-gradient-to-br from-brand-deep via-brand-dark to-brand-deep flex items-center justify-center border border-brand-dark/30">
                <FiCamera className="w-32 h-32 text-brand-accent/50" />
              </div>
              <div className="absolute -bottom-6 -right-6 w-32 h-32 bg-gradient-to-br from-brand-primary to-brand-accent rounded-2xl flex items-center justify-center text-zinc-50 shadow-2xl shadow-brand-primary/30">
                <FiVideo className="w-12 h-12" />
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Our Values */}
      <section className="py-20 px-4 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-zinc-50 mb-4">
              Our Values
            </h2>
            <p className="text-zinc-400 max-w-2xl mx-auto">
              The principles that guide us in delivering exceptional results
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {values.map((value) => (
              <div
                key={value.title}
                className="text-center p-8 bg-zinc-950/50 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-brand-primary/20 transition-shadow border border-brand-dark/30"
              >
                <span className="text-brand-accent mb-4 flex justify-center">
                  <value.icon className="w-12 h-12" />
                </span>
                <h3 className="text-xl font-semibold text-zinc-50 mb-2">{value.title}</h3>
                <p className="text-zinc-400">{value.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Team Section */}
      <section className="py-20 px-4 bg-zinc-950">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-zinc-50 mb-4">
              Meet Our Team
            </h2>
            <p className="text-zinc-400 max-w-2xl mx-auto">
              The creative minds behind your stunning visuals
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {team.map((member) => (
              <div
                key={member.name}
                className="bg-gradient-to-br from-brand-deep to-zinc-950 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:shadow-brand-primary/20 transition-shadow border border-brand-dark/30"
              >
                <div className="aspect-[4/3] bg-gradient-to-br from-brand-dark/30 via-brand-deep to-brand-primary/30 flex items-center justify-center">
                  <div className="w-24 h-24 rounded-full bg-gradient-to-br from-brand-primary to-brand-accent flex items-center justify-center text-zinc-50">
                    <FiUser className="w-12 h-12" />
                  </div>
                </div>
                <div className="p-6">
                  <h3 className="text-xl font-semibold text-zinc-50 mb-1">{member.name}</h3>
                  <p className="text-brand-accent font-medium mb-3">{member.role}</p>
                  <p className="text-zinc-400 text-sm">{member.bio}</p>
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
