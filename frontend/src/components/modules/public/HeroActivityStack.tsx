"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import Image from "next/image";
import { motion, useMotionValue, useTransform, AnimatePresence } from "framer-motion";
import { formatDate } from "@/lib/dates";
import type { Activity } from "@/types/models";

const FALLBACK_IMAGES = [
  "/images/condet-mural.jpg",
  "/images/library-cover.jpg",
  "/images/book-cover.jpg",
];

interface HeroActivityStackProps {
  activities: Activity[];
}

function getDynamicStatusBadge(act: Activity) {
  if (!act.startAt) {
    return { label: "AKAN DATANG", style: "bg-amber-400/25 text-amber-300 border border-amber-400/40" };
  }

  const startDate = new Date(act.startAt);
  const endDate = act.endAt ? new Date(act.endAt) : new Date(startDate.getTime() + 2 * 60 * 60 * 1000);
  const now = new Date();

  if (now.getTime() > endDate.getTime()) {
    return { label: "SUDAH LEWAT", style: "bg-rose-600/90 text-white border border-rose-400/40" };
  }

  if (now.getTime() >= startDate.getTime() && now.getTime() <= endDate.getTime()) {
    return { label: "BERLANGSUNG", style: "bg-emerald-600 text-white border border-emerald-400 animate-pulse font-bold" };
  }

  // Calculate days until
  const diffMs = startDate.getTime() - now.getTime();
  const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

  if (diffDays <= 1) {
    return { label: "MULAI SEGERA", style: "bg-amber-400 text-[#002d20] font-black shadow-xs" };
  }

  return { label: `H-${diffDays} HARI`, style: "bg-amber-400/25 text-amber-300 border border-amber-400/40 font-bold" };
}

