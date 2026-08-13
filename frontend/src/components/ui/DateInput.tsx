import { cn } from "@/lib/cn";
import { forwardRef, InputHTMLAttributes } from "react";
import { Input } from "./Input";

interface DateInputProps extends Omit<InputHTMLAttributes<HTMLInputElement>, "type"> {
  error?: string;
}

export const DateInput = forwardRef<HTMLInputElement, DateInputProps>(
  ({ className, error, ...props }, ref) => {
    return (
      <Input
        ref={ref}
        type="date"
        error={error}
        className={cn("[color-scheme:light]", className)}
        {...props}
      />
    );
  }
);
DateInput.displayName = "DateInput";
