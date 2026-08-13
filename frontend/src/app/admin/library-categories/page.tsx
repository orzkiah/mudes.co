import { TaxonomyPage } from "@/components/modules/TaxonomyPage";

export default function LibraryCategoriesPage() {
  return (
    <TaxonomyPage
      endpoint="/admin/library-categories"
      permissionPrefix="library-categories"
      title="Kategori Perpustakaan"
      description="Kelola kategori dokumen perpustakaan."
      itemCountKey="documentCount"
      icon="library_books"
    />
  );
}
