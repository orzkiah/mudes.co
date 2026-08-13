"use client";

import React from "react";
import { Breadcrumbs, BreadcrumbItem } from "./Breadcrumbs";

export function PageContainer(props: {
  title?: React.ReactNode;
  subtitle?: React.ReactNode;
  actions?: React.ReactNode;
  breadcrumbs?: BreadcrumbItem[];
  children: React.ReactNode;
}) {
  const { title, subtitle, actions, breadcrumbs, children } = props;
  return (
    <div className="max-w-container-max mx-auto w-full">
      <div className="space-y-4 py-6">
        {breadcrumbs && <Breadcrumbs items={breadcrumbs} />}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            {title && <div className="text-headline-md font-headline-md text-on-surface">{title}</div>}
            {subtitle && <div className="text-body-md text-on-surface-variant mt-1">{subtitle}</div>}
          </div>

          {actions && <div className="flex items-center gap-2">{actions}</div>}
        </div>
      </div>

      <div className="px-0">
        {children}
      </div>
    </div>
  );
}
