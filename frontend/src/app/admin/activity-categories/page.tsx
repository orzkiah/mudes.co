import { TaxonomyPage } from "@/components/modules/TaxonomyPage";

export default function ActivityCategoriesPage() {
  return (
    <TaxonomyPage
      endpoint="/admin/activity-categories"
      permissionPrefix="activity-categories"
      title="Kategori Kegiatan"
      description="Kelola kategori kegiatan."
      itemCountKey="activityCount"
      icon="event"
    />
  );
}
