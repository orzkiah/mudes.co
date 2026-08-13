import { cn } from "@/lib/cn";
import { forwardRef, InputHTMLAttributes } from "react";
import { Input } from "./Input";

interface TimeInputProps extends Omit<InputHTMLAttributes<HTMLInputElement>, "type"> {
  error?: string;
}

export const TimeInput = forwardRef<HTMLInputElement, TimeInputProps>(
  ({ className, error, ...props }, ref) => {
    return (
      <Input
        ref={ref}
        type="time"
        error={error}
        className={cn("[color-scheme:light]", className)}
        {...props}
      />
    );
  }
);
TimeInput.displayName = "TimeInput";
