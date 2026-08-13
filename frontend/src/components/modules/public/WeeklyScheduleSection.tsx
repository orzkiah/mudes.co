"use client";

import { useState, useEffect, useMemo } from "react";
import Link from "next/link";
import { formatDate, formatTime } from "@/lib/dates";
import type { Activity } from "@/types/models";

const DAY_NAMES = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
const INDONESIAN_MONTHS = [
  "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
  "Jul", "Agu", "Sep", "Okt", "Nov", "Des"
];

function formatIndonesianDate(date: Date): string {
  const dayName = DAY_NAMES[date.getDay()];
  const dayNum = date.getDate();
  const monthName = INDONESIAN_MONTHS[date.getMonth()];
  const year = date.getFullYear();
  return `${dayName}, ${dayNum} ${monthName} ${year}`;
}

interface ProcessedAgenda {
  id: string;
  title: string;
  categoryName: string;
  targetDate: Date;
  startDateStr: string;
  startTimeStr?: string | null;
  location?: string | null;
  slug?: string | null;
  status: "AKAN DATANG" | "BERLANGSUNG" | "SELESAI";
}

interface WeeklyScheduleSectionProps {
  activities?: Activity[];
  isLoading?: boolean;
}

export function WeeklyScheduleSection({ activities = [], isLoading = false }: WeeklyScheduleSectionProps) {
  const [now, setNow] = useState<Date>(() => new Date());

  // Update current time every second for live countdown & status
  useEffect(() => {
    const timer = setInterval(() => {
      setNow(new Date());
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  // Compute upcoming agenda items strictly from Admin Agenda & Kegiatan (activities)
  const calculatedItems = useMemo<ProcessedAgenda[]>(() => {
    const list: ProcessedAgenda[] = [];

    if (activities && activities.length > 0) {
      activities.forEach((a) => {
        if (!a.startAt) return;
        const startDate = new Date(a.startAt);
        const endDate = a.endAt ? new Date(a.endAt) : new Date(startDate.getTime() + 2 * 60 * 60 * 1000);

        let status: "AKAN DATANG" | "BERLANGSUNG" | "SELESAI" = "AKAN DATANG";
        if (now.getTime() > endDate.getTime()) {
          status = "SELESAI";
        } else if (now.getTime() >= startDate.getTime() && now.getTime() <= endDate.getTime()) {
          status = "BERLANGSUNG";
        }

        list.push({
          id: `act-${a.id}`,
          title: a.title,
          categoryName: a.category?.name ?? "AGENDA",
          targetDate: startDate,
          startDateStr: a.startAt.slice(0, 10),
          startTimeStr: a.startAt.slice(11, 16),
          location: a.location,
          slug: a.slug,
          status,
        });
      });
    }

    // Sort: Upcoming & Today events first (nearest targetDate first), Past events last
    return list.sort((a, b) => {
      const isAPast = a.status === "SELESAI";
      const isBPast = b.status === "SELESAI";
      if (!isAPast && isBPast) return -1;
      if (isAPast && !isBPast) return 1;
      return a.targetDate.getTime() - b.targetDate.getTime();
    });
  }, [activities, now]);

  // Hero item is the first upcoming or ongoing activity
  const heroItem = calculatedItems.find((i) => i.status !== "SELESAI") || calculatedItems[0];

  // Calculate live countdown for hero item
  const countdown = useMemo(() => {
    if (!heroItem) return { days: 0, hours: 0, minutes: 0, seconds: 0, isZero: true };

    const diffMs = heroItem.targetDate.getTime() - now.getTime();
    if (diffMs <= 0) return { days: 0, hours: 0, minutes: 0, seconds: 0, isZero: true };

    const days = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diffMs / (1000 * 60 * 60)) % 24);
    const minutes = Math.floor((diffMs / (1000 * 60)) % 60);
    const seconds = Math.floor((diffMs / 1000) % 60);

    return { days, hours, minutes, seconds, isZero: false };
  }, [heroItem, now]);

  const formatDigit = (num: number) => num.toString().padStart(2, "0");

  if (isLoading) {
    return (
      <div className="py-16 px-6 max-w-container-max mx-auto space-y-6 animate-pulse">
        <div className="h-64 bg-primary/10 rounded-3xl" />
        <div className="grid gap-6 sm:grid-cols-3">
          <div className="h-44 bg-primary/10 rounded-2xl" />
          <div className="h-44 bg-primary/10 rounded-2xl" />
          <div className="h-44 bg-primary/10 rounded-2xl" />
        </div>
      </div>
    );
  }

  // Empty state if no events
  if (calculatedItems.length === 0) {
    return (
      <section className="py-16 md:py-20 px-6 md:px-10 max-w-container-max mx-auto text-center space-y-4">
        <div className="p-10 rounded-3xl bg-white border border-outline/10 shadow-2xs max-w-lg mx-auto space-y-3">
          <span className="material-symbols-outlined text-[48px] text-[#004b36]/40">calendar_today</span>
          <h3 className="text-[20px] font-bold text-[#004b36] font-headline-sm">Belum Ada Agenda Terjadwal</h3>
          <p className="text-body-md text-on-surface-variant">
            Agenda kegiatan belum ditambahkan oleh pengurus. Silakan cek kembali secara berkala.
          </p>
        </div>
      </section>
    );
  }

  return (
    <section className="py-16 md:py-20 px-6 md:px-10 max-w-container-max mx-auto space-y-10">
      
      {/* ── 1. HERO COUNTDOWN SHOWCASE CARD (Agenda Terdekat) ── */}
      {heroItem && (
        <div className="rounded-3xl bg-white border border-outline/10 shadow-2xs overflow-hidden grid grid-cols-1 md:grid-cols-[1.3fr_1fr] items-stretch">
          
          {/* Left Side: Detail Agenda Utama */}
          <div className="p-8 sm:p-10 space-y-5 flex flex-col justify-between">
            <div className="space-y-3">
              <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-100/80 px-3.5 py-1 text-[11px] font-bold text-emerald-800 uppercase tracking-wider">
                <span className="w-2 h-2 rounded-full bg-emerald-600 animate-pulse" />
                {heroItem.status === "BERLANGSUNG" ? "BERLANGSUNG SEKARANG" : "AGENDA BERIKUTNYA"}
              </span>

              <h2 className="text-[26px] sm:text-[34px] font-bold leading-tight text-on-surface font-headline-sm tracking-tight">
                {heroItem.title}
              </h2>

              <div className="flex flex-wrap items-center gap-4 text-body-md text-on-surface-variant font-medium pt-1">
                <div className="flex items-center gap-1.5">
                  <span className="material-symbols-outlined text-[20px] text-[#004b36]">label</span>
                  <span>{heroItem.categoryName}</span>
                </div>
                {heroItem.location && (
                  <div className="flex items-center gap-1.5">
                    <span className="material-symbols-outlined text-[20px] text-[#004b36]">location_on</span>
                    <span>{heroItem.location}</span>
                  </div>
                )}
              </div>
            </div>

            <div className="space-y-5 pt-2">
              <div className="flex flex-wrap items-center gap-4 text-body-md font-semibold text-on-surface-variant">
                <div className="flex items-center gap-2">
                  <span className="material-symbols-outlined text-[20px] text-on-surface-variant">calendar_month</span>
                  <span>{formatIndonesianDate(heroItem.targetDate)}</span>
                </div>
                {heroItem.startTimeStr && (
                  <>
                    <span>•</span>
                    <div className="flex items-center gap-2">
                      <span className="material-symbols-outlined text-[20px] text-on-surface-variant">schedule</span>
                      <span>{formatTime(heroItem.startTimeStr)} WIB</span>
                    </div>
                  </>
                )}
              </div>

              <div>
                <Link
                  href={heroItem.slug ? `/activities/${heroItem.slug}` : "/schedule"}
                  className="inline-flex items-center gap-2 rounded-xl bg-[#004b36] px-6 py-3 text-body-md font-bold text-white shadow-xs hover:bg-emerald-900 transition-all cursor-pointer"
                >
                  Lihat Detail
                </Link>
              </div>
            </div>
          </div>

          {/* Right Side: Dynamic Live Countdown Timer */}
          <div className="bg-[#f2f7f4] p-8 sm:p-10 flex flex-col items-center justify-center border-t md:border-t-0 md:border-l border-outline/10 text-center">
            <p className="text-body-md font-semibold text-on-surface-variant mb-6">
              {heroItem.status === "BERLANGSUNG" ? "Sesi Berlangsung Sekarang" : "Dimulai dalam"}
            </p>

            {/* 4 Digit Boxes */}
            <div className="grid grid-cols-4 gap-3 sm:gap-4 max-w-xs sm:max-w-sm w-full">
              {/* Box 1: HARI */}
              <div className="flex flex-col items-center">
                <div className="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-2xl shadow-2xs border border-outline/10 flex items-center justify-center">
                  <span className="text-[26px] sm:text-[34px] font-extrabold text-[#004b36] font-headline-sm">
                    {formatDigit(countdown.days)}
                  </span>
                </div>
                <span className="text-[10px] uppercase font-bold tracking-widest text-on-surface-variant/80 mt-2">
                  HARI
                </span>
              </div>

              {/* Box 2: JAM */}
              <div className="flex flex-col items-center">
                <div className="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-2xl shadow-2xs border border-outline/10 flex items-center justify-center">
                  <span className="text-[26px] sm:text-[34px] font-extrabold text-[#004b36] font-headline-sm">
                    {formatDigit(countdown.hours)}
                  </span>
                </div>
                <span className="text-[10px] uppercase font-bold tracking-widest text-on-surface-variant/80 mt-2">
                  JAM
                </span>
              </div>

              {/* Box 3: MENIT */}
              <div className="flex flex-col items-center">
                <div className="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-2xl shadow-2xs border border-outline/10 flex items-center justify-center">
                  <span className="text-[26px] sm:text-[34px] font-extrabold text-[#004b36] font-headline-sm">
                    {formatDigit(countdown.minutes)}
                  </span>
                </div>
                <span className="text-[10px] uppercase font-bold tracking-widest text-on-surface-variant/80 mt-2">
                  MENIT
                </span>
              </div>

              {/* Box 4: DETIK */}
              <div className="flex flex-col items-center">
                <div className="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-2xl shadow-2xs border border-outline/10 flex items-center justify-center">
                  <span className="text-[26px] sm:text-[34px] font-extrabold text-[#004b36] font-headline-sm">
                    {formatDigit(countdown.seconds)}
                  </span>
                </div>
                <span className="text-[10px] uppercase font-bold tracking-widest text-on-surface-variant/80 mt-2">
                  DETIK
                </span>
              </div>
            </div>
          </div>

        </div>
      )}

      {/* ── 2. JADWAL MENDATANG CARDS GRID ── */}
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h3 className="text-[22px] font-bold text-on-surface font-headline-sm">
            Agenda Mendatang
          </h3>

          <Link
            href="/schedule"
            className="p-2 rounded-xl border border-outline/20 text-on-surface-variant hover:bg-white hover:border-outline/40 transition-colors shadow-2xs cursor-pointer flex items-center gap-1.5 text-body-sm font-semibold text-[#004b36]"
          >
            <span className="material-symbols-outlined text-[18px]">calendar_month</span>
            Lihat Kalender Full
          </Link>
        </div>

        {/* Dynamic Grid Cards from API */}
        <div className="grid gap-6 sm:grid-cols-2 md:grid-cols-3">
          {calculatedItems.slice(0, 3).map((item) => {
            const isBerlangsung = item.status === "BERLANGSUNG";
            const isSelesai = item.status === "SELESAI";

            const cardNode = (
              <div
                className={`p-6 rounded-2xl space-y-4 transition-all h-full ${
                  isBerlangsung
                    ? "bg-white border-2 border-emerald-400 shadow-sm"
                    : isSelesai
                    ? "bg-slate-100/70 border border-slate-200/80 opacity-70"
                    : "bg-white border border-outline/10 shadow-2xs hover:shadow-xs hover:border-[#004b36]/30"
                }`}
              >
                {/* Header Icon + Options */}
                <div className="flex items-center justify-between">
                  <div
                    className={`h-10 w-10 rounded-xl flex items-center justify-center ${
                      isBerlangsung
                        ? "bg-emerald-100 text-emerald-700"
                        : isSelesai
                        ? "bg-slate-200 text-slate-500"
                        : "bg-emerald-50 text-[#004b36]"
                    }`}
                  >
                    <span className="material-symbols-outlined text-[20px]">calendar_month</span>
                  </div>
                  <span className="material-symbols-outlined text-[18px] text-on-surface-variant/40">
                    event_available
                  </span>
                </div>

                {/* Status Badges */}
                <div className="flex flex-wrap items-center gap-2">
                  {isBerlangsung ? (
                    <span className="rounded-md bg-emerald-600 px-2.5 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider flex items-center gap-1">
                      <span className="w-1.5 h-1.5 rounded-full bg-white animate-ping" />
                      BERLANGSUNG
                    </span>
                  ) : isSelesai ? (
                    <span className="rounded-md bg-slate-200 px-2.5 py-0.5 text-[10px] font-bold text-slate-600 uppercase tracking-wider">
                      SELESAI
                    </span>
                  ) : (
                    <span className="rounded-md bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 uppercase tracking-wider">
                      AKAN DATANG
                    </span>
                  )}

                  <span className="rounded-md bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-600 uppercase tracking-wider">
                    {item.categoryName}
                  </span>
                </div>

                {/* Title */}
                <div>
                  <h4
                    className={`text-[17px] font-bold leading-snug ${
                      isSelesai ? "text-slate-600 line-through" : "text-on-surface"
                    }`}
                  >
                    {item.title}
                  </h4>
                </div>

                {/* Date & Location */}
                <div className="pt-2 border-t border-outline/10 space-y-1.5 text-body-sm text-on-surface-variant font-medium">
                  <p className="flex items-center gap-1.5">
                    <span className="material-symbols-outlined text-[16px]">schedule</span>
                    {formatIndonesianDate(item.targetDate)}
                    {item.startTimeStr ? ` • ${formatTime(item.startTimeStr)} WIB` : ""}
                  </p>
                  {item.location && (
                    <p className="flex items-center gap-1.5">
                      <span className="material-symbols-outlined text-[16px]">location_on</span>
                      {item.location}
                    </p>
                  )}
                </div>
              </div>
            );

            if (item.slug) {
              return (
                <Link key={item.id} href={`/activities/${item.slug}`} className="block">
                  {cardNode}
                </Link>
              );
            }

            return <div key={item.id}>{cardNode}</div>;
          })}
        </div>
      </div>

    </section>
  );
}
