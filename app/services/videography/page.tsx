import Link from "next/link";
import { Metadata } from "next";
import { COMPANY, SERVICES } from "@/lib/constants";
import CTASection from "@/components/CTASection";
import Icon from "@/components/ui/IconMap";
import { FiVideo } from "react-icons/fi";
import { BsCameraVideo, BsMic } from "react-icons/bs";
import { TbDrone } from "react-icons/tb";
import { GiFilmStrip } from "react-icons/gi";
import { IoColorPaletteOutline } from "react-icons/io5";
import { BsLightbulb, BsGear } from "react-icons/bs";
import { HiSparkles } from "react-icons/hi";

export const metadata: Metadata = {
  title: `Videography Services | ${COMPANY.name}`,
  description:
    "Cinematic video production for weddings, corporate events, music videos, documentaries, and commercials. Creating visual stories that captivate.",
};

export default function VideographyServicePage() {
  const videographyService = SERVICES.videography;

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
            <FiVideo className="h-4 w-4" />
            Cinematic Videography
          </span>
          <h1 className="mb-6 text-4xl leading-tight font-bold text-zinc-50 md:text-6xl lg:text-7xl">
            Stories That
            <br />
            <span className="from-brand-primary via-brand-accent to-brand-primary bg-linear-to-r bg-clip-text text-transparent">
              Move People
            </span>
          </h1>
          <p className="mx-auto mb-10 max-w-2xl text-xl text-zinc-400">
            {videographyService.description}. Our cinematographers craft visual narratives that
            leave lasting impressions.
          </p>
          <div className="flex flex-col justify-center gap-4 sm:flex-row">
            <Link
              href="/contact"
              className="from-brand-primary to-brand-accent hover:shadow-brand-primary/30 rounded-full bg-linear-to-r px-8 py-4 font-semibold text-zinc-50 transition-all hover:scale-105 hover:shadow-2xl"
            >
              Start Your Project
            </Link>
            <Link
              href="/portfolio"
              className="border-brand-primary/30 rounded-full border bg-zinc-50/10 px-8 py-4 font-semibold text-zinc-50 backdrop-blur-sm transition-all hover:bg-zinc-50/20"
            >
              Watch Showreel
            </Link>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="bg-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">
              Videography Services
            </h2>
            <p className="mx-auto max-w-2xl text-zinc-400">
              From wedding films to commercial productions, we bring your vision to life
            </p>
          </div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            {videographyService.categories.map((category) => (
              <div
                key={category.slug}
                className="group from-brand-deep hover:shadow-brand-primary/20 border-brand-dark/30 relative overflow-hidden rounded-2xl border bg-linear-to-br to-zinc-950 shadow-sm transition-all duration-300 hover:shadow-xl"
              >
                <div className="from-brand-dark/30 via-brand-deep to-brand-primary/30 flex aspect-video items-center justify-center bg-linear-to-br">
                  <span className="text-brand-accent transition-transform duration-300 group-hover:scale-110">
                    <Icon name={category.icon} className="h-16 w-16" />
                  </span>
                </div>
                <div className="p-6">
                  <h3 className="mb-2 text-xl font-semibold text-zinc-50">{category.name}</h3>
                  <p className="mb-4 text-zinc-400">
                    Cinematic {category.name.toLowerCase()} that capture the essence of your story.
                  </p>
                  <Link
                    href={`/services/videography/${category.slug}`}
                    className="text-brand-accent hover:text-brand-primary inline-flex items-center font-medium transition-colors"
                  >
                    Learn more
                    <svg
                      className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M9 5l7 7-7 7"
                      />
                    </svg>
                  </Link>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Process Section */}
      <section className="from-brand-deep to-brand-dark bg-linear-to-br via-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">Our Process</h2>
            <p className="mx-auto max-w-2xl text-zinc-400">
              How we bring your vision to the screen
            </p>
          </div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-4">
            {[
              {
                step: "01",
                title: "Consultation",
                desc: "Understanding your vision and requirements",
              },
              {
                step: "02",
                title: "Pre-Production",
                desc: "Planning, scripting, and storyboarding",
              },
              {
                step: "03",
                title: "Production",
                desc: "Professional filming with latest equipment",
              },
              {
                step: "04",
                title: "Post-Production",
                desc: "Editing, color grading, and final delivery",
              },
            ].map((item, index) => (
              <div key={item.step} className="relative">
                <div className="border-brand-dark/30 rounded-2xl border bg-zinc-950/50 p-6 text-center">
                  <span className="from-brand-primary to-brand-accent bg-linear-to-r bg-clip-text text-5xl font-bold text-transparent">
                    {item.step}
                  </span>
                  <h3 className="mt-4 mb-2 text-lg font-semibold text-zinc-50">{item.title}</h3>
                  <p className="text-sm text-zinc-400">{item.desc}</p>
                </div>
                {index < 3 && (
                  <div className="text-brand-dark absolute top-1/2 -right-4 hidden -translate-y-1/2 transform text-2xl md:block">
                    →
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Equipment Section */}
      <section className="bg-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">
              Professional Equipment
            </h2>
            <p className="mx-auto max-w-2xl text-zinc-400">
              We use industry-leading gear for exceptional quality
            </p>
          </div>

          <div className="grid grid-cols-2 gap-6 md:grid-cols-4">
            {[
              { icon: BsCameraVideo, name: "Cinema Cameras" },
              { icon: BsMic, name: "Pro Audio Gear" },
              { icon: BsLightbulb, name: "Studio Lighting" },
              { icon: TbDrone, name: "Drone Footage" },
              { icon: GiFilmStrip, name: "Gimbal Stabilizers" },
              { icon: BsGear, name: "4K/8K Editing" },
              { icon: IoColorPaletteOutline, name: "Color Suite" },
              { icon: HiSparkles, name: "Sound Studio" },
            ].map((item) => (
              <div
                key={item.name}
                className="from-brand-deep border-brand-dark/30 rounded-xl border bg-linear-to-br to-zinc-950 p-6 text-center"
              >
                <span className="text-brand-accent mb-3 flex justify-center">
                  <item.icon className="h-10 w-10" />
                </span>
                <p className="font-medium text-zinc-300">{item.name}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <CTASection
        title="Ready to Tell Your Story?"
        description="Let's create something cinematic together"
        primaryButton={{ text: "Get Started", href: "/contact" }}
      />
    </main>
  );
}
