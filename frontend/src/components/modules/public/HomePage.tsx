"use client";

import Link from "next/link";
import Image from "next/image";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { formatDate } from "@/lib/dates";
import type { Article, Activity, Announcement } from "@/types/models";
import { HeroActivityStack } from "./HeroActivityStack";
import { WeeklyScheduleSection } from "./WeeklyScheduleSection";
import { PhilosophyBentoSection } from "./PhilosophyBentoSection";

export function HomePage() {

  // ─── API Data Fetching ───────────────────────────────────────────────────

  const { data: announcements } = useQuery<Announcement[]>({
    queryKey: ["public", "announcements"],
    queryFn: async () => {
      const res = await api.get("/public/announcements");
      return res.data.data;
    },
    staleTime: 5 * 60 * 1000,
  });

  const { data: activities, isLoading: loadingActivities } = useQuery<Activity[]>({
    queryKey: ["public", "activities", 4],
    queryFn: async () => {
      const res = await api.get("/public/activities", { params: { perPage: 4 } });
      return res.data.data;
    },
    staleTime: 10 * 1000,
  });

  const { data: articles, isLoading: loadingArticles } = useQuery<Article[]>({
    queryKey: ["public", "articles", 3],
    queryFn: async () => {
      const res = await api.get("/public/articles", { params: { perPage: 3 } });
      return res.data.data;
    },
    staleTime: 5 * 60 * 1000,
  });

  // Sort activities for hero stack (upcoming first)
  const sortedActivities = activities
    ? [...activities].sort((a, b) => {
        const dateA = new Date(a.startAt).getTime();
        const dateB = new Date(b.startAt).getTime();
        const now = Date.now();

        const isUpcomingA = dateA >= now;
        const isUpcomingB = dateB >= now;

        if (isUpcomingA && isUpcomingB) return dateA - dateB;
        if (isUpcomingA && !isUpcomingB) return -1;
        if (!isUpcomingA && isUpcomingB) return 1;
        return dateB - dateA;
      })
    : [];

  const featuredActivity = sortedActivities[0];
  const secondaryActivities = sortedActivities.slice(1, 4);

  return (
    <div className="text-on-surface font-body-md selection:bg-amber-200 selection:text-emerald-950">

      {/* ── 1. HERO SECTION (Deep Emerald & Royal Gold Gradient with Islamic Pattern) ── */}
      <section className="relative bg-islamic-pattern text-white border-b-4 border-amber-400/50 overflow-hidden">
        {/* Glow Light Ambient Effects */}
        <div className="absolute top-0 right-1/4 w-96 h-96 bg-emerald-500/25 rounded-full blur-3xl pointer-events-none" />
        <div className="absolute bottom-0 left-10 w-80 h-80 bg-amber-500/20 rounded-full blur-3xl pointer-events-none" />

        <div className="relative z-10 pt-12 pb-16 md:pt-20 md:pb-24 px-6 md:px-10 max-w-container-max mx-auto">
          <div className="grid gap-10 lg:grid-cols-[1.1fr_1fr] lg:items-center">

            {/* Left Column — Large Gold Accent Typography */}
            <div className="space-y-6 animate-in fade-in slide-in-from-bottom-3 duration-500">
              <span className="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-amber-400/25 to-amber-500/10 border border-amber-400/40 px-4 py-1.5 text-label-sm uppercase tracking-[0.2em] font-bold text-amber-300 shadow-xs">
                <span className="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping" />
                Pemuda Pemudi LDII Condet
              </span>

              <h1 className="text-[44px] sm:text-[56px] lg:text-[64px] font-bold leading-[1.05] tracking-tight text-white font-headline-sm drop-shadow-md">
                Bergerak.<br />
                Bersama.<br />
                <span className="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-amber-400 to-amber-200">Tumbuh Bersama.</span>
              </h1>

              <p className="text-body-lg text-white/90 max-w-xl leading-relaxed font-body-md drop-shadow-xs">
                <strong className="block text-amber-300 text-[20px] font-headline-sm mb-1.5">Tumbuh Bersama, Berkarya, dan Memberi Manfaat</strong>
                Menjadi generasi muda yang berilmu, berakhlak, mandiri, dan siap berkontribusi untuk agama, masyarakat, dan Indonesia.
              </p>

              <div className="pt-4 flex items-center gap-10 text-label-sm text-white/90">
                <div className="border-l-4 border-amber-400 pl-4 bg-white/5 py-2 px-3 rounded-r-xl backdrop-blur-xs">
                  <span className="block font-bold text-body-md text-amber-300 font-headline-sm">01 / Pemuda</span>
                  <span className="text-white/80 text-[13px]">Generasi Pembina</span>
                </div>
                <div className="border-l-4 border-amber-400 pl-4 bg-white/5 py-2 px-3 rounded-r-xl backdrop-blur-xs">
                  <span className="block font-bold text-body-md text-amber-300 font-headline-sm">02 / Muamala</span>
                  <span className="text-white/80 text-[13px]">Aksi &amp; Kemandirian</span>
                </div>
              </div>
            </div>

            {/* Right Column — Interactive 3D Card Stack */}
            <div className="relative animate-in fade-in slide-in-from-right-3 duration-700 pt-6">
              <HeroActivityStack activities={sortedActivities} />
            </div>

          </div>
        </div>
      </section>

      {/* ── 2. COMMUNITY MARQUEE TICKER (Papan Clip Design Matching Screenshot) ── */}
      {announcements && announcements.length > 0 && (
        <section className="bg-light-motif py-5 px-6 md:px-10 border-b border-amber-400/20">
          <div className="max-w-container-max mx-auto">
            <div className="relative rounded-[22px] bg-[#f3f1f7] border border-slate-300/80 shadow-sm overflow-hidden py-3.5 px-5">
              {/* Subtle dot matrix pattern background */}
              <div className="absolute inset-0 opacity-[0.12] pointer-events-none bg-[radial-gradient(#004b36_1px,transparent_1px)] [background-size:12px_12px]" />

              <div className="relative z-10 overflow-hidden whitespace-nowrap w-full flex items-center">
                <div className="inline-block animate-marquee space-x-16">
                  {announcements.map((a) => (
                    <span key={a.id} className="inline-flex items-center gap-3.5">
                      <span className="inline-flex items-center gap-1.5 rounded-full bg-[#004b36] px-4 py-1 text-label-sm font-black tracking-widest text-white shadow-xs uppercase shrink-0">
                        <span className="material-symbols-outlined text-[15px] text-amber-300">campaign</span>
                        PENGUMUMAN
                      </span>
                      <span className="text-[17px] sm:text-[19px] font-bold text-[#0c382b] tracking-tight">
                        {a.title ? `${a.title}: ` : ""}{a.body}
                      </span>
                    </span>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </section>
      )}

      {/* ── 3. FILOSOFI & QUOTES (Bento Grid Showcase) ── */}
      <PhilosophyBentoSection />

      {/* ── 4. KEGIATAN TERKINI (Midnight Sapphire to Deep Emerald Gradient Section) ── */}
      {((activities && activities.length > 0) || loadingActivities) && (
        <section className="py-20 px-6 md:px-10 bg-gradient-to-br from-[#071e16] via-[#003828] to-[#004b36] text-white border-b-4 border-amber-400/40 relative overflow-hidden">
          <div className="absolute bottom-0 right-0 w-96 h-96 bg-emerald-400/15 rounded-full blur-3xl pointer-events-none" />

          <div className="max-w-container-max mx-auto relative z-10">
            <div className="flex items-end justify-between mb-10 pb-4 border-b border-amber-400/30">
              <div>
                <span className="inline-flex items-center gap-1.5 rounded-full bg-amber-400/20 border border-amber-400/40 px-3.5 py-1 text-label-sm uppercase tracking-[0.2em] font-bold text-amber-300 mb-2">
                  <span className="material-symbols-outlined text-[14px]">newspaper</span>
                  Kabar Komunitas
                </span>
                <h2 className="text-[32px] sm:text-[40px] font-bold text-white font-headline-sm">
                  Kegiatan Terkini
                </h2>
              </div>
              <Link
                href="/activities"
                className="flex items-center gap-1.5 text-body-md font-bold text-amber-300 hover:text-white transition-colors"
              >
                Lihat semua agenda
                <span className="material-symbols-outlined text-[18px]">arrow_forward</span>
              </Link>
            </div>

            <div className="grid gap-10 lg:grid-cols-[1.4fr_1fr] lg:items-start">
              {/* Featured Activity Card */}
              {featuredActivity && (
                <Link
                  href={`/activities/${featuredActivity.slug}`}
                  className="block space-y-4 group cursor-pointer"
                >
                  <div className="relative h-[280px] sm:h-[360px] w-full rounded-3xl overflow-hidden border-2 border-amber-400/40 shadow-lg">
                    <Image
                      src={featuredActivity.cover?.url ?? "/images/condet-mural.jpg"}
                      alt={featuredActivity.title}
                      fill
                      className="object-cover group-hover:scale-105 transition-transform duration-500"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-[#002d20] via-transparent to-transparent opacity-80" />
                    <span className="absolute top-4 left-4 rounded-full bg-[#004b36] border border-amber-400/60 px-4 py-1 text-label-sm font-bold text-amber-300 shadow-md">
                      {featuredActivity.category?.name ?? "KEGIATAN"}
                    </span>
                  </div>

                  <div className="space-y-2 pt-1">
                    <div className="flex items-center gap-3 text-label-sm text-white/80 font-medium">
                      <span>{formatDate(featuredActivity.startAt)}</span>
                      {featuredActivity.location && (
                        <>
                          <span>•</span>
                          <span className="flex items-center gap-1">
                            <span className="material-symbols-outlined text-[14px] text-amber-400">location_on</span>
                            {featuredActivity.location}
                          </span>
                        </>
                      )}
                    </div>

                    <h3 className="text-[24px] sm:text-[28px] font-bold text-white font-headline-sm leading-tight group-hover:text-amber-300 transition-colors">
                      {featuredActivity.title}
                    </h3>
                  </div>
                </Link>
              )}

              {/* Secondary Activity List */}
              <div className="space-y-6">
                {secondaryActivities.map((act, index) => (
                  <Link
                    key={act.id}
                    href={`/activities/${act.slug}`}
                    className="block group p-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 hover:border-amber-400/60 transition-all shadow-sm hover:bg-white/15"
                  >
                    <div className="flex gap-4 items-start">
                      <div className="relative h-24 w-28 shrink-0 rounded-xl overflow-hidden border border-amber-400/30">
                        <Image
                          src={
                            act.cover?.url ??
                            (index % 2 === 0 ? "/images/library-cover.jpg" : "/images/book-cover.jpg")
                          }
                          alt={act.title}
                          fill
                          className="object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                      </div>
                      <div className="flex-1 min-w-0 space-y-1">
                        <span className="text-label-sm font-bold text-amber-300">
                          {act.category?.name ?? "KEGIATAN"} · {formatDate(act.startAt)}
                        </span>
                        <h4 className="text-body-lg font-bold text-white line-clamp-2 leading-snug group-hover:text-amber-300 transition-colors">
                          {act.title}
                        </h4>
                        {act.location && (
                          <p className="text-body-sm text-white/70 flex items-center gap-1">
                            <span className="material-symbols-outlined text-[13px] text-amber-400">location_on</span>
                            {act.location}
                          </p>
                        )}
                      </div>
                    </div>
                  </Link>
                ))}
              </div>
            </div>
          </div>
        </section>
      )}

      {/* ── 5. JADWAL & AGENDA KEGIATAN (Warm Amber Pearl Gradient Showcase) ── */}
      <section className="bg-gradient-to-br from-[#fffdfa] via-[#f7f1e5] to-[#ece3d2] border-b-2 border-amber-400/30">
        <WeeklyScheduleSection activities={activities} isLoading={loadingActivities} />
      </section>

      {/* ── 6. Artikel (Deep Islamic Emerald Pattern Section) ── */}
      {((articles && articles.length > 0) || loadingArticles) && (
        <section className="py-20 px-6 md:px-10 bg-islamic-pattern text-white relative overflow-hidden">
          <div className="max-w-container-max mx-auto relative z-10">
            <div className="flex items-end justify-between mb-10 pb-4 border-b border-amber-400/30">
              <div>
                <span className="inline-flex items-center gap-1.5 rounded-full bg-amber-400/20 border border-amber-400/40 px-3.5 py-1 text-label-sm uppercase tracking-[0.2em] font-bold text-amber-300 mb-2">
                  <span className="material-symbols-outlined text-[14px]">article</span>
                  Artikel
                </span>
                <h2 className="text-[32px] sm:text-[40px] font-bold text-white font-headline-sm">
                  Artikel Terbaru
                </h2>
              </div>
              <Link
                href="/articles"
                className="flex items-center gap-1.5 text-body-md font-bold text-amber-300 hover:text-white transition-colors"
              >
                Lihat semua Artikel
                <span className="material-symbols-outlined text-[18px]">arrow_forward</span>
              </Link>
            </div>

            <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
              {articles?.map((art, index) => (
                <Link
                  key={art.id}
                  href={`/articles/${art.slug}`}
                  className="group flex flex-col rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 hover:border-amber-400/60 overflow-hidden shadow-sm hover:shadow-lg transition-all"
                >
                  <div className="relative h-48 w-full">
                    <Image
                      src={
                        art.cover?.url ??
                        (index % 2 === 0 ? "/images/library-cover.jpg" : "/images/book-cover.jpg")
                      }
                      alt={art.title}
                      fill
                      className="object-cover group-hover:scale-105 transition-transform duration-500"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-[#002d20] via-transparent to-transparent opacity-70" />
                    <span className="absolute top-3 left-3 rounded-full bg-[#004b36] border border-amber-400/60 px-3.5 py-0.5 text-label-sm font-bold text-amber-300">
                      {art.category?.name ?? "Artikel"}
                    </span>
                  </div>
                  <div className="p-6 flex-1 flex flex-col justify-between space-y-3">
                    <div>
                      <span className="text-label-sm text-amber-300/80 font-medium">
                        {formatDate(art.publishedAt || art.createdAt)}
                      </span>
                      <h3 className="mt-1 text-[18px] font-bold text-white line-clamp-2 leading-snug group-hover:text-amber-300 transition-colors">
                        {art.title}
                      </h3>
                      {art.excerpt && (
                        <p className="mt-2 text-body-sm text-white/70 line-clamp-2 leading-relaxed">
                          {art.excerpt}
                        </p>
                      )}
                    </div>
                    <span className="text-label-sm font-bold text-amber-300 flex items-center gap-1 pt-3 border-t border-white/15">
                      Baca Selengkapnya <span className="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </span>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}

    </div>
  );
}
