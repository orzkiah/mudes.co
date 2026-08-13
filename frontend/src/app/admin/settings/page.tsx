"use client";

import { CrudPage } from "@/components/modules/CrudPage";
import { SettingForm } from "@/components/modules/SettingForm";
import { Badge } from "@/components/ui/Badge";
import type { Setting } from "@/types/models";
import type { SettingFormValues } from "@/schemas/setting";
import { removeEmptyStrings } from "@/lib/form-utils";

export default function SettingsPage() {
  return (
    <CrudPage<Setting>
      endpoint="/admin/settings"
      permissionPrefix="settings"
      title="Pengaturan"
      description="Kelola konfigurasi website dan integrasi sistem."
      icon="settings"
      keyExtractor={(row) => row.id}
      columns={[
        { header: "Key", cell: (row) => row.key },
        { header: "Grup", cell: (row) => row.group || "-" },
        { header: "Tipe", cell: (row) => <Badge variant="default">{row.type}</Badge> },
        { header: "Value", cell: (row) => String(row.value ?? "-").slice(0, 64) },
        { header: "Encrypted", cell: (row) => row.isEncrypted ? <Badge variant="warning">Ya</Badge> : "-" },
        { header: "Autoload", cell: (row) => row.autoload ? <Badge variant="primary">Ya</Badge> : "-" },
      ]}
      FormComponent={({ initial, onSubmit, onCancel, loading }) => (
        <SettingForm initial={initial} onSubmit={(values: SettingFormValues) => onSubmit(removeEmptyStrings(values) as Record<string, unknown>)} onCancel={onCancel} loading={loading} />
      )}
      emptyTitle="Belum ada pengaturan"
      emptyDescription="Tambahkan konfigurasi sistem untuk mengelola pengaturan aplikasi."
      sortOptions={[{ value: "key", label: "Key" }, { value: "group", label: "Grup" }, { value: "created_at", label: "Terbaru" }]}
      filterRender={(filter, setFilter) => (
        <>
          <input value={filter.group || ""} onChange={(e) => setFilter({ ...filter, group: e.target.value })} placeholder="Grup" className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface" />
          <select value={filter.type || ""} onChange={(e) => setFilter({ ...filter, type: e.target.value })} className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface">
            <option value="">Semua tipe</option>
            <option value="string">String</option>
            <option value="number">Number</option>
            <option value="boolean">Boolean</option>
            <option value="json">JSON</option>
            <option value="encrypted">Encrypted</option>
          </select>
        </>
      )}
    />
  );
}
