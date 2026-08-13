"use client";

import Link from "next/link";
import { Icon } from "@/components/ui/Icon";

export interface BreadcrumbItem {
  href?: string;
  label: string;
}

export function Breadcrumbs({ items }: { items: BreadcrumbItem[] }) {
  if (!items || items.length === 0) return null;
  return (
    <nav aria-label="Breadcrumb" className="text-label-sm text-on-surface-variant">
      <ol className="flex items-center gap-2">
        {items.map((it, idx) => {
          const last = idx === items.length - 1;
          return (
            <li key={idx} className="flex items-center">
              {!last && it.href ? (
                <Link href={it.href} className="text-on-surface-variant hover:text-primary">
                  {it.label}
                </Link>
              ) : (
                <span className="text-on-surface">{it.label}</span>
              )}

              {!last && <span className="mx-2 text-on-surface-variant">/</span>}
            </li>
          );
        })}
      </ol>
    </nav>
  );
}
