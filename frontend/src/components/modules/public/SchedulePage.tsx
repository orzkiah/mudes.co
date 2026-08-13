"use client";

import { useState, useMemo } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";

import { api } from "@/lib/api";
import { Alert } from "@/components/ui/Alert";
import { Skeleton } from "@/components/ui/Skeleton";
import { formatDate, formatTime, dayjs } from "@/lib/dates";
import type { Activity, ActivityCategory } from "@/types/models";

const DAY_SHORT = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
const MONTH_NAMES = [
  "Januari", "Februari", "Maret", "April", "Mei", "Juni",
  "Juli", "Agustus", "September", "Oktober", "November", "Desember",
];

interface CombinedEvent {
  id: string;
  type: "activity";
  title: string;
  categoryName: string;
  categoryId?: string;
  eventDate: string; // "YYYY-MM-DD"
  startTime?: string | null;
  endTime?: string | null;
  location?: string | null;
  ustadzName?: string | null;
  slug?: string | null;
}

function buildCalendarDays(year: number, month: number) {
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const cells: (number | null)[] = Array(firstDay).fill(null);
  for (let d = 1; d <= daysInMonth; d++) cells.push(d);
  return cells;
}

export function SchedulePage() {
  const now = dayjs();
  const todayStr = dayjs().format("YYYY-MM-DD");
  const [calYear, setCalYear] = useState(now.year());
  const [calMonth, setCalMonth] = useState(now.month());
  const [activeFilter, setActiveFilter] = useState<string>("all");

  // Fetch Public Activities/Events strictly from API (Matches Admin Agenda & Kegiatan 1-to-1)
  const { data: activities, isLoading, error } = useQuery<Activity[]>({
    queryKey: ["public", "activities", "calendar"],
    queryFn: async () => {
      const res = await api.get("/public/activities", { params: { perPage: 100 } });
      return res.data.data;
    },
    staleTime: 60 * 1000,
  });

  // Fetch Categories for filters
  const { data: activityCategories } = useQuery<ActivityCategory[]>({
    queryKey: ["public", "activity-categories"],
    queryFn: async () => {
      const res = await api.get("/public/activity-categories");
      return res.data.data;
    },
    staleTime: 5 * 60 * 1000,
  });

  // Process ONLY Activities (Agenda & Kegiatan from Admin Dashboard)
  const combinedEvents = useMemo<CombinedEvent[]>(() => {
    const list: CombinedEvent[] = [];

    if (activities && activities.length > 0) {
      activities.forEach((a) => {
        const dateStr = a.startAt ? a.startAt.slice(0, 10) : "";
        if (!dateStr) return;

        list.push({
          id: `act-${a.id}`,
          type: "activity",
          title: a.title,
          categoryName: a.category?.name ?? "AGENDA",
          categoryId: a.activityCategoryId || a.category?.id,
          eventDate: dateStr,
          startTime: a.startAt ? a.startAt.slice(11, 16) : null,
          endTime: a.endAt ? a.endAt.slice(11, 16) : null,
          location: a.location,
          slug: a.slug,
        });
      });
    }

    // Sort: Upcoming & Today events at top (nearest first), Past events at bottom
    return list.sort((a, b) => {
      const isAPast = a.eventDate < todayStr;
      const isBPast = b.eventDate < todayStr;

      // 1. Upcoming & Today first, Past events at bottom
      if (!isAPast && isBPast) return -1;
      if (isAPast && !isBPast) return 1;

      // 2. Both Upcoming/Today: sort ascending by date (nearest date first)
      if (!isAPast && !isBPast) {
        return a.eventDate.localeCompare(b.eventDate);
      }

      // 3. Both Past: sort descending by date (most recently passed first)
      return b.eventDate.localeCompare(a.eventDate);
    });
  }, [activities, todayStr]);

  const calDays = buildCalendarDays(calYear, calMonth);

  // Dynamic filter categories from Activity Categories
  const filterOptions = useMemo(() => {
    const opts = [{ key: "all", label: "Semua Agenda" }];
    if (activityCategories) {
      activityCategories.forEach((c) => {
        opts.push({ key: c.id, label: c.name });
      });
    }
    return opts;
  }, [activityCategories]);

  const prevMonth = () => {
    if (calMonth === 0) { setCalMonth(11); setCalYear(calYear - 1); }
    else setCalMonth(calMonth - 1);
  };
  const nextMonth = () => {
    if (calMonth === 11) { setCalMonth(0); setCalYear(calYear + 1); }
    else setCalMonth(calMonth + 1);
  };

  return (
    <div className="min-h-screen bg-light-motif">
      {/* Page header with rich emerald gradient + Islamic geometric motif banner */}
      <section className="bg-islamic-pattern border-b-2 border-amber-400/40 text-white px-6 py-12 md:px-10 relative overflow-hidden">
        <div className="mx-auto max-w-container-max relative z-10">
          <span className="inline-flex items-center gap-1.5 rounded-full bg-amber-400/20 border border-amber-400/30 px-3.5 py-1 text-label-sm uppercase tracking-widest text-amber-300 font-bold">
            <span className="w-2 h-2 rounded-full bg-amber-400 animate-pulse" />
            Agenda &amp; Kegiatan
          </span>
          <h1 className="mt-3 text-[32px] sm:text-[42px] font-bold leading-tight text-white font-headline-sm tracking-tight drop-shadow-sm">
            Kalender Agenda Mudes Condet
          </h1>
          <p className="mt-3 max-w-2xl text-body-lg text-white/80 leading-relaxed font-body-md">
            Jadwal kegiatan dan acara resmi pemuda-pemudi Desa Condet. Seluruh agenda terdaftar secara langsung sesuai tanggal pelaksanaan di Admin Dashboard.
          </p>
        </div>
      </section>

      <div className="mx-auto max-w-container-max px-6 py-8 md:px-10">
        <div className="grid gap-8 lg:grid-cols-[280px_1fr]">

          {/* ── LEFT SIDEBAR — Filters & Legend ── */}
          <aside className="space-y-5">
            <div className="rounded-2xl bg-white p-5 shadow-2xs border border-outline/10">
              <div className="flex items-center gap-2 text-label-sm uppercase tracking-widest text-[#004b36] font-semibold mb-4">
                <span className="material-symbols-outlined text-[16px]">filter_list</span>
                Filter Kategori
              </div>
              <div className="space-y-2">
                {filterOptions.map((f) => (
                  <button
                    key={f.key}
                    onClick={() => setActiveFilter(f.key)}
                    className={`flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-left text-body-md transition-colors cursor-pointer ${
                      activeFilter === f.key
                        ? "bg-[#004b36] text-white font-semibold shadow-2xs"
                        : "bg-[#f5f0e8] text-on-surface-variant hover:bg-[#004b36]/10"
                    }`}
                  >
                    <span className={`h-2.5 w-2.5 rounded-full flex-shrink-0 ${
                      activeFilter === f.key ? "bg-white" : "bg-[#004b36]"
                    }`} />
                    {f.label}
                  </button>
                ))}
              </div>
            </div>

            {/* Color Legend Card */}
            <div className="rounded-2xl bg-white p-5 shadow-2xs border border-outline/10 space-y-3">
              <div className="flex items-center gap-2 text-label-sm uppercase tracking-widest text-[#004b36] font-bold">
                <span className="material-symbols-outlined text-[16px]">palette</span>
                Keterangan Warna
              </div>
              <div className="space-y-2.5 text-body-sm">
                <div className="flex items-center gap-2.5">
                  <span className="h-3.5 w-3.5 rounded-full bg-emerald-600 border border-emerald-700 shrink-0" />
                  <span className="font-semibold text-emerald-950">Hari H Acara (Hijau)</span>
                </div>
                <div className="flex items-center gap-2.5">
                  <span className="h-3.5 w-3.5 rounded-full bg-amber-400 border border-amber-500 shrink-0" />
                  <span className="font-semibold text-amber-950">Acara Mendatang (Kuning)</span>
                </div>
                <div className="flex items-center gap-2.5">
                  <span className="h-3.5 w-3.5 rounded-full bg-rose-500 border border-rose-600 shrink-0" />
                  <span className="font-semibold text-rose-950">Acara Sudah Lewat (Merah)</span>
                </div>
              </div>
            </div>
          </aside>


          {/* ── RIGHT — Calendar + Events ── */}
          <div className="space-y-6">
            {/* Calendar widget */}
            <div className="rounded-2xl bg-white p-6 shadow-2xs border border-outline/10">
              <div className="flex items-center justify-between mb-5">
                <div>
                  <h2 className="text-[22px] font-bold font-headline-sm text-[#004b36]">
                    {MONTH_NAMES[calMonth]} {calYear}
                  </h2>
                  <p className="text-body-sm text-on-surface-variant mt-0.5 font-medium">
                    {combinedEvents.length} agenda kegiatan terdaftar
                  </p>
                </div>
                <div className="flex items-center gap-1">
                  <button
                    onClick={prevMonth}
                    className="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-[#004b36]/10 transition-colors"
                  >
                    <span className="material-symbols-outlined text-[18px] text-on-surface-variant">chevron_left</span>
                  </button>
                  <button
                    onClick={nextMonth}
                    className="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-[#004b36]/10 transition-colors"
                  >
                    <span className="material-symbols-outlined text-[18px] text-on-surface-variant">chevron_right</span>
                  </button>
                  <button
                    onClick={() => { setCalMonth(now.month()); setCalYear(now.year()); }}
                    className="ml-2 rounded-lg bg-[#004b36]/10 px-3 py-1.5 text-label-sm text-[#004b36] font-bold hover:bg-[#004b36]/20 transition-colors"
                  >
                    Hari Ini
                  </button>
                </div>
              </div>

              {/* Day headers */}
              <div className="grid grid-cols-7 text-center mb-2">
                {DAY_SHORT.map((d) => (
                  <div key={d} className="py-1.5 text-label-sm text-on-surface-variant/70 font-bold uppercase">{d}</div>
                ))}
              </div>

              {/* Calendar cells with Event Color Coding */}
              <div className="grid grid-cols-7 gap-1.5 md:gap-2">
                {calDays.map((day, i) => {
                  if (day === null) {
                    return <div key={i} className="min-h-[85px] md:min-h-[100px] rounded-xl bg-slate-50/50" />;
                  }

                  const mm = String(calMonth + 1).padStart(2, "0");
                  const dd = String(day).padStart(2, "0");
                  const cellDateStr = `${calYear}-${mm}-${dd}`;

                  const isCellToday = cellDateStr === todayStr;
                  const isCellPast = cellDateStr < todayStr;
                  const isCellFuture = cellDateStr > todayStr;

                  // Matching events for this exact date
                  const matchingEvents = combinedEvents.filter((ev) => {
                    if (ev.eventDate !== cellDateStr) return false;
                    if (activeFilter === "all") return true;
                    return ev.categoryId === activeFilter;
                  });

                  const totalEvents = matchingEvents.length;

                  // Cell container style
                  let cellStyle = "border-outline/10 bg-white hover:bg-slate-50";
                  if (isCellToday) {
                    cellStyle = "border-2 border-emerald-600 bg-emerald-50/60 shadow-2xs";
                  } else if (totalEvents > 0) {
                    if (isCellPast) {
                      cellStyle = "border border-rose-300 bg-rose-50/40 hover:bg-white";
                    } else if (isCellFuture) {
                      cellStyle = "border border-amber-300 bg-amber-50/40 hover:bg-white";
                    }
                  }

                  return (
                    <div
                      key={i}
                      className={`min-h-[85px] md:min-h-[100px] p-1.5 flex flex-col justify-start rounded-xl border transition-all ${cellStyle}`}
                    >
                      {/* Top bar: date number */}
                      <div className="flex items-center justify-between w-full mb-1">
                        <span
                          className={`text-body-sm font-bold flex items-center justify-center ${
                            isCellToday
                              ? "h-6 w-6 rounded-full bg-emerald-600 text-white text-xs shadow-xs"
                              : "text-on-surface"
                          }`}
                        >
                          {day}
                        </span>
                        {totalEvents > 0 && !isCellToday && (
                          <span
                            className={`h-2.5 w-2.5 rounded-full shrink-0 ${
                              isCellPast ? "bg-rose-500" : "bg-amber-500"
                            }`}
                          />
                        )}
                      </div>

                      {/* Event badges */}
                      <div className="flex-1 w-full space-y-1 overflow-hidden">
                        {matchingEvents.map((ev) => {
                          const isEventToday = ev.eventDate === todayStr;
                          const isEventPast = ev.eventDate < todayStr;

                          let badgeStyle = "bg-amber-100 text-amber-950 border-amber-300/80 hover:bg-amber-200";
                          if (isEventToday) {
                            badgeStyle = "bg-emerald-600 text-white border-emerald-700 hover:bg-emerald-700 shadow-2xs";
                          } else if (isEventPast) {
                            badgeStyle = "bg-rose-100 text-rose-950 border-rose-300/80 hover:bg-rose-200";
                          }

                          const content = (
                            <div
                              className={`w-full flex items-center gap-1 px-1.5 py-0.5 rounded-md border text-[11px] font-bold leading-tight transition-colors cursor-pointer ${badgeStyle}`}
                              title={`${ev.title} ${ev.location ? `@ ${ev.location}` : ""}`}
                            >
                              <span className="material-symbols-outlined text-[12px] shrink-0">event</span>
                              <span className="truncate">{ev.title}</span>
                            </div>
                          );

                          if (ev.slug) {
                            return (
                              <Link key={ev.id} href={`/activities/${ev.slug}`}>
                                {content}
                              </Link>
                            );
                          }

                          return <div key={ev.id}>{content}</div>;
                        })}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>

            {/* Explicit Dated Activities List Cards */}
            <div className="space-y-4 pt-2">
              <h2 className="text-[22px] font-bold text-[#004b36] font-headline-sm">Daftar Agenda Terjadwal</h2>
              
              {isLoading ? (
                <div className="space-y-3">
                  {Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-20 rounded-2xl" />)}
                </div>
              ) : error ? (
                <Alert variant="error">Tidak dapat memuat agenda kegiatan. Coba muat ulang halaman.</Alert>
              ) : combinedEvents.length === 0 ? (
                <div className="rounded-2xl bg-white p-8 text-center text-on-surface-variant border border-outline/10">
                  <span className="material-symbols-outlined text-[48px] text-outline">calendar_today</span>
                  <p className="mt-2 text-body-md font-semibold text-[#004b36]">Belum ada agenda kegiatan terjadwal.</p>
                  <p className="text-body-sm text-on-surface-variant mt-1">
                    Agenda kegiatan akan muncul setelah dibuat oleh pengurus pada menu Agenda &amp; Kegiatan Admin.
                  </p>
                </div>
              ) : (
                <div className="space-y-3">
                  {combinedEvents
                    .filter((ev) => {
                      if (activeFilter === "all") return true;
                      return ev.categoryId === activeFilter;
                    })
                    .map((ev) => {
                      const isEventToday = ev.eventDate === todayStr;
                      const isEventPast = ev.eventDate < todayStr;

                      let cardContainerStyle = "bg-amber-50/70 border-amber-300/80 hover:bg-amber-100/60";
                      let dateBoxStyle = "bg-amber-500 text-amber-950 font-bold";

                      if (isEventToday) {
                        cardContainerStyle = "bg-emerald-50/90 border-emerald-400 shadow-2xs hover:bg-emerald-100/70";
                        dateBoxStyle = "bg-emerald-600 text-white font-bold";
                      } else if (isEventPast) {
                        cardContainerStyle = "bg-rose-50/60 border-rose-300/80 hover:bg-rose-100/50 opacity-90";
                        dateBoxStyle = "bg-rose-600 text-white font-bold";
                      }

                      const cardContent = (
                        <div className={`flex items-center gap-4 sm:gap-5 rounded-2xl px-5 py-4 border shadow-2xs transition-all ${cardContainerStyle}`}>
                          <div className={`flex h-12 w-14 sm:h-14 sm:w-16 flex-shrink-0 flex-col items-center justify-center rounded-xl ${dateBoxStyle}`}>
                            <span className="text-[10px] font-bold uppercase">{dayjs(ev.eventDate).format("MMM")}</span>
                            <span className="text-[18px] font-bold leading-none">{dayjs(ev.eventDate).format("DD")}</span>
                          </div>
                          <div className="flex-1 min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                              <span className="rounded-full bg-[#004b36]/10 px-2.5 py-0.5 text-label-sm font-semibold text-[#004b36]">
                                {ev.categoryName}
                              </span>
                              <span className="text-label-sm text-on-surface-variant font-medium">
                                {formatDate(ev.eventDate)}
                              </span>
                            </div>
                            <h3 className="mt-1 text-body-md font-bold text-on-surface line-clamp-1 group-hover:text-[#004b36] transition-colors">
                              {ev.title}
                            </h3>
                            <div className="flex flex-wrap items-center gap-3 text-body-sm text-on-surface-variant font-medium mt-1">
                              {ev.ustadzName && (
                                <span className="flex items-center gap-1">
                                  👤 {ev.ustadzName}
                                </span>
                              )}
                              {ev.startTime && (
                                <span className="flex items-center gap-1">
                                  🕒 {formatTime(ev.startTime)} {ev.endTime ? `- ${formatTime(ev.endTime)}` : ""} WIB
                                </span>
                              )}
                              {ev.location && (
                                <span className="flex items-center gap-1">
                                  📍 {ev.location}
                                </span>
                              )}
                            </div>
                          </div>
                          <div className="flex-shrink-0 text-right">
                            <span className="text-label-sm text-[#004b36] font-semibold flex items-center gap-0.5 justify-end">
                              Lihat Detail <span className="material-symbols-outlined text-[14px]">arrow_forward</span>
                            </span>
                          </div>
                        </div>
                      );

                      if (ev.slug) {
                        return (
                          <Link key={ev.id} href={`/activities/${ev.slug}`} className="block">
                            {cardContent}
                          </Link>
                        );
                      }

                      return (
                        <div key={ev.id} className="block">
                          {cardContent}
                        </div>
                      );
                    })}
                </div>
              )}
            </div>

          </div>
        </div>
      </div>

    </div>
  );
}
