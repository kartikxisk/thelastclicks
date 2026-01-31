import Link from "next/link";

type CTAVariant = "dark" | "gradient" | "brand" | "amber" | "blue" | "light";

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
    primaryBtn: "bg-gradient-to-r from-[#C3110C] to-[#E6501B] text-zinc-50 hover:shadow-[#C3110C]/30",
    secondaryBtn: "bg-zinc-50/10 text-zinc-50 border border-[#C3110C]/30 hover:bg-zinc-50/20",
  },
  gradient: {
    bg: "bg-gradient-to-br from-[#280905] via-zinc-950 to-[#740A03]",
    text: "text-zinc-50",
    subtext: "text-zinc-400",
    primaryBtn: "bg-zinc-50 text-[#C3110C]",
    secondaryBtn: "bg-zinc-50/10 text-zinc-50 hover:bg-zinc-50/20",
  },
  brand: {
    bg: "bg-gradient-to-r from-[#740A03] via-[#C3110C] to-[#E6501B]",
    text: "text-zinc-50",
    subtext: "text-zinc-50/90",
    primaryBtn: "bg-zinc-50 text-[#C3110C]",
    secondaryBtn: "bg-zinc-50/10 text-zinc-50 hover:bg-zinc-50/20",
  },
  amber: {
    bg: "bg-gradient-to-br from-[#280905] via-zinc-950 to-[#E6501B]",
    text: "text-zinc-50",
    subtext: "text-zinc-400",
    primaryBtn: "bg-zinc-50 text-[#E6501B]",
    secondaryBtn: "bg-zinc-50/10 text-zinc-50 hover:bg-zinc-50/20",
  },
  blue: {
    bg: "bg-gradient-to-br from-[#280905] via-zinc-950 to-[#740A03]",
    text: "text-zinc-50",
    subtext: "text-zinc-400",
    primaryBtn: "bg-zinc-50 text-[#740A03]",
    secondaryBtn: "bg-zinc-50/10 text-zinc-50 hover:bg-zinc-50/20",
  },
  light: {
    bg: "bg-zinc-950",
    text: "text-zinc-50",
    subtext: "text-zinc-400",
    primaryBtn: "bg-gradient-to-r from-[#C3110C] to-[#E6501B] text-zinc-50 hover:shadow-[#C3110C]/30",
    secondaryBtn: "bg-[#280905] text-zinc-50 border border-[#740A03]/30 hover:bg-[#740A03]/30",
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
