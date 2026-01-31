import Link from "next/link";
import { Metadata } from "next";
import { COMPANY, SERVICES } from "@/lib/constants";
import CTASection from "@/components/CTASection";
import Icon from "@/components/ui/IconMap";
import { FiCamera, FiVideo, FiEdit3 } from "react-icons/fi";
import { BiTargetLock } from "react-icons/bi";

export const metadata: Metadata = {
  title: `Our Services | ${COMPANY.name}`,
  description:
    "Explore our professional photography, videography, and editing services. We capture and create stunning visual content for all occasions.",
};

export default function ServicesPage() {
  const services = Object.values(SERVICES);

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
            <BiTargetLock className="h-4 w-4" />
            Professional Services
          </span>
          <h1 className="mb-6 text-4xl leading-tight font-bold text-zinc-50 md:text-6xl lg:text-7xl">
            Our Creative
            <br />
            <span className="from-brand-primary via-brand-accent to-brand-primary bg-linear-to-r bg-clip-text text-transparent">
              Services
            </span>
          </h1>
          <p className="mx-auto mb-10 max-w-2xl text-xl text-zinc-400">
            From capturing your precious moments to transforming them into stunning visual stories,
            we offer comprehensive creative services.
          </p>
        </div>
      </section>

      {/* Services Grid */}
      <section className="bg-zinc-950 px-4 py-20">
        <div className="container">
          <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
            {services.map((service, index) => (
              <div
                key={service.href}
                className="group from-brand-deep hover:shadow-brand-primary/20 border-brand-dark/30 relative overflow-hidden rounded-3xl border bg-linear-to-br to-zinc-950 shadow-sm transition-all duration-500 hover:shadow-2xl"
              >
                {/* Card Header */}
                <div className="from-brand-dark to-brand-primary relative h-48 overflow-hidden bg-linear-to-br">
                  <div className="absolute inset-0 bg-zinc-950/20" />
                  <div className="absolute inset-0 flex items-center justify-center">
                    <span className="text-zinc-50/50 transition-transform duration-500 group-hover:scale-110">
                      {index === 0 ? (
                        <FiCamera className="h-20 w-20" />
                      ) : index === 1 ? (
                        <FiVideo className="h-20 w-20" />
                      ) : (
                        <FiEdit3 className="h-20 w-20" />
                      )}
                    </span>
                  </div>
                  <div className="from-brand-deep absolute right-0 bottom-0 left-0 h-20 bg-linear-to-t to-transparent" />
                </div>

                {/* Card Content */}
                <div className="relative -mt-8 p-8">
                  <h2 className="mb-3 text-2xl font-bold text-zinc-50">{service.name}</h2>
                  <p className="mb-6 text-zinc-400">{service.description}</p>

                  {/* Categories Preview */}
                  <div className="mb-6 flex flex-wrap gap-2">
                    {service.categories.slice(0, 3).map((category) => (
                      <span
                        key={category.slug}
                        className="bg-brand-primary/20 text-brand-accent border-brand-primary/30 inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-sm font-medium"
                      >
                        <Icon name={category.icon} className="h-3.5 w-3.5" />
                        {category.name}
                      </span>
                    ))}
                    {service.categories.length > 3 && (
                      <span className="rounded-full bg-zinc-800 px-3 py-1 text-sm text-zinc-400">
                        +{service.categories.length - 3} more
                      </span>
                    )}
                  </div>

                  <Link
                    href={service.href}
                    className="from-brand-primary to-brand-accent hover:shadow-brand-primary/30 inline-flex items-center rounded-full bg-linear-to-r px-6 py-3 font-medium text-zinc-50 transition-all hover:scale-105 hover:shadow-lg"
                  >
                    Explore {service.name}
                    <svg
                      className="ml-2 h-4 w-4"
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

      <CTASection
        title="Ready to Get Started?"
        description="Let's discuss your project and bring your vision to life"
        primaryButton={{ text: "Contact Us Today", href: "/contact" }}
      />
    </main>
  );
}
