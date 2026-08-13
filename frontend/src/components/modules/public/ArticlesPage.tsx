"use client";

import { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { formatDate } from "@/lib/dates";
import type { Article, ArticleCategory } from "@/types/models";

export function ArticlesPage() {
  const [activeCategory, setActiveCategory] = useState<string>("all");
  const [search, setSearch] = useState<string>("");

  const { data: categories } = useQuery<ArticleCategory[]>({
    queryKey: ["public", "article-categories"],
    queryFn: async () => {
      const res = await api.get("/public/article-categories");
      return res.data.data;
    },
    staleTime: 10 * 60 * 1000,
  });

  const { data: articles, isLoading, error } = useQuery<Article[]>({
    queryKey: ["public", "articles", activeCategory, search],
    queryFn: async () => {
      const params: Record<string, string> = { perPage: "20" };
      if (activeCategory !== "all") params["filter[article_category_id]"] = activeCategory;
      if (search) params["search"] = search;
      const res = await api.get("/public/articles", { params });
      return res.data.data;
    },
    staleTime: 5 * 60 * 1000,
  });

  return (
    <div className="min-h-screen bg-[#f5f0e8] text-on-surface font-body-md">
      
      {/* ── Page Header ── */}
      <section className="bg-white border-b border-outline/10 px-6 py-12 md:px-10">
        <div className="mx-auto max-w-container-max">
          <span className="text-label-sm uppercase tracking-[0.2em] font-semibold text-[#004b36]">
            Artikel
          </span>
          <h1 className="mt-3 text-[38px] font-bold leading-tight text-[#004b36] font-headline-sm">
            Kabar Komunitas Mudes Condet
          </h1>
          <p className="mt-4 max-w-2xl text-body-lg text-on-surface-variant">
            Artikel, rilis pers, dan catatan seputar pergerakan pemuda, kajian keagamaan, serta Artikel warga Desa Condet.
          </p>

          {/* Search Input */}
          <div className="mt-8 relative max-w-xl">
            <span className="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant text-[20px]">
              search
            </span>
            <input
              type="text"
              placeholder="Cari artikel..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full rounded-full border border-outline/20 bg-[#f5f0e8] py-3 pl-11 pr-4 text-body-md text-on-surface placeholder:text-on-surface-variant/60 focus:outline-none focus:border-[#004b36] focus:ring-2 focus:ring-[#004b36]/10"
            />
          </div>
        </div>
      </section>

      {/* ── Category Filter & Articles Grid ── */}
      <div className="mx-auto max-w-container-max px-6 py-12 md:px-10">
        
        {/* Category Pills */}
        {categories && categories.length > 0 && (
          <div className="flex flex-wrap items-center gap-2 mb-10">
            <button
              onClick={() => setActiveCategory("all")}
              className={`rounded-full px-5 py-2 text-label-md font-semibold transition-colors ${
                activeCategory === "all"
                  ? "bg-[#004b36] text-white"
                  : "bg-white text-on-surface-variant hover:bg-white/80"
              }`}
            >
              Semua Artikel
            </button>
            {categories.map((cat) => (
              <button
                key={cat.id}
                onClick={() => setActiveCategory(cat.id)}
                className={`rounded-full px-5 py-2 text-label-md font-semibold transition-colors ${
                  activeCategory === cat.id
                    ? "bg-[#004b36] text-white"
                    : "bg-white text-on-surface-variant hover:bg-white/80"
                }`}
              >
                {cat.name}
              </button>
            ))}
          </div>
        )}

        {/* Loading State */}
        {isLoading ? (
          <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: 6 }).map((_, i) => (
              <div key={i} className="h-[320px] rounded-2xl bg-primary/10 animate-pulse" />
            ))}
          </div>
        ) : error ? (
          <div className="rounded-2xl bg-white p-12 text-center text-on-surface-variant">
            <span className="material-symbols-outlined text-[48px] text-outline">error</span>
            <p className="mt-2 text-body-md">Gagal memuat artikel. Silakan coba lagi nanti.</p>
          </div>
        ) : !articles?.length ? (
          <div className="rounded-2xl bg-white p-12 text-center text-on-surface-variant">
            <span className="material-symbols-outlined text-[48px] text-outline">article</span>
            <p className="mt-2 text-body-md">Belum ada artikel yang dipublikasikan.</p>
          </div>
        ) : (
          <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {articles.map((art) => (
              <Link
                key={art.id}
                href={`/articles/${art.slug}`}
                className="group block space-y-3"
              >
                <div className="relative h-[220px] w-full rounded-2xl overflow-hidden bg-primary/10">
                  {art.cover?.url ? (
                    <Image
                      src={art.cover.url}
                      alt={art.title}
                      fill
                      className="object-cover group-hover:scale-103 transition-transform duration-500"
                    />
                  ) : (
                    <div className="flex h-full items-center justify-center bg-[#004b36]/10 text-[#004b36]">
                      <span className="material-symbols-outlined text-[48px]">article</span>
                    </div>
                  )}
                  <span className="absolute top-3 left-3 rounded-full bg-[#004b36] px-3.5 py-1 text-label-sm font-semibold text-white">
                    {art.category?.name ?? "Artikel"}
                  </span>
                </div>

                <div className="space-y-1.5">
                  <span className="text-label-sm text-on-surface-variant font-medium">
                    {art.publishedAt ? formatDate(art.publishedAt) : "Baru Diterbitkan"}
                  </span>
                  <h3 className="text-body-lg font-bold text-on-surface line-clamp-2 leading-snug group-hover:text-[#004b36] transition-colors">
                    {art.title}
                  </h3>
                  {art.excerpt && (
                    <p className="text-body-sm text-on-surface-variant line-clamp-2 leading-relaxed">
                      {art.excerpt}
                    </p>
                  )}
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>

    </div>
  );
}