export function HeroActivityStack({ activities }: HeroActivityStackProps) {
  // Take up to max 5 activities
  const initialStack = activities.slice(0, 5);

  const [cards, setCards] = useState<Activity[]>(initialStack);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [dragDirection, setDragDirection] = useState<"up" | "down" | null>(null);

  // Sync state if activities prop changes
  useEffect(() => {
    if (activities.length > 0) {
      setCards(activities.slice(0, 5));
      setCurrentIndex(0);
    }
  }, [activities]);

  const dragY = useMotionValue(0);
  const rotateX = useTransform(dragY, [-200, 0, 200], [12, 0, -12]);

  const offset = 12;
  const scaleStep = 0.05;
  const dimStep = 0.12;
  const swipeThreshold = 50;

  const spring = {
    type: "spring" as const,
    stiffness: 180,
    damping: 24,
  };

  const moveToEnd = () => {
    if (cards.length <= 1) return;
    setCards((prev) => [...prev.slice(1), prev[0]]);
    setCurrentIndex((prev) => (prev + 1) % cards.length);
  };

  const moveToStart = () => {
    if (cards.length <= 1) return;
    setCards((prev) => [prev[prev.length - 1], ...prev.slice(0, -1)]);
    setCurrentIndex((prev) => (prev - 1 + cards.length) % cards.length);
  };

  const handleDragEnd = (_: unknown, info: { velocity: { y: number }; offset: { y: number } }) => {
    const velocity = info.velocity.y;
    const offsetVal = info.offset.y;

    if (Math.abs(offsetVal) > swipeThreshold || Math.abs(velocity) > 500) {
      if (offsetVal < 0 || velocity < 0) {
        setDragDirection("up");
        setTimeout(() => {
          moveToEnd();
          setDragDirection(null);
        }, 120);
      } else {
        setDragDirection("down");
        setTimeout(() => {
          moveToStart();
          setDragDirection(null);
        }, 120);
      }
    }
    dragY.set(0);
  };

  if (!cards.length) {
    return (
      <div className="relative h-[360px] sm:h-[440px] w-full rounded-3xl overflow-hidden shadow-sm bg-[#004b36]/20 border border-amber-400/30 flex items-center justify-center text-amber-300">
        <div className="text-center space-y-2 p-6">
          <span className="material-symbols-outlined text-[48px] text-amber-400/80">event</span>
          <p className="text-body-md font-semibold text-white">Belum Ada Agenda Komunitas</p>
        </div>
      </div>
    );
  }

  return (
    <div className="relative w-full h-[380px] sm:h-[460px] lg:h-[500px] flex flex-col items-center justify-center select-none">
      {/* Top Header Dots / Navigation — Auto Contrast Color (Bright Amber Gold) */}
      <div className="absolute -top-7 left-0 right-0 flex items-center justify-between z-30 px-2">
        <span className="text-label-sm font-bold text-amber-300 tracking-wide uppercase flex items-center gap-2 drop-shadow-xs">
          <span className="inline-block w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse" />
          Agenda Terdekat ({currentIndex + 1}/{cards.length})
        </span>

        {/* Indicator dots */}
        <div className="flex items-center gap-1.5">
          {cards.map((_, idx) => (
            <button
              key={idx}
              onClick={() => {
                if (idx !== 0) {
                  const newStack = [...cards.slice(idx), ...cards.slice(0, idx)];
                  setCards(newStack);
                  setCurrentIndex((currentIndex + idx) % cards.length);
                }
              }}
              className={`h-2.5 rounded-full transition-all duration-300 ${
                idx === 0 ? "w-7 bg-amber-400 shadow-xs" : "w-2.5 bg-white/40 hover:bg-white/70"
              }`}
              aria-label={`Switch to activity ${idx + 1}`}
            />
          ))}
        </div>
      </div>

      {/* Stack Navigation Arrows */}
      {cards.length > 1 && (
        <>
          <motion.button
            onClick={moveToStart}
            className="absolute left-3 top-1/2 -translate-y-1/2 p-2.5 rounded-full bg-white/95 text-[#003828] shadow-md border border-amber-400/30 backdrop-blur-md z-40 hover:bg-[#004b36] hover:text-white transition-colors flex items-center justify-center"
            whileHover={{ scale: 1.1, x: -2 }}
            whileTap={{ scale: 0.9 }}
            aria-label="Previous Activity"
          >
            <span className="material-symbols-outlined text-[22px]">chevron_left</span>
          </motion.button>

          <motion.button
            onClick={moveToEnd}
            className="absolute right-3 top-1/2 -translate-y-1/2 p-2.5 rounded-full bg-white/95 text-[#003828] shadow-md border border-amber-400/30 backdrop-blur-md z-40 hover:bg-[#004b36] hover:text-white transition-colors flex items-center justify-center"
            whileHover={{ scale: 1.1, x: 2 }}
            whileTap={{ scale: 0.9 }}
            aria-label="Next Activity"
          >
            <span className="material-symbols-outlined text-[22px]">chevron_right</span>
          </motion.button>
        </>
      )}

      {/* Card Stack List */}
      <div className="relative w-full h-full">
        <AnimatePresence>
          {cards.map((act, i) => {
            const isFront = i === 0;
            const brightness = Math.max(0.4, 1 - i * dimStep);
            const baseZ = cards.length - i;
            const coverUrl = act.cover?.url || FALLBACK_IMAGES[i % FALLBACK_IMAGES.length];
            const badge = getDynamicStatusBadge(act);

            return (
              <motion.div
                key={act.id}
                className="absolute inset-0 w-full h-full rounded-3xl overflow-hidden border border-amber-400/30 shadow-md bg-white cursor-grab active:cursor-grabbing"
                style={{
                  boxShadow: isFront
                    ? "0 20px 40px -10px rgba(0, 0, 0, 0.4)"
                    : "0 10px 20px -5px rgba(0, 0, 0, 0.2)",
                  rotateX: isFront ? rotateX : 0,
                  transformPerspective: 1000,
                  touchAction: "none",
                }}
                animate={{
                  top: `${i * -offset}px`,
                  scale: 1 - i * scaleStep,
                  filter: `brightness(${brightness})`,
                  zIndex: baseZ,
                  opacity: dragDirection && isFront ? 0 : 1,
                }}
                exit={{
                  opacity: 0,
                  scale: 0.85,
                  transition: { duration: 0.2 },
                }}
                transition={spring}
                drag={isFront ? "y" : false}
                dragConstraints={{ top: 0, bottom: 0 }}
                dragElastic={0.6}
                onDrag={(_, info) => {
                  if (isFront) dragY.set(info.offset.y);
                }}
                onDragEnd={handleDragEnd}
                whileDrag={
                  isFront
                    ? {
                        zIndex: cards.length + 10,
                        scale: 1.02,
                      }
                    : {}
                }
              >
                {/* Background Cover Image */}
                <div className="relative w-full h-full">
                  <Image
                    src={coverUrl}
                    alt={act.title}
                    fill
                    className="object-cover pointer-events-none select-none"
                    priority={isFront}
                    draggable={false}
                  />

                  {/* Gradient Overlay & Islamic Geometric Motif for Legibility */}
                  <div className="absolute inset-0 bg-gradient-to-t from-[#002d20] via-[#004b36]/70 to-transparent opacity-95" />
                  <div className="absolute inset-0 bg-islamic-pattern opacity-25 pointer-events-none mix-blend-overlay" />

                  {/* Top Badges */}
                  <div className="absolute top-4 left-4 right-4 flex items-center justify-between z-10 pointer-events-none">
                    <span className="rounded-full bg-[#004b36]/90 border border-[#f9c74f]/50 px-3.5 py-1 text-label-sm font-bold text-[#f9c74f] shadow-xs">
                      {act.category?.name ?? "KEGIATAN"}
                    </span>
                    <span className={`rounded-full backdrop-blur-md px-3.5 py-1 text-[11px] uppercase tracking-wider ${badge.style}`}>
                      {badge.label}
                    </span>
                  </div>

                  {/* Bottom Content Overlay */}
                  <div className="absolute bottom-0 left-0 right-0 p-5 sm:p-7 text-white z-20 flex flex-col justify-end">
                    <div className="flex items-center gap-2 text-label-sm text-amber-300 font-semibold mb-1">
                      <span className="material-symbols-outlined text-[16px] text-amber-400">event</span>
                      <span>{formatDate(act.startAt)}</span>
                      {act.location && (
                        <>
                          <span>•</span>
                          <span className="material-symbols-outlined text-[16px] text-amber-400">location_on</span>
                          <span className="truncate">{act.location}</span>
                        </>
                      )}
                    </div>

                    <h3 className="text-[22px] sm:text-[26px] font-bold leading-tight font-headline-sm text-white line-clamp-2 drop-shadow-sm">
                      {act.title}
                    </h3>

                    {act.description && (
                      <p className="mt-2 text-body-sm text-white/80 line-clamp-2 leading-relaxed">
                        {act.description}
                      </p>
                    )}

                    {/* Action Button Link */}
                    <div className="mt-4 pt-3 border-t border-white/20 flex items-center justify-between">
                      <Link
                        href={`/activities/${act.slug}`}
                        className="inline-flex items-center gap-2 rounded-full bg-[#f9c74f] px-4 py-2 text-body-sm font-bold text-[#003828] hover:bg-white transition-colors shadow-sm"
                        onClick={(e) => e.stopPropagation()}
                      >
                        Lihat Detail Agenda
                        <span className="material-symbols-outlined text-[18px]">arrow_forward</span>
                      </Link>

                      <span className="text-[12px] text-white/70 font-medium hidden sm:inline-block">
                        ↕ Geser untuk lihat agenda lain
                      </span>
                    </div>
                  </div>
                </div>
              </motion.div>
            );
          })}
        </AnimatePresence>
      </div>

      {/* Gold Accent Bar on Right Edge */}
      <div className="absolute -right-3 top-1/2 -translate-y-1/2 h-28 w-2 bg-gradient-to-b from-amber-300 via-amber-400 to-amber-500 rounded-full hidden lg:block shadow-sm z-30" />
    </div>
  );
}
