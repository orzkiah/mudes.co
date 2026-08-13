"use client";

import Link from "next/link";
import Image from "next/image";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { formatDate } from "@/lib/dates";
import type { Article } from "@/types/models";

interface ArticleDetailPageProps {
  slug: string;
}

export function ArticleDetailPage({ slug }: ArticleDetailPageProps) {
  const { data: article, isLoading, error } = useQuery<Article>({
    queryKey: ["public", "articles", slug],
    queryFn: async () => {
      const res = await api.get(`/public/articles/${slug}`);
      return res.data.data;
    },
    staleTime: 5 * 60 * 1000,
  });

  return (
    <div className="min-h-screen bg-[#f5f0e8] text-on-surface font-body-md selection:bg-emerald-200 selection:text-emerald-950">
      
      {/* ── Header Bar / Back Navigation ── */}
      <div className="max-w-container-max mx-auto px-6 pt-10 md:px-10">
        <Link
          href="/articles"
          className="inline-flex items-center gap-2 text-body-sm font-semibold text-[#004b36] hover:underline"
        >
          <span className="material-symbols-outlined text-[18px]">arrow_back</span>
          Kembali ke Berita &amp; Artikel
        </Link>
      </div>

      {/* ── Main Article Container ── */}
      <article className="max-w-4xl mx-auto px-6 py-10 md:px-10">
        {isLoading ? (
          <div className="space-y-6 animate-pulse">
            <div className="h-6 w-32 bg-primary/10 rounded-full" />
            <div className="h-12 w-full bg-primary/10 rounded-xl" />
            <div className="h-[320px] w-full bg-primary/10 rounded-2xl" />
            <div className="space-y-3 pt-4">
              <div className="h-4 w-full bg-primary/10 rounded" />
              <div className="h-4 w-5/6 bg-primary/10 rounded" />
              <div className="h-4 w-4/6 bg-primary/10 rounded" />
            </div>
          </div>
        ) : error || !article ? (
          <div className="py-20 text-center space-y-4">
            <span className="material-symbols-outlined text-[64px] text-outline">article</span>
            <h1 className="text-[28px] font-bold text-[#004b36] font-headline-sm">Artikel Tidak Ditemukan</h1>
            <p className="text-body-md text-on-surface-variant max-w-md mx-auto">
              Artikel yang Anda cari mungkin telah dipindahkan atau belum dipublikasikan.
            </p>
            <div className="pt-4">
              <Link
                href="/articles"
                className="inline-flex items-center gap-2 rounded-full bg-[#004b36] px-6 py-2.5 text-label-md font-semibold text-white hover:bg-emerald-950"
              >
                Lihat Semua Artikel
              </Link>
            </div>
          </div>
        ) : (
          <div className="space-y-8 animate-in fade-in duration-500">
            {/* Header info */}
            <div className="space-y-4">
              <div className="flex flex-wrap items-center gap-3">
                <span className="rounded-full bg-[#004b36] px-3.5 py-1 text-label-sm font-semibold text-white uppercase tracking-wider">
                  {article.category?.name ?? "Artikel KOMUNITAS"}
                </span>
                <span className="text-label-sm text-on-surface-variant font-medium">
                  {article.publishedAt ? formatDate(article.publishedAt) : "Baru Diterbitkan"}
                </span>
                {article.viewCount > 0 && (
                  <span className="text-label-sm text-on-surface-variant/70 flex items-center gap-1">
                    <span className="material-symbols-outlined text-[14px]">visibility</span>
                    {article.viewCount.toLocaleString("id-ID")} dibaca
                  </span>
                )}
              </div>

              <h1 className="text-[36px] sm:text-[48px] font-bold leading-[1.12] text-[#004b36] font-headline-sm tracking-tight">
                {article.title}
              </h1>

              {article.excerpt && (
                <p className="text-[20px] leading-relaxed text-on-surface-variant font-medium border-l-4 border-amber-400 pl-4 py-1">
                  {article.excerpt}
                </p>
              )}
            </div>

            {/* Main Cover image */}
            {article.cover?.url && (
              <div className="relative h-[300px] sm:h-[440px] w-full rounded-3xl overflow-hidden shadow-sm bg-slate-900 border border-outline/10">
                <Image
                  src={article.cover.url}
                  alt={article.title}
                  fill
                  priority
                  className="object-cover"
                />
              </div>
            )}

            {/* Article body with inline image & rich section parser */}
            <div className="prose prose-lg max-w-none text-on-surface font-body-md leading-relaxed space-y-6 pt-6 border-t border-outline/10">
              {article.body ? (
                article.body.split("\n\n").map((paragraph, idx) => {
                  const trimmed = paragraph.trim();

                  // 1. Check Sub-heading: ### Sub-judul
                  if (trimmed.startsWith("### ") || trimmed.startsWith("## ")) {
                    const headingText = trimmed.replace(/^#+\s*/, "");
                    return (
                      <h3 key={idx} className="text-[24px] sm:text-[28px] font-bold text-[#004b36] pt-4 font-headline-sm tracking-tight border-b border-[#004b36]/15 pb-2">
                        {headingText}
                      </h3>
                    );
                  }

                  // 2. Check Inline Image: ![caption](url)
                  const imgMatch = trimmed.match(/^!\[(.*?)\]\((.*?)\)$/);
                  if (imgMatch) {
                    const caption = imgMatch[1];
                    const imgUrl = imgMatch[2];

                    return (
                      <figure key={idx} className="my-8 rounded-3xl overflow-hidden border border-[#004b36]/20 bg-white shadow-md">
                        <div className="relative h-[300px] sm:h-[450px] w-full bg-slate-900">
                          <Image
                            src={imgUrl}
                            alt={caption || "Gambar Ilustrasi Artikel"}
                            fill
                            className="object-cover"
                          />
                        </div>
                        {caption && (
                          <figcaption className="p-4 sm:p-5 bg-[#f8f6f0] border-t border-outline/10 text-body-md font-semibold text-[#004b36] flex items-center gap-2.5 leading-snug">
                            <span className="material-symbols-outlined text-[20px] text-[#004b36] shrink-0">photo_camera</span>
                            <span>{caption}</span>
                          </figcaption>
                        )}
                      </figure>
                    );
                  }

                  // 3. Regular Paragraph text (support multiple lines inside paragraph)
                  return (
                    <p key={idx} className="text-[17px] sm:text-[18px] text-on-surface leading-[1.8] tracking-normal font-normal">
                      {paragraph}
                    </p>
                  );
                })
              ) : (
                <p className="text-body-md text-on-surface-variant italic">Belum ada isi artikel.</p>
              )}
            </div>
          </div>
        )}
      </article>

    </div>
  );
}
