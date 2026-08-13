"use client";

import Image from "next/image";
import { CrudPage } from "@/components/modules/CrudPage";
import { OrganizationPositionForm } from "@/components/modules/OrganizationPositionForm";
import type { OrganizationPosition } from "@/types/models";
import type { OrganizationPositionFormValues } from "@/schemas/organizationPosition";
import { removeEmptyStrings } from "@/lib/form-utils";

export default function OrganizationPositionsPage() {
  return (
    <CrudPage<OrganizationPosition>
      endpoint="/admin/organization/positions"
      permissionPrefix="organization-positions"
      title="Struktur Organisasi"
      description="Kelola pengurus, dapukan, dan urutan tampilan struktur."
      icon="account_tree"
      keyExtractor={(row) => row.id}
      columns={[
        {
          header: "Pengurus",
          cell: (row) => {
            const name = row.member?.fullName ?? "Vacant (Belum Diisi)";
            const photoUrl = row.member?.photo?.url;
            return (
              <div className="flex items-center gap-3">
                <div className="relative h-9 w-9 rounded-full overflow-hidden shrink-0 bg-[#004b36]/10 border border-outline/10">
                  {photoUrl ? (
                    <Image src={photoUrl} alt={name} fill className="object-cover" />
                  ) : (
                    <div className="flex h-full w-full items-center justify-center bg-[#004b36] text-[#f9c74f] text-[12px] font-bold">
                      {name[0] ?? "P"}
                    </div>
                  )}
                </div>
                <span className="font-semibold text-on-surface text-body-sm">{name}</span>
              </div>
            );
          },
        },
        {
          header: "Dapukan / Jabatan",
          cell: (row) => (
            <span className="font-bold text-[#004b36] text-body-sm">
              {row.title}
            </span>
          ),
        },
        {
          header: "Urutan Tampilan",
          cell: (row) => (
            <span className="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#004b36]/10 text-[#004b36] text-label-sm font-bold">
              {row.displayOrder}
            </span>
          ),
        },
      ]}
      FormComponent={({ initial, onSubmit, onCancel, loading }) => (
        <OrganizationPositionForm
          initial={initial}
          onSubmit={(values: OrganizationPositionFormValues) =>
            onSubmit(removeEmptyStrings(values) as Record<string, unknown>)
          }
          onCancel={onCancel}
          loading={loading}
        />
      )}
      emptyTitle="Belum ada data struktur organisasi"
      emptyDescription="Tambahkan pengurus & dapukan untuk membangun struktur organisasi."
      sortOptions={[
        { value: "display_order", label: "Urutan Tampilan" },
        { value: "title", label: "Dapukan / Jabatan" },
        { value: "created_at", label: "Tanggal Dibuat" },
      ]}
    />
  );
}
