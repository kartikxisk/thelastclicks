import Link from "next/link";
import { Metadata } from "next";
import { COMPANY, SERVICES, EDITING_FEATURES, EDITING_PRICING, EDITORS } from "@/lib/constants";
import CTASection from "@/components/CTASection";
import Icon from "@/components/ui/IconMap";
import { FiEdit3, FiUser } from "react-icons/fi";
import { HiSparkles } from "react-icons/hi";

export const metadata: Metadata = {
  title: `Editing Services | ${COMPANY.name}`,
  description: "Professional photo and video editing services. Transform your raw footage into stunning masterpieces with our expert editors.",
};

export default function EditingServicePage() {
  const editingService = SERVICES.editing;

  return (
    <main className="min-h-screen">
      {/* Hero Section */}
      <section className="relative pt-32 pb-20 px-4 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-20" />
        
        {/* Animated Gradient Orbs */}
        <div className="absolute top-20 left-10 w-72 h-72 bg-brand-primary rounded-full blur-[128px] opacity-30 animate-pulse" />
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-brand-accent rounded-full blur-[128px] opacity-30 animate-pulse delay-1000" />

        <div className="container relative text-center">
          <span className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-zinc-50/10 text-brand-accent text-sm font-medium mb-6 backdrop-blur-sm border border-brand-primary/30">
            <HiSparkles className="w-4 h-4" />
            Professional Editing Services
          </span>
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-zinc-50 mb-6 leading-tight">
            Transform Your Vision
            <br />
            <span className="bg-gradient-to-r from-brand-primary via-brand-accent to-brand-primary bg-clip-text text-transparent">
              Into Reality
            </span>
          </h1>
          <p className="text-xl text-zinc-400 max-w-2xl mx-auto mb-10">
            {editingService.description}. Our team of skilled editors brings your content to life with precision and creativity.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              href="/contact"
              className="px-8 py-4 bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 rounded-full font-semibold hover:shadow-2xl hover:shadow-brand-primary/30 hover:scale-105 transition-all"
            >
              Start Your Project
            </Link>
            <Link
              href="/services/editing/editors"
              className="px-8 py-4 bg-zinc-50/10 text-zinc-50 rounded-full font-semibold backdrop-blur-sm hover:bg-zinc-50/20 transition-all border border-brand-primary/30"
            >
              Meet Our Editors
            </Link>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="py-20 px-4 bg-zinc-950">
        <div className="container">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-zinc-50 mb-4">
              Our Editing Services
            </h2>
            <p className="text-zinc-400 max-w-2xl mx-auto">
              From basic color correction to advanced VFX, we offer comprehensive editing solutions
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {editingService.categories.map((category) => (
              <div
                key={category.slug}
                className="group relative bg-gradient-to-br from-brand-deep to-zinc-950 rounded-2xl p-8 shadow-sm hover:shadow-xl hover:shadow-brand-primary/20 transition-all duration-300 border border-brand-dark/30"
              >
                <div className="absolute inset-0 bg-gradient-to-br from-brand-primary/5 to-brand-accent/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity" />
                <div className="relative">
                  <span className="text-brand-accent mb-4 block">
                    <Icon name={category.icon} className="w-10 h-10" />
                  </span>
                  <h3 className="text-xl font-semibold text-zinc-50 mb-2">{category.name}</h3>
                  <p className="text-zinc-400 mb-4">
                    Professional {category.name.toLowerCase()} services tailored to your needs
                  </p>
                  <Link
                    href={`/services/editing/${category.slug}`}
                    className="inline-flex items-center text-brand-accent font-medium hover:text-brand-primary transition-colors"
                  >
                    Learn more
                    <svg className="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                  </Link>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20 px-4 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark">
        <div className="container">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-zinc-50 mb-4">
              Why Choose Us?
            </h2>
            <p className="text-zinc-400 max-w-2xl mx-auto">
              We deliver exceptional quality with features that set us apart
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {EDITING_FEATURES.map((feature) => (
              <div
                key={feature.title}
                className="text-center p-8 rounded-2xl bg-zinc-950/50 border border-brand-dark/30"
              >
                <span className="text-brand-accent mb-4 block flex justify-center">
                  <Icon name={feature.icon} className="w-12 h-12" />
                </span>
                <h3 className="text-lg font-semibold text-zinc-50 mb-2">{feature.title}</h3>
                <p className="text-zinc-400 text-sm">{feature.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Process Section */}
      <section className="py-20 px-4 bg-zinc-950">
        <div className="container">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-zinc-50 mb-4">
              How It Works
            </h2>
            <p className="text-zinc-400 max-w-2xl mx-auto">
              Simple, streamlined process to get your project done
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            {[
              { step: "01", title: "Upload", desc: "Share your raw files via our secure portal" },
              { step: "02", title: "Brief", desc: "Tell us your vision and requirements" },
              { step: "03", title: "Edit", desc: "Our experts work their magic" },
              { step: "04", title: "Deliver", desc: "Get polished content delivered to you" },
            ].map((item, index) => (
              <div key={item.step} className="relative">
                <div className="text-6xl font-bold text-brand-primary/20 mb-4">{item.step}</div>
                <h3 className="text-xl font-semibold text-zinc-50 mb-2">{item.title}</h3>
                <p className="text-zinc-400">{item.desc}</p>
                {index < 3 && (
                  <div className="hidden md:block absolute top-8 left-full w-full h-0.5 bg-gradient-to-r from-brand-primary/50 to-transparent -translate-x-8" />
                )}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Pricing Section */}
      <section className="py-20 px-4 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark">
        <div className="container">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-zinc-50 mb-4">
              Transparent Pricing
            </h2>
            <p className="text-zinc-400 max-w-2xl mx-auto">
              Choose the plan that fits your project needs
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            {EDITING_PRICING.map((plan) => (
              <div
                key={plan.name}
                className={`relative bg-zinc-950 rounded-2xl p-8 ${
                  plan.popular
                    ? "ring-2 ring-brand-primary shadow-xl shadow-brand-primary/20 scale-105"
                    : "border border-brand-dark/30"
                }`}
              >
                {plan.popular && (
                  <span className="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 text-sm font-medium rounded-full">
                    Most Popular
                  </span>
                )}
                <h3 className="text-xl font-semibold text-zinc-50 mb-2">{plan.name}</h3>
                <div className="mb-6">
                  <span className="text-4xl font-bold text-zinc-50">{plan.price}</span>
                  <span className="text-zinc-500">/{plan.period}</span>
                </div>
                <ul className="space-y-3 mb-8">
                  {plan.features.map((feature) => (
                    <li key={feature} className="flex items-center text-zinc-400">
                      <svg className="w-5 h-5 text-brand-accent mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                      </svg>
                      {feature}
                    </li>
                  ))}
                </ul>
                <Link
                  href="/contact"
                  className={`block text-center py-3 rounded-full font-semibold transition-all ${
                    plan.popular
                      ? "bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 hover:shadow-lg hover:shadow-brand-primary/30"
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
      <section className="py-20 px-4 bg-zinc-950">
        <div className="container">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-zinc-50 mb-4">
              Meet Our Expert Editors
            </h2>
            <p className="text-zinc-400 max-w-2xl mx-auto">
              Talented professionals dedicated to bringing your vision to life
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {EDITORS.slice(0, 3).map((editor) => (
              <div
                key={editor.id}
                className="group bg-gradient-to-br from-brand-deep to-zinc-950 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:shadow-brand-primary/20 transition-all border border-brand-dark/30"
              >
                <div className="aspect-[4/3] bg-gradient-to-br from-brand-dark/30 to-brand-primary/30 relative overflow-hidden">
                  <div className="absolute inset-0 flex items-center justify-center text-brand-accent">
                    <FiUser className="w-16 h-16" />
                  </div>
                </div>
                <div className="p-6">
                  <h3 className="text-xl font-semibold text-zinc-50">{editor.name}</h3>
                  <p className="text-brand-accent font-medium mb-2">{editor.role}</p>
                  <p className="text-zinc-400 text-sm mb-4">{editor.bio}</p>
                  <div className="flex flex-wrap gap-2">
                    {editor.specialization.slice(0, 2).map((spec) => (
                      <span
                        key={spec}
                        className="px-3 py-1 bg-brand-primary/20 text-brand-accent rounded-full text-xs font-medium border border-brand-primary/30"
                      >
                        {spec}
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            ))}
          </div>

          <div className="text-center mt-12">
            <Link
              href="/services/editing/editors"
              className="inline-flex items-center px-8 py-4 bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 rounded-full font-semibold hover:shadow-lg hover:shadow-brand-primary/30 transition-colors"
            >
              View All Editors
              <svg className="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
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
        variant="dark"
      />
    </main>
  );
}
