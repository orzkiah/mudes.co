"use client";

import { usePathname } from "next/navigation";
import Link from "next/link";
import { cn } from "@/lib/cn";
import { usePermission } from "@/hooks/usePermission";
import { Icon } from "@/components/ui/Icon";
import { useAuth } from "@/providers/AuthProvider";

interface NavItem {
  href: string;
  label: string;
  icon: string;
  permission: string;
}

const navItems: NavItem[] = [
  { href: "/admin", label: "Dashboard", icon: "dashboard", permission: "dashboard.view" },
  { href: "/admin/analytics", label: "Analytics", icon: "analytics", permission: "dashboard.view" },
  { href: "/admin/members", label: "Anggota", icon: "group", permission: "members.view" },
  { href: "/admin/organization", label: "Struktur Organisasi", icon: "account_tree", permission: "organization-positions.view" },
  { href: "/admin/activities", label: "Agenda & Kegiatan", icon: "event", permission: "activities.view" },
  { href: "/admin/activity-categories", label: "Kategori Agenda", icon: "label", permission: "activity-categories.view" },
  { href: "/admin/articles", label: "Artikel", icon: "article", permission: "articles.view" },
  { href: "/admin/article-categories", label: "Kategori Artikel", icon: "label", permission: "article-categories.view" },
  { href: "/admin/gallery", label: "Galeri", icon: "photo_library", permission: "galleries.view" },
  { href: "/admin/gallery-categories", label: "Kategori Galeri", icon: "label", permission: "gallery-categories.view" },
  { href: "/admin/mosques", label: "Lokasi Masjid & Majelis", icon: "location_on", permission: "settings.view" },
  { href: "/admin/philosophy", label: "Filosofi & Quotes", icon: "auto_awesome", permission: "settings.view" },
  { href: "/admin/announcements", label: "Pengumuman", icon: "campaign", permission: "announcements.view" },
  { href: "/admin/attendance", label: "Kehadiran", icon: "qr_code_scanner", permission: "attendance-sessions.view" },
  { href: "/admin/notifications", label: "Notifikasi", icon: "notifications", permission: "" },
  { href: "/admin/settings", label: "Pengaturan", icon: "settings", permission: "settings.view" },
];

export function Sidebar({ onClose }: { onClose: () => void }) {
  const pathname = usePathname();
  const { can } = usePermission();
  const { logout, user } = useAuth();

  const filtered = navItems.filter((item) => !item.permission || can(item.permission.split(".")[0], item.permission.split(".")[1] as "view"));

  return (
    <aside className="flex flex-col h-full w-full bg-surface-container-lowest border-r border-outline-variant py-md px-sm premium-shadow">
      <div className="px-md mb-lg flex items-center gap-3 border-b border-outline-variant/60 pb-md">
        <img
          src="/logo-mudesco.png"
          alt="Generus MUDES.CO"
          className="h-12 sm:h-14 w-auto object-contain"
        />
        <div className="min-w-0">
          <p className="font-label-sm text-[11px] font-bold uppercase tracking-wider text-primary">Dashboard Admin</p>
        </div>
      </div>

      <nav className="flex-1 overflow-y-auto no-scrollbar space-y-1">
        {filtered.map((item) => {
          const active = pathname === item.href || (item.href !== "/admin" && pathname.startsWith(`${item.href}/`));
          return (
            <Link
              key={item.href}
              href={item.href}
              onClick={onClose}
              className={cn(
                "flex items-center gap-md px-md py-sm rounded-lg font-body-md transition-colors duration-200",
                active
                  ? "bg-primary-container/10 text-primary font-bold border-r-4 border-primary active:scale-[0.98]"
                  : "text-on-surface-variant hover:bg-gold-light hover:text-primary"
              )}
            >
              <Icon name={item.icon} filled={active} className={active ? "text-primary" : "text-on-surface-variant"} />
              <span className="font-body-md text-body-md">{item.label}</span>
            </Link>
          );
        })}
      </nav>

      <div className="mt-auto space-y-1 border-t border-outline-variant pt-md">
        <Link
          href="/"
          target="_blank"
          className="w-full bg-primary text-white font-label-md text-label-md py-sm rounded-lg mb-sm hover:opacity-90 transition-opacity flex items-center justify-center gap-xs"
        >
          <Icon name="open_in_new" className="text-[18px]" />
          Portal Publik
        </Link>


        <div className="px-md py-xs rounded-lg bg-surface-muted border border-outline-variant/40 mb-xs">
          <p className="font-label-md text-label-md text-on-surface truncate">{user?.name}</p>
          <p className="font-label-sm text-label-sm text-on-surface-variant truncate">{user?.email}</p>
        </div>

        <button
          onClick={logout}
          className="w-full flex items-center gap-md px-md py-sm rounded-lg text-on-surface-variant hover:bg-error/10 hover:text-error transition-colors"
        >
          <Icon name="logout" />
          <span className="font-body-md text-body-md">Keluar System</span>
        </button>
      </div>
    </aside>
  );
}

