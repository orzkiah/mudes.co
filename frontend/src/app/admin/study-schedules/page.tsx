"use client";

import { CrudPage } from "@/components/modules/CrudPage";
import { StudyScheduleForm } from "@/components/modules/StudyScheduleForm";
import { CategorySelect } from "@/components/ui/CategorySelect";
import { Badge } from "@/components/ui/Badge";
import type { StudySchedule } from "@/types/models";
import type { StudyScheduleFormValues } from "@/schemas/studySchedule";
import { removeEmptyStrings } from "@/lib/form-utils";

const dayLabels: Record<number, string> = {
  0: "Minggu",
  1: "Senin",
  2: "Selasa",
  3: "Rabu",
  4: "Kamis",
  5: "Jumat",
  6: "Sabtu",
};

const endpoint = "/admin/schedule";

export default function StudySchedulesPage() {
  return (
    <CrudPage<StudySchedule>
      endpoint={endpoint}
      permissionPrefix="study-schedules"
      title="Jadwal Kajian"
      description="Kelola jadwal kajian rutin."
      icon="menu_book"
      keyExtractor={(row) => row.id}
      columns={[
        {
          header: "Kategori",
          cell: (row) => (
            <div className="flex items-center gap-2">
              {row.category?.icon && (
                <span style={{ color: row.category.color || undefined }}>{row.category.icon}</span>
              )}
              {row.category?.name || "-"}
            </div>
          ),
        },
        {
          header: "Tanggal / Hari",
          cell: (row) => {
            const dayName = dayLabels[row.dayOfWeek] || "-";
            if (row.scheduledDate) {
              const parts = row.scheduledDate.split("-").map(Number);
              if (parts.length === 3) {
                const months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
                return `${dayName}, ${parts[2]} ${months[parts[1] - 1]} ${parts[0]}`;
              }
            }
            return dayName;
          },
        },
        { header: "Waktu", cell: (row) => `${row.startTime.slice(0, 5)} - ${row.endTime.slice(0, 5)}` },
        { header: "Topik", cell: (row) => row.topic || "-" },
        { header: "Ustadz", cell: (row) => row.ustadzName },
        { header: "Lokasi", cell: (row) => row.location || "-" },
        {
          header: "Status",
          cell: (row) => <Badge variant={row.isActive ? "success" : "outline"}>{row.isActive ? "Aktif" : "Nonaktif"}</Badge>,
        },
      ]}
      FormComponent={({ initial, onSubmit, onCancel, loading }) => {
        const handleSubmit = (values: StudyScheduleFormValues) => {
          onSubmit(removeEmptyStrings(values) as Record<string, unknown>);
        };
        return (
          <StudyScheduleForm
            initial={initial}
            onSubmit={handleSubmit}
            onCancel={onCancel}
            loading={loading}
          />
        );
      }}
      emptyTitle="Belum ada jadwal kajian"
      emptyDescription="Tambahkan jadwal kajian rutin untuk ditampilkan kepada anggota."
      sortOptions={[
        { value: "day_of_week", label: "Hari" },
        { value: "created_at", label: "Terbaru" },
      ]}
      filterRender={(filter, setFilter) => (
        <>
          <CategorySelect
            endpoint="/admin/study-categories"
            value={filter.study_category_id || ""}
            onChange={(e) => setFilter({ ...filter, study_category_id: e.target.value })}
            className="lg:w-56"
          />
          <select
            value={filter.is_active || ""}
            onChange={(e) => setFilter({ ...filter, is_active: e.target.value })}
            className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface"
          >
            <option value="">Semua status</option>
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
          </select>
        </>
      )}
    />
  );
}
