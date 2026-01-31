import { Metadata } from "next";
import { COMPANY } from "@/lib/constants";

export const metadata: Metadata = {
  title: `Portfolio | ${COMPANY.name}`,
  description: `Explore our portfolio of stunning photography and videography work. See the quality and creativity we bring to every project.`,
};

export default function PortfolioLayout({
  children,
}: {
  readonly children: React.ReactNode;
}) {
  return <>{children}</>;
}
