import Image from "next/image";
import Link from "next/link";
import { COMPANY } from "@/lib/constants";

interface LogoProps {
  readonly className?: string;
  readonly size?: "sm" | "md" | "lg";
}

const sizeClasses = {
  sm: { image: 32, text: "text-lg" },
  md: { image: 40, text: "text-xl" },
  lg: { image: 48, text: "text-2xl" },
};

export default function Logo({ className = "", size = "md" }: LogoProps) {
  const { image: imageSize } = sizeClasses[size];

  return (
    <Link href="/" className={`flex items-center space-x-2 ${className}`}>
      <Image
        src={COMPANY.logo}
        alt={`${COMPANY.name} Logo`}
        width={imageSize}
        height={imageSize}
        className="object-contain"
        priority
      />
    </Link>
  );
}
