"use client";

import { useState, useEffect } from "react";
import { Button } from "@/components/ui/Button";
import { Modal } from "@/components/ui/Modal";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { Textarea } from "@/components/ui/Textarea";
import { Select } from "@/components/ui/Select";
import { MediaReferenceField } from "@/components/ui/MediaReferenceField";
import {
  getStoredPhilosophyQuotes,
  saveStoredPhilosophyQuotes,
  POSITION_LABELS,
  type PhilosophyQuote,
} from "@/lib/philosophy-storage";

export default function AdminPhilosophyPage() {
  const [quotes, setQuotes] = useState<PhilosophyQuote[]>([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState<PhilosophyQuote | null>(null);

  // Form State
  const [position, setPosition] = useState<number>(1);
  const [eyebrow, setEyebrow] = useState("");
  const [title, setTitle] = useState("");
  const [quoteText, setQuoteText] = useState("");
  const [author, setAuthor] = useState("");
  const [imageUrl, setImageUrl] = useState("/images/library-cover.jpg");
  const [isActive, setIsActive] = useState(true);

  useEffect(() => {
    setQuotes(getStoredPhilosophyQuotes());
  }, []);

  const openCreateModal = () => {
    setEditingItem(null);
    // Find next available unused position 1..5 if possible
    const usedPositions = new Set(quotes.map((q) => q.position));
    let nextPos = 1;
    for (let p = 1; p <= 5; p++) {
      if (!usedPositions.has(p)) {
        nextPos = p;
        break;
      }
    }
    setPosition(nextPos);
    setEyebrow("Filosofi Condet");
    setTitle("");
    setQuoteText("");
    setAuthor("Sesanti Sepuh Condet");
    setImageUrl("/images/library-cover.jpg");
    setIsActive(true);
    setIsModalOpen(true);
  };

  const openEditModal = (item: PhilosophyQuote) => {
    setEditingItem(item);
    setPosition(item.position);
    setEyebrow(item.eyebrow);
    setTitle(item.title);
    setQuoteText(item.quote);
    setAuthor(item.author || "");
    setImageUrl(item.imageUrl || "/images/library-cover.jpg");
    setIsActive(item.isActive);
    setIsModalOpen(true);
  };

  const handleDelete = (id: string) => {
    if (!confirm("Apakah Anda yakin ingin menghapus quote filosofi ini?")) return;
    const updated = quotes.filter((q) => q.id !== id);
    setQuotes(updated);
    saveStoredPhilosophyQuotes(updated);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();

    if (!title.trim() || !quoteText.trim()) {
      alert("Judul dan Teks Quote wajib diisi.");
      return;
    }

    let updatedList: PhilosophyQuote[];

    if (editingItem) {
      updatedList = quotes.map((q) =>
        q.id === editingItem.id
          ? {
              ...q,
              position: Number(position),
              eyebrow: eyebrow.trim() || "Filosofi",
              title: title.trim(),
              quote: quoteText.trim(),
              author: author.trim() || undefined,
              imageUrl: imageUrl || "/images/library-cover.jpg",
              isActive,
            }
          : q
      );
    } else {
      const newItem: PhilosophyQuote = {
        id: `phil-${Date.now()}`,
        position: Number(position),
        eyebrow: eyebrow.trim() || "Filosofi",
        title: title.trim(),
        quote: quoteText.trim(),
        author: author.trim() || undefined,
        imageUrl: imageUrl || "/images/library-cover.jpg",
        isActive,
        createdAt: new Date().toISOString(),
      };
      updatedList = [...quotes, newItem];
    }

    setQuotes(updatedList);
    saveStoredPhilosophyQuotes(updatedList);
    setIsModalOpen(false);
  };

  // Map quotes by position 1..5 for table & diagram
  const sortedQuotes = [...quotes].sort((a, b) => a.position - b.position);

  return (
    <div className="p-6 md:p-10 space-y-8 bg-surface-container-lowest min-h-screen text-on-surface">
      
      {/* ── Page Header ── */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-outline/10 pb-6">
        <div>
          <span className="inline-flex items-center gap-1.5 rounded-full bg-[#004b36]/10 px-3.5 py-1 text-label-sm font-bold text-[#004b36] uppercase tracking-wider mb-2">
            <span className="material-symbols-outlined text-[14px]">auto_awesome</span>
            Bento Grid Layout Showcase
          </span>
          <h1 className="text-[28px] sm:text-[34px] font-bold text-[#004b36] font-headline-sm">
            Kelola Filosofi &amp; Quotes Bento Grid
          </h1>
          <p className="text-body-md text-on-surface-variant mt-1 max-w-2xl">
            Kelola mutiara hikmah, petuah sepuh, dan nilai perjuangan MUDES.CO yang tampil cantik pada layout Bento Grid 5 kartu di Halaman Beranda.
          </p>
        </div>

        <Button
          onClick={openCreateModal}
          className="bg-[#004b36] text-white hover:bg-[#003828] font-bold shadow-xs shrink-0"
        >
          <span className="material-symbols-outlined mr-1.5 text-[20px]">add</span>
          Tambah Quote Baru
        </Button>
      </div>

      {/* ── Diagram Layout Penanda Posisi Bento Grid ── */}
      <div className="rounded-2xl border border-emerald-900/15 bg-emerald-900/5 p-6 space-y-4 shadow-2xs">
        <div className="flex items-center justify-between">
          <h3 className="font-bold text-[#004b36] text-body-lg flex items-center gap-2">
            <span className="material-symbols-outlined text-[22px]">grid_view</span>
            Peta Penanda Posisi Tabel Bento Grid (1 s/d 5)
          </h3>
          <span className="text-label-sm font-semibold text-[#004b36] bg-emerald-100 border border-emerald-200 px-3 py-0.5 rounded-full">
            5 Posisi Kartu
          </span>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-6 gap-3 pt-1">
          <div className="md:col-span-3 rounded-xl border-2 border-emerald-500/40 bg-white p-3 shadow-2xs">
            <span className="text-label-sm font-bold text-[#004b36] block mb-1">
              📍 Posisi 1 (Kiri Atas - Grid 3 Kolom)
            </span>
            <p className="text-label-sm text-on-surface-variant truncate">
              {sortedQuotes.find((q) => q.position === 1)?.title || "Kosong"}
            </p>
          </div>

          <div className="md:col-span-3 rounded-xl border-2 border-emerald-500/40 bg-white p-3 shadow-2xs">
            <span className="text-label-sm font-bold text-[#004b36] block mb-1">
              📍 Posisi 2 (Kanan Atas - Grid 3 Kolom)
            </span>
            <p className="text-label-sm text-on-surface-variant truncate">
              {sortedQuotes.find((q) => q.position === 2)?.title || "Kosong"}
            </p>
          </div>

          <div className="md:col-span-2 rounded-xl border border-emerald-500/30 bg-white p-3 shadow-2xs">
            <span className="text-label-sm font-bold text-[#004b36] block mb-1">
              📍 Posisi 3 (Bawah Kiri)
            </span>
            <p className="text-label-sm text-on-surface-variant truncate">
              {sortedQuotes.find((q) => q.position === 3)?.title || "Kosong"}
            </p>
          </div>

          <div className="md:col-span-2 rounded-xl border border-emerald-500/30 bg-white p-3 shadow-2xs">
            <span className="text-label-sm font-bold text-[#004b36] block mb-1">
              📍 Posisi 4 (Bawah Tengah)
            </span>
            <p className="text-label-sm text-on-surface-variant truncate">
              {sortedQuotes.find((q) => q.position === 4)?.title || "Kosong"}
            </p>
          </div>

          <div className="md:col-span-2 rounded-xl border border-emerald-500/30 bg-white p-3 shadow-2xs">
            <span className="text-label-sm font-bold text-[#004b36] block mb-1">
              📍 Posisi 5 (Bawah Kanan)
            </span>
            <p className="text-label-sm text-on-surface-variant truncate">
              {sortedQuotes.find((q) => q.position === 5)?.title || "Kosong"}
            </p>
          </div>
        </div>
      </div>

      {/* ── Table List of Quotes ── */}
      <div className="rounded-2xl border border-outline/10 bg-white shadow-2xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-body-sm border-collapse">
            <thead>
              <tr className="bg-surface-container/50 border-b border-outline/10 text-on-surface font-bold">
                <th className="py-4 px-5 w-44">Posisi Grid</th>
                <th className="py-4 px-5 w-24">Gambar</th>
                <th className="py-4 px-5">Tag &amp; Judul Quote</th>
                <th className="py-4 px-5">Isi Mutiara Hikmah</th>
                <th className="py-4 px-5 w-28">Status</th>
                <th className="py-4 px-5 w-28 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-outline/10">
              {sortedQuotes.length === 0 ? (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-on-surface-variant">
                    Belum ada quote filosofi. Klik <strong>Tambah Quote Baru</strong> di atas untuk membuat.
                  </td>
                </tr>
              ) : (
                sortedQuotes.map((q) => (
                  <tr key={q.id} className="hover:bg-surface-container/20 transition-colors">
                    <td className="py-4 px-5">
                      <span className="inline-flex items-center gap-1 rounded-full bg-[#004b36]/10 border border-[#004b36]/20 px-3 py-1 text-label-sm font-bold text-[#004b36]">
                        <span className="material-symbols-outlined text-[14px]">grid_view</span>
                        Posisi #{q.position}
                      </span>
                    </td>
                    <td className="py-4 px-5">
                      <div className="h-14 w-14 rounded-xl overflow-hidden border border-outline/10 bg-slate-900 shadow-2xs shrink-0">
                        <img src={q.imageUrl} alt={q.title} className="w-full h-full object-cover" />
                      </div>
                    </td>
                    <td className="py-4 px-5 space-y-1">
                      <span className="text-[11px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                        {q.eyebrow}
                      </span>
                      <p className="font-bold text-on-surface leading-snug">{q.title}</p>
                      {q.author && (
                        <p className="text-label-sm text-on-surface-variant font-medium">— {q.author}</p>
                      )}
                    </td>
                    <td className="py-4 px-5 text-on-surface-variant max-w-sm">
                      <p className="line-clamp-2 italic leading-relaxed">&ldquo;{q.quote}&rdquo;</p>
                    </td>
                    <td className="py-4 px-5">
                      <span
                        className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-label-sm font-bold ${
                          q.isActive
                            ? "bg-emerald-100 text-emerald-800 border border-emerald-200"
                            : "bg-slate-100 text-slate-600 border border-slate-200"
                        }`}
                      >
                        {q.isActive ? "Aktif" : "Nonaktif"}
                      </span>
                    </td>
                    <td className="py-4 px-5 text-right space-x-2">
                      <button
                        type="button"
                        onClick={() => openEditModal(q)}
                        className="p-1.5 rounded-lg text-primary hover:bg-primary/10 transition-colors"
                        title="Edit Quote"
                      >
                        <span className="material-symbols-outlined text-[18px]">edit</span>
                      </button>
                      <button
                        type="button"
                        onClick={() => handleDelete(q.id)}
                        className="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors"
                        title="Hapus Quote"
                      >
                        <span className="material-symbols-outlined text-[18px]">delete</span>
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* ── Modal Form Create / Edit ── */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={editingItem ? "Edit Quote Bento Grid" : "Tambah Quote Bento Grid Baru"}
        closeOnOutsideClick={false}
      >
        <form onSubmit={handleSave} className="space-y-4">
          
          {/* Select Posisi Bento Grid */}
          <div>
            <Label htmlFor="position" className="font-bold text-[#004b36]">
              Posisi Tampilan Bento Grid (1 s/d 5) *
            </Label>
            <Select
              id="position"
              value={position.toString()}
              onChange={(e) => setPosition(Number(e.target.value))}
              options={[
                { value: "1", label: "Posisi 1: Kiri Atas (Grid 3 Kolom)" },
                { value: "2", label: "Posisi 2: Kanan Atas (Grid 3 Kolom)" },
                { value: "3", label: "Posisi 3: Bawah Kiri (Baris Kedua #1)" },
                { value: "4", label: "Posisi 4: Bawah Tengah (Baris Kedua #2)" },
                { value: "5", label: "Posisi 5: Bawah Kanan (Baris Kedua #3)" },
              ]}
            />
            <p className="text-label-sm text-on-surface-variant mt-1">
              {POSITION_LABELS[position]?.desc}
            </p>
          </div>

          <div>
            <Label htmlFor="eyebrow">Tag Kategori / Eyebrow *</Label>
            <Input
              id="eyebrow"
              value={eyebrow}
              onChange={(e) => setEyebrow(e.target.value)}
              placeholder="Contoh: Merawat Tradisi / Ukhuwah / Ilmu & Akhlak"
            />
          </div>

          <div>
            <Label htmlFor="title">Judul Quote / Sub-Judul *</Label>
            <Input
              id="title"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="Contoh: Menjaga Keturunan & Budaya Condet"
            />
          </div>

          <div>
            <Label htmlFor="quoteText">Isi Teks Quote &amp; Mutiara Hikmah *</Label>
            <Textarea
              id="quoteText"
              value={quoteText}
              onChange={(e) => setQuoteText(e.target.value)}
              rows={4}
              placeholder="Tuliskan petuah, kata mutiara hikmah, atau quotes perjuangan di sini..."
            />
          </div>

          <div>
            <Label htmlFor="author">Tokoh / Penulis / Sumber Quote (Opsional)</Label>
            <Input
              id="author"
              value={author}
              onChange={(e) => setAuthor(e.target.value)}
              placeholder="Contoh: Sesanti Sepuh Condet / Ustadz H. Ahmad"
            />
          </div>

          {/* Image Background Upload Field */}
          <MediaReferenceField
            collection="article-cover"
            label="Gambar Background Card Quote"
            onMediaChange={(id, url) => {
              if (url) {
                setImageUrl(url);
              }
            }}
          />

          <div className="flex items-center gap-3 pt-2">
            <input
              type="checkbox"
              id="isActive"
              checked={isActive}
              onChange={(e) => setIsActive(e.target.checked)}
              className="h-4 w-4 rounded border-outline/30 text-[#004b36] focus:ring-[#004b36]"
            />
            <Label htmlFor="isActive" className="cursor-pointer font-bold">
              Tampilkan Quote Ini di Halaman Publik
            </Label>
          </div>

          <div className="flex justify-end gap-3 pt-4 border-t border-outline/10">
            <Button type="button" variant="outline" onClick={() => setIsModalOpen(false)}>
              Batal
            </Button>
            <Button type="submit" className="bg-[#004b36] text-white hover:bg-[#003828]">
              Simpan Quote
            </Button>
          </div>
        </form>
      </Modal>

    </div>
  );
}
