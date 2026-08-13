"use client";

import { CrudPage } from "@/components/modules/CrudPage";
import { ActivityForm } from "@/components/modules/ActivityForm";
import { CategorySelect } from "@/components/ui/CategorySelect";
import { Badge } from "@/components/ui/Badge";
import type { Activity } from "@/types/models";
import type { ActivityFormValues } from "@/schemas/activity";
import { removeEmptyStrings } from "@/lib/form-utils";
import { formatDateTime } from "@/lib/dates";

const endpoint = "/admin/activities";

const statusBadge: Record<string, "default" | "primary" | "success" | "error"> = {
  upcoming: "default",
  ongoing: "primary",
  completed: "success",
  cancelled: "error",
};

const statusLabel: Record<string, string> = {
  upcoming: "Akan datang",
  ongoing: "Berlangsung",
  completed: "Selesai",
  cancelled: "Dibatalkan",
};

export default function ActivitiesPage() {
  return (
    <CrudPage<Activity>
      endpoint={endpoint}
      permissionPrefix="activities"
      title="Agenda & Kegiatan"
      description="Kelola seluruh agenda acara, kajian, dan kegiatan komunitas dalam satu tempat."
      icon="event"
      keyExtractor={(row) => row.id}
      emptyTitle="Belum ada agenda & kegiatan"
      emptyDescription="Tambahkan agenda atau kegiatan baru untuk mulai mengelola acara komunitas."
      columns={[
        {
          header: "Kategori",
          cell: (row) => row.category?.name || "-",
        },
        { header: "Judul", cell: (row) => row.title },
        { header: "Mulai", cell: (row) => formatDateTime(row.startAt) },
        { header: "Selesai", cell: (row) => (row.endAt ? formatDateTime(row.endAt) : "-") },
        { header: "Lokasi", cell: (row) => row.location || "-" },
        {
          header: "Status",
          cell: (row) => {
            const statusKey = (() => {
              if (row.status === "cancelled") return "cancelled";
              const now = new Date();
              const start = new Date(row.startAt);
              const end = row.endAt ? new Date(row.endAt) : new Date(start.getTime() + 24 * 60 * 60 * 1000);
              if (now > end) return "completed";
              if (now >= start && now <= end) return "ongoing";
              return "upcoming";
            })();

            return (
              <Badge variant={statusBadge[statusKey] || "default"}>
                {statusLabel[statusKey] || statusKey}
              </Badge>
            );
          },
        },
      ]}
      FormComponent={({ initial, onSubmit, onCancel, loading }) => {
        const handleSubmit = (values: ActivityFormValues) => {
          onSubmit(removeEmptyStrings(values) as Record<string, unknown>);
        };
        return (
          <ActivityForm
            initial={initial}
            onSubmit={handleSubmit}
            onCancel={onCancel}
            loading={loading}
          />
        );
      }}
      sortOptions={[
        { value: "start_at", label: "Waktu Mulai" },
        { value: "created_at", label: "Terbaru" },
      ]}
      filterRender={(filter, setFilter) => (
        <>
          <CategorySelect
            endpoint="/admin/activity-categories"
            value={filter.activity_category_id || ""}
            onChange={(e) => setFilter({ ...filter, activity_category_id: e.target.value })}
            className="lg:w-56"
          />
          <select
            value={filter.status || ""}
            onChange={(e) => setFilter({ ...filter, status: e.target.value })}
            className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface"
          >
            <option value="">Semua status</option>
            <option value="upcoming">Akan datang</option>
            <option value="ongoing">Berlangsung</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
          </select>
        </>
      )}
    />
  );
}
