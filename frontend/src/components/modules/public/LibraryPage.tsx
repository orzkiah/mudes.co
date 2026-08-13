"use client";

import { useState } from "react";
import Image from "next/image";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { Alert } from "@/components/ui/Alert";
import { Skeleton } from "@/components/ui/Skeleton";
import type { LibraryDocument, LibraryCategory } from "@/types/models";

const TYPE_ICONS: Record<string, string> = {
  pdf: "picture_as_pdf",
  audio: "headphones",
  video_link: "play_circle",
};

const TYPE_LABELS: Record<string, string> = {
  pdf: "Dokumen PDF",
  audio: "Audio Kajian",
  video_link: "Video Tutorial",
};

const TYPE_COLORS: Record<string, string> = {
  pdf: "bg-red-50 text-red-600 border-red-200",
  audio: "bg-purple-50 text-purple-600 border-purple-200",
  video_link: "bg-blue-50 text-blue-600 border-blue-200",
};

const COVER_IMAGES = [
  "/images/library-cover.jpg",
  "/images/book-cover.jpg",
  "/images/condet-mural.jpg",
];

function getCoverImage(doc: LibraryDocument, index: number): string {
  if (
    doc.file?.url &&
    (doc.file.url.endsWith(".jpg") ||
      doc.file.url.endsWith(".jpeg") ||
      doc.file.url.endsWith(".png") ||
      doc.file.url.endsWith(".webp"))
  ) {
    return doc.file.url;
  }
  return COVER_IMAGES[index % COVER_IMAGES.length];
}

