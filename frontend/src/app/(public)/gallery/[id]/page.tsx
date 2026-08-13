import { GalleryDetailPage } from "@/components/modules/public/GalleryDetailPage";

export const dynamic = "force-dynamic";

interface PageProps {
  params: Promise<{ id: string }>;
}

export default async function GalleryDetailRoute({ params }: PageProps) {
  const { id } = await params;
  return <GalleryDetailPage id={id} />;
}
