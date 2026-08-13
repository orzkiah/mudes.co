"use client";

import { useState, useEffect } from "react";
import { useForm } from "react-hook-form";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { Textarea } from "@/components/ui/Textarea";
import { Button } from "@/components/ui/Button";
import { api } from "@/lib/api";
import { getApiErrorMessage } from "@/lib/query-client";
import { useToast } from "@/providers/ToastProvider";
import { getStoredMosques } from "@/lib/mosques-storage";
import type { MosqueLocation } from "@/types/models";

interface ContactFormValues {
  name: string;
  email: string;
  message: string;
  _honey?: string;
}

function getGoogleMapsDirectionsUrl(m: MosqueLocation): string {
  if (m.mapDirectUrl && m.mapDirectUrl.trim().startsWith("http")) {
    return m.mapDirectUrl.trim();
  }
  const query = encodeURIComponent(`${m.name}, ${m.address}`);
  return `https://www.google.com/maps/search/?api=1&query=${query}`;
}

function getGoogleMapsEmbedUrl(m: MosqueLocation): string {
  if (m.mapEmbedUrl && m.mapEmbedUrl.includes("output=embed")) {
    return m.mapEmbedUrl.trim();
  }
  if (m.mapEmbedUrl && m.mapEmbedUrl.includes("/maps/embed")) {
    return m.mapEmbedUrl.trim();
  }
  const query = encodeURIComponent(`${m.name}, ${m.address}`);
  return `https://maps.google.com/maps?q=${query}&z=15&output=embed`;
}

