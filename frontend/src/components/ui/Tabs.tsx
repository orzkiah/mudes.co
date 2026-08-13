"use client";

import { cn } from "@/lib/cn";
import type { ReactNode } from "react";

interface Tab {
  value: string;
  label: ReactNode;
}

interface TabsProps {
  tabs: Tab[];
  value: string;
  onChange: (value: string) => void;
  className?: string;
}

export function Tabs({ tabs, value, onChange, className }: TabsProps) {
  return (
    <div className={cn("flex items-center gap-1 border-b border-outline-variant", className)}>
      {tabs.map((tab) => (
        <button
          key={tab.value}
          onClick={() => onChange(tab.value)}
          className={cn(
            "px-4 py-2 text-body-md font-body-md transition-colors relative",
            value === tab.value
              ? "text-primary font-semibold"
              : "text-on-surface-variant hover:text-on-surface"
          )}
        >
          {tab.label}
          {value === tab.value && (
            <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-t" />
          )}
        </button>
      ))}
    </div>
  );
}
