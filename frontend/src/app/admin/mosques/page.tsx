"use client";

import { useState, useEffect } from "react";
import { getStoredMosques, saveStoredMosques } from "@/lib/mosques-storage";
import type { MosqueLocation } from "@/types/models";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { Textarea } from "@/components/ui/Textarea";
import { Modal } from "@/components/ui/Modal";
import { Badge } from "@/components/ui/Badge";
import { useToast } from "@/providers/ToastProvider";

export default function AdminMosquesPage() {
  const { toast } = useToast();
  const [mosques, setMosques] = useState<MosqueLocation[]>([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingMosque, setEditingMosque] = useState<MosqueLocation | null>(null);

  // Form State
  const [formData, setFormData] = useState<Omit<MosqueLocation, "id">>({
    name: "",
    address: "",
    contactName: "",
    phone: "",
    mapEmbedUrl: "",
    mapDirectUrl: "",
    notes: "",
    isActive: true,
  });

  useEffect(() => {
    setMosques(getStoredMosques());
  }, []);

  const handleOpenAdd = () => {
    setEditingMosque(null);
    setFormData({
      name: "",
      address: "",
      contactName: "",
      phone: "",
      mapEmbedUrl: "https://maps.google.com/maps?q=-6.2789,106.8570&z=15&output=embed",
      mapDirectUrl: "https://maps.google.com/?q=-6.2789,106.8570",
      notes: "",
      isActive: true,
    });
    setIsModalOpen(true);
  };

  const handleOpenEdit = (mosque: MosqueLocation) => {
    setEditingMosque(mosque);
    setFormData({
      name: mosque.name,
      address: mosque.address,
      contactName: mosque.contactName || "",
      phone: mosque.phone || "",
      mapEmbedUrl: mosque.mapEmbedUrl || "",
      mapDirectUrl: mosque.mapDirectUrl || "",
      notes: mosque.notes || "",
      isActive: mosque.isActive,
    });
    setIsModalOpen(true);
  };

  const handleDelete = (id: string) => {
    if (!confirm("Apakah Anda yakin ingin menghapus titik lokasi masjid ini?")) return;
    const updated = mosques.filter((m) => m.id !== id);
    setMosques(updated);
    saveStoredMosques(updated);
    toast("Lokasi masjid berhasil dihapus", "success");
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.name.trim() || !formData.address.trim()) {
      toast("Nama dan Alamat masjid wajib diisi", "error");
      return;
    }

    let updated: MosqueLocation[];
    if (editingMosque) {
      updated = mosques.map((m) =>
        m.id === editingMosque.id ? { ...formData, id: editingMosque.id } : m
      );
      toast("Lokasi masjid berhasil diperbarui", "success");
    } else {
      const newMosque: MosqueLocation = {
        ...formData,
        id: `mosque-${Date.now()}`,
      };
      updated = [newMosque, ...mosques];
      toast("Lokasi masjid baru berhasil ditambahkan", "success");
    }

    setMosques(updated);
    saveStoredMosques(updated);
    setIsModalOpen(false);
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-headline-md font-headline-md text-[#004b36] font-bold flex items-center gap-2">
            <span className="material-symbols-outlined text-[28px]">location_on</span>
            Kelola Lokasi Masjid &amp; Majelis
          </h1>
          <p className="text-body-md text-on-surface-variant mt-1">
            Kelola titik lokasi masjid, alamat, link Google Maps, dan kontak pengurus yang tampil di halaman Kontak &amp; Peta Publik.
          </p>
        </div>
        <Button onClick={handleOpenAdd} className="bg-[#004b36] text-white hover:bg-[#003828]">
          <span className="material-symbols-outlined text-[20px] mr-1">add_location_alt</span>
          Tambah Titik Masjid
        </Button>
      </div>

      <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {mosques.map((m) => (
          <div
            key={m.id}
            className="rounded-2xl border border-outline/10 bg-white p-5 shadow-xs flex flex-col justify-between space-y-4 hover:shadow-md transition-shadow"
          >
            <div className="space-y-2">
              <div className="flex items-start justify-between gap-2">
                <h3 className="text-body-lg font-bold text-[#004b36] font-headline-sm leading-snug">
                  {m.name}
                </h3>
                <Badge variant={m.isActive ? "primary" : "secondary"}>
                  {m.isActive ? "Aktif" : "Non-aktif"}
                </Badge>
              </div>

              <p className="text-body-sm text-on-surface-variant flex items-start gap-1.5 leading-relaxed">
                <span className="material-symbols-outlined text-[16px] text-amber-600 shrink-0 mt-0.5">location_on</span>
                <span>{m.address}</span>
              </p>

              {m.contactName && (
                <p className="text-label-sm text-on-surface-variant flex items-center gap-1.5">
                  <span className="material-symbols-outlined text-[14px] text-amber-600">person</span>
                  <span>{m.contactName} {m.phone ? `(${m.phone})` : ""}</span>
                </p>
              )}

              {m.notes && (
                <p className="text-label-sm text-emerald-800 bg-emerald-50 rounded-lg p-2 font-medium">
                  📌 {m.notes}
                </p>
              )}
            </div>

            <div className="pt-3 border-t border-outline/10 flex items-center justify-between">
              {m.mapDirectUrl ? (
                <a
                  href={m.mapDirectUrl}
                  target="_blank"
                  rel="noreferrer"
                  className="text-label-sm font-bold text-amber-600 hover:underline flex items-center gap-1"
                >
                  <span className="material-symbols-outlined text-[14px]">map</span>
                  Google Maps
                </a>
              ) : <div />}

              <div className="flex items-center gap-2">
                <Button size="sm" variant="outline" onClick={() => handleOpenEdit(m)}>
                  Edit
                </Button>
                <Button size="sm" variant="ghost" onClick={() => handleDelete(m.id)} className="text-rose-600 hover:bg-rose-50">
                  Hapus
                </Button>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Modal Form Tambah / Edit */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={editingMosque ? "Edit Titik Lokasi Masjid" : "Tambah Titik Lokasi Masjid"}
      >
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <Label htmlFor="name">Nama Masjid / Majelis *</Label>
            <Input
              id="name"
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              placeholder="Contoh: Masjid Al-Muflihun Condet"
              required
            />
          </div>

          <div>
            <Label htmlFor="address">Alamat Lengkap *</Label>
            <Textarea
              id="address"
              value={formData.address}
              onChange={(e) => setFormData({ ...formData, address: e.target.value })}
              placeholder="Jl. Raya Condet No. 18, Balekambang, Kramat Jati, Jakarta Timur"
              required
            />
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <Label htmlFor="contactName">Nama Pengurus / Taklim</Label>
              <Input
                id="contactName"
                value={formData.contactName}
                onChange={(e) => setFormData({ ...formData, contactName: e.target.value })}
                placeholder="H. Ahmad Syafi'i"
              />
            </div>
            <div>
              <Label htmlFor="phone">Nomor Telepon / WA</Label>
              <Input
                id="phone"
                value={formData.phone}
                onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                placeholder="0812-3456-7890"
              />
            </div>
          </div>

          <div>
            <Label htmlFor="mapEmbedUrl">Link iFrame Embed Map (Google Maps)</Label>
            <Input
              id="mapEmbedUrl"
              value={formData.mapEmbedUrl}
              onChange={(e) => setFormData({ ...formData, mapEmbedUrl: e.target.value })}
              placeholder="https://maps.google.com/maps?q=-6.2789,106.8570&z=15&output=embed"
            />
            <span className="text-[11px] text-on-surface-variant">
              Gunakan link embed dari Google Maps atau format `https://maps.google.com/maps?q=LAT,LNG&output=embed`
            </span>
          </div>

          <div>
            <Label htmlFor="mapDirectUrl">Link Buka Langsung Google Maps</Label>
            <Input
              id="mapDirectUrl"
              value={formData.mapDirectUrl}
              onChange={(e) => setFormData({ ...formData, mapDirectUrl: e.target.value })}
              placeholder="https://maps.google.com/?q=-6.2789,106.8570"
            />
          </div>

          <div>
            <Label htmlFor="notes">Catatan / Jadwal Rutin</Label>
            <Input
              id="notes"
              value={formData.notes}
              onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
              placeholder="Pusat Kegiatan Pemuda Mudes &amp; Sholat Berjamaah"
            />
          </div>

          <div className="flex items-center gap-2 pt-1">
            <input
              type="checkbox"
              id="isActive"
              checked={formData.isActive}
              onChange={(e) => setFormData({ ...formData, isActive: e.target.checked })}
              className="h-4 w-4 rounded border-outline-variant text-[#004b36] focus:ring-[#004b36]"
            />
            <Label htmlFor="isActive" className="cursor-pointer font-semibold text-body-sm">
              Tampilkan di Portal Publik
            </Label>
          </div>

          <div className="flex justify-end gap-3 pt-4 border-t border-outline/10">
            <Button type="button" variant="outline" onClick={() => setIsModalOpen(false)}>
              Batal
            </Button>
            <Button type="submit" className="bg-[#004b36] text-white hover:bg-[#003828]">
              Simpan Titik Lokasi
            </Button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
