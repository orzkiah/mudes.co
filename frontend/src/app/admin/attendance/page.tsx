"use client";

import { CrudPage } from "@/components/modules/CrudPage";
import { AttendanceSessionForm } from "@/components/modules/AttendanceSessionForm";
import { Badge } from "@/components/ui/Badge";
import type { AttendanceSession } from "@/types/models";
import type { AttendanceSessionFormValues } from "@/schemas/attendanceSession";
import { formatDateTime } from "@/lib/dates";

export default function AttendancePage() {
  return (
    <CrudPage<AttendanceSession>
      endpoint="/admin/attendance/sessions"
      permissionPrefix="attendance-sessions"
      title="Kehadiran"
      description="Kelola sesi presensi QR untuk kajian dan kegiatan."
      icon="qr_code_scanner"
      keyExtractor={(row) => row.id}
      columns={[
        { header: "Sumber", cell: (row) => <Badge variant="primary">{row.sourceType === "activity" ? "Kegiatan" : "Kajian"}</Badge> },
        { header: "ID Sumber", cell: (row) => row.sourceId },
        { header: "Buka", cell: (row) => formatDateTime(row.opensAt) },
        { header: "Tutup", cell: (row) => formatDateTime(row.closesAt) },
        { header: "Hadir", cell: (row) => row.attendanceCount.toLocaleString("id-ID") },
        { header: "Token QR", cell: (row) => row.qrToken ? `${row.qrToken.slice(0, 8)}…` : "-" },
      ]}
      FormComponent={({ initial, onSubmit, onCancel, loading }) => (
        <AttendanceSessionForm initial={initial} onSubmit={(values: AttendanceSessionFormValues) => onSubmit(values as Record<string, unknown>)} onCancel={onCancel} loading={loading} />
      )}
      emptyTitle="Belum ada sesi kehadiran"
      emptyDescription="Buat sesi presensi QR untuk kajian atau kegiatan."
      sortOptions={[{ value: "opens_at", label: "Waktu Buka" }, { value: "created_at", label: "Terbaru" }]}
      filterRender={(filter, setFilter) => (
        <select value={filter.sourceType || ""} onChange={(e) => setFilter({ ...filter, sourceType: e.target.value })} className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface">
          <option value="">Semua sumber</option>
          <option value="activity">Kegiatan</option>
          <option value="schedule_occurrence">Kajian</option>
        </select>
      )}
    />
  );
}
