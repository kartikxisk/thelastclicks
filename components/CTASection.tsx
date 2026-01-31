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
    <section className="relative py-24 px-4 overflow-hidden">
      {/* Background */}
      <div className="absolute inset-0 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark" />
      
      {/* Decorative Elements */}
      <div className="absolute top-0 left-1/4 w-96 h-96 bg-brand-primary rounded-full blur-[150px] opacity-15" />
      <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-brand-accent rounded-full blur-[150px] opacity-15" />
      
      <div className="container relative text-center">
        <h2 className="text-3xl md:text-4xl lg:text-5xl font-bold text-zinc-50 mb-6 font-display italic">
          {title}
        </h2>
        <p className="text-lg md:text-xl text-zinc-400 mb-10 max-w-2xl mx-auto">
          {description}
        </p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Link
            href={primaryButton.href}
            className="inline-flex items-center justify-center px-8 py-4 rounded-full font-semibold bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 hover:shadow-2xl hover:shadow-brand-primary/30 hover:scale-105 transition-all"
          >
            {primaryButton.text}
            <HiArrowRight className="w-5 h-5 ml-2" />
          </Link>
          {secondaryButton && (
            <Link
              href={secondaryButton.href}
              className="inline-flex items-center justify-center px-8 py-4 rounded-full font-semibold bg-zinc-950/50 text-zinc-50 border border-brand-dark/50 backdrop-blur-sm hover:bg-brand-dark/30 hover:border-brand-primary/30 transition-all"
            >
              {secondaryButton.text}
            </Link>
          )}
        </div>
      </div>
    </section>
  );
}
