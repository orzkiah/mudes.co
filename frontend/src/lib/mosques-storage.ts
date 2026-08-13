import type { MosqueLocation } from "@/types/models";

export const DEFAULT_MOSQUES: MosqueLocation[] = [
  {
    id: "mosque-1",
    name: "Masjid Al-Muflihun Condet",
    address: "Jl. Raya Condet No. 18, Balekambang, Kramat Jati, Jakarta Timur",
    contactName: "H. Ahmad Syafi'i (Taklim)",
    phone: "0812-3456-7890",
    mapEmbedUrl: "https://maps.google.com/maps?q=-6.2789,106.8570&z=15&output=embed",
    mapDirectUrl: "https://maps.google.com/?q=-6.2789,106.8570",
    notes: "Pusat Kegiatan Pemuda Mudes, Sholat Berjamaah, & Kajian Rutin Minggu",
    isActive: true,
  },
  {
    id: "mosque-2",
    name: "Masjid Baitul Haq Condet",
    address: "Jl. Condet Batu Ampar III No. 45, Kramat Jati, Jakarta Timur",
    contactName: "Ust. Syamsuddin",
    phone: "0857-1122-3344",
    mapEmbedUrl: "https://maps.google.com/maps?q=-6.2845,106.8612&z=15&output=embed",
    mapDirectUrl: "https://maps.google.com/?q=-6.2845,106.8612",
    notes: "Tempat Kajian Subuh & Pembinaan Generasi Muda",
    isActive: true,
  },
  {
    id: "mosque-3",
    name: "Majelis Taklim & Musholla Al-Ikhlas",
    address: "Jl. Mindi No. 12, Condet Pejaten, Jakarta Timur",
    contactName: "Ust. Fauzan",
    phone: "0813-9876-5432",
    mapEmbedUrl: "https://maps.google.com/maps?q=-6.2730,106.8520&z=15&output=embed",
    mapDirectUrl: "https://maps.google.com/?q=-6.2730,106.8520",
    notes: "Tempat Tahsin Al-Qur'an & Diskusi Kepemudaan",
    isActive: true,
  },
];

const STORAGE_KEY = "mudes_mosques_data";

export function getStoredMosques(): MosqueLocation[] {
  if (typeof window === "undefined") return DEFAULT_MOSQUES;

  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(DEFAULT_MOSQUES));
      return DEFAULT_MOSQUES;
    }
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) && parsed.length > 0 ? parsed : DEFAULT_MOSQUES;
  } catch {
    return DEFAULT_MOSQUES;
  }
}

export function saveStoredMosques(mosques: MosqueLocation[]): void {
  if (typeof window === "undefined") return;
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(mosques));
  } catch (err) {
    console.error("Failed to save mosques to storage", err);
  }
}
