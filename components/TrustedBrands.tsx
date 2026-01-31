"use client";

import { useEffect, useRef } from "react";

const brands = [
  { name: "Sony", logo: "SONY" },
  { name: "Canon", logo: "Canon" },
  { name: "Netflix", logo: "NETFLIX" },
  { name: "Adobe", logo: "Adobe" },
  { name: "Red Bull", logo: "RED BULL" },
  { name: "Nike", logo: "NIKE" },
  { name: "Apple", logo: "Apple" },
  { name: "Samsung", logo: "SAMSUNG" },
  { name: "Google", logo: "Google" },
  { name: "Meta", logo: "Meta" },
];

export default function TrustedBrands() {
  const scrollRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const scrollContainer = scrollRef.current;
    if (!scrollContainer) return;

    let animationId: number;
    let scrollPosition = 0;

    const animate = () => {
      scrollPosition += 0.5;
      if (scrollPosition >= scrollContainer.scrollWidth / 2) {
        scrollPosition = 0;
      }
      scrollContainer.scrollLeft = scrollPosition;
      animationId = requestAnimationFrame(animate);
    };

    animationId = requestAnimationFrame(animate);

    return () => {
      cancelAnimationFrame(animationId);
    };
  }, []);

  return (
    <section className="relative overflow-hidden px-4 py-20">
      {/* Background */}
      <div className="absolute inset-0 bg-zinc-950" />

      {/* Subtle gradient overlay */}
      <div className="pointer-events-none absolute inset-0 z-10 bg-linear-to-r from-zinc-950 via-transparent to-zinc-950" />

      <div className="relative container">
        {/* Header */}
        <div className="mb-12 text-center">
          <span className="bg-brand-primary/20 text-brand-accent border-brand-primary/30 mb-4 inline-block rounded-full border px-4 py-2 text-sm font-medium">
            Our Partners
          </span>
          <h2 className="font-display mb-4 text-4xl text-zinc-50 italic md:text-5xl">
            Trusted by Leading Brands
          </h2>
          <p className="mx-auto max-w-2xl text-lg text-zinc-400">
            We&apos;ve had the privilege of working with some of the most innovative companies in
            the world
          </p>
        </div>

        {/* Scrolling Logos */}
        <div
          ref={scrollRef}
          className="flex gap-12 overflow-hidden"
          style={{ scrollBehavior: "auto" }}
        >
          {/* Duplicate brands for infinite scroll effect */}
          {[...brands, ...brands].map((brand, index) => (
            <div key={`${brand.name}-${index}`} className="group flex-shrink-0">
              <div className="from-brand-deep/50 border-brand-dark/30 hover:border-brand-primary/50 hover:bg-brand-deep/30 flex h-24 w-48 items-center justify-center rounded-2xl border bg-linear-to-br to-zinc-950 transition-all duration-300">
                <span className="group-hover:text-brand-accent text-2xl font-bold tracking-wider text-zinc-500 transition-colors duration-300">
                  {brand.logo}
                </span>
              </div>
            </div>
          ))}
        </div>

        {/* Stats below brands */}
        <div className="border-brand-dark/30 mt-16 grid grid-cols-2 gap-8 border-t pt-16 md:grid-cols-4">
          {[
            { value: "100+", label: "Brand Partners" },
            { value: "500+", label: "Projects Delivered" },
            { value: "50M+", label: "Content Views" },
            { value: "98%", label: "Client Retention" },
          ].map((stat) => (
            <div key={stat.label} className="group text-center">
              <div className="from-brand-primary to-brand-accent mb-2 bg-linear-to-r bg-clip-text text-4xl font-bold text-transparent transition-transform duration-300 group-hover:scale-110 md:text-5xl">
                {stat.value}
              </div>
              <div className="text-zinc-400 transition-colors group-hover:text-zinc-300">
                {stat.label}
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