export function LibraryPage() {
  const [activeCategory, setActiveCategory] = useState<string>("all");
  const [search, setSearch] = useState("");

  const { data: categories } = useQuery<LibraryCategory[]>({
    queryKey: ["public", "library-categories"],
    queryFn: async () => {
      const res = await api.get("/public/library-categories");
      return res.data.data;
    },
    staleTime: 10 * 60 * 1000,
  });

  const { data: documents, isLoading, error } = useQuery<LibraryDocument[]>({
    queryKey: ["public", "library", activeCategory, search],
    queryFn: async () => {
      const params: Record<string, string> = { perPage: "20" };
      if (activeCategory !== "all") params["filter[library_category_id]"] = activeCategory;
      if (search) params["search"] = search;
      const res = await api.get("/public/library", { params });
      return res.data.data;
    },
    staleTime: 5 * 60 * 1000,
  });

  return (
    <div className="min-h-screen bg-[#f5f0e8]">
      {/* Header Banner Showcase */}
      <section className="bg-white border-b border-outline/10 px-6 py-10 md:px-10">
        <div className="mx-auto max-w-container-max">
          <div className="relative overflow-hidden rounded-3xl bg-[#004b36] p-8 md:p-12 text-white shadow-md">
            {/* Background Image Overlay */}
            <div className="absolute inset-0 z-0 opacity-35 mix-blend-overlay">
              <Image
                src="/images/library-cover.jpg"
                alt="Pustaka Digital Header"
                fill
                className="object-cover"
                priority
              />
            </div>
            <div className="absolute inset-0 bg-gradient-to-r from-[#004b36] via-[#004b36]/90 to-transparent z-0" />

            <div className="relative z-10 max-w-2xl">
              <span className="inline-flex items-center gap-1.5 rounded-full bg-[#f9c74f]/20 border border-[#f9c74f]/40 px-3.5 py-1 text-label-sm font-semibold text-[#f9c74f]">
                <span className="material-symbols-outlined text-[14px]">local_library</span>
                Pustaka Digital Mudes Condet
              </span>
              <h1 className="mt-4 text-[34px] md:text-[42px] font-bold leading-tight font-headline-sm text-white">
                Koleksi Materi, E-Book & Audio Pembelajaran Pemuda
              </h1>
              <p className="mt-3 text-body-lg text-white/80 leading-relaxed font-body-md">
                Akses perpustakaan digital resmi Desa Condet. Unduh modul keislaman, rekaman kajian rutin, serta referensi organisasi secara gratis 24/7.
              </p>

              {/* Search bar inside header */}
              <div className="mt-6 relative max-w-xl">
                <span className="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant text-[20px]">
                  search
                </span>
                <input
                  type="text"
                  placeholder="Cari e-book, materi kajian, dokumen..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="w-full rounded-full border border-white/20 bg-white/95 py-3.5 pl-11 pr-4 text-body-md text-on-surface placeholder:text-on-surface-variant/60 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#f9c74f] shadow-sm"
                />
              </div>
            </div>
          </div>
        </div>
      </section>

      <div className="mx-auto max-w-container-max px-6 py-10 md:px-10">
        <div className="grid gap-8 lg:grid-cols-[260px_1fr]">

          {/* Sidebar Filters */}
          <aside className="space-y-5">
            <div className="rounded-2xl bg-white p-5 shadow-2xs">
              <h2 className="text-body-md font-bold text-[#004b36] mb-4 flex items-center gap-2">
                <span className="material-symbols-outlined text-[18px]">folder_open</span>
                Kategori Materi
              </h2>
              <div className="space-y-1.5">
                <button
                  onClick={() => setActiveCategory("all")}
                  className={`flex w-full items-center justify-between rounded-xl px-4 py-3 text-left text-body-md transition-colors ${
                    activeCategory === "all"
                      ? "bg-[#004b36] text-white font-semibold"
                      : "hover:bg-[#f5f0e8] text-on-surface-variant"
                  }`}
                >
                  <span>Semua Berkas</span>
                  <span className="material-symbols-outlined text-[18px]">chevron_right</span>
                </button>
                {categories?.map((cat) => (
                  <button
                    key={cat.id}
                    onClick={() => setActiveCategory(cat.id)}
                    className={`flex w-full items-center justify-between rounded-xl px-4 py-3 text-left text-body-md transition-colors ${
                      activeCategory === cat.id
                        ? "bg-[#004b36] text-white font-semibold"
                        : "hover:bg-[#f5f0e8] text-on-surface-variant"
                    }`}
                  >
                    <span>{cat.name}</span>
                    <span className="text-label-sm opacity-60">{cat.documentCount}</span>
                  </button>
                ))}
              </div>
            </div>

            {/* File types filter */}
            <div className="rounded-2xl bg-white p-5 shadow-2xs">
              <h2 className="text-body-md font-bold text-[#004b36] mb-3 flex items-center gap-2">
                <span className="material-symbols-outlined text-[18px]">extension</span>
                Tipe Berkas
              </h2>
              <div className="flex flex-wrap gap-2">
                {Object.entries(TYPE_LABELS).map(([type, label]) => (
                  <span
                    key={type}
                    className={`flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-label-sm font-medium ${
                      TYPE_COLORS[type] ?? "bg-surface-container text-on-surface"
                    }`}
                  >
                    <span className="material-symbols-outlined text-[14px]">{TYPE_ICONS[type]}</span>
                    {label}
                  </span>
                ))}
              </div>
            </div>
          </aside>

          {/* Document Cards Grid */}
          <div>
            {isLoading ? (
              <div className="grid gap-6 sm:grid-cols-2">
                {Array.from({ length: 4 }).map((_, i) => (
                  <Skeleton key={i} className="h-72 rounded-2xl" />
                ))}
              </div>
            ) : error ? (
              <Alert variant="error">Gagal memuat perpustakaan. Coba muat ulang halaman.</Alert>
            ) : !documents?.length ? (
              <div className="rounded-2xl bg-white p-12 text-center text-on-surface-variant shadow-2xs">
                <span className="material-symbols-outlined text-[48px] text-outline">menu_book</span>
                <p className="mt-2 text-body-md font-medium">
                  {search ? `Tidak ada hasil untuk "${search}"` : "Belum ada dokumen tersedia."}
                </p>
              </div>
            ) : (
              <div className="grid gap-6 sm:grid-cols-2">
                {documents.map((doc, index) => {
                  const coverImage = getCoverImage(doc, index);
                  return (
                    <div
                      key={doc.id}
                      className="group rounded-2xl bg-white overflow-hidden shadow-2xs hover:shadow-md transition-all duration-300 flex flex-col border border-outline/10"
                    >
                      {/* Visual Cover Media Banner */}
                      <div className="relative h-44 w-full bg-slate-100 overflow-hidden">
                        <Image
                          src={coverImage}
                          alt={doc.title}
                          fill
                          className="object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />

                        {/* Top Badges */}
                        <div className="absolute top-3 left-3 right-3 flex items-center justify-between">
                          <span
                            className={`flex items-center gap-1 rounded-full border px-2.5 py-1 text-label-sm font-semibold backdrop-blur-md ${
                              TYPE_COLORS[doc.libraryType] ?? "bg-white/90 text-on-surface"
                            }`}
                          >
                            <span className="material-symbols-outlined text-[13px]">
                              {TYPE_ICONS[doc.libraryType]}
                            </span>
                            {TYPE_LABELS[doc.libraryType] ?? "File"}
                          </span>
                          <span className="rounded-full bg-black/50 backdrop-blur-md px-2.5 py-1 text-label-sm text-white/90 font-medium">
                            {doc.category?.name ?? "Umum"}
                          </span>
                        </div>

                        {/* Bottom overlay title info */}
                        <div className="absolute bottom-3 left-3 right-3 text-white">
                          <span className="text-[11px] uppercase tracking-wider text-white/80 font-semibold">
                            {doc.downloadCount > 0 ? `${doc.downloadCount}x diunduh` : "Akses Publik"}
                          </span>
                        </div>
                      </div>

                      {/* Content Card Body */}
                      <div className="p-5 flex-1 flex flex-col justify-between">
                        <div>
                          <h3 className="text-body-md font-bold text-[#004b36] group-hover:text-emerald-700 transition-colors line-clamp-2 leading-snug">
                            {doc.title}
                          </h3>
                          {doc.description && (
                            <p className="mt-2 text-body-sm text-on-surface-variant line-clamp-2 leading-relaxed">
                              {doc.description}
                            </p>
                          )}
                        </div>

                        {/* Card Footer Action */}
                        <div className="mt-5 pt-4 border-t border-outline/10 flex items-center justify-between">
                          {doc.externalUrl ? (
                            <a
                              href={doc.externalUrl}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="inline-flex items-center gap-1.5 rounded-full bg-[#004b36]/10 px-4 py-2 text-body-sm font-semibold text-[#004b36] hover:bg-[#004b36] hover:text-white transition-colors"
                            >
                              <span className="material-symbols-outlined text-[16px]">open_in_new</span>
                              Buka Link External
                            </a>
                          ) : doc.file?.url ? (
                            <a
                              href={doc.file.url}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="inline-flex items-center gap-1.5 rounded-full bg-[#004b36] px-4 py-2 text-body-sm font-semibold text-white hover:bg-emerald-950 transition-colors shadow-2xs"
                            >
                              <span className="material-symbols-outlined text-[16px]">download</span>
                              Unduh Materi
                            </a>
                          ) : (
                            <span className="text-body-sm text-on-surface-variant italic">
                              Dokumen belum diunggah
                            </span>
                          )}
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
