// Company Information
export const COMPANY = {
  name: "The Last Clicks",
  email: "info@thelastclicks.com",
  mobile: "+91-8770155842",
  tagline: "Capturing Moments, Creating Memories",
  logo: "/images/logo.png",
} as const;

// Navigation Links
export const NAV_LINKS = [
  { name: "Services", href: "/services" },
  { name: "Portfolio", href: "/portfolio" },
  { name: "About", href: "/about" },
  { name: "Contact", href: "/contact" },
] as const;

// Services
export const SERVICES = {
  photography: {
    name: "Photography",
    href: "/services/photography",
    description: "Professional photography services for all occasions",
    categories: [
      { name: "Wedding Photography", slug: "wedding", icon: "💒" },
      { name: "Portrait Photography", slug: "portrait", icon: "👤" },
      { name: "Event Photography", slug: "event", icon: "🎉" },
      { name: "Product Photography", slug: "product", icon: "📦" },
      { name: "Fashion Photography", slug: "fashion", icon: "👗" },
      { name: "Corporate Photography", slug: "corporate", icon: "🏢" },
    ],
  },
  videography: {
    name: "Videography",
    href: "/services/videography",
    description: "Cinematic video production for memorable stories",
    categories: [
      { name: "Wedding Films", slug: "wedding-films", icon: "🎬" },
      { name: "Corporate Videos", slug: "corporate-videos", icon: "🎥" },
      { name: "Music Videos", slug: "music-videos", icon: "🎵" },
      { name: "Documentary", slug: "documentary", icon: "📽️" },
      { name: "Commercial Ads", slug: "commercial", icon: "📺" },
      { name: "Event Coverage", slug: "event-coverage", icon: "🎭" },
    ],
  },
  editing: {
    name: "Editing Service",
    href: "/services/editing",
    description: "Transform your raw footage into stunning masterpieces",
    categories: [
      { name: "Photo Retouching", slug: "photo-retouching", icon: "✨" },
      { name: "Video Editing", slug: "video-editing", icon: "🎞️" },
      { name: "Color Grading", slug: "color-grading", icon: "🎨" },
      { name: "Motion Graphics", slug: "motion-graphics", icon: "💫" },
      { name: "Audio Mixing", slug: "audio-mixing", icon: "🎧" },
      { name: "VFX & Animation", slug: "vfx-animation", icon: "🌟" },
    ],
  },
} as const;

// Editors Data
export const EDITORS = [
  {
    id: 1,
    name: "Rahul Sharma",
    role: "Senior Video Editor",
    specialization: ["Video Editing", "Color Grading", "Motion Graphics"],
    experience: "8+ Years",
    projects: 500,
    image: "/images/editors/editor-1.jpg",
    bio: "Award-winning video editor with expertise in cinematic storytelling and color grading.",
  },
  {
    id: 2,
    name: "Priya Patel",
    role: "Photo Retouching Expert",
    specialization: ["Photo Retouching", "Portrait Enhancement", "Beauty Editing"],
    experience: "6+ Years",
    projects: 1200,
    image: "/images/editors/editor-2.jpg",
    bio: "Specialized in high-end photo retouching for fashion and portrait photography.",
  },
  {
    id: 3,
    name: "Amit Kumar",
    role: "Motion Graphics Designer",
    specialization: ["Motion Graphics", "VFX", "Animation"],
    experience: "5+ Years",
    projects: 300,
    image: "/images/editors/editor-3.jpg",
    bio: "Creative motion designer bringing ideas to life through stunning animations.",
  },
  {
    id: 4,
    name: "Sneha Reddy",
    role: "Color Grading Specialist",
    specialization: ["Color Grading", "Film Look", "Cinematic Tones"],
    experience: "7+ Years",
    projects: 400,
    image: "/images/editors/editor-4.jpg",
    bio: "Expert colorist known for creating signature looks for films and commercials.",
  },
  {
    id: 5,
    name: "Vikram Singh",
    role: "Audio Engineer",
    specialization: ["Audio Mixing", "Sound Design", "Music Production"],
    experience: "10+ Years",
    projects: 600,
    image: "/images/editors/editor-5.jpg",
    bio: "Professional audio engineer with background in music and film sound design.",
  },
  {
    id: 6,
    name: "Anjali Verma",
    role: "VFX Artist",
    specialization: ["VFX", "Compositing", "3D Integration"],
    experience: "4+ Years",
    projects: 200,
    image: "/images/editors/editor-6.jpg",
    bio: "Skilled VFX artist creating seamless visual effects for diverse projects.",
  },
] as const;

// Editing Service Features
export const EDITING_FEATURES = [
  {
    title: "Fast Turnaround",
    description: "Get your edited content delivered within 24-72 hours",
    icon: "⚡",
  },
  {
    title: "Unlimited Revisions",
    description: "We work until you're 100% satisfied with the result",
    icon: "🔄",
  },
  {
    title: "Premium Quality",
    description: "4K & 8K support with professional-grade output",
    icon: "💎",
  },
  {
    title: "Secure Transfer",
    description: "Your files are encrypted and handled with care",
    icon: "🔒",
  },
] as const;

// Pricing Tiers
export const EDITING_PRICING = [
  {
    name: "Basic",
    price: "₹999",
    period: "per project",
    features: [
      "Basic color correction",
      "Simple cuts & transitions",
      "Background music",
      "1 revision round",
      "720p delivery",
    ],
    popular: false,
  },
  {
    name: "Professional",
    price: "₹2,999",
    period: "per project",
    features: [
      "Advanced color grading",
      "Smooth transitions & effects",
      "Custom music & sound design",
      "3 revision rounds",
      "4K delivery",
      "Motion graphics",
    ],
    popular: true,
  },
  {
    name: "Premium",
    price: "₹7,999",
    period: "per project",
    features: [
      "Cinematic color grading",
      "Premium VFX & animations",
      "Professional sound mixing",
      "Unlimited revisions",
      "4K/8K delivery",
      "Priority support",
      "Raw files included",
    ],
    popular: false,
  },
] as const;
