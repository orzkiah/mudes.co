"use client";

import Link from "next/link";
import Image from "next/image";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { formatDate } from "@/lib/dates";
import type { Activity } from "@/types/models";

export function ActivitiesPage() {
  const { data: activities, isLoading, error } = useQuery<Activity[]>({
    queryKey: ["public", "activities"],
    queryFn: async () => {
      const res = await api.get("/public/activities");
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
            Agenda Komunitas
          </span>
          <h1 className="mt-3 text-[38px] font-bold leading-tight text-[#004b36] font-headline-sm">
            Kegiatan Pemuda Mudes Condet
          </h1>
          <p className="mt-4 max-w-2xl text-body-lg text-on-surface-variant">
            Ikuti berbagai kegiatan sosial, keagamaan, olahraga, dan pengembangan potensi pemuda-pemudi di Desa Condet.
          </p>
        </div>
      </section>

      {/* ── Activities List ── */}
      <div className="mx-auto max-w-container-max px-6 py-12 md:px-10">
        {isLoading ? (
          <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: 6 }).map((_, i) => (
              <div key={i} className="h-[320px] rounded-2xl bg-primary/10 animate-pulse" />
            ))}
          </div>
        ) : error ? (
          <div className="rounded-2xl bg-white p-12 text-center text-on-surface-variant">
            <span className="material-symbols-outlined text-[48px] text-outline">error</span>
            <p className="mt-2 text-body-md">Gagal memuat agenda kegiatan. Silakan coba lagi.</p>
          </div>
        ) : !activities?.length ? (
          <div className="rounded-2xl bg-white p-12 text-center text-on-surface-variant">
            <span className="material-symbols-outlined text-[48px] text-outline">event</span>
            <p className="mt-2 text-body-md">Belum ada agenda kegiatan yang terdaftar saat ini.</p>
          </div>
        ) : (
          <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {activities.map((act) => (
              <Link
                key={act.id}
                href={`/activities/${act.slug}`}
                className="group block space-y-3"
              >
                <div className="relative h-[220px] w-full rounded-2xl overflow-hidden bg-primary/10">
                  {act.cover?.url ? (
                    <Image
                      src={act.cover.url}
                      alt={act.title}
                      fill
                      className="object-cover group-hover:scale-103 transition-transform duration-500"
                    />
                  ) : (
                    <div className="flex h-full items-center justify-center bg-[#004b36]/10 text-[#004b36]">
                      <span className="material-symbols-outlined text-[48px]">event</span>
                    </div>
                  )}
                  <span className="absolute top-3 left-3 rounded-full bg-[#004b36] px-3.5 py-1 text-label-sm font-semibold text-white">
                    {act.category?.name ?? "KEGIATAN"}
                  </span>
                </div>

                <div className="space-y-1.5">
                  <div className="flex items-center gap-2 text-label-sm text-on-surface-variant font-medium">
                    <span>{formatDate(act.startAt)}</span>
                    {act.location && (
                      <>
                        <span>•</span>
                        <span className="truncate">{act.location}</span>
                      </>
                    )}
                  </div>

                  <h3 className="text-body-lg font-bold text-on-surface line-clamp-2 leading-snug group-hover:text-[#004b36] transition-colors">
                    {act.title}
                  </h3>

                  {act.description && (
                    <p className="text-body-sm text-on-surface-variant line-clamp-2 leading-relaxed">
                      {act.description}
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
