import { ArticleDetailPage } from "@/components/modules/public/ArticleDetailPage";

export const dynamic = "force-dynamic";

interface PageProps {
  params: Promise<{ slug: string }>;
}

export default async function ArticleDetailRoute({ params }: PageProps) {
  const { slug } = await params;
  return <ArticleDetailPage slug={slug} />;
}
