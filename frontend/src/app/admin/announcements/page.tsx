"use client";

import { CrudPage } from "@/components/modules/CrudPage";
import { AnnouncementForm } from "@/components/modules/AnnouncementForm";
import { Badge } from "@/components/ui/Badge";
import type { Announcement } from "@/types/models";
import type { AnnouncementFormValues } from "@/schemas/announcement";
import { removeEmptyStrings } from "@/lib/form-utils";
import { formatDateTime } from "@/lib/dates";

export default function AnnouncementsPage() {
  return (
    <CrudPage<Announcement>
      endpoint="/admin/announcements"
      permissionPrefix="announcements"
      title="Pengumuman"
      description="Kelola pengumuman publik dan internal."
      icon="campaign"
      keyExtractor={(row) => row.id}
      columns={[
        { header: "Judul", cell: (row) => row.title },
        { header: "Prioritas", cell: (row) => <Badge variant={row.priority === "urgent" ? "error" : "default"}>{row.priority === "urgent" ? "Urgent" : "Normal"}</Badge> },
        { header: "Audiens", cell: (row) => row.audience === "public" ? "Publik" : "Internal" },
        { header: "Pinned", cell: (row) => row.pinned ? <Badge variant="primary">Ya</Badge> : "-" },
        { header: "Mulai", cell: (row) => formatDateTime(row.startsAt) },
        { header: "Berakhir", cell: (row) => formatDateTime(row.expiresAt) },
      ]}
      FormComponent={({ initial, onSubmit, onCancel, loading }) => (
        <AnnouncementForm initial={initial} onSubmit={(values: AnnouncementFormValues) => onSubmit(removeEmptyStrings(values) as Record<string, unknown>)} onCancel={onCancel} loading={loading} />
      )}
      emptyTitle="Belum ada pengumuman"
      emptyDescription="Buat pengumuman baru untuk menginformasikan anggota komunitas."
      sortOptions={[{ value: "starts_at", label: "Waktu Mulai" }, { value: "created_at", label: "Terbaru" }]}
      filterRender={(filter, setFilter) => (
        <>
          <select value={filter.priority || ""} onChange={(e) => setFilter({ ...filter, priority: e.target.value })} className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface">
            <option value="">Semua prioritas</option>
            <option value="normal">Normal</option>
            <option value="urgent">Urgent</option>
          </select>
          <select value={filter.audience || ""} onChange={(e) => setFilter({ ...filter, audience: e.target.value })} className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface">
            <option value="">Semua audiens</option>
            <option value="public">Publik</option>
            <option value="internal">Internal</option>
          </select>
        </>
      )}
    />
  );
}
