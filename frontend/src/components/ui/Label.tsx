import { cn } from "@/lib/cn";
import { LabelHTMLAttributes } from "react";

export function Label({ className, children, ...props }: LabelHTMLAttributes<HTMLLabelElement>) {
  return (
    <label
      className={cn("block text-label-md font-label-md text-on-surface mb-1", className)}
      {...props}
    >
      {children}
    </label>
  );
}
