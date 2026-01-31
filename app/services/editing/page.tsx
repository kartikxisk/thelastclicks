import Link from "next/link";
import { Metadata } from "next";
import { COMPANY, SERVICES, EDITING_FEATURES, EDITING_PRICING, EDITORS } from "@/lib/constants";
import CTASection from "@/components/CTASection";
import Icon from "@/components/ui/IconMap";
import { FiEdit3, FiUser } from "react-icons/fi";
import { HiSparkles } from "react-icons/hi";

export const metadata: Metadata = {
  title: `Editing Services | ${COMPANY.name}`,
  description:
    "Professional photo and video editing services. Transform your raw footage into stunning masterpieces with our expert editors.",
};

export default function EditingServicePage() {
  const editingService = SERVICES.editing;

  return (
    <main className="min-h-screen">
      {/* Hero Section */}
      <section className="relative overflow-hidden px-4 pt-32 pb-20">
        <div className="from-brand-deep to-brand-dark absolute inset-0 bg-linear-to-br via-zinc-950" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-20" />

        {/* Animated Gradient Orbs */}
        <div className="bg-brand-primary absolute top-20 left-10 h-72 w-72 animate-pulse rounded-full opacity-30 blur-[128px]" />
        <div className="bg-brand-accent absolute right-10 bottom-10 h-96 w-96 animate-pulse rounded-full opacity-30 blur-[128px] delay-1000" />

        <div className="relative container text-center">
          <span className="text-brand-accent border-brand-primary/30 mb-6 inline-flex items-center gap-2 rounded-full border bg-zinc-50/10 px-4 py-2 text-sm font-medium backdrop-blur-sm">
            <HiSparkles className="h-4 w-4" />
            Professional Editing Services
          </span>
          <h1 className="mb-6 text-4xl leading-tight font-bold text-zinc-50 md:text-6xl lg:text-7xl">
            Transform Your Vision
            <br />
            <span className="from-brand-primary via-brand-accent to-brand-primary bg-linear-to-r bg-clip-text text-transparent">
              Into Reality
            </span>
          </h1>
          <p className="mx-auto mb-10 max-w-2xl text-xl text-zinc-400">
            {editingService.description}. Our team of skilled editors brings your content to life
            with precision and creativity.
          </p>
          <div className="flex flex-col justify-center gap-4 sm:flex-row">
            <Link
              href="/contact"
              className="from-brand-primary to-brand-accent hover:shadow-brand-primary/30 rounded-full bg-linear-to-r px-8 py-4 font-semibold text-zinc-50 transition-all hover:scale-105 hover:shadow-2xl"
            >
              Start Your Project
            </Link>
            <Link
              href="/services/editing/editors"
              className="border-brand-primary/30 rounded-full border bg-zinc-50/10 px-8 py-4 font-semibold text-zinc-50 backdrop-blur-sm transition-all hover:bg-zinc-50/20"
            >
              Meet Our Editors
            </Link>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="bg-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">
              Our Editing Services
            </h2>
            <p className="mx-auto max-w-2xl text-zinc-400">
              From basic color correction to advanced VFX, we offer comprehensive editing solutions
            </p>
          </div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            {editingService.categories.map((category) => (
              <div
                key={category.slug}
                className="group from-brand-deep hover:shadow-brand-primary/20 border-brand-dark/30 relative rounded-2xl border bg-linear-to-br to-zinc-950 p-8 shadow-sm transition-all duration-300 hover:shadow-xl"
              >
                <div className="from-brand-primary/5 to-brand-accent/5 absolute inset-0 rounded-2xl bg-linear-to-br opacity-0 transition-opacity group-hover:opacity-100" />
                <div className="relative">
                  <span className="text-brand-accent mb-4 block">
                    <Icon name={category.icon} className="h-10 w-10" />
                  </span>
                  <h3 className="mb-2 text-xl font-semibold text-zinc-50">{category.name}</h3>
                  <p className="mb-4 text-zinc-400">
                    Professional {category.name.toLowerCase()} services tailored to your needs
                  </p>
                  <Link
                    href={`/services/editing/${category.slug}`}
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

      {/* Features Section */}
      <section className="from-brand-deep to-brand-dark bg-linear-to-br via-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">Why Choose Us?</h2>
            <p className="mx-auto max-w-2xl text-zinc-400">
              We deliver exceptional quality with features that set us apart
            </p>
          </div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
            {EDITING_FEATURES.map((feature) => (
              <div
                key={feature.title}
                className="border-brand-dark/30 rounded-2xl border bg-zinc-950/50 p-8 text-center"
              >
                <span className="text-brand-accent mb-4 block flex justify-center">
                  <Icon name={feature.icon} className="h-12 w-12" />
                </span>
                <h3 className="mb-2 text-lg font-semibold text-zinc-50">{feature.title}</h3>
                <p className="text-sm text-zinc-400">{feature.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Process Section */}
      <section className="bg-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">How It Works</h2>
            <p className="mx-auto max-w-2xl text-zinc-400">
              Simple, streamlined process to get your project done
            </p>
          </div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-4">
            {[
              { step: "01", title: "Upload", desc: "Share your raw files via our secure portal" },
              { step: "02", title: "Brief", desc: "Tell us your vision and requirements" },
              { step: "03", title: "Edit", desc: "Our experts work their magic" },
              { step: "04", title: "Deliver", desc: "Get polished content delivered to you" },
            ].map((item, index) => (
              <div key={item.step} className="relative">
                <div className="text-brand-primary/20 mb-4 text-6xl font-bold">{item.step}</div>
                <h3 className="mb-2 text-xl font-semibold text-zinc-50">{item.title}</h3>
                <p className="text-zinc-400">{item.desc}</p>
                {index < 3 && (
                  <div className="from-brand-primary/50 absolute top-8 left-full hidden h-0.5 w-full -translate-x-8 bg-linear-to-r to-transparent md:block" />
                )}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Pricing Section */}
      <section className="from-brand-deep to-brand-dark bg-linear-to-br via-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">
              Transparent Pricing
            </h2>
            <p className="mx-auto max-w-2xl text-zinc-400">
              Choose the plan that fits your project needs
            </p>
          </div>

          <div className="mx-auto grid max-w-5xl grid-cols-1 gap-8 md:grid-cols-3">
            {EDITING_PRICING.map((plan) => (
              <div
                key={plan.name}
                className={`relative rounded-2xl bg-zinc-950 p-8 ${
                  plan.popular
                    ? "ring-brand-primary shadow-brand-primary/20 scale-105 shadow-xl ring-2"
                    : "border-brand-dark/30 border"
                }`}
              >
                {plan.popular && (
                  <span className="from-brand-primary to-brand-accent absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-linear-to-r px-4 py-1 text-sm font-medium text-zinc-50">
                    Most Popular
                  </span>
                )}
                <h3 className="mb-2 text-xl font-semibold text-zinc-50">{plan.name}</h3>
                <div className="mb-6">
                  <span className="text-4xl font-bold text-zinc-50">{plan.price}</span>
                  <span className="text-zinc-500">/{plan.period}</span>
                </div>
                <ul className="mb-8 space-y-3">
                  {plan.features.map((feature) => (
                    <li key={feature} className="flex items-center text-zinc-400">
                      <svg
                        className="text-brand-accent mr-3 h-5 w-5 flex-shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M5 13l4 4L19 7"
                        />
                      </svg>
                      {feature}
                    </li>
                  ))}
                </ul>
                <Link
                  href="/contact"
                  className={`block rounded-full py-3 text-center font-semibold transition-all ${
                    plan.popular
                      ? "from-brand-primary to-brand-accent hover:shadow-brand-primary/30 bg-linear-to-r text-zinc-50 hover:shadow-lg"
                      : "bg-zinc-800 text-zinc-50 hover:bg-zinc-700"
                  }`}
                >
                  Get Started
                </Link>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Team Preview */}
      <section className="bg-zinc-950 px-4 py-20">
        <div className="container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">
              Meet Our Expert Editors
            </h2>
            <p className="mx-auto max-w-2xl text-zinc-400">
              Talented professionals dedicated to bringing your vision to life
            </p>
          </div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
            {EDITORS.slice(0, 3).map((editor) => (
              <div
                key={editor.id}
                className="group from-brand-deep hover:shadow-brand-primary/20 border-brand-dark/30 overflow-hidden rounded-2xl border bg-linear-to-br to-zinc-950 shadow-sm transition-all hover:shadow-xl"
              >
                <div className="from-brand-dark/30 to-brand-primary/30 relative aspect-[4/3] overflow-hidden bg-linear-to-br">
                  <div className="text-brand-accent absolute inset-0 flex items-center justify-center">
                    <FiUser className="h-16 w-16" />
                  </div>
                </div>
                <div className="p-6">
                  <h3 className="text-xl font-semibold text-zinc-50">{editor.name}</h3>
                  <p className="text-brand-accent mb-2 font-medium">{editor.role}</p>
                  <p className="mb-4 text-sm text-zinc-400">{editor.bio}</p>
                  <div className="flex flex-wrap gap-2">
                    {editor.specialization.slice(0, 2).map((spec) => (
                      <span
                        key={spec}
                        className="bg-brand-primary/20 text-brand-accent border-brand-primary/30 rounded-full border px-3 py-1 text-xs font-medium"
                      >
                        {spec}
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            ))}
          </div>

          <div className="mt-12 text-center">
            <Link
              href="/services/editing/editors"
              className="from-brand-primary to-brand-accent hover:shadow-brand-primary/30 inline-flex items-center rounded-full bg-linear-to-r px-8 py-4 font-semibold text-zinc-50 transition-colors hover:shadow-lg"
            >
              View All Editors
              <svg className="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M17 8l4 4m0 0l-4 4m4-4H3"
                />
              </svg>
            </Link>
          </div>
        </div>
      </section>

      <CTASection
        title="Ready to Transform Your Content?"
        description="Let our expert editors bring your vision to life. Get started today with a free consultation."
        primaryButton={{ text: "Get Free Quote", href: "/contact" }}
        secondaryButton={{ text: `Call ${COMPANY.mobile}`, href: `tel:${COMPANY.mobile}` }}
      />
    </main>
  );
}
