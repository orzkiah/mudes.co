import { cn } from "@/lib/cn";

interface BadgeProps {
  children: React.ReactNode;
  variant?:
    | "default"
    | "primary"
    | "secondary"
    | "success"
    | "warning"
    | "error"
    | "info"
    | "outline";
  className?: string;
}

export function Badge({ children, variant = "default", className }: BadgeProps) {
  const variants = {
    default: "bg-surface-container-low text-on-surface border border-outline-variant",
    primary: "bg-primary/10 text-primary border border-primary/20",
    secondary: "bg-secondary-container/30 text-secondary border border-secondary/20",
    success: "bg-success/10 text-success border border-success/20",
    warning: "bg-warning/10 text-warning border border-warning/20",
    error: "bg-error/10 text-error border border-error/20",
    info: "bg-surface-container text-on-surface border border-outline-variant",
    outline: "border border-outline-variant text-on-surface-variant bg-transparent",
  };

  return (
    <span
      className={cn(
        "inline-flex items-center px-2.5 py-0.5 rounded-full text-label-sm font-label-sm",
        variants[variant],
        className
      )}
    >
      {children}
    </span>
  );
}
