"use client";

import Link from "next/link";
import Image from "next/image";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import type { Gallery } from "@/types/models";

function getMediaCounts(gallery: Gallery) {
  if (!gallery.photos || gallery.photos.length === 0) {
    return { photos: gallery.photoCount ?? 0, videos: 0 };
  }

  let photoCount = 0;
  let videoCount = 0;

  for (const item of gallery.photos) {
    const url = item.url || item.media?.url || "";
    const mime = item.media?.mimeType || "";
    if (mime.startsWith("video/") || url.match(/\.(mp4|webm|mov|m4v)(\?.*)?$/i)) {
      videoCount++;
    } else {
      photoCount++;
    }
  }

  return { photos: photoCount, videos: videoCount };
}

export function GalleryListPage() {
  const { data: galleries, isLoading, error } = useQuery<Gallery[]>({
    queryKey: ["public", "galleries"],
    queryFn: async () => {
      const res = await api.get("/public/galleries");
      return res.data.data;
    },
    staleTime: 5 * 1000,
  });

  return (
    <div className="min-h-screen bg-light-motif text-on-surface font-body-md">
      
      {/* ── Page Header ── */}
      <section className="bg-islamic-pattern text-white border-b-4 border-amber-400/40 px-6 py-12 md:px-10 shadow-sm relative overflow-hidden">
        <div className="mx-auto max-w-container-max relative z-10">
          <span className="inline-flex items-center gap-1.5 rounded-full bg-amber-400/20 border border-amber-400/40 px-3.5 py-1 text-label-sm uppercase tracking-[0.2em] font-bold text-amber-300">
            <span className="material-symbols-outlined text-[14px]">photo_library</span>
            Dokumentasi Visual
          </span>
          <h1 className="mt-3 text-[38px] md:text-[46px] font-bold leading-tight text-white font-headline-sm">
            Galeri Komunitas Mudes Condet
          </h1>
          <p className="mt-3 max-w-2xl text-body-lg text-white/90 leading-relaxed font-body-md">
            Kumpulan momen kebersamaan, aksi sosial, kajian, serta dokumentasi foto dan video pemuda-pemudi Desa Condet.
          </p>
        </div>
      </section>

      {/* ── Gallery List Grid ── */}
      <div className="mx-auto max-w-container-max px-6 py-12 md:px-10">
        {isLoading ? (
          <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: 6 }).map((_, i) => (
              <div key={i} className="h-[280px] rounded-3xl bg-primary/10 animate-pulse" />
            ))}
          </div>
        ) : error ? (
          <div className="rounded-3xl bg-white p-12 text-center text-on-surface-variant border border-outline/10 shadow-2xs">
            <span className="material-symbols-outlined text-[48px] text-outline">error</span>
            <p className="mt-2 text-body-md font-semibold text-[#004b36]">Gagal memuat album galeri. Silakan coba lagi.</p>
          </div>
        ) : !galleries?.length ? (
          <div className="rounded-3xl bg-white p-12 text-center text-on-surface-variant border border-outline/10 shadow-2xs">
            <span className="material-symbols-outlined text-[48px] text-outline">photo_library</span>
            <p className="mt-2 text-body-md font-semibold text-[#004b36]">Belum ada album galeri yang tersedia saat ini.</p>
          </div>
        ) : (
          <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {galleries.map((gallery) => {
              const { photos, videos } = getMediaCounts(gallery);

              return (
                <Link
                  key={gallery.id}
                  href={`/gallery/${gallery.id}`}
                  className="group block space-y-3.5"
                >
                  <div className="relative h-[250px] w-full rounded-3xl overflow-hidden bg-slate-900 border border-amber-400/30 shadow-md">
                    {gallery.coverPhoto?.url ? (
                      <Image
                        src={gallery.coverPhoto.url}
                        alt={gallery.title}
                        fill
                        className="object-cover group-hover:scale-105 transition-transform duration-500"
                      />
                    ) : (
                      <div className="flex h-full items-center justify-center bg-[#004b36]/20 text-amber-300">
                        <span className="material-symbols-outlined text-[56px]">photo_library</span>
                      </div>
                    )}

                    {/* Gradient Overlay */}
                    <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-80" />

                    {/* Badge: Foto & Video Counts */}
                    <span className="absolute bottom-3 right-3 rounded-full bg-[#002d20]/85 border border-amber-400/50 backdrop-blur-md px-3.5 py-1 text-label-sm font-bold text-white flex items-center gap-2 shadow-md">
                      {photos > 0 && (
                        <span className="flex items-center gap-1.5 text-white">
                          <span className="material-symbols-outlined text-[15px] text-amber-400">photo_camera</span>
                          {photos} Foto
                        </span>
                      )}
                      {photos > 0 && videos > 0 && <span className="text-amber-400/60">•</span>}
                      {videos > 0 && (
                        <span className="flex items-center gap-1.5 text-amber-300">
                          <span className="material-symbols-outlined text-[15px] text-amber-400">videocam</span>
                          {videos} Video
                        </span>
                      )}
                      {photos === 0 && videos === 0 && (
                        <span className="flex items-center gap-1">
                          <span className="material-symbols-outlined text-[15px]">photo_camera</span>
                          0 Media
                        </span>
                      )}
                    </span>
                  </div>

                  <div className="space-y-1.5 px-1">
                    <span className="text-label-sm font-bold text-amber-600 uppercase tracking-wider">
                      {gallery.category?.name ?? "ALBUM"}
                    </span>
                    <h3 className="text-body-lg font-bold text-[#003828] line-clamp-2 leading-snug group-hover:text-amber-600 transition-colors font-headline-sm">
                      {gallery.title}
                    </h3>
                    {gallery.description && (
                      <p className="text-body-sm text-on-surface-variant line-clamp-2 leading-relaxed">
                        {gallery.description}
                      </p>
                    )}
                  </div>
                </Link>
              );
            })}
          </div>
        )}
      </div>

    </div>
  );
}
