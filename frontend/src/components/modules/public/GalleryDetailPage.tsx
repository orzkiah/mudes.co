"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import Image from "next/image";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import type { Gallery } from "@/types/models";

interface GalleryDetailPageProps {
  id: string;
}

export function GalleryDetailPage({ id }: GalleryDetailPageProps) {
  const [activeMediaUrl, setActiveMediaUrl] = useState<string | null>(null);

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape") setActiveMediaUrl(null);
    };
    if (activeMediaUrl) {
      window.addEventListener("keydown", handleKeyDown);
    }
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [activeMediaUrl]);

  const { data: gallery, isLoading, error } = useQuery<Gallery>({
    queryKey: ["public", "galleries", id],
    queryFn: async () => {
      const res = await api.get(`/public/galleries/${id}`);
      return res.data.data;
    },
    staleTime: 5 * 1000,
  });

  const isVideoUrl = (url: string | undefined | null) => {
    if (!url) return false;
    return Boolean(url.match(/\.(mp4|webm|mov|m4v)(\?.*)?$/i));
  };

  return (
    <div className="min-h-screen bg-light-motif text-on-surface font-body-md">
      
      {/* ── Back Navigation ── */}
      <div className="max-w-container-max mx-auto px-6 pt-10 md:px-10">
        <Link
          href="/gallery"
          className="inline-flex items-center gap-2 text-body-sm font-bold text-[#004b36] hover:underline"
        >
          <span className="material-symbols-outlined text-[18px]">arrow_back</span>
          Kembali ke Daftar Galeri
        </Link>
      </div>

      {/* ── Main Gallery Detail ── */}
      <div className="max-w-container-max mx-auto px-6 py-10 md:px-10">
        {isLoading ? (
          <div className="space-y-6 animate-pulse">
            <div className="h-6 w-32 bg-primary/10 rounded-full" />
            <div className="h-10 w-full bg-primary/10 rounded-xl" />
            <div className="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
              {Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="h-64 rounded-2xl bg-primary/10" />
              ))}
            </div>
          </div>
        ) : error || !gallery ? (
          <div className="py-20 text-center space-y-4">
            <span className="material-symbols-outlined text-[64px] text-outline">photo_library</span>
            <h1 className="text-[28px] font-bold text-[#004b36] font-headline-sm">Album Tidak Ditemukan</h1>
            <p className="text-body-md text-on-surface-variant max-w-md mx-auto">
              Album foto/video yang Anda cari tidak tersedia atau telah dihapus.
            </p>
            <div className="pt-4">
              <Link
                href="/gallery"
                className="inline-flex items-center gap-2 rounded-full bg-[#004b36] px-6 py-2.5 text-label-md font-semibold text-white hover:bg-emerald-950"
              >
                Lihat Album Lainnya
              </Link>
            </div>
          </div>
        ) : (
          <div className="space-y-8 animate-in fade-in duration-500">
            {/* Header info */}
            <div className="space-y-3">
              <span className="rounded-full bg-gradient-to-r from-[#004b36] to-[#006046] px-4 py-1 text-label-sm font-bold text-amber-300 uppercase tracking-wider shadow-2xs">
                {gallery.category?.name ?? "ALBUM DOKUMENTASI"}
              </span>
              <h1 className="text-[36px] sm:text-[44px] font-bold leading-tight text-[#004b36] font-headline-sm">
                {gallery.title}
              </h1>
              {gallery.description && (
                <p className="text-body-lg text-on-surface-variant max-w-3xl leading-relaxed">
                  {gallery.description}
                </p>
              )}
            </div>

            {/* Photos & Videos Grid */}
            {gallery.photos && gallery.photos.length > 0 ? (
              <div className="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                {gallery.photos.map((photo) => {
                  const mediaUrl = photo.url;
                  if (!mediaUrl) return null;
                  const isVideo = isVideoUrl(mediaUrl) || photo.media?.mimeType?.startsWith("video/");

                  return (
                    <div
                      key={photo.id}
                      onClick={() => setActiveMediaUrl(mediaUrl)}
                      className="group relative h-64 w-full rounded-2xl overflow-hidden cursor-pointer bg-slate-900 shadow-2xs border border-outline/10"
                    >
                      {isVideo ? (
                        <div className="relative w-full h-full">
                          <video
                            src={mediaUrl}
                            muted
                            loop
                            playsInline
                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                          />
                          <div className="absolute inset-0 bg-black/30 flex items-center justify-center">
                            <span className="h-12 w-12 rounded-full bg-amber-400 text-[#003828] flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                              <span className="material-symbols-outlined text-[28px] pl-0.5">play_arrow</span>
                            </span>
                          </div>
                        </div>
                      ) : (
                        <Image
                          src={mediaUrl}
                          alt={photo.caption || gallery.title}
                          fill
                          className="object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                      )}

                      {photo.caption && (
                        <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                          <p className="text-body-sm text-white line-clamp-2">{photo.caption}</p>
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>
            ) : gallery.coverPhoto?.url ? (
              <div className="relative h-[400px] w-full rounded-2xl overflow-hidden bg-primary/10 border border-amber-400/30">
                <Image
                  src={gallery.coverPhoto.url}
                  alt={gallery.title}
                  fill
                  className="object-cover"
                />
              </div>
            ) : (
              <div className="rounded-2xl bg-white p-12 text-center text-on-surface-variant border border-outline/10">
                <p className="text-body-md font-semibold text-[#004b36]">Belum ada foto atau video yang diunggah ke album ini.</p>
              </div>
            )}
          </div>
        )}
      </div>

      {/* Lightbox / Video Player Modal Overlay */}
      {activeMediaUrl && (
        <div
          onClick={() => setActiveMediaUrl(null)}
          className="fixed inset-0 z-50 bg-black/95 backdrop-blur-md flex items-center justify-center p-4 cursor-zoom-out animate-in fade-in duration-200"
        >
          <button
            onClick={() => setActiveMediaUrl(null)}
            className="absolute top-6 right-6 text-white text-3xl hover:opacity-80 z-50"
          >
            <span className="material-symbols-outlined text-[36px]">close</span>
          </button>
          <div className="relative max-w-5xl max-h-[85vh] w-full h-full flex items-center justify-center" onClick={(e) => e.stopPropagation()}>
            {isVideoUrl(activeMediaUrl) ? (
              <video
                src={activeMediaUrl}
                controls
                autoPlay
                className="max-h-full max-w-full rounded-xl shadow-2xl"
              />
            ) : (
              <img
                src={activeMediaUrl}
                alt="Preview foto galeri"
                className="max-h-full max-w-full rounded-xl object-contain shadow-2xl"
              />
            )}
          </div>
        </div>
      )}

    </div>
  );
}
