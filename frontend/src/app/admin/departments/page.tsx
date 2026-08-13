import { TaxonomyPage } from "@/components/modules/TaxonomyPage";

export default function DepartmentsPage() {
  return (
    <TaxonomyPage
      endpoint="/admin/departments"
      permissionPrefix="departments"
      title="Departemen"
      description="Kelola departemen organisasi."
      itemCountKey="positionCount"
      icon="workspaces"
    />
  );
}
