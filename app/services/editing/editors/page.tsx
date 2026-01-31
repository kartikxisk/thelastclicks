import Link from "next/link";
import { Metadata } from "next";
import { COMPANY, EDITORS } from "@/lib/constants";
import CTASection from "@/components/CTASection";
import { FiUser } from "react-icons/fi";
import { BiCameraMovie } from "react-icons/bi";
import { IoColorPaletteOutline } from "react-icons/io5";
import { HiSparkles } from "react-icons/hi";
import { TbSparkles } from "react-icons/tb";
import { BsHeadphones } from "react-icons/bs";
import { HiOutlineStar } from "react-icons/hi";

export const metadata: Metadata = {
  title: `Our Editors | ${COMPANY.name}`,
  description:
    "Meet our talented team of professional editors specializing in video editing, photo retouching, color grading, motion graphics, and more.",
};

export default function EditorsListPage() {
  return (
    <main className="min-h-screen">
      {/* Hero Section */}
      <section className="relative overflow-hidden px-4 pt-32 pb-16">
        <div className="from-brand-deep to-brand-dark absolute inset-0 bg-linear-to-br via-zinc-950" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-10" />

        {/* Animated Background Elements */}
        <div className="bg-brand-primary absolute top-40 left-20 h-64 w-64 rounded-full opacity-20 blur-[100px]" />
        <div className="bg-brand-accent absolute right-20 bottom-20 h-80 w-80 rounded-full opacity-20 blur-[100px]" />

        <div className="relative container text-center">
          <Link
            href="/services/editing"
            className="text-brand-accent hover:text-brand-primary mb-6 inline-flex items-center transition-colors"
          >
            <svg className="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M15 19l-7-7 7-7"
              />
            </svg>
            Back to Editing Services
          </Link>
          <h1 className="mb-6 text-4xl font-bold text-zinc-50 md:text-5xl lg:text-6xl">
            Meet Our
            <span className="from-brand-primary to-brand-accent block bg-linear-to-r bg-clip-text text-transparent">
              Expert Editors
            </span>
          </h1>
          <p className="mx-auto max-w-2xl text-xl text-zinc-400">
            Talented professionals with years of experience in transforming raw footage into
            stunning visual stories
          </p>
        </div>
      </section>

      {/* Stats Section */}
      <section className="border-brand-dark/30 border-b bg-zinc-950 px-4 py-12">
        <div className="container">
          <div className="grid grid-cols-2 gap-8 md:grid-cols-4">
            {[
              { value: "6+", label: "Expert Editors" },
              { value: "40+", label: "Years Combined Experience" },
              { value: "3200+", label: "Projects Completed" },
              { value: "98%", label: "Client Satisfaction" },
            ].map((stat) => (
              <div key={stat.label} className="text-center">
                <div className="from-brand-primary to-brand-accent bg-linear-to-r bg-clip-text text-3xl font-bold text-transparent md:text-4xl">
                  {stat.value}
                </div>
                <div className="mt-1 text-sm text-zinc-400">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Editors Grid */}
      <section className="bg-zinc-950 px-4 py-20">
        <div className="container">
          <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            {EDITORS.map((editor) => (
              <div
                key={editor.id}
                className="group from-brand-deep hover:shadow-brand-primary/20 border-brand-dark/30 overflow-hidden rounded-3xl border bg-linear-to-br to-zinc-950 shadow-sm transition-all duration-500 hover:shadow-2xl"
              >
                {/* Image/Avatar Section */}
                <div className="from-brand-dark/30 via-brand-deep to-brand-primary/30 relative aspect-[4/3] overflow-hidden bg-linear-to-br">
                  <div className="absolute inset-0 flex items-center justify-center">
                    <div className="from-brand-primary to-brand-accent flex h-32 w-32 items-center justify-center rounded-full bg-linear-to-br text-zinc-50 shadow-xl transition-transform duration-500 group-hover:scale-110">
                      <FiUser className="h-16 w-16" />
                    </div>
                  </div>
                  {/* Decorative Elements */}
                  <div className="text-brand-accent border-brand-primary/30 absolute top-4 right-4 rounded-full border bg-zinc-950/90 px-3 py-1 text-sm font-medium backdrop-blur-sm">
                    {editor.experience}
                  </div>
                  <div className="from-brand-deep absolute right-0 bottom-0 left-0 h-20 bg-linear-to-t to-transparent" />
                </div>

                {/* Content Section */}
                <div className="relative -mt-6 p-6">
                  <div className="mb-2 flex items-center justify-between">
                    <h3 className="text-xl font-bold text-zinc-50">{editor.name}</h3>
                    <div className="flex items-center text-sm text-zinc-400">
                      <svg
                        className="text-brand-accent mr-1 h-4 w-4"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                      {editor.projects}+ projects
                    </div>
                  </div>

                  <p className="text-brand-accent mb-3 font-medium">{editor.role}</p>
                  <p className="mb-4 line-clamp-2 text-sm text-zinc-400">{editor.bio}</p>

                  {/* Specializations */}
                  <div className="mb-5">
                    <p className="mb-2 text-xs font-semibold tracking-wider text-zinc-500 uppercase">
                      Specializations
                    </p>
                    <div className="flex flex-wrap gap-2">
                      {editor.specialization.map((spec) => (
                        <span
                          key={spec}
                          className="bg-brand-primary/20 text-brand-accent border-brand-primary/30 rounded-full border px-3 py-1 text-xs font-medium"
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
                      className="from-brand-primary to-brand-accent hover:shadow-brand-primary/30 flex-1 rounded-xl bg-linear-to-r py-3 text-center font-medium text-zinc-50 transition-all hover:shadow-lg"
                    >
                      View Profile
                    </Link>
                    <button
                      className="border-brand-dark/30 rounded-xl border bg-zinc-800 px-4 py-3 text-zinc-300 transition-colors hover:bg-zinc-700"
                      aria-label="Save editor"
                    >
                      <svg
                        className="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                        />
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
      <section className="from-brand-deep to-brand-dark bg-linear-to-br via-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">Areas of Expertise</h2>
            <p className="mx-auto max-w-2xl text-zinc-400">
              Our team covers all aspects of professional editing
            </p>
          </div>

          <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            {[
              {
                name: "Video Editing",
                icon: BiCameraMovie,
                color: "from-brand-primary to-brand-accent",
              },
              {
                name: "Color Grading",
                icon: IoColorPaletteOutline,
                color: "from-brand-primary to-brand-accent",
              },
              {
                name: "Photo Retouching",
                icon: HiSparkles,
                color: "from-brand-primary to-brand-accent",
              },
              {
                name: "Motion Graphics",
                icon: TbSparkles,
                color: "from-brand-primary to-brand-accent",
              },
              {
                name: "Audio Mixing",
                icon: BsHeadphones,
                color: "from-brand-primary to-brand-accent",
              },
              {
                name: "VFX & Animation",
                icon: HiOutlineStar,
                color: "from-brand-primary to-brand-accent",
              },
            ].map((expertise) => (
              <div
                key={expertise.name}
                className="group hover:shadow-brand-primary/20 border-brand-dark/30 relative cursor-pointer rounded-2xl border bg-zinc-950/50 p-6 text-center transition-all duration-300 hover:bg-zinc-950 hover:shadow-xl"
              >
                <div
                  className={`absolute inset-0 bg-linear-to-br ${expertise.color} rounded-2xl opacity-0 transition-opacity group-hover:opacity-5`}
                />
                <span className="text-brand-accent mb-3 flex justify-center">
                  <expertise.icon className="h-10 w-10" />
                </span>
                <p className="text-sm font-medium text-zinc-50">{expertise.name}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
      <CTASection
        title="Ready to Work with Our Editors?"
        description="Get in touch to discuss your project and find the perfect editor for your needs"
        primaryButton={{ text: "Start a Project", href: "/contact" }}
        secondaryButton={{ text: COMPANY.email, href: `mailto:${COMPANY.email}` }}
      />
    </main>
  );
}
