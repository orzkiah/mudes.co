import { cn } from "@/lib/cn";
import { Icon } from "./Icon";
import type { ReactNode } from "react";

interface AlertProps {
  children: ReactNode;
  variant?: "info" | "success" | "warning" | "error";
  className?: string;
  icon?: string;
}

export function Alert({ children, variant = "info", className, icon }: AlertProps) {
  const variants = {
    info: "bg-surface-container text-on-surface border-outline-variant",
    success: "bg-success/10 text-success border-success/20",
    warning: "bg-warning/10 text-warning border-warning/20",
    error: "bg-error/10 text-error border-error/20",
  };

  const icons = {
    info: "info",
    success: "check_circle",
    warning: "warning",
    error: "error",
  };

  return (
    <div
      className={cn(
        "flex items-start gap-3 rounded-lg border px-4 py-3 text-body-sm",
        variants[variant],
        className
      )}
    >
      <Icon name={icon || icons[variant]} className="text-lg shrink-0 mt-0.5" />
      <div className="flex-1">{children}</div>
    </div>
  );
}
