"use client";

import { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { COMPANY, PHOTOGRAPHY_PORTFOLIO, VIDEOGRAPHY_PORTFOLIO, PORTFOLIO_CATEGORIES } from "@/lib/constants";
import CTASection from "@/components/CTASection";
import { FiCamera, FiVideo, FiPlay, FiMapPin, FiCalendar, FiClock, FiExternalLink, FiX } from "react-icons/fi";
import { HiSparkles } from "react-icons/hi";
import { BiCameraMovie } from "react-icons/bi";

type PortfolioTab = "photography" | "videography";

export default function PortfolioPage() {
  const [activeTab, setActiveTab] = useState<PortfolioTab>("photography");
  const [activeCategory, setActiveCategory] = useState("All");
  const [selectedVideo, setSelectedVideo] = useState<typeof VIDEOGRAPHY_PORTFOLIO[number] | null>(null);
  const [selectedPhoto, setSelectedPhoto] = useState<typeof PHOTOGRAPHY_PORTFOLIO[number] | null>(null);

  const categories = PORTFOLIO_CATEGORIES[activeTab];
  
  const filteredPhotos = activeCategory === "All" 
    ? PHOTOGRAPHY_PORTFOLIO 
    : PHOTOGRAPHY_PORTFOLIO.filter(item => item.category === activeCategory);
    
  const filteredVideos = activeCategory === "All"
    ? VIDEOGRAPHY_PORTFOLIO
    : VIDEOGRAPHY_PORTFOLIO.filter(item => item.category === activeCategory);

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
      <section className="relative pt-32 pb-20 px-4 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-10" />
        
        {/* Decorative Elements */}
        <div className="absolute top-20 left-10 w-72 h-72 bg-brand-primary rounded-full blur-[128px] opacity-20 animate-pulse" />
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-brand-accent rounded-full blur-[128px] opacity-20 animate-pulse" />
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] border border-brand-dark/20 rounded-full" />
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] border border-brand-dark/10 rounded-full" />

        <div className="relative max-w-7xl mx-auto text-center">
          <span className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brand-primary/20 text-brand-accent text-sm font-medium mb-6 backdrop-blur-sm border border-brand-primary/30">
            <HiSparkles className="w-4 h-4" />
            Our Creative Work
          </span>
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-zinc-50 mb-6 leading-tight">
            Explore Our
            <br />
            <span className="bg-gradient-to-r from-brand-primary via-brand-accent to-brand-primary bg-clip-text text-transparent">
              Portfolio
            </span>
          </h1>
          <p className="text-xl text-zinc-400 max-w-2xl mx-auto mb-12">
            A curated collection of our finest photography and videography work, showcasing creativity and excellence
          </p>

          {/* Main Tab Switcher */}
          <div className="inline-flex p-2 bg-zinc-900/80 backdrop-blur-sm rounded-2xl border border-brand-dark/30">
            <button
              onClick={() => handleTabChange("photography")}
              className={`flex items-center gap-3 px-8 py-4 rounded-xl font-semibold transition-all duration-300 ${
                activeTab === "photography"
                  ? "bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 shadow-lg shadow-brand-primary/30"
                  : "text-zinc-400 hover:text-zinc-50"
              }`}
            >
              <FiCamera className="w-5 h-5" />
              Photography
            </button>
            <button
              onClick={() => handleTabChange("videography")}
              className={`flex items-center gap-3 px-8 py-4 rounded-xl font-semibold transition-all duration-300 ${
                activeTab === "videography"
                  ? "bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 shadow-lg shadow-brand-primary/30"
                  : "text-zinc-400 hover:text-zinc-50"
              }`}
            >
              <FiVideo className="w-5 h-5" />
              Videography
            </button>
          </div>
        </div>
      </section>

      {/* Category Filter */}
      <section className="py-6 px-4 bg-zinc-950 border-b border-brand-dark/30 sticky top-16 z-40 backdrop-blur-md">
        <div className="max-w-7xl mx-auto">
          <div className="flex flex-wrap gap-3 justify-center">
            {categories.map((category) => (
              <button
                key={category}
                onClick={() => setActiveCategory(category)}
                className={`px-5 py-2.5 rounded-full font-medium transition-all duration-300 ${
                  activeCategory === category
                    ? "bg-gradient-to-r from-brand-primary to-brand-accent text-zinc-50 shadow-lg shadow-brand-primary/20"
                    : "bg-zinc-900 text-zinc-400 hover:bg-brand-dark/30 hover:text-brand-accent border border-brand-dark/30"
                }`}
              >
                {category}
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* Portfolio Grid */}
      <section className="py-20 px-4 bg-zinc-950">
        <div className="max-w-7xl mx-auto">
          {/* Photography Grid */}
          {activeTab === "photography" && (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {filteredPhotos.map((item, index) => (
                <div
                  key={item.id}
                  onClick={() => setSelectedPhoto(item)}
                  className={`group relative cursor-pointer rounded-3xl overflow-hidden border border-brand-dark/30 transition-all duration-500 hover:shadow-2xl hover:shadow-brand-primary/20 hover:border-brand-primary/50 ${
                    item.featured && index === 0 ? "md:col-span-2 md:row-span-2" : ""
                  }`}
                >
                  {/* Image Container */}
                  <div className={`relative overflow-hidden bg-gradient-to-br from-brand-deep via-zinc-900 to-brand-dark/30 ${
                    item.featured && index === 0 ? "aspect-square" : "aspect-[4/3]"
                  }`}>
                    {/* Placeholder with icon - replace with actual images */}
                    <div className="absolute inset-0 flex items-center justify-center">
                      <FiCamera className="w-16 h-16 text-brand-dark/50" />
                    </div>
                    
                    {/* Overlay */}
                    <div className="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/20 to-transparent opacity-60 group-hover:opacity-80 transition-opacity duration-300" />
                    
                    {/* Featured Badge */}
                    {item.featured && (
                      <div className="absolute top-4 left-4 px-3 py-1.5 bg-gradient-to-r from-brand-primary to-brand-accent rounded-full text-xs font-semibold text-zinc-50 flex items-center gap-1.5">
                        <HiSparkles className="w-3 h-3" />
                        Featured
                      </div>
                    )}

                    {/* Content Overlay */}
                    <div className="absolute bottom-0 left-0 right-0 p-6">
                      <span className="inline-block px-3 py-1 bg-brand-primary/30 backdrop-blur-sm rounded-full text-xs font-medium text-brand-accent mb-3 border border-brand-primary/30">
                        {item.category}
                      </span>
                      <h3 className={`font-bold text-zinc-50 mb-2 group-hover:text-brand-accent transition-colors ${
                        item.featured && index === 0 ? "text-2xl md:text-3xl" : "text-xl"
                      }`}>
                        {item.title}
                      </h3>
                      <div className="flex items-center gap-4 text-zinc-400 text-sm">
                        <span className="flex items-center gap-1.5">
                          <FiMapPin className="w-3.5 h-3.5" />
                          {item.location}
                        </span>
                        <span className="flex items-center gap-1.5">
                          <FiCalendar className="w-3.5 h-3.5" />
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
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {filteredVideos.map((item, index) => (
                <div
                  key={item.id}
                  onClick={() => setSelectedVideo(item)}
                  className={`group relative cursor-pointer rounded-3xl overflow-hidden border border-brand-dark/30 transition-all duration-500 hover:shadow-2xl hover:shadow-brand-primary/20 hover:border-brand-primary/50 ${
                    item.featured && index === 0 ? "md:col-span-2 md:row-span-2" : ""
                  }`}
                >
                  {/* Video Thumbnail */}
                  <div className={`relative overflow-hidden bg-gradient-to-br from-brand-deep via-zinc-900 to-brand-dark/30 ${
                    item.featured && index === 0 ? "aspect-video md:aspect-square" : "aspect-video"
                  }`}>
                    {/* Placeholder with icon - replace with actual thumbnails */}
                    <div className="absolute inset-0 flex items-center justify-center">
                      <BiCameraMovie className="w-16 h-16 text-brand-dark/50" />
                    </div>

                    {/* Overlay */}
                    <div className="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/30 to-transparent opacity-70 group-hover:opacity-80 transition-opacity duration-300" />

                    {/* Play Button - centered in card */}
                    <div className="absolute inset-0 flex items-center justify-center z-10 pointer-events-none">
                      <div className="w-14 h-14 rounded-full bg-gradient-to-br from-brand-primary to-brand-accent flex items-center justify-center group-hover:scale-110 transition-transform duration-300 shadow-2xl shadow-brand-primary/50 pointer-events-auto">
                        <FiPlay className="w-5 h-5 text-zinc-50 ml-0.5" />
                      </div>
                    </div>

                    {/* Duration Badge */}
                    <div className="absolute top-4 right-4 px-3 py-1.5 bg-zinc-950/80 backdrop-blur-sm rounded-full text-xs font-medium text-zinc-50 flex items-center gap-1.5 border border-zinc-800 z-20">
                      <FiClock className="w-3 h-3" />
                      {item.duration}
                    </div>

                    {/* Top Left Badges */}
                    <div className="absolute top-4 left-4 flex flex-col gap-2 z-20">
                      {/* Featured Badge */}
                      {item.featured && (
                        <div className="px-3 py-1.5 bg-gradient-to-r from-brand-primary to-brand-accent rounded-full text-xs font-semibold text-zinc-50 flex items-center gap-1.5 w-fit">
                          <HiSparkles className="w-3 h-3" />
                          Featured
                        </div>
                      )}
                      {/* Video Type Badge */}
                      <div className="px-2.5 py-1 bg-zinc-950/80 backdrop-blur-sm rounded-lg text-xs font-medium text-zinc-400 border border-zinc-800 w-fit">
                        {item.videoType === "youtube" ? "YouTube" : "HD Video"}
                      </div>
                    </div>

                    {/* Content Overlay */}
                    <div className="absolute bottom-0 left-0 right-0 p-5 z-20">
                      <span className="inline-block px-3 py-1 bg-brand-primary/30 backdrop-blur-sm rounded-full text-xs font-medium text-brand-accent border border-brand-primary/30 mb-2">
                        {item.category}
                      </span>
                      <h3 className={`font-bold text-zinc-50 mb-1.5 group-hover:text-brand-accent transition-colors ${
                        item.featured && index === 0 ? "text-2xl md:text-3xl" : "text-base"
                      }`}>
                        {item.title}
                      </h3>
                      <div className="flex flex-wrap items-center gap-3 text-zinc-400 text-sm">
                        <span className="flex items-center gap-1.5">
                          <FiMapPin className="w-3.5 h-3.5" />
                          {item.location}
                        </span>
                        <span className="flex items-center gap-1.5">
                          <FiCalendar className="w-3.5 h-3.5" />
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
      <section className="py-20 px-4 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark relative overflow-hidden">
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-5" />
        <div className="absolute top-0 left-1/4 w-96 h-96 bg-brand-primary rounded-full blur-[150px] opacity-10" />
        <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-brand-accent rounded-full blur-[150px] opacity-10" />
        
        <div className="max-w-7xl mx-auto relative">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-zinc-50 mb-4">
              Numbers That Speak
            </h2>
            <p className="text-zinc-400 max-w-xl mx-auto">
              Our journey of capturing moments and creating memories
            </p>
          </div>
          
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {[
              { value: "500+", label: "Projects Completed", icon: HiSparkles },
              { value: "200+", label: "Happy Clients", icon: FiCamera },
              { value: "15+", label: "Industry Awards", icon: BiCameraMovie },
              { value: "10+", label: "Years Experience", icon: FiVideo },
            ].map((stat) => (
              <div key={stat.label} className="text-center group">
                <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-primary/20 to-brand-accent/20 border border-brand-primary/30 mb-4 group-hover:scale-110 transition-transform">
                  <stat.icon className="w-7 h-7 text-brand-accent" />
                </div>
                <div className="text-4xl md:text-5xl font-bold bg-gradient-to-r from-brand-primary to-brand-accent bg-clip-text text-transparent mb-2">
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
          className="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/95 backdrop-blur-sm p-4"
          onClick={() => setSelectedVideo(null)}
        >
          <div 
            className="relative w-full max-w-5xl bg-zinc-900 rounded-3xl overflow-hidden border border-brand-dark/30"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Close Button */}
            <button
              onClick={() => setSelectedVideo(null)}
              className="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center text-zinc-50 hover:bg-brand-primary transition-colors border border-zinc-800"
            >
              <FiX className="w-5 h-5" />
            </button>

            {/* Video Player */}
            <div className="aspect-video bg-zinc-950">
              {selectedVideo.videoType === "youtube" ? (
                <iframe
                  src={getYouTubeEmbedUrl(selectedVideo.videoUrl)}
                  className="w-full h-full"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                />
              ) : (
                <video
                  src={selectedVideo.videoUrl}
                  controls
                  autoPlay
                  className="w-full h-full object-contain"
                />
              )}
            </div>

            {/* Video Info */}
            <div className="p-6">
              <div className="flex items-start justify-between gap-4">
                <div>
                  <span className="inline-block px-3 py-1 bg-brand-primary/20 rounded-full text-xs font-medium text-brand-accent mb-2 border border-brand-primary/30">
                    {selectedVideo.category}
                  </span>
                  <h3 className="text-2xl font-bold text-zinc-50 mb-2">{selectedVideo.title}</h3>
                  <p className="text-zinc-400 mb-4">{selectedVideo.description}</p>
                  <div className="flex items-center gap-4 text-zinc-500 text-sm">
                    <span className="flex items-center gap-1.5">
                      <FiMapPin className="w-4 h-4" />
                      {selectedVideo.location}
                    </span>
                    <span className="flex items-center gap-1.5">
                      <FiCalendar className="w-4 h-4" />
                      {selectedVideo.date}
                    </span>
                    <span className="flex items-center gap-1.5">
                      <FiClock className="w-4 h-4" />
                      {selectedVideo.duration}
                    </span>
                  </div>
                </div>
                {selectedVideo.videoType === "youtube" && (
                  <a
                    href={selectedVideo.videoUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex items-center gap-2 px-4 py-2 bg-brand-primary rounded-xl text-zinc-50 text-sm font-medium hover:bg-brand-accent transition-colors"
                  >
                    <FiExternalLink className="w-4 h-4" />
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
          className="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/95 backdrop-blur-sm p-4"
          onClick={() => setSelectedPhoto(null)}
        >
          <div 
            className="relative w-full max-w-5xl bg-zinc-900 rounded-3xl overflow-hidden border border-brand-dark/30"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Close Button */}
            <button
              onClick={() => setSelectedPhoto(null)}
              className="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center text-zinc-50 hover:bg-brand-primary transition-colors border border-zinc-800"
            >
              <FiX className="w-5 h-5" />
            </button>

            {/* Image Gallery Placeholder */}
            <div className="aspect-video bg-gradient-to-br from-brand-deep via-zinc-900 to-brand-dark/30 flex items-center justify-center">
              <div className="text-center">
                <FiCamera className="w-20 h-20 text-brand-dark/50 mx-auto mb-4" />
                <p className="text-zinc-500">Image gallery - {selectedPhoto.images.length} photos</p>
              </div>
            </div>

            {/* Photo Info */}
            <div className="p-6">
              <span className="inline-block px-3 py-1 bg-brand-primary/20 rounded-full text-xs font-medium text-brand-accent mb-2 border border-brand-primary/30">
                {selectedPhoto.category}
              </span>
              <h3 className="text-2xl font-bold text-zinc-50 mb-2">{selectedPhoto.title}</h3>
              <p className="text-zinc-400 mb-4">{selectedPhoto.description}</p>
              <div className="flex items-center gap-4 text-zinc-500 text-sm">
                <span className="flex items-center gap-1.5">
                  <FiMapPin className="w-4 h-4" />
                  {selectedPhoto.location}
                </span>
                <span className="flex items-center gap-1.5">
                  <FiCalendar className="w-4 h-4" />
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
        variant="gradient"
      />
    </main>
  );
}
