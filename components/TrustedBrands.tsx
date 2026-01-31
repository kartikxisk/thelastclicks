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
    <section className="relative py-20 px-4 overflow-hidden">
      {/* Background */}
      <div className="absolute inset-0 bg-zinc-950" />
      
      {/* Subtle gradient overlay */}
      <div className="absolute inset-0 bg-gradient-to-r from-zinc-950 via-transparent to-zinc-950 z-10 pointer-events-none" />

      <div className="container relative">
        {/* Header */}
        <div className="text-center mb-12">
          <span className="inline-block px-4 py-2 rounded-full bg-brand-primary/20 text-brand-accent text-sm font-medium mb-4 border border-brand-primary/30">
            Our Partners
          </span>
          <h2 className="text-4xl md:text-5xl font-display text-zinc-50 mb-4 italic">
            Trusted by Leading Brands
          </h2>
          <p className="text-lg text-zinc-400 max-w-2xl mx-auto">
            We&apos;ve had the privilege of working with some of the most innovative companies in the world
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
            <div
              key={`${brand.name}-${index}`}
              className="flex-shrink-0 group"
            >
              <div className="w-48 h-24 rounded-2xl bg-gradient-to-br from-brand-deep/50 to-zinc-950 border border-brand-dark/30 flex items-center justify-center transition-all duration-300 hover:border-brand-primary/50 hover:bg-brand-deep/30">
                <span className="text-2xl font-bold text-zinc-500 group-hover:text-brand-accent transition-colors duration-300 tracking-wider">
                  {brand.logo}
                </span>
              </div>
            </div>
          ))}
        </div>

        {/* Stats below brands */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-8 mt-16 pt-16 border-t border-brand-dark/30">
          {[
            { value: "100+", label: "Brand Partners" },
            { value: "500+", label: "Projects Delivered" },
            { value: "50M+", label: "Content Views" },
            { value: "98%", label: "Client Retention" },
          ].map((stat) => (
            <div key={stat.label} className="text-center group">
              <div className="text-4xl md:text-5xl font-bold bg-gradient-to-r from-brand-primary to-brand-accent bg-clip-text text-transparent mb-2 group-hover:scale-110 transition-transform duration-300">
                {stat.value}
              </div>
              <div className="text-zinc-400 group-hover:text-zinc-300 transition-colors">
                {stat.label}
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
