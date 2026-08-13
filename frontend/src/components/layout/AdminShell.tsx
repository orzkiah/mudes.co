"use client";

import { useEffect, useState } from "react";
import { usePathname, useRouter } from "next/navigation";
import { useAuth } from "@/providers/AuthProvider";
import { usePermission } from "@/hooks/usePermission";
import type { PermissionAction } from "@/lib/permissions";
import { Sidebar } from "./Sidebar";
import { AdminTopbar } from "./AdminTopbar";
import { Footer } from "./Footer";
import { Drawer } from "@/components/ui/Drawer";
import { Spinner } from "@/components/ui/Spinner";
import { EmptyState } from "@/components/ui/EmptyState";
import { Button } from "@/components/ui/Button";

/**
 * Maps route prefixes to the permission required to view them.
 * Format: "<resource>.<action>" where action must be a valid PermissionAction.
 * Entries are matched by startsWith, so the most specific prefix wins (longer
 * keys are checked first via sort in the lookup below).
 */
const routePermissions: Record<string, `${string}.${PermissionAction}`> = {
  "/admin/analytics": "dashboard.view",
  "/admin/members": "members.view",
  "/admin/organization": "organization-positions.view",
  "/admin/departments": "departments.view",
  "/admin/study-schedules": "study-schedules.view",
  "/admin/study-categories": "study-categories.view",
  "/admin/activities": "activities.view",
  "/admin/activity-categories": "activity-categories.view",
  "/admin/articles": "articles.view",
  "/admin/article-categories": "article-categories.view",
  "/admin/gallery": "galleries.view",
  "/admin/gallery-categories": "gallery-categories.view",
  "/admin/library": "library-documents.view",
  "/admin/library-categories": "library-categories.view",
  "/admin/announcements": "announcements.view",
  "/admin/attendance": "attendance-sessions.view",
  "/admin/settings": "settings.view",
};

export function AdminShell({ children }: { children: React.ReactNode }) {
  const { user, isLoading } = useAuth();
  const router = useRouter();
  const pathname = usePathname();
  const { can } = usePermission();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  useEffect(() => {
    if (!isLoading && !user) {
      router.replace("/login");
    }
  }, [user, isLoading, router]);

  if (isLoading || !user) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-surface">
        <Spinner className="w-10 h-10 text-primary" />
      </div>
    );
  }

  // Sort by descending length so more-specific prefixes (e.g. /admin/library-categories)
  // are checked before shorter ones (e.g. /admin/library).
  const matchedRoute = Object.keys(routePermissions)
    .sort((a, b) => b.length - a.length)
    .find((route) => pathname.startsWith(route));

  const requiredPermission = matchedRoute ? routePermissions[matchedRoute] : undefined;

  let hasAccess = true;
  if (requiredPermission) {
    const dotIndex = requiredPermission.lastIndexOf(".");
    const resource = requiredPermission.slice(0, dotIndex);
    const action = requiredPermission.slice(dotIndex + 1) as PermissionAction;
    hasAccess = can(resource, action);
  }

  return (
    <div className="min-h-screen bg-surface">
      <aside className="hidden md:flex fixed left-0 top-0 h-full w-[240px] z-50">
        <Sidebar onClose={() => {}} />
      </aside>

      <Drawer isOpen={mobileMenuOpen} onClose={() => setMobileMenuOpen(false)}>
        <Sidebar onClose={() => setMobileMenuOpen(false)} />
      </Drawer>

      <AdminTopbar onMenuClick={() => setMobileMenuOpen(true)} />

      <main className="md:ml-[240px] pt-20 md:pt-24 min-h-screen px-md md:px-lg pb-16 max-w-7xl mx-auto">
        {hasAccess ? children : (
          <EmptyState
            title="Akses ditolak"
            description="Anda tidak memiliki izin untuk mengakses halaman ini."
            icon="lock"
            action={
              <Button variant="outline" onClick={() => router.push("/admin")}>
                Kembali ke Dashboard
              </Button>
            }
          />
        )}
      </main>

      <Footer />
    </div>
  );
}

