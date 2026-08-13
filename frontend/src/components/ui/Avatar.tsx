import { cn } from "@/lib/cn";
import Image from "next/image";
import { Icon } from "./Icon";

interface AvatarProps {
  src?: string | null;
  name?: string;
  className?: string;
  size?: "sm" | "md" | "lg";
}

export function Avatar({ src, name, className, size = "md" }: AvatarProps) {
  const sizes = { sm: "w-8 h-8", md: "w-10 h-10", lg: "w-14 h-14" };
  const fallback = name ? name.charAt(0).toUpperCase() : "?";

  return (
    <div
      className={cn(
        "relative rounded-full overflow-hidden bg-gold-light flex items-center justify-center text-on-surface font-headline-sm",
        sizes[size],
        className
      )}
    >
      {src ? (
        <Image src={src} alt={name || "Avatar"} fill className="object-cover" />
      ) : (
        <span className="text-label-md font-label-md">{fallback}</span>
      )}
    </div>
  );
}
