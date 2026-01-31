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
      <section className="relative overflow-hidden px-4 pt-32 pb-20">
        <div className="from-brand-deep to-brand-dark absolute inset-0 bg-linear-to-br via-zinc-950" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-20" />

        <div className="bg-brand-primary absolute top-20 left-10 h-72 w-72 animate-pulse rounded-full opacity-30 blur-[128px]" />
        <div className="bg-brand-accent absolute right-10 bottom-10 h-96 w-96 animate-pulse rounded-full opacity-30 blur-[128px]" />

        <div className="relative container text-center">
          <span className="text-brand-accent border-brand-primary/30 mb-6 inline-flex items-center gap-2 rounded-full border bg-zinc-50/10 px-4 py-2 text-sm font-medium backdrop-blur-sm">
            <HiSparkles className="h-4 w-4" />
            Our Story
          </span>
          <h1 className="mb-6 text-4xl leading-tight font-bold text-zinc-50 md:text-6xl lg:text-7xl">
            About
            <br />
            <span className="from-brand-primary via-brand-accent to-brand-primary bg-linear-to-r bg-clip-text text-transparent">
              {COMPANY.name}
            </span>
          </h1>
          <p className="mx-auto max-w-2xl text-xl text-zinc-400">
            {COMPANY.tagline}. We are passionate about preserving your precious moments through
            stunning visual storytelling.
          </p>
        </div>
      </section>

      {/* Stats Section */}
      <section className="border-brand-dark/30 border-b bg-zinc-950 px-4 py-16">
        <div className="container">
          <div className="grid grid-cols-2 gap-8 md:grid-cols-4">
            {stats.map((stat) => (
              <div key={stat.label} className="text-center">
                <div className="from-brand-primary to-brand-accent bg-linear-to-r bg-clip-text text-4xl font-bold text-transparent md:text-5xl">
                  {stat.value}
                </div>
                <div className="mt-2 text-zinc-400">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Our Story */}
      <section className="bg-zinc-950 px-4 py-20">
        <div className="container">
          <div className="grid items-center gap-12 lg:grid-cols-2">
            <div>
              <h2 className="mb-6 text-3xl font-bold text-zinc-50 md:text-4xl">Our Journey</h2>
              <div className="space-y-4 text-zinc-400">
                <p>
                  Founded in 2015, {COMPANY.name} began as a small passion project by a group of
                  photography enthusiasts who believed that every moment deserves to be captured
                  beautifully.
                </p>
                <p>
                  Over the years, we have grown into a full-service creative agency, offering
                  photography, videography, and professional editing services. Our team has expanded
                  to include some of the most talented artists in the industry.
                </p>
                <p>
                  Today, we are proud to have served thousands of clients, from intimate personal
                  events to large corporate projects. Our commitment to quality and creativity
                  remains at the heart of everything we do.
                </p>
              </div>
            </div>
            <div className="relative">
              <div className="from-brand-deep via-brand-dark to-brand-deep border-brand-dark/30 flex aspect-square items-center justify-center rounded-3xl border bg-linear-to-br">
                <FiCamera className="text-brand-accent/50 h-32 w-32" />
              </div>
              <div className="from-brand-primary to-brand-accent shadow-brand-primary/30 absolute -right-6 -bottom-6 flex h-32 w-32 items-center justify-center rounded-2xl bg-linear-to-br text-zinc-50 shadow-2xl">
                <FiVideo className="h-12 w-12" />
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Our Values */}
      <section className="from-brand-deep to-brand-dark bg-linear-to-br via-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">Our Values</h2>
            <p className="mx-auto max-w-2xl text-zinc-400">
              The principles that guide us in delivering exceptional results
            </p>
          </div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
            {values.map((value) => (
              <div
                key={value.title}
                className="hover:shadow-brand-primary/20 border-brand-dark/30 rounded-2xl border bg-zinc-950/50 p-8 text-center shadow-sm transition-shadow hover:shadow-xl"
              >
                <span className="text-brand-accent mb-4 flex justify-center">
                  <value.icon className="h-12 w-12" />
                </span>
                <h3 className="mb-2 text-xl font-semibold text-zinc-50">{value.title}</h3>
                <p className="text-zinc-400">{value.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Team Section */}
      <section className="bg-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">Meet Our Team</h2>
            <p className="mx-auto max-w-2xl text-zinc-400">
              The creative minds behind your stunning visuals
            </p>
          </div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
            {team.map((member) => (
              <div
                key={member.name}
                className="from-brand-deep hover:shadow-brand-primary/20 border-brand-dark/30 overflow-hidden rounded-3xl border bg-linear-to-br to-zinc-950 shadow-sm transition-shadow hover:shadow-xl"
              >
                <div className="from-brand-dark/30 via-brand-deep to-brand-primary/30 flex aspect-[4/3] items-center justify-center bg-linear-to-br">
                  <div className="from-brand-primary to-brand-accent flex h-24 w-24 items-center justify-center rounded-full bg-linear-to-br text-zinc-50">
                    <FiUser className="h-12 w-12" />
                  </div>
                </div>
                <div className="p-6">
                  <h3 className="mb-1 text-xl font-semibold text-zinc-50">{member.name}</h3>
                  <p className="text-brand-accent mb-3 font-medium">{member.role}</p>
                  <p className="text-sm text-zinc-400">{member.bio}</p>
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
      />
    </main>
  );
}
