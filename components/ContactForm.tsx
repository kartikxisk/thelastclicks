"use client";

import { useState, FormEvent } from "react";
import { SERVICES } from "@/lib/constants";
import { HiCheckCircle } from "react-icons/hi";

export default function ContactForm() {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSubmitted, setIsSubmitted] = useState(false);

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setIsSubmitting(true);

    // Simulate form submission
    await new Promise((resolve) => setTimeout(resolve, 1000));

    setIsSubmitting(false);
    setIsSubmitted(true);
  };

  if (isSubmitted) {
    return (
      <div className="py-12 text-center">
        <HiCheckCircle className="text-brand-accent mx-auto mb-4 h-16 w-16" />
        <h4 className="mb-2 text-2xl font-bold text-zinc-50">Thank You!</h4>
        <p className="text-zinc-400">
          We&apos;ve received your message and will get back to you soon.
        </p>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
          <label htmlFor="name" className="mb-2 block text-sm font-medium text-zinc-300">
            Full Name *
          </label>
          <input
            type="text"
            id="name"
            name="name"
            required
            className="border-brand-dark/50 focus:border-brand-primary focus:ring-brand-primary/20 w-full rounded-xl border bg-zinc-950/50 px-4 py-3 text-zinc-50 transition-all outline-none placeholder:text-zinc-500 focus:ring-2"
            placeholder="John Doe"
          />
        </div>
        <div>
          <label htmlFor="email" className="mb-2 block text-sm font-medium text-zinc-300">
            Email Address *
          </label>
          <input
            type="email"
            id="email"
            name="email"
            required
            className="border-brand-dark/50 focus:border-brand-primary focus:ring-brand-primary/20 w-full rounded-xl border bg-zinc-950/50 px-4 py-3 text-zinc-50 transition-all outline-none placeholder:text-zinc-500 focus:ring-2"
            placeholder="john@example.com"
          />
        </div>
      </div>

      <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
          <label htmlFor="phone" className="mb-2 block text-sm font-medium text-zinc-300">
            Phone Number
          </label>
          <input
            type="tel"
            id="phone"
            name="phone"
            className="border-brand-dark/50 focus:border-brand-primary focus:ring-brand-primary/20 w-full rounded-xl border bg-zinc-950/50 px-4 py-3 text-zinc-50 transition-all outline-none placeholder:text-zinc-500 focus:ring-2"
            placeholder="+91 9876543210"
          />
        </div>
        <div>
          <label htmlFor="service" className="mb-2 block text-sm font-medium text-zinc-300">
            Service Interested In
          </label>
          <select
            id="service"
            name="service"
            className="border-brand-dark/50 focus:border-brand-primary focus:ring-brand-primary/20 w-full cursor-pointer appearance-none rounded-xl border bg-zinc-950/50 px-4 py-3 text-zinc-50 transition-all outline-none focus:ring-2"
            style={{
              backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23a1a1aa'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E")`,
              backgroundRepeat: "no-repeat",
              backgroundPosition: "right 1rem center",
              backgroundSize: "1.5rem",
            }}
          >
            <option value="" className="bg-zinc-900 text-zinc-400">
              Select a service
            </option>
            {Object.values(SERVICES).map((service) => (
              <option key={service.href} value={service.name} className="bg-zinc-900 text-zinc-50">
                {service.name}
              </option>
            ))}
          </select>
        </div>
      </div>

      <div>
        <label htmlFor="message" className="mb-2 block text-sm font-medium text-zinc-300">
          Your Message *
        </label>
        <textarea
          id="message"
          name="message"
          required
          rows={5}
          className="border-brand-dark/50 focus:border-brand-primary focus:ring-brand-primary/20 w-full resize-none rounded-xl border bg-zinc-950/50 px-4 py-3 text-zinc-50 transition-all outline-none placeholder:text-zinc-500 focus:ring-2"
          placeholder="Tell us about your project..."
        />
      </div>

      <button
        type="submit"
        disabled={isSubmitting}
        className="from-brand-primary to-brand-accent hover:shadow-brand-primary/30 w-full rounded-xl bg-linear-to-r px-8 py-4 font-semibold text-zinc-50 transition-all hover:scale-[1.02] hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100"
      >
        {isSubmitting ? (
          <span className="flex items-center justify-center">
            <svg
              className="mr-3 -ml-1 h-5 w-5 animate-spin text-zinc-50"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                className="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                strokeWidth="4"
              />
              <path
                className="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              />
            </svg>
            Sending...
          </span>
        ) : (
          "Send Message"
        )}
      </button>
    </form>
  );
}
