import { TaxonomyPage } from "@/components/modules/TaxonomyPage";

export default function StudyCategoriesPage() {
  return (
    <TaxonomyPage
      endpoint="/admin/study-categories"
      permissionPrefix="study-categories"
      title="Kategori Kajian"
      description="Kelola kategori jadwal kajian."
      itemCountKey="scheduleCount"
      icon="menu_book"
    />
  );
}
