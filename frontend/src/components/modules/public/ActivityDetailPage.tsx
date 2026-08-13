"use client";

import { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { formatDate, formatTime } from "@/lib/dates";
import { Alert } from "@/components/ui/Alert";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import type { Activity } from "@/types/models";

interface ActivityDetailPageProps {
  slug: string;
}

export function ActivityDetailPage({ slug }: ActivityDetailPageProps) {
  const { data: activity, isLoading, error } = useQuery<Activity>({
    queryKey: ["public", "activities", slug],
    queryFn: async () => {
      const res = await api.get(`/public/activities/${slug}`);
      return res.data.data;
    },
    staleTime: 5 * 60 * 1000,
  });

  // Presensi Form State
  const [memberId, setMemberId] = useState("");
  const [qrTokenInput, setQrTokenInput] = useState("");
  const [presensiLoading, setPresensiLoading] = useState(false);
  const [presensiError, setPresensiError] = useState("");
  const [presensiSuccess, setPresensiSuccess] = useState(false);
  const [showForm, setShowForm] = useState(false);

  // Time-gated attendance logic calculation
  const now = Date.now();
  const startTime = activity ? new Date(activity.startAt).getTime() : 0;
  const endTime = activity?.endAt
    ? new Date(activity.endAt).getTime()
    : startTime + 4 * 60 * 60 * 1000; // Default 4 hours if endAt is empty

  const isBeforeStart = now < startTime;
  const isActive = now >= startTime && now <= endTime;
  const isEnded = now > endTime;

  const handlePresensiSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!memberId.trim()) {
      setPresensiError("ID / Nomor Anggota wajib diisi.");
      return;
    }

    setPresensiLoading(true);
    setPresensiError("");

    try {
      await api.post("/public/attendance/check-in", {
        qrToken: qrTokenInput.trim() || `activity-${activity?.id}`,
        memberId: memberId.trim(),
      });
      setPresensiSuccess(true);
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      setPresensiError(
        error.response?.data?.message ||
          "Gagal mencatat presensi. Pastikan ID Anggota Anda terdaftar dan panitia telah membuka sesi QR."
      );
    } finally {
      setPresensiLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#f5f0e8] text-on-surface font-body-md">
      {/* ── Back Navigation ── */}
      <div className="max-w-5xl mx-auto px-6 pt-6 md:px-8">
        <Link
          href="/activities"
          className="inline-flex items-center gap-1.5 text-label-sm font-bold uppercase tracking-wider text-[#004b36] hover:underline"
        >
          <span className="material-symbols-outlined text-[16px]">arrow_back</span>
          Kembali ke Daftar Kegiatan
        </Link>
      </div>

      {/* ── Main Activity Container (Balanced Compact Sizing at 100% Zoom) ── */}
      <article className="max-w-5xl mx-auto px-6 py-6 md:px-8">
        {isLoading ? (
          <div className="space-y-5 animate-pulse">
            <div className="h-5 w-28 bg-primary/10 rounded-full" />
            <div className="h-10 w-3/4 bg-primary/10 rounded-xl" />
            <div className="grid gap-6 lg:grid-cols-[1.3fr_1fr]">
              <div className="h-[280px] w-full bg-primary/10 rounded-2xl" />
              <div className="h-[280px] w-full bg-primary/10 rounded-2xl" />
            </div>
          </div>
        ) : error || !activity ? (
          <div className="py-16 text-center space-y-4">
            <span className="material-symbols-outlined text-[56px] text-outline">event</span>
            <h1 className="text-[24px] font-bold text-[#004b36] font-headline-sm">Kegiatan Tidak Ditemukan</h1>
            <p className="text-body-md text-on-surface-variant max-w-md mx-auto">
              Agenda kegiatan yang Anda cari mungkin telah berakhir atau belum terdaftar.
            </p>
            <div className="pt-3">
              <Link
                href="/activities"
                className="inline-flex items-center gap-2 rounded-full bg-[#004b36] px-5 py-2 text-label-md font-semibold text-white hover:bg-emerald-950"
              >
                Lihat Agenda Lainnya
              </Link>
            </div>
          </div>
        ) : (
          <div className="space-y-6 animate-in fade-in duration-500">
            {/* Header Title Section */}
            <div className="space-y-2.5">
              <div className="flex flex-wrap items-center gap-2">
                <span className="rounded-full bg-[#004b36] px-3 py-0.5 text-[11px] font-bold text-white uppercase tracking-wider">
                  {activity.category?.name ?? "KEGIATAN"}
                </span>
                <span className="rounded-full bg-[#f9c74f] px-3 py-0.5 text-[11px] font-bold text-[#004b36] uppercase tracking-wider">
                  {activity.status}
                </span>
              </div>

              <h1 className="text-[28px] sm:text-[34px] md:text-[38px] font-bold leading-[1.15] text-[#004b36] font-headline-sm tracking-tight">
                {activity.title}
              </h1>
            </div>

            {/* Main Content Grid: Image (Left) + Metadata Sidebar (Right) - Compact Sizing */}
            <div className="grid gap-6 lg:grid-cols-[1.3fr_1fr] items-start">
              {/* Left Column: Compact Activity Image Banner */}
              <div className="relative h-[240px] sm:h-[280px] lg:h-[310px] w-full rounded-2xl overflow-hidden shadow-2xs bg-[#004b36]/10 border border-outline/10">
                <Image
                  src={activity.cover?.url ?? "/images/condet-mural.jpg"}
                  alt={activity.title}
                  fill
                  priority
                  className="object-cover"
                />
              </div>

              {/* Right Column: Compact Sidebar Metadata Card */}
              <div className="rounded-2xl bg-[#004b36]/5 p-5 border border-[#004b36]/10 shadow-2xs space-y-4">
                {/* 1. Waktu Pelaksanaan */}
                <div className="flex items-start gap-3">
                  <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-[#004b36]/10 text-[#004b36] shrink-0">
                    <span className="material-symbols-outlined text-[18px]">calendar_month</span>
                  </div>
                  <div>
                    <p className="text-[10px] uppercase tracking-widest font-bold text-on-surface-variant/80">
                      WAKTU PELAKSANAAN
                    </p>
                    <p className="text-[15px] font-bold text-[#004b36] mt-0.5 leading-snug">
                      {formatDate(activity.startAt)}
                    </p>
                    <p className="text-body-sm font-semibold text-[#004b36]">
                      {formatTime(activity.startAt)} WIB
                    </p>
                    {activity.endAt && (
                      <p className="text-[12px] text-on-surface-variant mt-0.5 font-medium">
                        Sampai {formatDate(activity.endAt)} ({formatTime(activity.endAt)} WIB)
                      </p>
                    )}
                  </div>
                </div>

                <div className="border-b border-[#004b36]/10" />

                {/* 2. Lokasi Kegiatan */}
                <div className="flex items-start gap-3">
                  <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-[#004b36]/10 text-[#004b36] shrink-0">
                    <span className="material-symbols-outlined text-[18px]">location_on</span>
                  </div>
                  <div>
                    <p className="text-[10px] uppercase tracking-widest font-bold text-on-surface-variant/80">
                      LOKASI KEGIATAN
                    </p>
                    <p className="text-[15px] font-bold text-[#004b36] mt-0.5">
                      {activity.location ?? "Desa Condet"}
                    </p>
                  </div>
                </div>

                <div className="border-b border-[#004b36]/10" />

                {/* 3. Tombol Status Presensi */}
                <div className="pt-0.5">
                  {isBeforeStart ? (
                    <div>
                      <button
                        type="button"
                        disabled
                        className="w-full rounded-full bg-red-100 border border-red-200 py-2.5 text-label-sm font-bold text-red-700 flex items-center justify-center gap-1.5 cursor-not-allowed shadow-2xs"
                      >
                        <span className="material-symbols-outlined text-[16px]">lock</span>
                        PRESENSI BELUM DIBUKA
                      </button>
                      <span className="text-[11px] text-center text-red-700/80 font-semibold block mt-1.5">
                        Mulai: {formatTime(activity.startAt)} WIB
                      </span>
                    </div>
                  ) : isActive ? (
                    <button
                      type="button"
                      onClick={() => setShowForm(!showForm)}
                      className="w-full rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 text-label-sm flex items-center justify-center gap-1.5 shadow-xs cursor-pointer transition-all animate-pulse"
                    >
                      <span className="material-symbols-outlined text-[18px]">how_to_reg</span>
                      PRESENSI KEHADIRAN
                    </button>
                  ) : (
                    <button
                      type="button"
                      disabled
                      className="w-full rounded-full bg-slate-200 text-slate-600 font-bold py-2.5 text-label-sm flex items-center justify-center gap-1.5 cursor-not-allowed"
                    >
                      <span className="material-symbols-outlined text-[16px]">event_busy</span>
                      PRESENSI DITUTUP
                    </button>
                  )}
                </div>
              </div>
            </div>

            {/* ── TIME-GATED ABSENSI EXPANDED FORM (DITAMPILKAN SAAT BERLANGSUNG/HIJAU) ── */}
            {isActive && (showForm || true) && (
              <div className="rounded-2xl border-2 border-emerald-500 bg-emerald-50/80 p-5 sm:p-6 shadow-sm space-y-3 pt-3">
                <div className="flex items-center justify-between flex-wrap gap-2">
                  <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-700 px-3 py-0.5 text-[11px] font-bold text-white shadow-2xs">
                    <span className="inline-block w-2 h-2 rounded-full bg-white animate-ping" />
                    SESI PRESENSI DIBUKA
                  </span>
                  <span className="text-body-sm text-emerald-900 font-semibold">
                    Berlangsung Sekarang
                  </span>
                </div>

                <div>
                  <h3 className="text-[18px] font-bold text-emerald-950 font-headline-sm">
                    Form Absensi Kehadiran Peserta
                  </h3>
                  <p className="text-body-sm text-emerald-800 mt-0.5">
                    Silakan masukkan ID Anggota Anda untuk mencatat kehadiran pada kegiatan ini.
                  </p>
                </div>

                {presensiSuccess ? (
                  <div className="rounded-xl bg-white p-5 border border-emerald-300 text-center space-y-2 animate-in fade-in duration-300">
                    <span className="material-symbols-outlined text-[36px] text-emerald-600">check_circle</span>
                    <h4 className="text-body-md font-bold text-emerald-950">Presensi Berhasil Dicatat!</h4>
                    <p className="text-body-sm text-emerald-800">
                      Kehadiran Anda pada agenda <span className="font-semibold">{activity.title}</span> telah terverifikasi.
                    </p>
                  </div>
                ) : (
                  <form onSubmit={handlePresensiSubmit} className="space-y-3.5 bg-white p-5 rounded-xl border border-emerald-200 shadow-2xs">
                    {presensiError && <Alert variant="error">{presensiError}</Alert>}

                    <div>
                      <Label htmlFor="memberIdDetail">ID / Nomor Anggota</Label>
                      <div className="relative mt-1">
                        <Input
                          id="memberIdDetail"
                          type="text"
                          placeholder="Masukkan ID / UUID Anggota Anda"
                          value={memberId}
                          onChange={(e) => setMemberId(e.target.value)}
                          className="pl-10 text-body-sm"
                          required
                        />
                        <span className="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant text-[18px]">
                          badge
                        </span>
                      </div>
                    </div>

                    <div>
                      <Label htmlFor="qrTokenDetail">Token Sesi QR (Opsional jika disediakan Panitia)</Label>
                      <Input
                        id="qrTokenDetail"
                        type="text"
                        placeholder="Masukkan token dari layar QR Admin jika ada"
                        value={qrTokenInput}
                        onChange={(e) => setQrTokenInput(e.target.value)}
                        className="text-body-sm"
                      />
                    </div>

                    <Button
                      type="submit"
                      variant="primary"
                      loading={presensiLoading}
                      disabled={presensiLoading}
                      className="w-full py-2.5 text-body-sm font-bold shadow-xs bg-[#004b36] hover:bg-emerald-900"
                    >
                      <span className="material-symbols-outlined text-[18px] mr-1.5">send</span>
                      Kirim Absensi Kehadiran
                    </Button>
                  </form>
                )}
              </div>
            )}

            {/* Bottom Description Section */}
            <div className="space-y-3 pt-5 border-t border-outline/10">
              <h2 className="text-[22px] font-bold text-[#004b36] font-headline-sm">Deskripsi Kegiatan</h2>
              <p className="text-body-md text-on-surface leading-relaxed whitespace-pre-line">
                {activity.description ?? "Belum ada deskripsi rincian kegiatan."}
              </p>
            </div>
          </div>
        )}
      </article>
    </div>
  );
}
