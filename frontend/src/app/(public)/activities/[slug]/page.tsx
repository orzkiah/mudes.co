import { ActivityDetailPage } from "@/components/modules/public/ActivityDetailPage";

export const dynamic = "force-dynamic";

interface PageProps {
  params: Promise<{ slug: string }>;
}

export default async function ActivityDetailRoute({ params }: PageProps) {
  const { slug } = await params;
  return <ActivityDetailPage slug={slug} />;
}
