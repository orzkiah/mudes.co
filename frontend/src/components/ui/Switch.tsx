"use client";

import { cn } from "@/lib/cn";
import { forwardRef, InputHTMLAttributes } from "react";

interface SwitchProps extends Omit<InputHTMLAttributes<HTMLInputElement>, "type"> {
  label?: string;
  error?: string;
}

export const Switch = forwardRef<HTMLInputElement, SwitchProps>(
  ({ label, error, className, ...props }, ref) => {
    return (
      <label className={cn("inline-flex items-center gap-3 cursor-pointer", className)}>
        <div className="relative">
          <input ref={ref} type="checkbox" className="sr-only peer" {...props} />
          <div className="w-11 h-6 bg-outline-variant rounded-full peer-checked:bg-primary transition-colors" />
          <div className="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5" />
        </div>
        {label && <span className="text-body-md text-on-surface">{label}</span>}
      </label>
    );
  }
);
Switch.displayName = "Switch";
