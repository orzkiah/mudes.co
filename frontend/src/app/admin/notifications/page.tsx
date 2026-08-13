"use client";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import Link from "next/link";
import { Alert } from "@/components/ui/Alert";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Icon } from "@/components/ui/Icon";
import { Skeleton } from "@/components/ui/Skeleton";
import { api } from "@/lib/api";
import { getApiErrorMessage } from "@/lib/query-client";
import { formatDateTime } from "@/lib/dates";
import type { ApiResponse } from "@/lib/api-types";
import type { NotificationItem } from "@/types/models";

const typeLabel: Record<string, string> = {
  announcement: "Pengumuman",
  attendance_reminder: "Presensi",
  study_reminder: "Kajian",
  content_approval: "Approval Konten",
  system: "Sistem",
};

export default function NotificationsPage() {
  const queryClient = useQueryClient();
  const queryKey = ["admin", "notifications"];

  const { data, error, isLoading } = useQuery({
    queryKey,
    queryFn: async () => {
      const response = await api.get<ApiResponse<NotificationItem[]>>("/admin/notifications", {
        params: { perPage: 50 },
      });
      return response.data.data;
    },
  });

  const invalidate = () => queryClient.invalidateQueries({ queryKey });

  const markRead = useMutation({
    mutationFn: (id: string) => api.post(`/admin/notifications/${id}/mark-read`),
    onSuccess: invalidate,
    onError: (err) => console.error(getApiErrorMessage(err)),
  });

  const markAllRead = useMutation({
    mutationFn: () => api.post("/admin/notifications/mark-all-read"),
    onSuccess: invalidate,
    onError: (err) => console.error(getApiErrorMessage(err)),
  });

  const remove = useMutation({
    mutationFn: (id: string) => api.delete(`/admin/notifications/${id}`),
    onSuccess: invalidate,
    onError: (err) => console.error(getApiErrorMessage(err)),
  });

  const items = data ?? [];
  const hasUnread = items.some((n) => !n.isRead);

  return (
    <div className="space-y-6">
      {/* Page header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-headline-md font-headline-md text-on-surface flex items-center gap-2">
            <Icon name="notifications" /> Notifikasi
          </h1>
          <p className="text-body-md text-on-surface-variant mt-1">Daftar notifikasi untuk akun Anda.</p>
        </div>
        <Button
          variant="outline"
          leftIcon="done_all"
          onClick={() => markAllRead.mutate()}
          loading={markAllRead.isPending}
          disabled={markAllRead.isPending || !hasUnread || isLoading}
        >
          Tandai semua dibaca
        </Button>
      </div>

      {/* Error state */}
      {error && (
        <Alert variant="error">Gagal memuat notifikasi. Periksa koneksi dan coba lagi.</Alert>
      )}

      {/* Loading state */}
      {isLoading && (
        <Card className="divide-y divide-outline-variant overflow-hidden">
          {Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="p-md flex items-start gap-3">
              <Skeleton className="h-5 w-20 shrink-0" />
              <div className="flex-1 space-y-2">
                <Skeleton className="h-4 w-3/4" />
                <Skeleton className="h-3 w-1/3" />
              </div>
            </div>
          ))}
        </Card>
      )}

      {/* Empty state */}
      {!isLoading && !error && items.length === 0 && (
        <EmptyState
          title="Tidak ada notifikasi"
          description="Belum ada notifikasi untuk akun Anda saat ini."
          icon="notifications_none"
        />
      )}

      {/* Notification list */}
      {!isLoading && !error && items.length > 0 && (
        <Card className="divide-y divide-outline-variant overflow-hidden">
          {items.map((item) => {
            const actionUrl = typeof item.data.actionUrl === "string" ? item.data.actionUrl : null;
            const isRemoving = remove.isPending && remove.variables === item.id;
            const isMarkingRead = markRead.isPending && markRead.variables === item.id;

            return (
              <div
                key={item.id}
                className={`p-md flex flex-col lg:flex-row lg:items-center gap-3 justify-between transition-opacity ${
                  isRemoving ? "opacity-50 pointer-events-none" : ""
                }`}
              >
                <div className="space-y-1">
                  <div className="flex flex-wrap items-center gap-2">
                    <Badge variant={item.isRead ? "default" : "primary"}>
                      {typeLabel[item.type] || item.type}
                    </Badge>
                    {!item.isRead && <Badge variant="warning">Baru</Badge>}
                    <span className="text-label-sm text-on-surface-variant">
                      {formatDateTime(item.createdAt)}
                    </span>
                  </div>
                  <p className="text-body-md text-on-surface">
                    {String(item.data.message ?? "Notifikasi sistem")}
                  </p>
                  {actionUrl && (
                    <Link href={actionUrl} className="text-label-md text-primary hover:underline">
                      Buka detail
                    </Link>
                  )}
                </div>

                <div className="flex shrink-0 gap-2">
                  {!item.isRead && (
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => markRead.mutate(item.id)}
                      loading={isMarkingRead}
                      disabled={isMarkingRead}
                    >
                      Dibaca
                    </Button>
                  )}
                  <Button
                    size="sm"
                    variant="ghost"
                    onClick={() => remove.mutate(item.id)}
                    loading={isRemoving}
                    disabled={isRemoving}
                  >
                    Hapus
                  </Button>
                </div>
              </div>
            );
          })}
        </Card>
      )}
    </div>
  );
}
