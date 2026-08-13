import { TaxonomyPage } from "@/components/modules/TaxonomyPage";

export default function ArticleCategoriesPage() {
  return (
    <TaxonomyPage
      endpoint="/admin/article-categories"
      permissionPrefix="article-categories"
      title="Kategori Artikel"
      description="Kelola kategori artikel."
      itemCountKey="articleCount"
      icon="article"
    />
  );
}
