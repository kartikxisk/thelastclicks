import { Metadata } from "next";
import { COMPANY } from "@/lib/constants";
import ContactForm from "@/components/ContactForm";
import { FiMail, FiPhone, FiMapPin, FiClock } from "react-icons/fi";
import { BiMessageDetail } from "react-icons/bi";
import { HiOutlineLocationMarker } from "react-icons/hi";

export const metadata: Metadata = {
  title: `Contact Us | ${COMPANY.name}`,
  description: `Get in touch with ${COMPANY.name}. We'd love to hear about your project and discuss how we can help bring your vision to life.`,
};

const contactInfo = [
  {
    icon: FiMail,
    title: "Email",
    value: COMPANY.email,
    link: `mailto:${COMPANY.email}`,
  },
  {
    icon: FiPhone,
    title: "Phone",
    value: COMPANY.mobile,
    link: `tel:${COMPANY.mobile}`,
  },
  {
    icon: FiMapPin,
    title: "Location",
    value: "Bhopal, Madhya Pradesh, India",
    link: "#",
  },
];

export default function ContactPage() {
  return (
    <main className="min-h-screen">
      {/* Hero Section */}
      <section className="relative pt-32 pb-20 px-4 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-20" />
        
        <div className="absolute top-20 left-10 w-72 h-72 bg-brand-primary rounded-full blur-[128px] opacity-30 animate-pulse" />
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-brand-accent rounded-full blur-[128px] opacity-30 animate-pulse" />

        <div className="container relative text-center">
          <span className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brand-primary/20 text-brand-accent text-sm font-medium mb-6 backdrop-blur-sm border border-brand-primary/30">
            <BiMessageDetail className="w-4 h-4" />
            Get in Touch
          </span>
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-zinc-50 mb-6 leading-tight">
            Contact
            <br />
            <span className="bg-gradient-to-r from-brand-primary via-brand-accent to-brand-primary bg-clip-text text-transparent">
              Us
            </span>
          </h1>
          <p className="text-xl text-zinc-400 max-w-2xl mx-auto">
            Have a project in mind? We&apos;d love to hear from you. Send us a message and we&apos;ll respond as soon as possible.
          </p>
        </div>
      </section>

      {/* Contact Section */}
      <section className="py-20 px-4 bg-zinc-950">
        <div className="container">
          <div className="grid lg:grid-cols-2 gap-12">
            {/* Contact Info */}
            <div>
              <h2 className="text-3xl font-bold text-zinc-50 mb-6">
                Let&apos;s Start a Conversation
              </h2>
              <p className="text-zinc-400 mb-8">
                Whether you&apos;re looking for photography, videography, or editing services, we&apos;re here to help bring your vision to life.
              </p>

              <div className="space-y-6 mb-12">
                {contactInfo.map((info) => (
                  <a
                    key={info.title}
                    href={info.link}
                    className="flex items-center space-x-4 p-4 bg-brand-deep rounded-xl hover:bg-brand-dark/30 transition-all border border-brand-dark/30 group"
                  >
                    <span className="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-primary to-brand-accent flex items-center justify-center text-zinc-50 group-hover:scale-110 transition-transform">
                      <info.icon className="w-5 h-5" />
                    </span>
                    <div>
                      <p className="text-sm text-zinc-500">{info.title}</p>
                      <p className="font-medium text-zinc-50">{info.value}</p>
                    </div>
                  </a>
                ))}
              </div>

              {/* Business Hours */}
              <div className="bg-brand-deep p-6 rounded-xl border border-brand-dark/30">
                <div className="flex items-center gap-3 mb-4">
                  <FiClock className="w-5 h-5 text-brand-accent" />
                  <h3 className="text-lg font-semibold text-zinc-50">Business Hours</h3>
                </div>
                <div className="space-y-2 text-zinc-400">
                  <div className="flex justify-between">
                    <span>Monday - Friday</span>
                    <span className="font-medium text-zinc-50">9:00 AM - 7:00 PM</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Saturday</span>
                    <span className="font-medium text-zinc-50">10:00 AM - 5:00 PM</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Sunday</span>
                    <span className="font-medium text-zinc-50">By Appointment</span>
                  </div>
                </div>
              </div>
            </div>

            {/* Contact Form */}
            <div className="bg-gradient-to-br from-brand-deep to-zinc-950 p-8 rounded-3xl shadow-xl border border-brand-dark/30">
              <h3 className="text-2xl font-bold text-zinc-50 mb-6">Send us a Message</h3>
              <ContactForm />
            </div>
          </div>
        </div>
      </section>

      {/* Map Section (Placeholder) */}
      <section className="h-96 bg-gradient-to-br from-brand-deep via-zinc-950 to-brand-dark flex items-center justify-center border-t border-brand-dark/30">
        <div className="text-center">
          <div className="w-20 h-20 rounded-full bg-gradient-to-br from-brand-primary to-brand-accent flex items-center justify-center mx-auto mb-4">
            <HiOutlineLocationMarker className="w-10 h-10 text-zinc-50" />
          </div>
          <p className="text-zinc-400">Map integration coming soon</p>
        </div>
      </section>
    </main>
  );
}
