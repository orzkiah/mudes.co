"use client";

import { CrudPage } from "@/components/modules/CrudPage";
import { LibraryForm } from "@/components/modules/LibraryForm";
import { CategorySelect } from "@/components/ui/CategorySelect";
import { Badge } from "@/components/ui/Badge";
import type { LibraryDocument } from "@/types/models";
import type { LibraryFormValues } from "@/schemas/library";
import { removeEmptyStrings } from "@/lib/form-utils";

const typeLabel: Record<string, string> = { pdf: "PDF", audio: "Audio", video_link: "Video" };

export default function LibraryAdminPage() {
  return (
    <CrudPage<LibraryDocument>
      endpoint="/admin/library"
      permissionPrefix="library-documents"
      title="Perpustakaan Digital"
      description="Kelola materi kajian, dokumen, audio, dan tautan video."
      icon="library_books"
      keyExtractor={(row) => row.id}
      columns={[
        {
          header: "Sampul / Preview",
          cell: (row) => {
            const isImage = row.file?.mimeType?.startsWith("image/");
            const previewUrl =
              isImage && row.file?.url
                ? row.file.url
                : row.libraryType === "pdf"
                ? "/images/book-cover.jpg"
                : "/images/library-cover.jpg";
            return (
              <div className="relative h-12 w-20 overflow-hidden rounded-lg border border-outline/10 bg-surface-container shadow-2xs group">
                <img
                  src={previewUrl}
                  alt={row.title}
                  className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
              </div>
            );
          },
        },
        { header: "Kategori", cell: (row) => row.category?.name || "-" },
        { header: "Judul", cell: (row) => <span className="font-semibold text-[#004b36]">{row.title}</span> },
        { header: "Tipe", cell: (row) => <Badge variant="primary">{typeLabel[row.libraryType] || row.libraryType}</Badge> },
        { header: "Visibilitas", cell: (row) => <Badge variant={row.visibility === "public" ? "success" : "default"}>{row.visibility === "public" ? "Publik" : "Internal"}</Badge> },
        { header: "Unduhan", cell: (row) => row.downloadCount.toLocaleString("id-ID") },
      ]}
      FormComponent={({ initial, onSubmit, onCancel, loading }) => (
        <LibraryForm initial={initial} onSubmit={(values: LibraryFormValues) => onSubmit(removeEmptyStrings(values) as Record<string, unknown>)} onCancel={onCancel} loading={loading} />
      )}
      emptyTitle="Belum ada dokumen perpustakaan"
      emptyDescription="Unggah dokumen, audio, atau tautan video untuk perpustakaan digital."
      sortOptions={[{ value: "created_at", label: "Terbaru" }, { value: "title", label: "Judul" }]}
      filterRender={(filter, setFilter) => (
        <>
          <CategorySelect endpoint="/admin/library-categories" value={filter.library_category_id || ""} onChange={(e) => setFilter({ ...filter, library_category_id: e.target.value })} className="lg:w-56" />
          <select value={filter.visibility || ""} onChange={(e) => setFilter({ ...filter, visibility: e.target.value })} className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface">
            <option value="">Semua visibilitas</option>
            <option value="public">Publik</option>
            <option value="internal">Internal</option>
          </select>
        </>
      )}
    />
  );
}
