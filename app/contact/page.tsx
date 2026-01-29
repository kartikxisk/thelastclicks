import { Metadata } from "next";
import { COMPANY } from "@/lib/constants";
import ContactForm from "@/components/ContactForm";

export const metadata: Metadata = {
  title: `Contact Us | ${COMPANY.name}`,
  description: `Get in touch with ${COMPANY.name}. We'd love to hear about your project and discuss how we can help bring your vision to life.`,
};

const contactInfo = [
  {
    icon: "📧",
    title: "Email",
    value: COMPANY.email,
    link: `mailto:${COMPANY.email}`,
  },
  {
    icon: "📱",
    title: "Phone",
    value: COMPANY.mobile,
    link: `tel:${COMPANY.mobile}`,
  },
  {
    icon: "📍",
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
        <div className="absolute inset-0 bg-gradient-to-br from-purple-900 via-gray-900 to-pink-900" />
        <div className="absolute inset-0 bg-[url('/images/grid.svg')] opacity-20" />
        
        <div className="absolute top-20 left-10 w-72 h-72 bg-purple-500 rounded-full blur-[128px] opacity-30 animate-pulse" />
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-pink-500 rounded-full blur-[128px] opacity-30 animate-pulse" />

        <div className="relative max-w-7xl mx-auto text-center">
          <span className="inline-block px-4 py-2 rounded-full bg-white/10 text-purple-300 text-sm font-medium mb-6 backdrop-blur-sm">
            📞 Get in Touch
          </span>
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
            Contact
            <br />
            <span className="bg-gradient-to-r from-purple-400 via-pink-400 to-purple-400 bg-clip-text text-transparent">
              Us
            </span>
          </h1>
          <p className="text-xl text-gray-300 max-w-2xl mx-auto">
            Have a project in mind? We&apos;d love to hear from you. Send us a message and we&apos;ll respond as soon as possible.
          </p>
        </div>
      </section>

      {/* Contact Section */}
      <section className="py-20 px-4 bg-gray-50">
        <div className="max-w-7xl mx-auto">
          <div className="grid lg:grid-cols-2 gap-12">
            {/* Contact Info */}
            <div>
              <h2 className="text-3xl font-bold text-gray-900 mb-6">
                Let&apos;s Start a Conversation
              </h2>
              <p className="text-gray-600 mb-8">
                Whether you&apos;re looking for photography, videography, or editing services, we&apos;re here to help bring your vision to life.
              </p>

              <div className="space-y-6 mb-12">
                {contactInfo.map((info) => (
                  <a
                    key={info.title}
                    href={info.link}
                    className="flex items-center space-x-4 p-4 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border border-gray-100"
                  >
                    <span className="text-3xl">{info.icon}</span>
                    <div>
                      <p className="text-sm text-gray-500">{info.title}</p>
                      <p className="font-medium text-gray-900">{info.value}</p>
                    </div>
                  </a>
                ))}
              </div>

              {/* Business Hours */}
              <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">Business Hours</h3>
                <div className="space-y-2 text-gray-600">
                  <div className="flex justify-between">
                    <span>Monday - Friday</span>
                    <span className="font-medium">9:00 AM - 7:00 PM</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Saturday</span>
                    <span className="font-medium">10:00 AM - 5:00 PM</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Sunday</span>
                    <span className="font-medium">By Appointment</span>
                  </div>
                </div>
              </div>
            </div>

            {/* Contact Form */}
            <div className="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
              <h3 className="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h3>
              <ContactForm />
            </div>
          </div>
        </div>
      </section>

      {/* Map Section (Placeholder) */}
      <section className="h-96 bg-gradient-to-br from-purple-100 via-pink-50 to-purple-100 flex items-center justify-center">
        <div className="text-center">
          <span className="text-6xl mb-4 block">🗺️</span>
          <p className="text-gray-600">Map integration coming soon</p>
        </div>
      </section>
    </main>
  );
}
