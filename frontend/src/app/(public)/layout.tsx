import type { Metadata } from "next";
import { PublicSiteLayout } from "@/components/layout/PublicSiteLayout";

export const metadata: Metadata = {
  title: {
    default: "Mudes.co — Pemuda Desa Condet",
    template: "%s | Mudes.co",
  },
  description: "Platform digital resmi Musyawarah Desa & Pemuda Pemudi Condet. Merawat Tradisi, Menjaga Masa Depan. Informasi kajian rutin, agenda kegiatan, pustaka digital, dan struktur organisasi.",
  keywords: [
    "Mudes",
    "Condet",
    "Pemuda Condet",
    "Kajian Condet",
    "Organisasi Pemuda",
    "Pustaka Digital Mudes",
    "Agenda Komunitas Condet",
  ],
  authors: [{ name: "Mudes.co" }],
  openGraph: {
    title: "Mudes.co — Pemuda Desa Condet",
    description: "Platform digital resmi Pemuda Pemudi Condet. Merawat Tradisi, Menjaga Masa Depan.",
    url: "https://mudes.co",
    siteName: "Mudes.co",
    locale: "id_ID",
    type: "website",
  },
};

export default function PublicLayout({ children }: { children: React.ReactNode }) {
  return <PublicSiteLayout>{children}</PublicSiteLayout>;
}
