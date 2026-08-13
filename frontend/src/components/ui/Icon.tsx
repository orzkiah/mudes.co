import { cn } from "@/lib/cn";

interface IconProps {
  name: string;
  className?: string;
  style?: React.CSSProperties;
  filled?: boolean;
  weight?: number;
  grade?: number;
  opsz?: number;
}

export function Icon({
  name,
  className,
  style,
  filled = false,
  weight = 400,
  grade = 0,
  opsz = 24,
}: IconProps) {
  return (
    <span
      className={cn("material-symbols-outlined", className)}
      style={{
        ...style,
        fontVariationSettings: `'FILL' ${filled ? 1 : 0}, 'wght' ${weight}, 'GRAD' ${grade}, 'opsz' ${opsz}`,
      }}
    >
      {name}
    </span>
  );
}
