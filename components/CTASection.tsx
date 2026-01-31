import Link from "next/link";
import { HiArrowRight } from "react-icons/hi";

interface CTAButton {
  readonly text: string;
  readonly href: string;
}

interface CTASectionProps {
  readonly title: string;
  readonly description: string;
  readonly primaryButton: CTAButton;
  readonly secondaryButton?: CTAButton;
}

export default function CTASection({
  title,
  description,
  primaryButton,
  secondaryButton,
}: CTASectionProps) {
  return (
    <section className="relative overflow-hidden px-4 py-24">
      {/* Background */}
      <div className="from-brand-deep to-brand-dark absolute inset-0 bg-linear-to-br via-zinc-950" />

      {/* Decorative Elements */}
      <div className="bg-brand-primary absolute top-0 left-1/4 h-96 w-96 rounded-full opacity-15 blur-[150px]" />
      <div className="bg-brand-accent absolute right-1/4 bottom-0 h-96 w-96 rounded-full opacity-15 blur-[150px]" />

      <div className="relative container text-center">
        <h2 className="font-display mb-6 text-3xl font-bold text-zinc-50 italic md:text-4xl lg:text-5xl">
          {title}
        </h2>
        <p className="mx-auto mb-10 max-w-2xl text-lg text-zinc-400 md:text-xl">{description}</p>
        <div className="flex flex-col justify-center gap-4 sm:flex-row">
          <Link
            href={primaryButton.href}
            className="from-brand-primary to-brand-accent hover:shadow-brand-primary/30 inline-flex items-center justify-center rounded-full bg-linear-to-r px-8 py-4 font-semibold text-zinc-50 transition-all hover:scale-105 hover:shadow-2xl"
          >
            {primaryButton.text}
            <HiArrowRight className="ml-2 h-5 w-5" />
          </Link>
          {secondaryButton && (
            <Link
              href={secondaryButton.href}
              className="border-brand-dark/50 hover:bg-brand-dark/30 hover:border-brand-primary/30 inline-flex items-center justify-center rounded-full border bg-zinc-950/50 px-8 py-4 font-semibold text-zinc-50 backdrop-blur-sm transition-all"
            >
              {secondaryButton.text}
            </Link>
          )}
        </div>
      </div>
    </section>
  );
}
