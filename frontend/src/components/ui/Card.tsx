import { cn } from "@/lib/cn";
import type { ReactNode } from "react";

interface CardProps {
  children: ReactNode;
  className?: string;
  padding?: "none" | "sm" | "md" | "lg";
}

export function Card({ children, className, padding = "md" }: CardProps) {
  const paddings = {
    none: "",
    sm: "p-sm",
    md: "p-md",
    lg: "p-lg",
  };

  return (
    <div
      className={cn(
        "bg-surface-container-lowest rounded-xl border border-outline-variant shadow-premium",
        paddings[padding],
        className
      )}
    >
      {children}
    </div>
  );
}

interface CardHeaderProps {
  title: ReactNode;
  description?: ReactNode;
  action?: ReactNode;
  className?: string;
}

export function CardHeader({ title, description, action, className }: CardHeaderProps) {
  return (
    <div className={cn("flex items-start justify-between gap-4 pb-3", className)}>
      <div className="flex-1 min-w-0">
        <h3 className="text-headline-sm font-headline-sm text-on-surface">{title}</h3>
        {description && (
          <p className="text-body-sm text-on-surface-variant mt-1">{description}</p>
        )}
      </div>
      {action && <div className="shrink-0">{action}</div>}
    </div>
  );
}
