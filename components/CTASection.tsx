import Link from "next/link";

type CTAVariant = "dark" | "gradient" | "brand" | "amber" | "blue" | "light" | "purple";

interface CTAButton {
  readonly text: string;
  readonly href: string;
  readonly variant?: "primary" | "secondary";
}

interface CTASectionProps {
  readonly title: string;
  readonly description: string;
  readonly primaryButton: CTAButton;
  readonly secondaryButton?: CTAButton;
  readonly variant?: CTAVariant;
}

const variantStyles: Record<CTAVariant, { bg: string; text: string; subtext: string; primaryBtn: string; secondaryBtn: string }> = {
  dark: {
    bg: "bg-zinc-950",
    text: "text-zinc-50",
    subtext: "text-zinc-400",
    primaryBtn: "bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 hover:shadow-brand-primary/30",
    secondaryBtn: "bg-zinc-50/10 text-zinc-50 border border-brand-primary/30 hover:bg-zinc-50/20",
  },
  gradient: {
    bg: "bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark",
    text: "text-zinc-50",
    subtext: "text-zinc-400",
    primaryBtn: "bg-zinc-50 text-brand-primary",
    secondaryBtn: "bg-zinc-50/10 text-zinc-50 hover:bg-zinc-50/20",
  },
  brand: {
    bg: "bg-gradient-to-r from-brand-dark via-brand-primary to-brand-accent",
    text: "text-zinc-50",
    subtext: "text-zinc-50/90",
    primaryBtn: "bg-zinc-50 text-brand-primary",
    secondaryBtn: "bg-zinc-50/10 text-zinc-50 hover:bg-zinc-50/20",
  },
  amber: {
    bg: "bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-accent",
    text: "text-zinc-50",
    subtext: "text-zinc-400",
    primaryBtn: "bg-zinc-50 text-brand-accent",
    secondaryBtn: "bg-zinc-50/10 text-zinc-50 hover:bg-zinc-50/20",
  },
  blue: {
    bg: "bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark",
    text: "text-zinc-50",
    subtext: "text-zinc-400",
    primaryBtn: "bg-zinc-50 text-brand-dark",
    secondaryBtn: "bg-zinc-50/10 text-zinc-50 hover:bg-zinc-50/20",
  },
  light: {
    bg: "bg-zinc-950",
    text: "text-zinc-50",
    subtext: "text-zinc-400",
    primaryBtn: "bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 hover:shadow-brand-primary/30",
    secondaryBtn: "bg-brand-deep text-zinc-50 border border-brand-dark/30 hover:bg-brand-dark/30",
  },
  purple: {
    bg: "bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark",
    text: "text-zinc-50",
    subtext: "text-zinc-400",
    primaryBtn: "bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 hover:shadow-brand-primary/30",
    secondaryBtn: "bg-zinc-50/10 text-zinc-50 border border-brand-primary/30 hover:bg-zinc-50/20",
  },
};

export default function CTASection({
  title,
  description,
  primaryButton,
  secondaryButton,
  variant = "gradient",
}: CTASectionProps) {
  const styles = variantStyles[variant];

  return (
    <section className={`py-20 px-4 ${styles.bg}`}>
      <div className="max-w-4xl mx-auto text-center">
        <h2 className={`text-3xl md:text-4xl font-bold ${styles.text} mb-6`}>
          {title}
        </h2>
        <p className={`text-xl ${styles.subtext} mb-8`}>
          {description}
        </p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Link
            href={primaryButton.href}
            className={`inline-flex items-center justify-center px-8 py-4 rounded-full font-semibold hover:shadow-2xl hover:scale-105 transition-all ${styles.primaryBtn}`}
          >
            {primaryButton.text}
            <svg className="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </Link>
          {secondaryButton && (
            <Link
              href={secondaryButton.href}
              className={`inline-flex items-center justify-center px-8 py-4 rounded-full font-semibold backdrop-blur-sm transition-all ${styles.secondaryBtn}`}
            >
              {secondaryButton.text}
            </Link>
          )}
        </div>
      </div>
    </section>
  );
}
