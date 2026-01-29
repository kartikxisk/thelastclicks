import Link from "next/link";

type CTAVariant = "dark" | "gradient" | "purple" | "amber" | "blue" | "light";

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
    bg: "bg-gray-900",
    text: "text-white",
    subtext: "text-gray-400",
    primaryBtn: "bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:shadow-purple-500/30",
    secondaryBtn: "bg-white/10 text-white border border-white/20 hover:bg-white/20",
  },
  gradient: {
    bg: "bg-gradient-to-br from-purple-900 via-gray-900 to-pink-900",
    text: "text-white",
    subtext: "text-gray-300",
    primaryBtn: "bg-white text-purple-600",
    secondaryBtn: "bg-white/10 text-white hover:bg-white/20",
  },
  purple: {
    bg: "bg-gradient-to-r from-purple-600 to-pink-600",
    text: "text-white",
    subtext: "text-white/90",
    primaryBtn: "bg-white text-purple-600",
    secondaryBtn: "bg-white/10 text-white hover:bg-white/20",
  },
  amber: {
    bg: "bg-gradient-to-br from-amber-900 via-gray-900 to-orange-900",
    text: "text-white",
    subtext: "text-gray-300",
    primaryBtn: "bg-white text-amber-600",
    secondaryBtn: "bg-white/10 text-white hover:bg-white/20",
  },
  blue: {
    bg: "bg-gradient-to-br from-blue-900 via-gray-900 to-indigo-900",
    text: "text-white",
    subtext: "text-gray-300",
    primaryBtn: "bg-white text-blue-600",
    secondaryBtn: "bg-white/10 text-white hover:bg-white/20",
  },
  light: {
    bg: "bg-white",
    text: "text-gray-900",
    subtext: "text-gray-600",
    primaryBtn: "bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:shadow-purple-500/30",
    secondaryBtn: "bg-gray-100 text-gray-700 hover:bg-gray-200",
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
