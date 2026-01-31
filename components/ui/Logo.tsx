import Image from "next/image";
import Link from "next/link";
import { COMPANY } from "@/lib/constants";

interface LogoProps {
  readonly className?: string;
  readonly size?: "sm" | "md" | "lg";
}

const sizeClasses = {
  sm: { width: 100 },
  md: { width: 140 },
  lg: { width: 180 },
};

export default function Logo({ className = "", size = "lg" }: LogoProps) {
  const { width } = sizeClasses[size];

  return (
    <Link href="/" className={`flex items-center ${className}`}>
      <Image
        src={COMPANY.logo}
        alt={`${COMPANY.name} Logo`}
        width={width}
        height={Math.round(width * 9 / 16)}
        className="object-contain aspect-video object-left"
        priority
      />
    </Link>
  );
}
