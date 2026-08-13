export interface PhilosophyQuote {
  id: string;
  position: number; // 1, 2, 3, 4, 5
  eyebrow: string;
  title: string;
  quote: string;
  author?: string;
  imageUrl: string;
  isActive: boolean;
  createdAt: string;
}

export const POSITION_LABELS: Record<number, { name: string; desc: string; gridClass: string }> = {
  1: {
    name: "Posisi 1 — Kiri Atas (Grid Besar Kiri)",
    desc: "Tampil menonjol di baris pertama sebelah kiri (3 Kolom Grid).",
    gridClass: "max-lg:rounded-t-4xl lg:col-span-3 lg:rounded-tl-4xl",
  },
  2: {
    name: "Posisi 2 — Kanan Atas (Grid Besar Kanan)",
    desc: "Tampil menonjol di baris pertama sebelah kanan (3 Kolom Grid).",
    gridClass: "lg:col-span-3 lg:rounded-tr-4xl",
  },
  3: {
    name: "Posisi 3 — Bawah Kiri (Baris Kedua #1)",
    desc: "Tampil di baris kedua posisi pertama sebelah kiri (2 Kolom Grid).",
    gridClass: "lg:col-span-2 lg:rounded-bl-4xl",
  },
  4: {
    name: "Posisi 4 — Bawah Tengah (Baris Kedua #2)",
    desc: "Tampil di baris kedua posisi tengah (2 Kolom Grid).",
    gridClass: "lg:col-span-2",
  },
  5: {
    name: "Posisi 5 — Bawah Kanan (Baris Kedua #3)",
    desc: "Tampil di baris kedua posisi ujung kanan (2 Kolom Grid).",
    gridClass: "max-lg:rounded-b-4xl lg:col-span-2 lg:rounded-br-4xl",
  },
};

const DEFAULT_PHILOSOPHY_QUOTES: PhilosophyQuote[] = [
  {
    id: "phil-1",
    position: 1,
    eyebrow: "Merawat Tradisi",
    title: "Menjaga Keturunan & Budaya Condet",
    quote: "Kebudayaan dan tradisi leluhur Condet bukanlah sekadar kenangan masa lalu, melainkan pondasi moral dan identitas yang terus kita rawat bersama generasi muda.",
    author: "Sesanti Sepuh Condet",
    imageUrl: "/images/library-cover.jpg",
    isActive: true,
    createdAt: new Date().toISOString(),
  },
  {
    id: "phil-2",
    position: 2,
    eyebrow: "Ukhuwah & Kebersamaan",
    title: "Rukun Agawe Santosa, Crah Agawe Bubrah",
    quote: "Persatuan dan persaudaraan di antara pemuda adalah kunci kekuatan kita. Ketika kita berjalan bersama dalam kebaikan, tidak ada rintangan yang tak bisa kita lalui.",
    author: "Prinsip Generus Mudes",
    imageUrl: "/images/book-cover.jpg",
    isActive: true,
    createdAt: new Date().toISOString(),
  },
  {
    id: "phil-3",
    position: 3,
    eyebrow: "Ilmu & Akhlak",
    title: "Tumbuh Bersama Dalam Naungan Pengajian",
    quote: "Menuntut ilmu dan memperbaiki akhlak adalah perjalanan seumur hidup. Di majelis taklim inilah karakter pemuda islami dibentuk dan dikuatkan.",
    author: "Majelis Pengajian Condet",
    imageUrl: "/images/library-cover.jpg",
    isActive: true,
    createdAt: new Date().toISOString(),
  },
  {
    id: "phil-4",
    position: 4,
    eyebrow: "Kepedulian Sosial",
    title: "Bermanfaat Bagi Lingkungan & Sesama",
    quote: "Sebaik-baik manusia adalah yang paling bermanfaat bagi sesamanya. Tangan kita selalu terbuka untuk membantu warga dan menjaga keasrian kampung Condet.",
    author: "Generasi Muda Condet",
    imageUrl: "/images/book-cover.jpg",
    isActive: true,
    createdAt: new Date().toISOString(),
  },
  {
    id: "phil-5",
    position: 5,
    eyebrow: "Masa Depan",
    title: "Membangun Generasi Penerus Mandiri",
    quote: "Masa depan yang cerah diciptakan oleh pemuda yang berani melangkah hari ini. Bersama MUDES.CO, kita siap membentuk generasi penerus yang cerdas dan bertakwa.",
    author: "Pengurus MUDES.CO",
    imageUrl: "/images/library-cover.jpg",
    isActive: true,
    createdAt: new Date().toISOString(),
  },
];

const STORAGE_KEY = "mudes_philosophy_quotes_v1";

export function getStoredPhilosophyQuotes(): PhilosophyQuote[] {
  if (typeof window === "undefined") return DEFAULT_PHILOSOPHY_QUOTES;
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(DEFAULT_PHILOSOPHY_QUOTES));
      return DEFAULT_PHILOSOPHY_QUOTES;
    }
    return JSON.parse(raw);
  } catch (err) {
    console.error("Failed to load stored philosophy quotes", err);
    return DEFAULT_PHILOSOPHY_QUOTES;
  }
}

export function saveStoredPhilosophyQuotes(items: PhilosophyQuote[]) {
  if (typeof window === "undefined") return;
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  } catch (err) {
    console.error("Failed to save philosophy quotes", err);
  }
}
