import { cn } from "@/lib/cn";
import { Icon } from "./Icon";
import { Spinner } from "./Spinner";
import type { ButtonHTMLAttributes, ReactNode } from "react";

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: "primary" | "secondary" | "outline" | "ghost" | "danger";
  size?: "sm" | "md" | "lg";
  loading?: boolean;
  leftIcon?: string;
  rightIcon?: string;
  children: ReactNode;
}

export function Button({
  variant = "primary",
  size = "md",
  loading = false,
  leftIcon,
  rightIcon,
  children,
  className,
  disabled,
  ...props
}: ButtonProps) {
  const variants = {
    primary: "bg-emerald-600 text-white hover:bg-emerald-700 active:scale-95 shadow-sm hover:shadow transition-all [&_*]:text-white font-semibold",
    secondary: "bg-gold-light text-secondary hover:bg-secondary-fixed active:scale-95 shadow-sm transition-all font-semibold",
    outline: "border border-outline-variant bg-surface text-on-surface hover:bg-surface-container-low transition-colors font-medium",
    ghost: "text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface transition-colors",
    danger: "bg-error text-white hover:bg-error/90 active:scale-95 shadow-sm transition-all font-semibold",
  };

  const sizes = {
    sm: "px-3 py-1.5 text-label-sm rounded-lg",
    md: "px-4 py-2.5 text-label-md rounded-lg",
    lg: "px-6 py-3 text-body-md rounded-xl",
  };

  return (
    <button
      className={cn(
        "inline-flex items-center justify-center gap-2 font-label-md transition-all focus:outline-none focus:ring-2 focus:ring-emerald-600/40 disabled:opacity-50 disabled:cursor-not-allowed",
        variants[variant],
        sizes[size],
        className
      )}
      disabled={disabled || loading}
      {...props}
    >
      {loading && <Spinner className="w-4 h-4" />}
      {!loading && leftIcon && <Icon name={leftIcon} className="text-[1.25em]" />}
      <span>{children}</span>
      {!loading && rightIcon && <Icon name={rightIcon} className="text-[1.25em]" />}
    </button>
  );
}


export function IconButton({
  icon,
  className,
  ...props
}: Omit<ButtonProps, "leftIcon" | "rightIcon" | "children"> & { icon: string }) {
  return (
    <Button className={cn("p-2", className)} {...props}>
      <Icon name={icon} />
    </Button>
  );
}

