"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "@/providers/AuthProvider";
import { IconButton } from "@/components/ui/Button";
import { Icon } from "@/components/ui/Icon";
import { Avatar } from "@/components/ui/Avatar";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import type { ApiResponse } from "@/lib/api-types";

interface UnreadCount {
  count: number;
}

export function Header({ onMenuClick }: { onMenuClick: () => void }) {
  const router = useRouter();
  const { user, logout } = useAuth();
  const [profileOpen, setProfileOpen] = useState(false);

  const { data: unread } = useQuery<UnreadCount>({
    queryKey: ["notifications", "unread-count"],
    queryFn: async () => {
      const res = await api.get<ApiResponse<UnreadCount>>("/admin/notifications/unread-count");
      return res.data.data;
    },
    refetchInterval: 30_000,
    enabled: !!user,
  });

  return (
    <header className="fixed top-0 right-0 w-full md:w-[calc(100%-240px)] h-16 bg-surface-container-lowest flex items-center justify-between px-lg z-40 premium-shadow border-b border-outline-variant">
      <div className="flex items-center gap-md flex-1">
        <IconButton
          icon="menu"
          variant="ghost"
          className="md:hidden"
          onClick={onMenuClick}
          aria-label="Buka menu"
        />
        <div className="hidden sm:flex items-center bg-surface-muted px-md py-xs rounded-full border border-outline-variant w-72 md:w-96">
          <Icon name="search" className="text-on-surface-variant mr-sm" />
          <input
            type="text"
            placeholder="Cari anggota, jadwal kajian, dokumen..."
            className="bg-transparent border-none focus:ring-0 text-body-sm text-on-surface w-full outline-none placeholder:text-on-surface-variant/60"
          />
        </div>
      </div>

      <div className="flex items-center gap-md md:gap-lg">
        <div className="flex items-center gap-sm">
          <button
            onClick={() => router.push("/admin/notifications")}
            className="relative p-xs text-on-surface-variant hover:text-emerald-600 transition-all active:opacity-80 rounded-lg hover:bg-surface-muted"
            title="Notifikasi"
          >
            <Icon name="notifications" />
            {(unread?.count ?? 0) > 0 && (
              <span className="absolute top-1 right-1 w-2 h-2 bg-error rounded-full" />
            )}
          </button>
          <button
            onClick={() => router.push("/admin/settings")}
            className="p-xs text-on-surface-variant hover:text-emerald-600 transition-all active:opacity-80 rounded-lg hover:bg-surface-muted hidden sm:block"
            title="Bantuan & Pengaturan"
          >
            <Icon name="help" />
          </button>
        </div>

        <div className="h-8 w-[1px] bg-outline-variant hidden sm:block" />

        <div className="relative">
          <button
            onClick={() => setProfileOpen((v) => !v)}
            className="flex items-center gap-sm cursor-pointer group focus:outline-none"
          >
            <div className="text-right hidden md:block">
              <p className="font-label-md text-label-md text-on-surface group-hover:text-primary transition-colors truncate max-w-[150px]">
                {user?.name ?? "Administrator"}
              </p>
              <p className="font-label-sm text-label-sm text-on-surface-variant truncate max-w-[150px]">
                {user?.roles?.[0] ?? "Primary Admin"}
              </p>
            </div>
            <div className="w-10 h-10 rounded-full bg-gold-light flex items-center justify-center border-2 border-white overflow-hidden premium-shadow text-primary font-bold">
              <Avatar name={user?.name ?? "Admin"} size="md" />
            </div>
          </button>

          {profileOpen && (
            <>
              <div
                className="fixed inset-0 z-40"
                onClick={() => setProfileOpen(false)}
              />
              <div className="absolute right-0 top-full mt-2 w-52 bg-surface-container-lowest rounded-lg border border-outline-variant premium-shadow z-50 py-2">
                <div className="px-md py-2 border-b border-outline-variant md:hidden">
                  <p className="font-label-md text-label-md text-on-surface">{user?.name}</p>
                  <p className="font-label-sm text-label-sm text-on-surface-variant">{user?.email}</p>
                </div>
                <button
                  onClick={() => {
                    setProfileOpen(false);
                    router.push("/admin/settings");
                  }}
                  className="w-full flex items-center gap-md px-md py-sm text-body-sm text-on-surface hover:bg-gold-light hover:text-primary transition-colors"
                >
                  <Icon name="settings" />
                  Pengaturan System
                </button>
                <button
                  onClick={() => {
                    setProfileOpen(false);
                    logout();
                  }}
                  className="w-full flex items-center gap-md px-md py-sm text-body-sm text-error hover:bg-error/10 transition-colors"
                >
                  <Icon name="logout" />
                  Keluar dari System
                </button>
              </div>
            </>
          )}
        </div>
      </div>
    </header>
  );
}