export function ContactPage() {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [mosques, setMosques] = useState<MosqueLocation[]>([]);
  const [selectedMosque, setSelectedMosque] = useState<MosqueLocation | null>(null);

  useEffect(() => {
    const list = getStoredMosques().filter((m) => m.isActive);
    setMosques(list);
    if (list.length > 0) {
      setSelectedMosque(list[0]);
    }
  }, []);

  const { register, handleSubmit, formState: { errors }, reset } = useForm<ContactFormValues>({
    defaultValues: { name: "", email: "", message: "" },
  });

  const onSubmit = async (values: ContactFormValues) => {
    if (values._honey) return;
    setLoading(true);
    try {
      await api.post("/public/contact", {
        name: values.name,
        email: values.email,
        message: values.message,
      });
      toast("Pesan Anda telah berhasil dikirim. Terima kasih!", "success");
      reset();
    } catch (err) {
      toast(getApiErrorMessage(err), "error");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="w-full max-w-full overflow-x-hidden min-h-screen bg-light-motif text-on-surface font-body-md selection:bg-amber-200 selection:text-emerald-950">

      {/* ── 1. Page Banner ── */}
      <section className="bg-islamic-pattern text-white border-b-4 border-amber-400/40 px-5 py-12 md:px-10 shadow-sm relative overflow-hidden w-full">
        <div className="max-w-container-max mx-auto relative z-10 space-y-3">
          <span className="inline-flex items-center gap-1.5 rounded-full bg-amber-400/20 border border-amber-400/40 px-4 py-1 text-label-sm uppercase tracking-[0.2em] font-bold text-amber-300">
            <span className="material-symbols-outlined text-[14px]">location_on</span>
            Peta Lokasi &amp; Kontak
          </span>
          <h1 className="text-[32px] sm:text-[42px] md:text-[48px] font-bold leading-tight text-white font-headline-sm">
            Lokasi Masjid &amp; Kontak Mudes Condet
          </h1>
          <p className="max-w-2xl text-body-md sm:text-body-lg text-white/90 leading-relaxed font-body-md">
            Temukan lokasi masjid, majelis taklim, serta pusat kegiatan pemuda pemudi Desa Condet. Silakan hubungi kami untuk informasi dan silaturahmi.
          </p>
        </div>
      </section>

      {/* ── 2. Interactive Map & Mosque List Section ── */}
      <section className="py-10 md:py-14 px-4 sm:px-6 md:px-10 max-w-container-max mx-auto space-y-8 sm:space-y-10 w-full overflow-hidden">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-outline/10 w-full">
          <div>
            <span className="text-label-sm uppercase tracking-wider font-bold text-amber-600">
              Peta Interaktif
            </span>
            <h2 className="text-[24px] sm:text-[34px] font-bold text-[#003828] font-headline-sm">
              Titik Lokasi Masjid &amp; Majelis
            </h2>
          </div>
          <span className="text-label-sm text-on-surface-variant font-medium">
            {mosques.length} Titik Terdaftar
          </span>
        </div>

        <div className="grid gap-6 sm:gap-8 lg:grid-cols-2 lg:items-start w-full min-w-0">

          {/* Interactive Google Map Embed Frame */}
          <div className="space-y-3 sm:space-y-4 w-full min-w-0 overflow-hidden">
            <div className="relative w-full h-[260px] sm:h-[320px] rounded-2xl sm:rounded-3xl overflow-hidden border-2 border-amber-400/40 shadow-md bg-slate-900">
              {selectedMosque ? (
                <iframe
                  key={selectedMosque.id}
                  src={getGoogleMapsEmbedUrl(selectedMosque)}
                  width="100%"
                  height="100%"
                  style={{ border: 0 }}
                  allowFullScreen
                  loading="lazy"
                  referrerPolicy="no-referrer-when-downgrade"
                  title={selectedMosque.name}
                  className="w-full h-full"
                />
              ) : (
                <div className="w-full h-full flex flex-col items-center justify-center text-amber-300 p-6 text-center">
                  <span className="material-symbols-outlined text-[48px] text-amber-400">map</span>
                  <p className="mt-2 text-body-md font-semibold text-white">Peta Lokasi Masjid Condet</p>
                </div>
              )}
            </div>

            {/* Currently Selected Mosque Highlight Bar */}
            {selectedMosque && (
              <div className="p-3.5 sm:p-4 rounded-2xl bg-white border border-amber-400/40 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 w-full min-w-0 overflow-hidden">
                <div className="space-y-0.5 min-w-0 flex-1 overflow-hidden">
                  <span className="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-amber-600 block">
                    Peta Aktif Saat Ini
                  </span>
                  <h4 className="text-body-md font-bold text-[#003828] font-headline-sm truncate">{selectedMosque.name}</h4>
                  <p className="text-body-sm text-on-surface-variant truncate">{selectedMosque.address}</p>
                </div>

                <a
                  href={getGoogleMapsDirectionsUrl(selectedMosque)}
                  target="_blank"
                  rel="noreferrer"
                  className="inline-flex items-center justify-center gap-1.5 rounded-full bg-gradient-to-r from-[#004b36] to-[#006046] px-4 py-2.5 sm:py-2 text-label-sm font-bold text-amber-300 hover:brightness-110 transition-all shrink-0 shadow-sm border border-amber-400/30 whitespace-nowrap w-full sm:w-auto text-center"
                >
                  <span className="material-symbols-outlined text-[16px]">directions</span>
                  Petunjuk Arah Google Maps
                </a>
              </div>
            )}
          </div>

          {/* Mosque List Cards */}
          <div className="space-y-3.5 sm:space-y-4 max-h-[380px] sm:max-h-[460px] overflow-y-auto pr-1 w-full min-w-0">
            <h3 className="text-body-sm sm:text-body-md font-bold text-[#003828] uppercase tracking-wider">
              Daftar Masjid &amp; Majelis ({mosques.length})
            </h3>

            {mosques.map((m) => {
              const isSelected = selectedMosque?.id === m.id;

              return (
                <div
                  key={m.id}
                  onClick={() => setSelectedMosque(m)}
                  className={`p-4 sm:p-5 rounded-2xl border transition-all cursor-pointer shadow-2xs w-full min-w-0 overflow-hidden ${
                    isSelected
                      ? "bg-white border-2 border-amber-500 shadow-md ring-2 ring-amber-400/20"
                      : "bg-white/80 border-outline/10 hover:border-amber-400/60 hover:bg-white"
                  }`}
                >
                  <div className="space-y-2 min-w-0">
                    <div className="flex items-start justify-between gap-2 min-w-0">
                      <h4 className="text-body-md sm:text-body-lg font-bold text-[#003828] font-headline-sm leading-snug truncate">
                        {m.name}
                      </h4>
                      {isSelected && (
                        <span className="rounded-full bg-amber-400 text-[#002d20] px-2 py-0.5 text-[10px] font-black uppercase tracking-wider shrink-0">
                          Aktif
                        </span>
                      )}
                    </div>

                    <p className="text-body-sm text-on-surface-variant flex items-start gap-1.5 leading-relaxed min-w-0">
                      <span className="material-symbols-outlined text-[15px] text-amber-600 shrink-0 mt-0.5">location_on</span>
                      <span className="line-clamp-2 min-w-0 break-words">{m.address}</span>
                    </p>

                    {m.contactName && (
                      <p className="text-label-sm text-on-surface-variant flex items-center gap-1.5 truncate">
                        <span className="material-symbols-outlined text-[14px] text-amber-600 shrink-0">person</span>
                        <span className="truncate">{m.contactName} {m.phone ? `(${m.phone})` : ""}</span>
                      </p>
                    )}

                    {m.notes && (
                      <p className="text-label-sm text-[#004b36] bg-[#004b36]/5 rounded-lg p-2 font-medium break-words">
                        📌 {m.notes}
                      </p>
                    )}

                    <div className="pt-2 flex flex-wrap items-center justify-between gap-2 border-t border-outline/10">
                      <button
                        type="button"
                        onClick={(e) => {
                          e.stopPropagation();
                          setSelectedMosque(m);
                        }}
                        className="text-label-sm font-bold text-[#004b36] hover:underline flex items-center gap-1"
                      >
                        <span className="material-symbols-outlined text-[14px]">map</span>
                        Tampilkan di Peta
                      </button>

                      <a
                        href={getGoogleMapsDirectionsUrl(m)}
                        target="_blank"
                        rel="noreferrer"
                        onClick={(e) => e.stopPropagation()}
                        className="inline-flex items-center gap-1 text-label-sm font-bold text-amber-600 hover:text-[#004b36] transition-colors"
                      >
                        <span className="material-symbols-outlined text-[14px]">open_in_new</span>
                        Buka Google Maps
                      </a>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>

        </div>
      </section>

      {/* ── 3. Contact Form Section ── */}
      <section className="py-10 md:py-14 px-4 sm:px-6 md:px-10 bg-white border-t border-outline/10 w-full overflow-hidden">
        <div className="max-w-4xl mx-auto space-y-8 w-full min-w-0">
          <div>
            <span className="text-label-sm uppercase tracking-[0.2em] font-bold text-[#004b36]">
              Hubungi Pengurus
            </span>
            <h2 className="mt-2 text-[28px] sm:text-[38px] font-bold text-[#003828] font-headline-sm">
              Sampaikan Pesan &amp; Pertanyaan
            </h2>
            <p className="mt-2 text-body-md text-on-surface-variant">
              Silakan kirimkan pesan, masukan, atau pertanyaan Anda. Tim pengurus Mudes Condet siap merespons.
            </p>
          </div>

          <form onSubmit={handleSubmit(onSubmit)} className="grid gap-5 w-full">
            <input type="text" {...register("_honey")} className="hidden" tabIndex={-1} autoComplete="off" />

            <div className="grid sm:grid-cols-2 gap-4 w-full">
              <div>
                <Label htmlFor="name">Nama Lengkap *</Label>
                <Input
                  id="name"
                  {...register("name", { required: "Nama wajib diisi" })}
                  placeholder="Nama lengkap Anda"
                  error={errors.name?.message}
                />
              </div>

              <div>
                <Label htmlFor="email">Email *</Label>
                <Input
                  id="email"
                  type="email"
                  {...register("email", { required: "Email wajib diisi" })}
                  placeholder="email@domain.com"
                  error={errors.email?.message}
                />
              </div>
            </div>

            <div>
              <Label htmlFor="message">Pesan Anda *</Label>
              <Textarea
                id="message"
                {...register("message", { required: "Pesan wajib diisi" })}
                placeholder="Tulis pesan, masukan, atau pertanyaan Anda..."
                error={errors.message?.message}
                className="min-h-[160px]"
              />
            </div>

            <div className="flex justify-end pt-2">
              <Button
                type="submit"
                loading={loading}
                className="rounded-full bg-gradient-to-r from-[#004b36] to-[#006046] px-8 py-3 text-label-md font-bold text-white shadow-xs hover:brightness-110 w-full sm:w-auto"
              >
                Kirim Pesan
              </Button>
            </div>
          </form>
        </div>
      </section>

    </div>
  );
}
