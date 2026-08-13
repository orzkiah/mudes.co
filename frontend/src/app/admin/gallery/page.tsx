"use client";

import { CrudPage } from "@/components/modules/CrudPage";
import { GalleryForm } from "@/components/modules/GalleryForm";
import { CategorySelect } from "@/components/ui/CategorySelect";
import type { Gallery, Activity } from "@/types/models";
import type { GalleryFormValues } from "@/schemas/gallery";
import { removeEmptyStrings } from "@/lib/form-utils";
import { api } from "@/lib/api";
import type { ApiResponse } from "@/lib/api-types";
import { useQuery, useQueryClient } from "@tanstack/react-query";

const endpoint = "/admin/galleries";

export default function GalleryPage() {
  const { data: activities } = useQuery({
    queryKey: ["/admin/activities", "options"],
    queryFn: async () => {
      const res = await api.get<ApiResponse<Activity[]>>("/admin/activities", { params: { perPage: 100 } });
      return res.data.data;
    },
    staleTime: 5 * 60 * 1000,
  });

  return (
    <CrudPage<Gallery>
      endpoint={endpoint}
      permissionPrefix="galleries"
      title="Galeri"
      description="Kelola album foto kegiatan."
      icon="photo_library"
      keyExtractor={(row) => row.id}
      columns={[
        { header: "Kategori", cell: (row) => row.category?.name || "-" },
        { header: "Judul", cell: (row) => row.title },
        { header: "Foto", cell: (row) => row.photoCount },
        {
          header: "Kegiatan",
          cell: (row) => activities?.find((a) => a.id === row.activityId)?.title || "-",
        },
      ]}
      FormComponent={({ initial, onCancel }) => (
        <GalleryForm
          initial={initial}
          onCancel={onCancel}
        />
      )}
      emptyTitle="Belum ada album galeri"
      emptyDescription="Buat album foto pertama untuk mendokumentasikan kegiatan."
      sortOptions={[
        { value: "created_at", label: "Terbaru" },
        { value: "title", label: "Judul" },
      ]}
      filterRender={(filter, setFilter) => (
        <>
          <CategorySelect
            endpoint="/admin/gallery-categories"
            value={filter.gallery_category_id || ""}
            onChange={(e) => setFilter({ ...filter, gallery_category_id: e.target.value })}
            className="lg:w-56"
          />
          <select
            value={filter.activity_id || ""}
            onChange={(e) => setFilter({ ...filter, activity_id: e.target.value })}
            className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface"
          >
            <option value="">Semua kegiatan</option>
            {activities?.map((a) => (
              <option key={a.id} value={a.id}>
                {a.title}
              </option>
            ))}
          </select>
        </>
      )}
    />
  );
}
