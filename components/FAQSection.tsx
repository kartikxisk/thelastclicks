"use client";

import { useState } from "react";

interface FAQ {
  readonly question: string;
  readonly answer: string;
}

const faqs: readonly FAQ[] = [
  {
    question: "What types of photography services do you offer?",
    answer: "We offer a comprehensive range of photography services including corporate photography, event coverage, product photography, portrait sessions, real estate photography, and commercial shoots. Each project is tailored to meet your specific needs and brand requirements.",
  },
  {
    question: "How long does it take to receive the final deliverables?",
    answer: "Turnaround time varies based on the project scope. For photography, you can expect edited images within 5-7 business days. Video projects typically take 2-4 weeks depending on complexity. Rush delivery options are available for time-sensitive projects.",
  },
  {
    question: "Do you provide raw footage or unedited photos?",
    answer: "We typically deliver professionally edited content as part of our standard packages. However, raw footage and unedited photos can be provided upon request for an additional fee. This is often included in premium packages.",
  },
  {
    question: "What is your pricing structure?",
    answer: "Our pricing is project-based and depends on factors like duration, location, complexity, and deliverables required. We provide detailed quotes after understanding your needs. Contact us for a free consultation and custom quote.",
  },
  {
    question: "Do you travel for on-location shoots?",
    answer: "Yes! We travel locally and internationally for shoots. Travel costs within the city are typically included. For destinations outside our base area, travel expenses will be quoted separately based on the location.",
  },
  {
    question: "What equipment do you use?",
    answer: "We use industry-leading equipment including Cinema cameras, professional-grade DSLRs, cinema lenses, professional lighting setups, and state-of-the-art audio equipment. We also have access to drones for aerial shots and gimbals for smooth motion footage.",
  },
  {
    question: "Can you help with creative direction and concept development?",
    answer: "Absolutely! Creative direction is one of our strengths. We offer full pre-production services including concept development, storyboarding, location scouting, and creative consultation to ensure your vision comes to life perfectly.",
  },
  {
    question: "What formats do you deliver the final content in?",
    answer: "We deliver content in formats optimized for your needs—whether it's web, social media, broadcast, or print. Common video formats include MP4, MOV, and ProRes. Photos are typically delivered in high-resolution JPEG and/or TIFF formats.",
  },
];

export default function FAQSection() {
  const [openIndex, setOpenIndex] = useState<number | null>(0);

  const toggleFAQ = (index: number) => {
    setOpenIndex(openIndex === index ? null : index);
  };

  return (
    <section className="relative py-24 px-4 overflow-hidden">
      {/* Background */}
      <div className="absolute inset-0 bg-zinc-950" />
      
      {/* Decorative gradient */}
      <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-b from-[#280905] to-transparent rounded-full blur-[100px] opacity-50" />

      <div className="relative max-w-4xl mx-auto">
        {/* Header */}
        <div className="text-center mb-16">
          <span className="inline-block px-4 py-2 rounded-full bg-[#C3110C]/20 text-[#E6501B] text-sm font-medium mb-4 border border-[#C3110C]/30">
            Got Questions?
          </span>
          <h2 className="text-4xl md:text-5xl font-display text-zinc-50 mb-4 italic">
            Frequently Asked Questions
          </h2>
          <p className="text-lg text-zinc-400 max-w-2xl mx-auto">
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
                  ? "bg-gradient-to-br from-[#280905] to-[#280905]/50 border-[#C3110C]/50"
                  : "bg-zinc-950/50 border-[#740A03]/30 hover:border-[#740A03]/50"
              }`}
            >
              <button
                onClick={() => toggleFAQ(index)}
                className="w-full flex items-center justify-between p-6 text-left"
                aria-expanded={openIndex === index}
              >
                <span className={`text-lg font-medium pr-4 transition-colors duration-300 ${
                  openIndex === index ? "text-zinc-50" : "text-zinc-300"
                }`}>
                  {faq.question}
                </span>
                <span className={`flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 ${
                  openIndex === index
                    ? "bg-[#C3110C] text-zinc-50 rotate-180"
                    : "bg-[#280905] text-[#E6501B] border border-[#740A03]/50"
                }`}>
                  <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                  </svg>
                </span>
              </button>
              
              <div
                className={`overflow-hidden transition-all duration-300 ${
                  openIndex === index ? "max-h-96 opacity-100" : "max-h-0 opacity-0"
                }`}
              >
                <div className="px-6 pb-6 text-zinc-400 leading-relaxed">
                  {faq.answer}
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Contact CTA */}
        <div className="mt-12 text-center">
          <p className="text-zinc-400 mb-4">
            Still have questions? We&apos;re here to help!
          </p>
          <a
            href="/contact"
            className="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-[#C3110C] to-[#E6501B] text-zinc-50 rounded-full font-medium hover:shadow-lg hover:shadow-[#C3110C]/30 hover:scale-105 transition-all duration-300"
          >
            Contact Us
            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </a>
        </div>
      </div>
    </section>
  );
}
