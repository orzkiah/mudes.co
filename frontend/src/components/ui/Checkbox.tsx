"use client";

import { cn } from "@/lib/cn";
import { forwardRef, InputHTMLAttributes } from "react";

interface CheckboxProps extends Omit<InputHTMLAttributes<HTMLInputElement>, "type"> {
  label?: string;
}

export const Checkbox = forwardRef<HTMLInputElement, CheckboxProps>(
  ({ label, className, ...props }, ref) => {
    return (
      <label className={cn("inline-flex items-center gap-2 cursor-pointer", className)}>
        <input
          ref={ref}
          type="checkbox"
          className="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/30"
          {...props}
        />
        {label && <span className="text-body-md text-on-surface">{label}</span>}
      </label>
    );
  }
);
Checkbox.displayName = "Checkbox";
