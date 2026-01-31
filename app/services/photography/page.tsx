import Link from "next/link";
import { Metadata } from "next";
import { COMPANY, SERVICES } from "@/lib/constants";
import CTASection from "@/components/CTASection";
import Icon from "@/components/ui/IconMap";
import { FiCamera } from "react-icons/fi";
import { IoColorPaletteOutline } from "react-icons/io5";
import { BsCamera, BsLightning } from "react-icons/bs";
import { MdCheckCircle } from "react-icons/md";

export const metadata: Metadata = {
  title: `Photography Services | ${COMPANY.name}`,
  description:
    "Professional photography services for weddings, portraits, events, products, fashion, and corporate needs. Capturing your moments beautifully.",
};

export default function PhotographyServicePage() {
  const photographyService = SERVICES.photography;

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
            <FiCamera className="h-4 w-4" />
            Professional Photography
          </span>
          <h1 className="mb-6 text-4xl leading-tight font-bold text-zinc-50 md:text-6xl lg:text-7xl">
            Capture Every
            <br />
            <span className="from-brand-primary via-brand-accent to-brand-primary bg-linear-to-r bg-clip-text text-transparent">
              Beautiful Moment
            </span>
          </h1>
          <p className="mx-auto mb-10 max-w-2xl text-xl text-zinc-400">
            {photographyService.description}. Our expert photographers bring artistry and precision
            to every shot.
          </p>
          <div className="flex flex-col justify-center gap-4 sm:flex-row">
            <Link
              href="/contact"
              className="from-brand-primary to-brand-accent hover:shadow-brand-primary/30 rounded-full bg-linear-to-r px-8 py-4 font-semibold text-zinc-50 transition-all hover:scale-105 hover:shadow-2xl"
            >
              Book a Session
            </Link>
            <Link
              href="/portfolio"
              className="border-brand-primary/30 rounded-full border bg-zinc-50/10 px-8 py-4 font-semibold text-zinc-50 backdrop-blur-sm transition-all hover:bg-zinc-50/20"
            >
              View Portfolio
            </Link>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="bg-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">
              Photography Services
            </h2>
            <p className="mx-auto max-w-2xl text-zinc-400">
              From intimate portraits to grand celebrations, we cover it all
            </p>
          </div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            {photographyService.categories.map((category) => (
              <div
                key={category.slug}
                className="group from-brand-deep hover:shadow-brand-primary/20 border-brand-dark/30 relative overflow-hidden rounded-2xl border bg-linear-to-br to-zinc-950 shadow-sm transition-all duration-300 hover:shadow-xl"
              >
                <div className="from-brand-dark/30 via-brand-deep to-brand-primary/30 flex aspect-[4/3] items-center justify-center bg-linear-to-br">
                  <span className="text-brand-accent transition-transform duration-300 group-hover:scale-110">
                    <Icon name={category.icon} className="h-16 w-16" />
                  </span>
                </div>
                <div className="p-6">
                  <h3 className="mb-2 text-xl font-semibold text-zinc-50">{category.name}</h3>
                  <p className="mb-4 text-zinc-400">
                    Professional {category.name.toLowerCase()} tailored to your unique needs and
                    style.
                  </p>
                  <Link
                    href={`/services/photography/${category.slug}`}
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

      {/* Why Choose Us */}
      <section className="from-brand-deep to-brand-dark bg-linear-to-br via-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">Why Choose Us?</h2>
            <p className="mx-auto max-w-2xl text-zinc-400">What sets our photography apart</p>
          </div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
            {[
              {
                icon: IoColorPaletteOutline,
                title: "Artistic Vision",
                desc: "Creative compositions that tell your story",
              },
              { icon: BsCamera, title: "Pro Equipment", desc: "Latest cameras and lighting gear" },
              {
                icon: BsLightning,
                title: "Quick Delivery",
                desc: "Get your photos within 48-72 hours",
              },
              { icon: MdCheckCircle, title: "Satisfaction", desc: "100% satisfaction guaranteed" },
            ].map((item) => (
              <div
                key={item.title}
                className="border-brand-dark/30 rounded-2xl border bg-zinc-950/50 p-6 text-center"
              >
                <span className="text-brand-accent mb-4 block flex justify-center">
                  <item.icon className="h-12 w-12" />
                </span>
                <h3 className="mb-2 text-lg font-semibold text-zinc-50">{item.title}</h3>
                <p className="text-zinc-400">{item.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <CTASection
        title="Ready to Capture Your Moments?"
        description="Let's discuss your photography needs"
        primaryButton={{ text: "Get a Quote", href: "/contact" }}
      />
    </main>
  );
}
