"use client";

import { useState, useEffect, useCallback } from "react";

interface Testimonial {
  readonly id: number;
  readonly name: string;
  readonly role: string;
  readonly company: string;
  readonly content: string;
  readonly rating: number;
  readonly image?: string;
}

const testimonials: readonly Testimonial[] = [
  {
    id: 1,
    name: "Sarah Johnson",
    role: "Marketing Director",
    company: "TechVision Inc.",
    content: "The Last Clicks transformed our brand video into something truly exceptional. Their attention to detail and creative vision exceeded our expectations. The team was professional, responsive, and delivered on time.",
    rating: 5,
  },
  {
    id: 2,
    name: "Michael Chen",
    role: "CEO",
    company: "StartupX",
    content: "We've worked with many production companies, but none compare to the quality and dedication of The Last Clicks. They captured our product launch perfectly and the final edit was stunning.",
    rating: 5,
  },
  {
    id: 3,
    name: "Emily Rodriguez",
    role: "Event Manager",
    company: "Grand Events Co.",
    content: "Absolutely phenomenal work on our corporate event coverage. The photos and videos they delivered helped us showcase our brand in the best possible light. Highly recommend!",
    rating: 5,
  },
  {
    id: 4,
    name: "David Williams",
    role: "Creative Director",
    company: "Artisan Studios",
    content: "Their editing skills are top-notch. They took our raw footage and turned it into a cinematic masterpiece. The color grading and sound design were particularly impressive.",
    rating: 5,
  },
  {
    id: 5,
    name: "Lisa Park",
    role: "Brand Manager",
    company: "Fashion Forward",
    content: "The photography team captured our fashion line beautifully. Every shot was magazine-worthy. They understood our aesthetic and delivered beyond what we imagined possible.",
    rating: 5,
  },
];

export default function Testimonials() {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isAnimating, setIsAnimating] = useState(false);

  const handleNext = useCallback(() => {
    if (isAnimating) return;
    setIsAnimating(true);
    setCurrentIndex((prev) => (prev + 1) % testimonials.length);
    setTimeout(() => setIsAnimating(false), 500);
  }, [isAnimating]);

  const handlePrev = () => {
    if (isAnimating) return;
    setIsAnimating(true);
    setCurrentIndex((prev) => (prev - 1 + testimonials.length) % testimonials.length);
    setTimeout(() => setIsAnimating(false), 500);
  };

  const handleDotClick = (index: number) => {
    if (isAnimating || index === currentIndex) return;
    setIsAnimating(true);
    setCurrentIndex(index);
    setTimeout(() => setIsAnimating(false), 500);
  };

  useEffect(() => {
    const interval = setInterval(() => {
      handleNext();
    }, 5000);

    return () => clearInterval(interval);
  }, [handleNext]);

  return (
    <section className="relative py-24 px-4 overflow-hidden">
      {/* Background */}
      <div className="absolute inset-0 bg-gradient-to-b from-zinc-950 via-[#280905]/50 to-zinc-950" />
      
      {/* Decorative Elements */}
      <div className="absolute top-1/2 left-0 w-72 h-72 bg-[#C3110C] rounded-full blur-[200px] opacity-10 -translate-y-1/2" />
      <div className="absolute top-1/2 right-0 w-72 h-72 bg-[#E6501B] rounded-full blur-[200px] opacity-10 -translate-y-1/2" />

      {/* Quote Icon Background */}
      <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-5">
        <svg className="w-96 h-96" fill="currentColor" viewBox="0 0 24 24">
          <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
        </svg>
      </div>

      <div className="relative max-w-6xl mx-auto">
        {/* Header */}
        <div className="text-center mb-16">
          <span className="inline-block px-4 py-2 rounded-full bg-[#C3110C]/20 text-[#E6501B] text-sm font-medium mb-4 border border-[#C3110C]/30">
            Testimonials
          </span>
          <h2 className="text-4xl md:text-5xl font-display text-zinc-50 mb-4 italic">
            What Our Clients Say
          </h2>
          <p className="text-lg text-zinc-400 max-w-2xl mx-auto">
            Don&apos;t just take our word for it—hear from the brands and individuals who&apos;ve trusted us with their vision
          </p>
        </div>

        {/* Testimonial Card */}
        <div className="relative">
          <div 
            className={`bg-gradient-to-br from-[#280905] to-zinc-950 rounded-3xl p-8 md:p-12 border border-[#740A03]/30 transition-all duration-500 ${
              isAnimating ? "opacity-0 scale-95" : "opacity-100 scale-100"
            }`}
          >
            {/* Stars */}
            <div className="flex gap-1 mb-6">
              {Array.from({ length: testimonials[currentIndex].rating }).map((_, i) => (
                <svg key={`star-${testimonials[currentIndex].id}-${i}`} className="w-6 h-6 text-[#E6501B]" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              ))}
            </div>

            {/* Quote */}
            <blockquote className="text-xl md:text-2xl text-zinc-50 leading-relaxed mb-8">
              &ldquo;{testimonials[currentIndex].content}&rdquo;
            </blockquote>

            {/* Author */}
            <div className="flex items-center gap-4">
              <div className="w-14 h-14 rounded-full bg-gradient-to-br from-[#C3110C] to-[#E6501B] flex items-center justify-center text-zinc-50 font-bold text-lg">
                {testimonials[currentIndex].name.charAt(0)}
              </div>
              <div>
                <div className="text-lg font-semibold text-zinc-50">
                  {testimonials[currentIndex].name}
                </div>
                <div className="text-zinc-400">
                  {testimonials[currentIndex].role} at {testimonials[currentIndex].company}
                </div>
              </div>
            </div>
          </div>

          {/* Navigation Arrows */}
          <button
            onClick={handlePrev}
            className="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 md:-translate-x-6 w-12 h-12 rounded-full bg-[#280905] border border-[#740A03]/50 flex items-center justify-center text-zinc-50 hover:bg-[#740A03] hover:border-[#C3110C] transition-all duration-300 group"
            aria-label="Previous testimonial"
          >
            <svg className="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button
            onClick={handleNext}
            className="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 md:translate-x-6 w-12 h-12 rounded-full bg-[#280905] border border-[#740A03]/50 flex items-center justify-center text-zinc-50 hover:bg-[#740A03] hover:border-[#C3110C] transition-all duration-300 group"
            aria-label="Next testimonial"
          >
            <svg className="w-5 h-5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        {/* Dots Navigation */}
        <div className="flex justify-center gap-3 mt-8">
          {testimonials.map((testimonial) => (
            <button
              key={`dot-${testimonial.id}`}
              onClick={() => handleDotClick(testimonials.indexOf(testimonial))}
              className={`w-3 h-3 rounded-full transition-all duration-300 ${
                testimonials.indexOf(testimonial) === currentIndex 
                  ? "bg-[#C3110C] w-8" 
                  : "bg-[#740A03]/50 hover:bg-[#740A03]"
              }`}
              aria-label={`Go to testimonial ${testimonials.indexOf(testimonial) + 1}`}
            />
          ))}
        </div>
      </div>
    </section>
  );
}
