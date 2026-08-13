"use client";

import { useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { QueryProvider } from "@/providers/QueryProvider";

const navLinks = [
  { href: "/", label: "Beranda" },
  { href: "/structure", label: "Pengurus" },
  { href: "/schedule", label: "Jadwal" },
  { href: "/activities", label: "Kegiatan" },
  { href: "/articles", label: "Artikel" },
  { href: "/gallery", label: "Galeri" },
  { href: "/contact", label: "Kontak" },
];

export function PublicSiteLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const [mobileOpen, setMobileOpen] = useState(false);

  return (
    <QueryProvider>
      <div className="min-h-screen bg-light-motif text-on-surface font-body-md selection:bg-emerald-200 selection:text-emerald-950">
        {/* Navbar with glassmorphism & subtle gold gradient accent */}
        <header className="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-[#004b36]/10 shadow-2xs">
          <div className="mx-auto flex max-w-container-max items-center justify-between px-6 py-3 md:px-10">
            {/* Logo */}
            <Link href="/" className="flex items-center gap-2.5 group shrink-0">
              <img
                src="/logo-mudesco.png"
                alt="Generus MUDES.CO"
                className="h-10 sm:h-12 md:h-14 w-auto object-contain transition-transform group-hover:scale-105"
              />
            </Link>

            {/* Desktop Nav */}
            <nav className="hidden items-center gap-7 md:flex">
              {navLinks.map((link) => {
                const isActive = pathname === link.href || (link.href !== "/" && pathname.startsWith(link.href));
                return (
                  <Link
                    key={link.href}
                    href={link.href}
                    className={`text-[15px] transition-colors relative py-1 ${
                      isActive
                        ? "text-[#004b36] font-bold"
                        : "text-on-surface-variant hover:text-[#004b36]"
                    }`}
                  >
                    {link.label}
                    {isActive && (
                      <span className="absolute bottom-0 left-0 right-0 h-[2.5px] bg-gradient-to-r from-[#004b36] via-amber-400 to-[#004b36] rounded-full" />
                    )}
                  </Link>
                );
              })}
            </nav>

            {/* CTA + Mobile toggle */}
            <div className="flex items-center gap-3">
              <Link
                href="/contact"
                className="hidden sm:inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#004b36] to-[#006046] px-5 py-2.5 text-label-md font-bold text-white shadow-xs transition-all hover:brightness-110 active:scale-95 border border-amber-400/20"
              >
                Hubungi Kami
              </Link>
              <button
                onClick={() => setMobileOpen(!mobileOpen)}
                className="flex md:hidden items-center justify-center w-10 h-10 rounded-full bg-surface-container/60 hover:bg-surface-container text-[#004b36]"
                aria-label="Toggle menu"
                aria-expanded={mobileOpen}
              >
                <span className="material-symbols-outlined text-[22px]">
                  {mobileOpen ? "close" : "menu"}
                </span>
              </button>
            </div>
          </div>

          {/* Mobile Navigation Drawer */}
          {mobileOpen && (
            <div className="border-t border-outline/10 bg-white/98 backdrop-blur-lg px-6 py-5 md:hidden animate-in slide-in-from-top-2 duration-200">
              <nav className="flex flex-col gap-2">
                {navLinks.map((link) => {
                  const isActive = pathname === link.href || (link.href !== "/" && pathname.startsWith(link.href));
                  return (
                    <Link
                      key={link.href}
                      href={link.href}
                      onClick={() => setMobileOpen(false)}
                      className={`rounded-xl px-4 py-3 text-body-md transition-colors ${
                        isActive
                          ? "bg-[#004b36]/10 text-[#004b36] font-bold"
                          : "text-on-surface-variant hover:bg-surface-container"
                      }`}
                    >
                      {link.label}
                    </Link>
                  );
                })}
                <Link
                  href="/contact"
                  onClick={() => setMobileOpen(false)}
                  className="mt-3 flex items-center justify-center rounded-full bg-gradient-to-r from-[#004b36] to-[#006046] py-3 text-body-md font-semibold text-white shadow-xs"
                >
                  Hubungi Kami
                </Link>
              </nav>
            </div>
          )}
        </header>

        {/* Page content */}
        <main>{children}</main>

        {/* Footer — Clean White Background */}
        <footer className="bg-white text-on-surface border-t border-outline/15 relative overflow-hidden py-16 px-6 md:px-10">
          <div className="max-w-container-max mx-auto relative z-10 space-y-12">
            
            <div className="flex flex-col lg:flex-row justify-between gap-12 lg:gap-16">
              
              {/* Left Column — Brand Logo, Mission & Social Links */}
              <div className="flex flex-col gap-4 lg:w-1/3">
                <div className="flex items-center gap-3">
                  <Link href="/">
                    <img
                      src="/logo-mudesco.png"
                      alt="Generus MUDES.CO"
                      className="h-12 w-auto object-contain"
                    />
                  </Link>
                </div>

                <p className="text-body-sm text-on-surface-variant leading-relaxed font-body-sm max-w-sm">
                  Pemuda Pemudi Desa Condet. Wadah berkumpul, berdiskusi, merawat tradisi, dan membangun masa depan generasi penerus.
                </p>

                {/* Social Media Links (Instagram, TikTok, YouTube) */}
                <div className="flex items-center space-x-3 pt-1">
                  <a
                    href="https://instagram.com"
                    target="_blank"
                    rel="noreferrer"
                    aria-label="Instagram"
                    className="p-2.5 rounded-full bg-[#004b36]/10 text-[#004b36] hover:bg-[#004b36] hover:text-white transition-all shadow-2xs"
                    title="Instagram Mudes Condet"
                  >
                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                    </svg>
                  </a>

                  <a
                    href="https://tiktok.com"
                    target="_blank"
                    rel="noreferrer"
                    aria-label="TikTok"
                    className="p-2.5 rounded-full bg-[#004b36]/10 text-[#004b36] hover:bg-[#004b36] hover:text-white transition-all shadow-2xs"
                    title="TikTok Mudes Condet"
                  >
                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.82.57-1.31 1.57-1.3 2.56.01 1.23.73 2.37 1.84 2.87.97.45 2.12.37 3.02-.21.84-.54 1.34-1.5 1.35-2.5.02-4.71.01-9.42.01-14.13z" />
                    </svg>
                  </a>

                  <a
                    href="https://youtube.com"
                    target="_blank"
                    rel="noreferrer"
                    aria-label="YouTube"
                    className="p-2.5 rounded-full bg-[#004b36]/10 text-[#004b36] hover:bg-[#004b36] hover:text-white transition-all shadow-2xs"
                    title="YouTube Mudes Condet"
                  >
                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                    </svg>
                  </a>
                </div>
              </div>

              {/* Right Column — 3 Section Links Grid */}
              <div className="grid grid-cols-2 md:grid-cols-3 gap-8 lg:w-2/3">
                {/* Section 1 */}
                <div className="space-y-3">
                  <h3 className="font-bold text-[#004b36] uppercase tracking-wider text-label-md">Navigasi Utama</h3>
                  <ul className="space-y-2 text-body-sm text-on-surface-variant">
                    <li><Link href="/" className="hover:text-[#004b36] font-medium transition-colors">Beranda</Link></li>
                    <li><Link href="/structure" className="hover:text-[#004b36] font-medium transition-colors">Pengurus</Link></li>
                    <li><Link href="/schedule" className="hover:text-[#004b36] font-medium transition-colors">Jadwal Kajian</Link></li>
                    <li><Link href="/activities" className="hover:text-[#004b36] font-medium transition-colors">Agenda Kegiatan</Link></li>
                  </ul>
                </div>

                {/* Section 2 */}
                <div className="space-y-3">
                  <h3 className="font-bold text-[#004b36] uppercase tracking-wider text-label-md">Konten &amp; Galeri</h3>
                  <ul className="space-y-2 text-body-sm text-on-surface-variant">
                    <li><Link href="/articles" className="hover:text-[#004b36] font-medium transition-colors">Artikel &amp; Berita</Link></li>
                    <li><Link href="/gallery" className="hover:text-[#004b36] font-medium transition-colors">Dokumentasi Galeri</Link></li>
                    <li><Link href="/contact" className="hover:text-[#004b36] font-medium transition-colors">Peta Masjid Condet</Link></li>
                  </ul>
                </div>

                {/* Section 3 */}
                <div className="space-y-3 col-span-2 md:col-span-1">
                  <h3 className="font-bold text-[#004b36] uppercase tracking-wider text-label-md">Dukungan</h3>
                  <ul className="space-y-2 text-body-sm text-on-surface-variant">
                    <li><Link href="/contact" className="hover:text-[#004b36] font-medium transition-colors">Hubungi Pengurus</Link></li>
                  </ul>
                </div>
              </div>

            </div>

            {/* Bottom Bar — Copyright & Legal Links */}
            <div className="pt-8 flex flex-col md:flex-row justify-between items-center gap-4 border-t border-outline/10 text-body-sm text-on-surface-variant font-medium">
              <p className="order-2 md:order-1 text-center md:text-left">
                © 2026 Generus Mudes Condet (MUDES.CO). Hak Cipta Dilindungi.
              </p>
              <ul className="order-1 md:order-2 flex items-center gap-6">
                <li><Link href="/contact" className="hover:text-[#004b36] transition-colors">Peta Lokasi Masjid</Link></li>
              </ul>
            </div>

          </div>
        </footer>
      </div>
    </QueryProvider>
  );
}
