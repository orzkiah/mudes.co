import { cn } from "@/lib/cn";
import { Icon } from "./Icon";
import type { ReactNode } from "react";

interface EmptyStateProps {
  title?: string;
  description?: string;
  icon?: string;
  action?: ReactNode;
  className?: string;
}

export function EmptyState({
  title = "Tidak ada data",
  description = "Belum ada data untuk ditampilkan.",
  icon = "inbox",
  action,
  className,
}: EmptyStateProps) {
  return (
    <div
      className={cn(
        "flex flex-col items-center justify-center py-12 px-4 text-center rounded-xl border border-dashed border-outline-variant bg-surface-container-lowest",
        className
      )}
    >
      <div className="w-14 h-14 rounded-full bg-surface-container flex items-center justify-center mb-4">
        <Icon name={icon} className="text-3xl text-on-surface-variant" />
      </div>
      <h3 className="text-headline-sm font-headline-sm text-on-surface">{title}</h3>
      <p className="text-body-md text-on-surface-variant mt-1 max-w-sm">{description}</p>
      {action && <div className="mt-4">{action}</div>}
    </div>
  );
}
