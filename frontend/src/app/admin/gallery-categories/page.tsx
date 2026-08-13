import { TaxonomyPage } from "@/components/modules/TaxonomyPage";

export default function GalleryCategoriesPage() {
  return (
    <TaxonomyPage
      endpoint="/admin/gallery-categories"
      permissionPrefix="gallery-categories"
      title="Kategori Galeri"
      description="Kelola kategori galeri foto."
      itemCountKey="galleryCount"
      icon="photo_library"
    />
  );
}
