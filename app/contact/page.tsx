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
      <section className="relative overflow-hidden px-4 pt-32 pb-20">
        <div className="from-brand-deep to-brand-dark absolute inset-0 bg-linear-to-br via-zinc-950" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-20" />

        <div className="bg-brand-primary absolute top-20 left-10 h-72 w-72 animate-pulse rounded-full opacity-30 blur-[128px]" />
        <div className="bg-brand-accent absolute right-10 bottom-10 h-96 w-96 animate-pulse rounded-full opacity-30 blur-[128px]" />

        <div className="relative container text-center">
          <span className="bg-brand-primary/20 text-brand-accent border-brand-primary/30 mb-6 inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium backdrop-blur-sm">
            <BiMessageDetail className="h-4 w-4" />
            Get in Touch
          </span>
          <h1 className="mb-6 text-4xl leading-tight font-bold text-zinc-50 md:text-6xl lg:text-7xl">
            Contact
            <br />
            <span className="from-brand-primary via-brand-accent to-brand-primary bg-linear-to-r bg-clip-text text-transparent">
              Us
            </span>
          </h1>
          <p className="mx-auto max-w-2xl text-xl text-zinc-400">
            Have a project in mind? We&apos;d love to hear from you. Send us a message and
            we&apos;ll respond as soon as possible.
          </p>
        </div>
      </section>

      {/* Contact Section */}
      <section className="bg-zinc-950 px-4 py-20">
        <div className="container">
          <div className="grid gap-12 lg:grid-cols-2">
            {/* Contact Info */}
            <div>
              <h2 className="mb-6 text-3xl font-bold text-zinc-50">
                Let&apos;s Start a Conversation
              </h2>
              <p className="mb-8 text-zinc-400">
                Whether you&apos;re looking for photography, videography, or editing services,
                we&apos;re here to help bring your vision to life.
              </p>

              <div className="mb-12 space-y-6">
                {contactInfo.map((info) => (
                  <a
                    key={info.title}
                    href={info.link}
                    className="bg-brand-deep hover:bg-brand-dark/30 border-brand-dark/30 group flex items-center space-x-4 rounded-xl border p-4 transition-all"
                  >
                    <span className="from-brand-primary to-brand-accent flex h-12 w-12 items-center justify-center rounded-xl bg-linear-to-br text-zinc-50 transition-transform group-hover:scale-110">
                      <info.icon className="h-5 w-5" />
                    </span>
                    <div>
                      <p className="text-sm text-zinc-500">{info.title}</p>
                      <p className="font-medium text-zinc-50">{info.value}</p>
                    </div>
                  </a>
                ))}
              </div>

              {/* Business Hours */}
              <div className="bg-brand-deep border-brand-dark/30 rounded-xl border p-6">
                <div className="mb-4 flex items-center gap-3">
                  <FiClock className="text-brand-accent h-5 w-5" />
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
            <div className="from-brand-deep border-brand-dark/30 rounded-3xl border bg-linear-to-br to-zinc-950 p-8 shadow-xl">
              <h3 className="mb-6 text-2xl font-bold text-zinc-50">Send us a Message</h3>
              <ContactForm />
            </div>
          </div>
        </div>
      </section>

      {/* Map Section (Placeholder) */}
      <section className="from-brand-deep to-brand-dark border-brand-dark/30 flex h-96 items-center justify-center border-t bg-linear-to-br via-zinc-950">
        <div className="text-center">
          <div className="from-brand-primary to-brand-accent mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-linear-to-br">
            <HiOutlineLocationMarker className="h-10 w-10 text-zinc-50" />
          </div>
          <p className="text-zinc-400">Map integration coming soon</p>
        </div>
      </section>
    </main>
  );
}
