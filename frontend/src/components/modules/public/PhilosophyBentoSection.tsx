"use client";

import { useState, useEffect } from "react";
import { clsx } from "clsx";
import { motion } from "framer-motion";
import { getStoredPhilosophyQuotes, POSITION_LABELS, type PhilosophyQuote } from "@/lib/philosophy-storage";

export function PhilosophyBentoSection() {
  const [quotes, setQuotes] = useState<PhilosophyQuote[]>([]);

  useEffect(() => {
    setQuotes(getStoredPhilosophyQuotes());
  }, []);

  // Filter only active quotes and sort by position 1..5
  const activeQuotes = quotes
    .filter((q) => q.isActive)
    .sort((a, b) => a.position - b.position);

  // Map active quotes by position 1..5 for fast lookup
  const quoteByPos = new Map<number, PhilosophyQuote>();
  activeQuotes.forEach((q) => quoteByPos.set(q.position, q));

  return (
    <section className="py-24 px-6 md:px-10 bg-gradient-to-br from-[#fbf8f1] via-[#f5eee1] to-[#ece2ce] text-[#002d20] relative overflow-hidden border-b-2 border-amber-400/40 shadow-xs">
      {/* Subtle warm glow accents */}
      <div className="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-amber-400/15 blur-3xl pointer-events-none" />
      <div className="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-amber-300/20 blur-3xl pointer-events-none" />

      <div className="max-w-container-max mx-auto relative z-10">
        
        {/* Header Title & Subtitle */}
        <div className="max-w-3xl space-y-3 mb-12">
          <span className="inline-flex items-center gap-2 rounded-full bg-[#004b36] border border-amber-300/40 px-4 py-1 text-label-sm font-bold uppercase tracking-widest text-amber-300 shadow-xs">
            <span className="material-symbols-outlined text-[16px] text-amber-400">format_quote</span>
            Filosofi &amp; Mutiara Hikmah
          </span>

          <h2 className="text-[36px] sm:text-[48px] font-bold tracking-tight text-[#002d20] font-headline-sm leading-tight">
            Nilai Perjuangan &amp; Quotes MUDES.CO
          </h2>

          <p className="text-[18px] sm:text-[20px] font-medium leading-relaxed text-[#003828]/85">
            Pondasi moral, tradisi, dan cita-cita yang menuntun langkah Generasi Muda Condet dalam merawat kebersamaan dan kemajuan.
          </p>
        </div>

        {/* ── BENTO GRID LAYOUT ── */}
        <div className="grid grid-cols-1 gap-5 sm:gap-6 lg:grid-cols-6">
          {[1, 2, 3, 4, 5].map((posNum) => {
            const item = quoteByPos.get(posNum);
            const posConfig = POSITION_LABELS[posNum];

            if (!item) return null;

            return (
              <BentoCard
                key={item.id || posNum}
                eyebrow={item.eyebrow}
                title={item.title}
                description={item.quote}
                author={item.author}
                graphic={
                  <div
                    className="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105"
                    style={{ backgroundImage: `url('${item.imageUrl}')` }}
                  />
                }
                className={posConfig.gridClass}
              />
            );
          })}
        </div>

      </div>
    </section>
  );
}

export function BentoCard({
  dark = false,
  className = "",
  eyebrow,
  title,
  description,
  author,
  graphic,
  fade = ["bottom"],
}: {
  dark?: boolean;
  className?: string;
  eyebrow: React.ReactNode;
  title: React.ReactNode;
  description: React.ReactNode;
  author?: string;
  graphic?: React.ReactNode;
  fade?: ("top" | "bottom")[];
}) {
  return (
    <motion.div
      initial="idle"
      whileHover="active"
      variants={{ idle: {}, active: {} }}
      data-dark={dark ? "true" : undefined}
      className={clsx(
        className,
        "group relative flex flex-col overflow-hidden rounded-3xl min-h-[380px] border-2 border-amber-400/40 bg-white shadow-md transition-all duration-300 hover:shadow-xl hover:border-amber-500"
      )}
    >
      {/* Background Graphic Overlay */}
      <div className="relative h-[24rem] shrink-0 overflow-hidden">
        {graphic}
        {/* Soft Cream Gradient Overlay for Readability */}
        <div className="absolute inset-0 bg-gradient-to-t from-[#002d20] via-[#002d20]/50 to-transparent opacity-85" />
        
        {fade.includes("top") && (
          <div className="absolute inset-0 bg-gradient-to-b from-black/60 to-transparent opacity-50" />
        )}
        {fade.includes("bottom") && (
          <div className="absolute inset-0 bg-gradient-to-t from-[#002d20] to-transparent opacity-90" />
        )}
      </div>

      {/* Content Container */}
      <div className="relative p-6 sm:p-8 z-20 isolate mt-[-130px] flex-1 flex flex-col justify-end text-white backdrop-blur-md bg-[#002d20]/90 border-t border-amber-400/30">
        <span className="inline-flex items-center gap-1.5 rounded-full bg-[#004b36] border border-amber-300/50 px-3 py-0.5 text-[11px] font-bold uppercase tracking-widest text-amber-300 w-fit mb-2 shadow-2xs">
          {eyebrow}
        </span>

        <h3 className="text-[20px] sm:text-[22px] font-bold leading-snug tracking-tight text-white group-hover:text-amber-300 transition-colors">
          {title}
        </h3>

        <p className="mt-2 text-body-sm sm:text-body-md text-amber-100/90 leading-relaxed italic line-clamp-3">
          &ldquo;{description}&rdquo;
        </p>

        {author && (
          <p className="mt-3 text-label-sm font-bold text-amber-400 flex items-center gap-1.5 pt-2 border-t border-white/15">
            <span className="material-symbols-outlined text-[14px]">history_edu</span>
            {author}
          </p>
        )}
      </div>
    </motion.div>
  );
}
