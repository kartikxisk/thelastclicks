"use client";

import { useState } from "react";

interface FAQ {
  readonly question: string;
  readonly answer: string;
}

const faqs: readonly FAQ[] = [
  {
    question: "What types of photography services do you offer?",
    answer:
      "We offer a comprehensive range of photography services including corporate photography, event coverage, product photography, portrait sessions, real estate photography, and commercial shoots. Each project is tailored to meet your specific needs and brand requirements.",
  },
  {
    question: "How long does it take to receive the final deliverables?",
    answer:
      "Turnaround time varies based on the project scope. For photography, you can expect edited images within 5-7 business days. Video projects typically take 2-4 weeks depending on complexity. Rush delivery options are available for time-sensitive projects.",
  },
  {
    question: "Do you provide raw footage or unedited photos?",
    answer:
      "We typically deliver professionally edited content as part of our standard packages. However, raw footage and unedited photos can be provided upon request for an additional fee. This is often included in premium packages.",
  },
  {
    question: "What is your pricing structure?",
    answer:
      "Our pricing is project-based and depends on factors like duration, location, complexity, and deliverables required. We provide detailed quotes after understanding your needs. Contact us for a free consultation and custom quote.",
  },
  {
    question: "Do you travel for on-location shoots?",
    answer:
      "Yes! We travel locally and internationally for shoots. Travel costs within the city are typically included. For destinations outside our base area, travel expenses will be quoted separately based on the location.",
  },
  {
    question: "What equipment do you use?",
    answer:
      "We use industry-leading equipment including Cinema cameras, professional-grade DSLRs, cinema lenses, professional lighting setups, and state-of-the-art audio equipment. We also have access to drones for aerial shots and gimbals for smooth motion footage.",
  },
  {
    question: "Can you help with creative direction and concept development?",
    answer:
      "Absolutely! Creative direction is one of our strengths. We offer full pre-production services including concept development, storyboarding, location scouting, and creative consultation to ensure your vision comes to life perfectly.",
  },
  {
    question: "What formats do you deliver the final content in?",
    answer:
      "We deliver content in formats optimized for your needs—whether it's web, social media, broadcast, or print. Common video formats include MP4, MOV, and ProRes. Photos are typically delivered in high-resolution JPEG and/or TIFF formats.",
  },
];

export default function FAQSection() {
  const [openIndex, setOpenIndex] = useState<number | null>(0);

  const toggleFAQ = (index: number) => {
    setOpenIndex(openIndex === index ? null : index);
  };

  return (
    <section className="relative overflow-hidden px-4 py-24">
      {/* Background */}
      <div className="absolute inset-0 bg-zinc-950" />

      {/* Decorative gradient */}
      <div className="from-brand-deep absolute top-0 left-1/2 h-[400px] w-[800px] -translate-x-1/2 rounded-full bg-linear-to-b to-transparent opacity-50 blur-[100px]" />

      <div className="relative mx-auto max-w-4xl">
        {/* Header */}
        <div className="mb-16 text-center">
          <span className="bg-brand-primary/20 text-brand-accent border-brand-primary/30 mb-4 inline-block rounded-full border px-4 py-2 text-sm font-medium">
            Got Questions?
          </span>
          <h2 className="font-display mb-4 text-4xl text-zinc-50 italic md:text-5xl">
            Frequently Asked Questions
          </h2>
          <p className="mx-auto max-w-2xl text-lg text-zinc-400">
            Find answers to common questions about our services, process, and deliverables
          </p>
        </div>

        {/* FAQ Items */}
        <div className="space-y-4">
          {faqs.map((faq, index) => (
            <div
              key={index}
              className={`rounded-2xl border transition-all duration-300 ${
                openIndex === index
                  ? "from-brand-deep to-brand-deep/50 border-brand-primary/50 bg-linear-to-br"
                  : "border-brand-dark/30 hover:border-brand-dark/50 bg-zinc-950/50"
              }`}
            >
              <button
                onClick={() => toggleFAQ(index)}
                className="flex w-full items-center justify-between p-6 text-left"
                aria-expanded={openIndex === index}
              >
                <span
                  className={`pr-4 text-lg font-medium transition-colors duration-300 ${
                    openIndex === index ? "text-zinc-50" : "text-zinc-300"
                  }`}
                >
                  {faq.question}
                </span>
                <span
                  className={`flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300 ${
                    openIndex === index
                      ? "bg-brand-primary rotate-180 text-zinc-50"
                      : "bg-brand-deep text-brand-accent border-brand-dark/50 border"
                  }`}
                >
                  <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </span>
              </button>

              <div
                className={`overflow-hidden transition-all duration-300 ${
                  openIndex === index ? "max-h-96 opacity-100" : "max-h-0 opacity-0"
                }`}
              >
                <div className="px-6 pb-6 leading-relaxed text-zinc-400">{faq.answer}</div>
              </div>
            </div>
          ))}
        </div>

        {/* Contact CTA */}
        <div className="mt-12 text-center">
          <p className="mb-4 text-zinc-400">Still have questions? We&apos;re here to help!</p>
          <a
            href="/contact"
            className="from-brand-primary to-brand-accent hover:shadow-brand-primary/30 inline-flex items-center gap-2 rounded-full bg-linear-to-r px-6 py-3 font-medium text-zinc-50 transition-all duration-300 hover:scale-105 hover:shadow-lg"
          >
            Contact Us
            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M14 5l7 7m0 0l-7 7m7-7H3"
              />
            </svg>
          </a>
        </div>
      </div>
    </section>
  );
}
