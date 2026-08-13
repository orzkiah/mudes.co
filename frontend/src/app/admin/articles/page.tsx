"use client";

import { CrudPage } from "@/components/modules/CrudPage";
import { ArticleForm } from "@/components/modules/ArticleForm";
import { CategorySelect } from "@/components/ui/CategorySelect";
import { Badge } from "@/components/ui/Badge";
import type { Article } from "@/types/models";
import type { ArticleFormValues } from "@/schemas/article";
import { removeEmptyStrings } from "@/lib/form-utils";
import { formatDateTime } from "@/lib/dates";

const endpoint = "/admin/articles";

const statusBadge: Record<string, "default" | "primary" | "success" | "warning" | "error"> = {
  draft: "default",
  scheduled: "warning",
  published: "success",
  archived: "error",
};

const statusLabel: Record<string, string> = {
  draft: "Draft",
  scheduled: "Terjadwal",
  published: "Publikasi",
  archived: "Arsip",
};

export default function ArticlesPage() {
  return (
    <CrudPage<Article>
      endpoint={endpoint}
      permissionPrefix="articles"
      title="Artikel"
      description="Kelola artikel dan publikasi."
      icon="article"
      keyExtractor={(row) => row.id}
      columns={[
        { header: "Kategori", cell: (row) => row.category?.name || "-" },
        { header: "Judul", cell: (row) => row.title },
        {
          header: "Status",
          cell: (row) => <Badge variant={statusBadge[row.status] || "default"}>{statusLabel[row.status] || row.status}</Badge>,
        },
        { header: "Dipublikasikan", cell: (row) => formatDateTime(row.publishedAt) },
        { header: "Dilihat", cell: (row) => row.viewCount.toLocaleString("id-ID") },
      ]}
      FormComponent={({ initial, onSubmit, onCancel, loading }) => {
        const handleSubmit = (values: ArticleFormValues) => {
          onSubmit(removeEmptyStrings(values) as Record<string, unknown>);
        };
        return (
          <ArticleForm
            initial={initial}
            onSubmit={handleSubmit}
            onCancel={onCancel}
            loading={loading}
          />
        );
      }}
      emptyTitle="Belum ada artikel"
      emptyDescription="Buat artikel pertama untuk mulai mempublikasikan konten."
      sortOptions={[
        { value: "published_at", label: "Waktu Publikasi" },
        { value: "created_at", label: "Terbaru" },
      ]}
      filterRender={(filter, setFilter) => (
        <>
          <CategorySelect
            endpoint="/admin/article-categories"
            value={filter.article_category_id || ""}
            onChange={(e) => setFilter({ ...filter, article_category_id: e.target.value })}
            className="lg:w-56"
          />
          <select
            value={filter.status || ""}
            onChange={(e) => setFilter({ ...filter, status: e.target.value })}
            className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface"
          >
            <option value="">Semua status</option>
            <option value="draft">Draft</option>
            <option value="scheduled">Terjadwal</option>
            <option value="published">Publikasi</option>
            <option value="archived">Arsip</option>
          </select>
        </>
      )}
    />
  );
}
