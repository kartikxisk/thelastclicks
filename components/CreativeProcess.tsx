"use client";

import { useState } from "react";

interface ProcessStep {
  readonly icon: React.ReactNode;
  readonly title: string;
  readonly description: string;
}

const processSteps: readonly ProcessStep[] = [
  {
    icon: (
      <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth={1.5}
          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
        />
      </svg>
    ),
    title: "Requirement Analysis",
    description: "Understanding your vision, goals, and project requirements in detail",
  },
  {
    icon: (
      <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth={1.5}
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
        />
      </svg>
    ),
    title: "Proposal & Quotation",
    description: "Detailed project plan with transparent pricing and timeline",
  },
  {
    icon: (
      <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth={1.5}
          d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
        />
      </svg>
    ),
    title: "Script & Storyboard",
    description: "Creative planning with visual roadmap for your project",
  },
  {
    icon: (
      <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth={1.5}
          d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
        />
      </svg>
    ),
    title: "Filming & Editing",
    description: "Professional production with state-of-the-art equipment",
  },
  {
    icon: (
      <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth={1.5}
          d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
        />
      </svg>
    ),
    title: "Client Approval",
    description: "Review and feedback rounds until you're completely satisfied",
  },
  {
    icon: (
      <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M5 13l4 4L19 7" />
      </svg>
    ),
    title: "Completed Delivery",
    description: "Final delivery in your preferred format with all assets",
  },
];

export default function CreativeProcess() {
  const [hoveredIndex, setHoveredIndex] = useState<number | null>(null);

  return (
    <section className="relative overflow-hidden px-4 py-24">
      {/* Background */}
      <div className="via-brand-deep absolute inset-0 bg-linear-to-b from-zinc-950 to-zinc-950" />
      <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-5" />

      {/* Decorative Elements */}
      <div className="bg-brand-primary absolute top-0 left-0 h-96 w-96 rounded-full opacity-10 blur-[200px]" />
      <div className="bg-brand-accent absolute right-0 bottom-0 h-96 w-96 rounded-full opacity-10 blur-[200px]" />

      <div className="relative container">
        {/* Header */}
        <div className="mb-16 text-center">
          <span className="bg-brand-primary/20 text-brand-accent border-brand-primary/30 mb-4 inline-block rounded-full border px-4 py-2 text-sm font-medium">
            How We Work
          </span>
          <h2 className="font-display mb-4 text-4xl text-zinc-50 italic md:text-5xl">
            Our Creative Production Process
          </h2>
          <p className="mx-auto max-w-3xl text-lg text-zinc-400">
            A streamlined process from brief to delivery—rooted in research, strategy, and
            creativity—to ensure every video and photo project hits your goals with impact.
          </p>
        </div>

        {/* Process Steps */}
        <div className="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-6">
          {processSteps.map((step, index) => (
            <div
              key={step.title}
              className="group relative"
              onMouseEnter={() => setHoveredIndex(index)}
              onMouseLeave={() => setHoveredIndex(null)}
            >
              {/* Connector Line */}
              {index < processSteps.length - 1 && (
                <div className="from-brand-primary/50 absolute top-16 left-[60%] hidden h-0.5 w-full bg-linear-to-r to-transparent lg:block" />
              )}

              <div className="flex flex-col items-center text-center">
                {/* Icon Circle */}
                <div
                  className={`relative mb-4 flex h-24 w-24 items-center justify-center rounded-full transition-all duration-500 md:h-28 md:w-28 ${
                    hoveredIndex === index
                      ? "from-brand-primary to-brand-accent shadow-brand-primary/30 scale-110 bg-linear-to-br shadow-2xl"
                      : "from-brand-deep to-brand-dark border-brand-primary/30 border-2 bg-linear-to-br"
                  }`}
                >
                  <div
                    className={`transition-colors duration-300 ${
                      hoveredIndex === index ? "text-zinc-50" : "text-brand-accent"
                    }`}
                  >
                    {step.icon}
                  </div>

                  {/* Step Number */}
                  <span
                    className={`absolute -top-2 -right-2 flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold transition-all duration-300 ${
                      hoveredIndex === index
                        ? "text-brand-primary bg-zinc-50"
                        : "bg-brand-dark text-zinc-50"
                    }`}
                  >
                    {index + 1}
                  </span>
                </div>

                {/* Title */}
                <h3
                  className={`mb-2 text-base font-semibold transition-colors duration-300 ${
                    hoveredIndex === index ? "text-brand-accent" : "text-zinc-50"
                  }`}
                >
                  {step.title}
                </h3>

                {/* Description - Shows on hover */}
                <p
                  className={`text-sm text-zinc-400 transition-all duration-300 ${
                    hoveredIndex === index
                      ? "max-h-20 opacity-100"
                      : "max-h-0 overflow-hidden opacity-0 md:max-h-20 md:opacity-100"
                  }`}
                >
                  {step.description}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
