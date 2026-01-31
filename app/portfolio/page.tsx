"use client";

import { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import {
  COMPANY,
  PHOTOGRAPHY_PORTFOLIO,
  VIDEOGRAPHY_PORTFOLIO,
  PORTFOLIO_CATEGORIES,
} from "@/lib/constants";
import CTASection from "@/components/CTASection";
import {
  FiCamera,
  FiVideo,
  FiPlay,
  FiMapPin,
  FiCalendar,
  FiClock,
  FiExternalLink,
  FiX,
} from "react-icons/fi";
import { HiSparkles } from "react-icons/hi";
import { BiCameraMovie } from "react-icons/bi";

type PortfolioTab = "photography" | "videography";

export default function PortfolioPage() {
  const [activeTab, setActiveTab] = useState<PortfolioTab>("photography");
  const [activeCategory, setActiveCategory] = useState("All");
  const [selectedVideo, setSelectedVideo] = useState<(typeof VIDEOGRAPHY_PORTFOLIO)[number] | null>(
    null
  );
  const [selectedPhoto, setSelectedPhoto] = useState<(typeof PHOTOGRAPHY_PORTFOLIO)[number] | null>(
    null
  );

  const categories = PORTFOLIO_CATEGORIES[activeTab];

  const filteredPhotos =
    activeCategory === "All"
      ? PHOTOGRAPHY_PORTFOLIO
      : PHOTOGRAPHY_PORTFOLIO.filter((item) => item.category === activeCategory);

  const filteredVideos =
    activeCategory === "All"
      ? VIDEOGRAPHY_PORTFOLIO
      : VIDEOGRAPHY_PORTFOLIO.filter((item) => item.category === activeCategory);

  const handleTabChange = (tab: PortfolioTab) => {
    setActiveTab(tab);
    setActiveCategory("All");
  };

  const getYouTubeEmbedUrl = (url: string) => {
    const videoId = url.split("v=")[1]?.split("&")[0];
    return `https://www.youtube.com/embed/${videoId}?autoplay=1`;
  };

  return (
    <main className="min-h-screen">
      {/* Hero Section */}
      <section className="relative overflow-hidden px-4 pt-32 pb-20">
        <div className="from-brand-deep to-brand-dark absolute inset-0 bg-linear-to-br via-zinc-950" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-10" />

        {/* Decorative Elements */}
        <div className="bg-brand-primary absolute top-20 left-10 h-72 w-72 animate-pulse rounded-full opacity-20 blur-[128px]" />
        <div className="bg-brand-accent absolute right-10 bottom-10 h-96 w-96 animate-pulse rounded-full opacity-20 blur-[128px]" />
        <div className="border-brand-dark/20 absolute top-1/2 left-1/2 h-[600px] w-[600px] -translate-x-1/2 -translate-y-1/2 rounded-full border" />
        <div className="border-brand-dark/10 absolute top-1/2 left-1/2 h-[800px] w-[800px] -translate-x-1/2 -translate-y-1/2 rounded-full border" />

        <div className="relative container text-center">
          <span className="bg-brand-primary/20 text-brand-accent border-brand-primary/30 mb-6 inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium backdrop-blur-sm">
            <HiSparkles className="h-4 w-4" />
            Our Creative Work
          </span>
          <h1 className="mb-6 text-4xl leading-tight font-bold text-zinc-50 md:text-6xl lg:text-7xl">
            Explore Our
            <br />
            <span className="from-brand-primary via-brand-accent to-brand-primary bg-linear-to-r bg-clip-text text-transparent">
              Portfolio
            </span>
          </h1>
          <p className="mx-auto mb-12 max-w-2xl text-xl text-zinc-400">
            A curated collection of our finest photography and videography work, showcasing
            creativity and excellence
          </p>

          {/* Main Tab Switcher */}
          <div className="border-brand-dark/30 inline-flex rounded-2xl border bg-zinc-900/80 p-2 backdrop-blur-sm">
            <button
              onClick={() => handleTabChange("photography")}
              className={`flex items-center gap-3 rounded-xl px-8 py-4 font-semibold transition-all duration-300 ${
                activeTab === "photography"
                  ? "from-brand-primary to-brand-accent shadow-brand-primary/30 bg-linear-to-r text-zinc-50 shadow-lg"
                  : "text-zinc-400 hover:text-zinc-50"
              }`}
            >
              <FiCamera className="h-5 w-5" />
              Photography
            </button>
            <button
              onClick={() => handleTabChange("videography")}
              className={`flex items-center gap-3 rounded-xl px-8 py-4 font-semibold transition-all duration-300 ${
                activeTab === "videography"
                  ? "from-brand-primary to-brand-accent shadow-brand-primary/30 bg-linear-to-r text-zinc-50 shadow-lg"
                  : "text-zinc-400 hover:text-zinc-50"
              }`}
            >
              <FiVideo className="h-5 w-5" />
              Videography
            </button>
          </div>
        </div>
      </section>

      {/* Category Filter */}
      <section className="border-brand-dark/30 sticky top-16 z-40 border-b bg-zinc-950 px-4 py-6 backdrop-blur-md">
        <div className="container">
          <div className="flex flex-wrap justify-center gap-3">
            {categories.map((category) => (
              <button
                key={category}
                onClick={() => setActiveCategory(category)}
                className={`rounded-full px-5 py-2.5 font-medium transition-all duration-300 ${
                  activeCategory === category
                    ? "from-brand-primary to-brand-accent shadow-brand-primary/20 bg-linear-to-r text-zinc-50 shadow-lg"
                    : "hover:bg-brand-dark/30 hover:text-brand-accent border-brand-dark/30 border bg-zinc-900 text-zinc-400"
                }`}
              >
                {category}
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* Portfolio Grid */}
      <section className="bg-zinc-950 px-4 py-20">
        <div className="container">
          {/* Photography Grid */}
          {activeTab === "photography" && (
            <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
              {filteredPhotos.map((item, index) => (
                <div
                  key={item.id}
                  onClick={() => setSelectedPhoto(item)}
                  className={`group border-brand-dark/30 hover:shadow-brand-primary/20 hover:border-brand-primary/50 relative cursor-pointer overflow-hidden rounded-3xl border transition-all duration-500 hover:shadow-2xl ${
                    item.featured && index === 0 ? "md:col-span-2 md:row-span-2" : ""
                  }`}
                >
                  {/* Image Container */}
                  <div
                    className={`from-brand-deep to-brand-dark/30 relative overflow-hidden bg-linear-to-br via-zinc-900 ${
                      item.featured && index === 0 ? "aspect-square" : "aspect-[4/3]"
                    }`}
                  >
                    {/* Placeholder with icon - replace with actual images */}
                    <div className="absolute inset-0 flex items-center justify-center">
                      <FiCamera className="text-brand-dark/50 h-16 w-16" />
                    </div>

                    {/* Overlay */}
                    <div className="absolute inset-0 bg-linear-to-t from-zinc-950 via-zinc-950/20 to-transparent opacity-60 transition-opacity duration-300 group-hover:opacity-80" />

                    {/* Featured Badge */}
                    {item.featured && (
                      <div className="from-brand-primary to-brand-accent absolute top-4 left-4 flex items-center gap-1.5 rounded-full bg-linear-to-r px-3 py-1.5 text-xs font-semibold text-zinc-50">
                        <HiSparkles className="h-3 w-3" />
                        Featured
                      </div>
                    )}

                    {/* Content Overlay */}
                    <div className="absolute right-0 bottom-0 left-0 p-6">
                      <span className="bg-brand-primary/30 text-brand-accent border-brand-primary/30 mb-3 inline-block rounded-full border px-3 py-1 text-xs font-medium backdrop-blur-sm">
                        {item.category}
                      </span>
                      <h3
                        className={`group-hover:text-brand-accent mb-2 font-bold text-zinc-50 transition-colors ${
                          item.featured && index === 0 ? "text-2xl md:text-3xl" : "text-xl"
                        }`}
                      >
                        {item.title}
                      </h3>
                      <div className="flex items-center gap-4 text-sm text-zinc-400">
                        <span className="flex items-center gap-1.5">
                          <FiMapPin className="h-3.5 w-3.5" />
                          {item.location}
                        </span>
                        <span className="flex items-center gap-1.5">
                          <FiCalendar className="h-3.5 w-3.5" />
                          {item.date}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}

          {/* Videography Grid */}
          {activeTab === "videography" && (
            <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
              {filteredVideos.map((item, index) => (
                <div
                  key={item.id}
                  onClick={() => setSelectedVideo(item)}
                  className={`group border-brand-dark/30 hover:shadow-brand-primary/20 hover:border-brand-primary/50 relative cursor-pointer overflow-hidden rounded-3xl border transition-all duration-500 hover:shadow-2xl ${
                    item.featured && index === 0 ? "md:col-span-2 md:row-span-2" : ""
                  }`}
                >
                  {/* Video Thumbnail */}
                  <div
                    className={`from-brand-deep to-brand-dark/30 relative overflow-hidden bg-linear-to-br via-zinc-900 ${
                      item.featured && index === 0
                        ? "aspect-video md:aspect-square"
                        : "aspect-video"
                    }`}
                  >
                    {/* Placeholder with icon - replace with actual thumbnails */}
                    <div className="absolute inset-0 flex items-center justify-center">
                      <BiCameraMovie className="text-brand-dark/50 h-16 w-16" />
                    </div>

                    {/* Overlay */}
                    <div className="absolute inset-0 bg-linear-to-t from-zinc-950 via-zinc-950/30 to-transparent opacity-70 transition-opacity duration-300 group-hover:opacity-80" />

                    {/* Play Button - centered in card */}
                    <div className="pointer-events-none absolute inset-0 z-10 flex items-center justify-center">
                      <div className="from-brand-primary to-brand-accent shadow-brand-primary/50 pointer-events-auto flex h-14 w-14 items-center justify-center rounded-full bg-linear-to-br shadow-2xl transition-transform duration-300 group-hover:scale-110">
                        <FiPlay className="ml-0.5 h-5 w-5 text-zinc-50" />
                      </div>
                    </div>

                    {/* Duration Badge */}
                    <div className="absolute top-4 right-4 z-20 flex items-center gap-1.5 rounded-full border border-zinc-800 bg-zinc-950/80 px-3 py-1.5 text-xs font-medium text-zinc-50 backdrop-blur-sm">
                      <FiClock className="h-3 w-3" />
                      {item.duration}
                    </div>

                    {/* Top Left Badges */}
                    <div className="absolute top-4 left-4 z-20 flex flex-col gap-2">
                      {/* Featured Badge */}
                      {item.featured && (
                        <div className="from-brand-primary to-brand-accent flex w-fit items-center gap-1.5 rounded-full bg-linear-to-r px-3 py-1.5 text-xs font-semibold text-zinc-50">
                          <HiSparkles className="h-3 w-3" />
                          Featured
                        </div>
                      )}
                      {/* Video Type Badge */}
                      <div className="w-fit rounded-lg border border-zinc-800 bg-zinc-950/80 px-2.5 py-1 text-xs font-medium text-zinc-400 backdrop-blur-sm">
                        {item.videoType === "youtube" ? "YouTube" : "HD Video"}
                      </div>
                    </div>

                    {/* Content Overlay */}
                    <div className="absolute right-0 bottom-0 left-0 z-20 p-5">
                      <span className="bg-brand-primary/30 text-brand-accent border-brand-primary/30 mb-2 inline-block rounded-full border px-3 py-1 text-xs font-medium backdrop-blur-sm">
                        {item.category}
                      </span>
                      <h3
                        className={`group-hover:text-brand-accent mb-1.5 font-bold text-zinc-50 transition-colors ${
                          item.featured && index === 0 ? "text-2xl md:text-3xl" : "text-base"
                        }`}
                      >
                        {item.title}
                      </h3>
                      <div className="flex flex-wrap items-center gap-3 text-sm text-zinc-400">
                        <span className="flex items-center gap-1.5">
                          <FiMapPin className="h-3.5 w-3.5" />
                          {item.location}
                        </span>
                        <span className="flex items-center gap-1.5">
                          <FiCalendar className="h-3.5 w-3.5" />
                          {item.date}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </section>

      {/* Stats Section */}
      <section className="from-brand-deep to-brand-dark relative overflow-hidden bg-linear-to-br via-zinc-950 px-4 py-20">
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-5" />
        <div className="bg-brand-primary absolute top-0 left-1/4 h-96 w-96 rounded-full opacity-10 blur-[150px]" />
        <div className="bg-brand-accent absolute right-1/4 bottom-0 h-96 w-96 rounded-full opacity-10 blur-[150px]" />

        <div className="relative container">
          <div className="mb-16 text-center">
            <h2 className="mb-4 text-3xl font-bold text-zinc-50 md:text-4xl">Numbers That Speak</h2>
            <p className="mx-auto max-w-xl text-zinc-400">
              Our journey of capturing moments and creating memories
            </p>
          </div>

          <div className="grid grid-cols-2 gap-8 md:grid-cols-4">
            {[
              { value: "500+", label: "Projects Completed", icon: HiSparkles },
              { value: "200+", label: "Happy Clients", icon: FiCamera },
              { value: "15+", label: "Industry Awards", icon: BiCameraMovie },
              { value: "10+", label: "Years Experience", icon: FiVideo },
            ].map((stat) => (
              <div key={stat.label} className="group text-center">
                <div className="from-brand-primary/20 to-brand-accent/20 border-brand-primary/30 mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl border bg-linear-to-br transition-transform group-hover:scale-110">
                  <stat.icon className="text-brand-accent h-7 w-7" />
                </div>
                <div className="from-brand-primary to-brand-accent mb-2 bg-linear-to-r bg-clip-text text-4xl font-bold text-transparent md:text-5xl">
                  {stat.value}
                </div>
                <div className="text-zinc-400">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Video Modal */}
      {selectedVideo && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/95 p-4 backdrop-blur-sm"
          onClick={() => setSelectedVideo(null)}
        >
          <div
            className="border-brand-dark/30 relative w-full max-w-5xl overflow-hidden rounded-3xl border bg-zinc-900"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Close Button */}
            <button
              onClick={() => setSelectedVideo(null)}
              className="hover:bg-brand-primary absolute top-4 right-4 z-10 flex h-10 w-10 items-center justify-center rounded-full border border-zinc-800 bg-zinc-950/80 text-zinc-50 backdrop-blur-sm transition-colors"
            >
              <FiX className="h-5 w-5" />
            </button>

            {/* Video Player */}
            <div className="aspect-video bg-zinc-950">
              {selectedVideo.videoType === "youtube" ? (
                <iframe
                  src={getYouTubeEmbedUrl(selectedVideo.videoUrl)}
                  className="h-full w-full"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                />
              ) : (
                <video
                  src={selectedVideo.videoUrl}
                  controls
                  autoPlay
                  className="h-full w-full object-contain"
                />
              )}
            </div>

            {/* Video Info */}
            <div className="p-6">
              <div className="flex items-start justify-between gap-4">
                <div>
                  <span className="bg-brand-primary/20 text-brand-accent border-brand-primary/30 mb-2 inline-block rounded-full border px-3 py-1 text-xs font-medium">
                    {selectedVideo.category}
                  </span>
                  <h3 className="mb-2 text-2xl font-bold text-zinc-50">{selectedVideo.title}</h3>
                  <p className="mb-4 text-zinc-400">{selectedVideo.description}</p>
                  <div className="flex items-center gap-4 text-sm text-zinc-500">
                    <span className="flex items-center gap-1.5">
                      <FiMapPin className="h-4 w-4" />
                      {selectedVideo.location}
                    </span>
                    <span className="flex items-center gap-1.5">
                      <FiCalendar className="h-4 w-4" />
                      {selectedVideo.date}
                    </span>
                    <span className="flex items-center gap-1.5">
                      <FiClock className="h-4 w-4" />
                      {selectedVideo.duration}
                    </span>
                  </div>
                </div>
                {selectedVideo.videoType === "youtube" && (
                  <a
                    href={selectedVideo.videoUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="bg-brand-primary hover:bg-brand-accent flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium text-zinc-50 transition-colors"
                  >
                    <FiExternalLink className="h-4 w-4" />
                    YouTube
                  </a>
                )}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Photo Modal */}
      {selectedPhoto && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/95 p-4 backdrop-blur-sm"
          onClick={() => setSelectedPhoto(null)}
        >
          <div
            className="border-brand-dark/30 relative w-full max-w-5xl overflow-hidden rounded-3xl border bg-zinc-900"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Close Button */}
            <button
              onClick={() => setSelectedPhoto(null)}
              className="hover:bg-brand-primary absolute top-4 right-4 z-10 flex h-10 w-10 items-center justify-center rounded-full border border-zinc-800 bg-zinc-950/80 text-zinc-50 backdrop-blur-sm transition-colors"
            >
              <FiX className="h-5 w-5" />
            </button>

            {/* Image Gallery Placeholder */}
            <div className="from-brand-deep to-brand-dark/30 flex aspect-video items-center justify-center bg-linear-to-br via-zinc-900">
              <div className="text-center">
                <FiCamera className="text-brand-dark/50 mx-auto mb-4 h-20 w-20" />
                <p className="text-zinc-500">
                  Image gallery - {selectedPhoto.images.length} photos
                </p>
              </div>
            </div>

            {/* Photo Info */}
            <div className="p-6">
              <span className="bg-brand-primary/20 text-brand-accent border-brand-primary/30 mb-2 inline-block rounded-full border px-3 py-1 text-xs font-medium">
                {selectedPhoto.category}
              </span>
              <h3 className="mb-2 text-2xl font-bold text-zinc-50">{selectedPhoto.title}</h3>
              <p className="mb-4 text-zinc-400">{selectedPhoto.description}</p>
              <div className="flex items-center gap-4 text-sm text-zinc-500">
                <span className="flex items-center gap-1.5">
                  <FiMapPin className="h-4 w-4" />
                  {selectedPhoto.location}
                </span>
                <span className="flex items-center gap-1.5">
                  <FiCalendar className="h-4 w-4" />
                  {selectedPhoto.date}
                </span>
              </div>
            </div>
          </div>
        </div>
      )}

      <CTASection
        title="Ready to Create Something Amazing?"
        description="Let's bring your vision to life with our photography and videography expertise"
        primaryButton={{ text: "Start Your Project", href: "/contact" }}
        secondaryButton={{ text: "View Services", href: "/services" }}
      />
    </main>
  );
}
