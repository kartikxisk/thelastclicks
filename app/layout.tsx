import type { Metadata } from "next";
import { Playwrite_NZ } from "next/font/google";
import "./globals.css";
import Navbar from "@/components/ui/Navbar";
import Footer from "@/components/ui/Footer";
import { COMPANY } from "@/lib/constants";

const playwrite = Playwrite_NZ({
  variable: "--font-playwrite",
  weight: ["100", "200", "300", "400"],
});

export const metadata: Metadata = {
  title: {
    default: `${COMPANY.name} | Professional Photography, Videography & Editing`,
    template: `%s | ${COMPANY.name}`,
  },
  description: `${COMPANY.tagline}. Professional photography, videography, and editing services in India.`,
  keywords: ["photography", "videography", "editing", "wedding photography", "video editing", "color grading"],
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body className={`${playwrite.variable} antialiased`}>
        <Navbar />
        {children}
        <Footer />
      </body>
    </html>
  );
}
