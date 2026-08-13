"use client";

import { useQuery } from "@tanstack/react-query";
import { useRouter } from "next/navigation";
import {
  AreaChart,
  Area,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  BarChart,
  Bar,
  Legend,
  PieChart,
  Pie,
  Cell,
} from "recharts";
import { useAuth } from "@/providers/AuthProvider";
import { usePermission } from "@/hooks/usePermission";
import { Skeleton } from "@/components/ui/Skeleton";
import { EmptyState } from "@/components/ui/EmptyState";
import { Alert } from "@/components/ui/Alert";
import { Icon } from "@/components/ui/Icon";
import { api } from "@/lib/api";
import type { ApiResponse } from "@/lib/api-types";
import { getApiErrorMessage } from "@/lib/query-client";
import { formatDate } from "@/lib/dates";

interface DashboardSummary {
  totalMembers: number;
  totalArticles: number;
  totalActivities: number;
  totalAttendances: number;
  growth: {
    members: number;
    attendances: number;
  };
}

interface AttendanceTrendItem {
  period: string;
  count: number;
}

interface ContentVolumeItem {
  month: string;
  articles: number;
  announcements: number;
}

interface LibraryEngagementItem {
  categoryId: string;
  categoryName: string;
  downloads: number;
  documentCount: number;
}

interface ActivityParticipationItem {
  activityId: string;
  title: string;
  attendanceCount: number;
}

interface DashboardData {
  summary: DashboardSummary | null;
  attendanceTrend: AttendanceTrendItem[];
  contentVolume: ContentVolumeItem[];
  libraryEngagement: LibraryEngagementItem[];
  activityParticipation: ActivityParticipationItem[];
}

const CHART_COLORS = ["#059669", "#D4AF37", "#735c00", "#4f1f19", "#064e3b", "#003527"];

export function DashboardPage() {
  const router = useRouter();
  const { user } = useAuth();
  const { can } = usePermission();
  const hasAccess = can("dashboard", "view");

  const {
    data,
    isLoading,
    error,
  } = useQuery<DashboardData, Error>({
    queryKey: ["dashboard"],
    queryFn: async () => {
      const [summaryRes, trendRes, volumeRes, libraryRes, activityRes] = await Promise.all([
        api.get<ApiResponse<DashboardSummary>>("/admin/dashboard/summary"),
        api.get<ApiResponse<AttendanceTrendItem[]>>("/admin/dashboard/attendance-trend"),
        api.get<ApiResponse<ContentVolumeItem[]>>("/admin/dashboard/content-volume"),
        api.get<ApiResponse<LibraryEngagementItem[]>>("/admin/dashboard/library-engagement"),
        api.get<ApiResponse<ActivityParticipationItem[]>>("/admin/dashboard/activity-participation"),
      ]);
      return {
        summary: summaryRes.data.data,
        attendanceTrend: trendRes.data.data,
        contentVolume: volumeRes.data.data,
        libraryEngagement: libraryRes.data.data,
        activityParticipation: activityRes.data.data,
      };
    },
    enabled: hasAccess,
  });

  if (!hasAccess) {
    return (
      <EmptyState
        title="Akses ditolak"
        description="Anda tidak memiliki izin untuk melihat dashboard."
        icon="lock"
      />
    );
  }

  const membersCount = data?.summary?.totalMembers ?? 0;
  const articlesCount = data?.summary?.totalArticles ?? 0;
  const activitiesCount = data?.summary?.totalActivities ?? 0;
  const attendancesCount = data?.summary?.totalAttendances ?? 0;

  return (
    <div className="space-y-xl">
      {/* Announcements Marquee Banner */}
      <div className="bg-emerald-900 text-white py-xs md:py-sm px-lg rounded-lg premium-shadow relative overflow-hidden flex items-center">
        <div className="absolute left-0 top-0 bottom-0 bg-emerald-900 px-md z-10 flex items-center font-bold text-gold-accent border-r border-emerald-800 text-label-sm shrink-0">
          LATEST
        </div>
        <div className="overflow-hidden whitespace-nowrap w-full pl-24">
          <div className="inline-block animate-marquee font-label-md text-label-md space-x-3xl">
            <span>🌟 Pengajian Rutin Pemuda: 'Tafsir Surat Al-Kahfi' Jumat ini jam 18.30 WIB</span>
            <span>📢 Kerja Bakti Lingkungan & Masjid: Minggu Ba'da Asar.</span>
            <span>🕌 Penggalangan Wakaf Digital telah mencapai 85% dari target!</span>
            <span>💡 Pengingat: Batas pengumpulan usulan kegiatan bulanan hari Selasa.</span>
          </div>
        </div>
      </div>

      {/* Welcome & Quick Actions Header */}
      <div className="flex flex-col md:flex-row justify-between items-start md:items-end gap-lg">
        <div>
          <h2 className="font-headline-md text-headline-md font-bold text-primary mb-xs">
            As-salamu alaykum, {user?.name?.split(" ")[0] ?? "Admin"}
          </h2>
          <p className="font-body-md text-body-md text-on-surface-variant">
            Kelola kegiatan organisasi dan lihat statistik perkembangan partisipasi jemaah hari ini.
          </p>
        </div>
        <div className="flex items-center gap-md flex-wrap">
          <button
            onClick={() => router.push("/admin/announcements")}
            className="flex items-center gap-xs px-md py-sm bg-gold-light text-secondary font-label-md rounded-lg hover:bg-secondary-fixed transition-colors active:scale-95 shadow-sm"
          >
            <Icon name="add_circle" className="text-[20px]" />
            Pengumuman Baru
          </button>
          <button
            onClick={() => router.push("/admin/study-schedules")}
            className="flex items-center gap-xs px-md py-sm bg-emerald-600 text-white font-label-md rounded-lg hover:opacity-90 transition-colors active:scale-95 shadow-sm"
          >
            <Icon name="calendar_month" className="text-[20px]" />
            Jadwal Kajian
          </button>
          <button
            onClick={() => window.print()}
            className="flex items-center justify-center p-sm bg-surface-container text-primary rounded-lg hover:bg-surface-variant transition-colors active:scale-95"
            title="Cetak/Export"
          >
            <Icon name="download" />
          </button>
        </div>
      </div>

      {error && <Alert variant="error">{getApiErrorMessage(error)}</Alert>}

      {/* KPI Bento Grid */}
      <section className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-lg">
        {/* Total Members Card */}
        <div className="bg-surface-container-lowest p-lg rounded-lg border border-outline-variant premium-shadow flex flex-col justify-between">
          <div>
            <div className="flex justify-between items-start mb-sm">
              <span className="p-xs bg-emerald-900/10 text-emerald-900 rounded-lg">
                <Icon name="groups" />
              </span>
              <span className="text-success font-label-sm text-label-sm flex items-center gap-xs font-semibold">
                +{data?.summary?.growth?.members ?? 12}% <Icon name="trending_up" className="text-[14px]" />
              </span>
            </div>
            <p className="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Total Anggota</p>
            {isLoading ? (
              <Skeleton className="mt-2 h-8 w-24" />
            ) : (
              <h3 className="font-headline-sm text-headline-sm font-bold text-primary mt-xs">
                {membersCount.toLocaleString("id-ID")}
              </h3>
            )}
          </div>
        </div>

        {/* Total Articles Card */}
        <div className="bg-surface-container-lowest p-lg rounded-lg border border-outline-variant premium-shadow flex flex-col justify-between">
          <div>
            <div className="flex justify-between items-start mb-sm">
              <span className="p-xs bg-gold-light text-secondary rounded-lg">
                <Icon name="article" />
              </span>
              <span className="text-success font-label-sm text-label-sm flex items-center gap-xs font-semibold">
                +5.4% <Icon name="trending_up" className="text-[14px]" />
              </span>
            </div>
            <p className="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Artikel Publikasi</p>
            {isLoading ? (
              <Skeleton className="mt-2 h-8 w-24" />
            ) : (
              <h3 className="font-headline-sm text-headline-sm font-bold text-primary mt-xs">
                {articlesCount.toLocaleString("id-ID")} Rilis
              </h3>
            )}
          </div>
        </div>

        {/* Total Activities Card */}
        <div className="bg-surface-container-lowest p-lg rounded-lg border border-outline-variant premium-shadow flex flex-col justify-between">
          <div>
            <div className="flex justify-between items-start mb-sm">
              <span className="p-xs bg-primary-container/10 text-primary-container rounded-lg">
                <Icon name="event" />
              </span>
              <span className="text-on-surface-variant font-label-sm text-label-sm">Aktif</span>
            </div>
            <p className="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Total Kegiatan</p>
            {isLoading ? (
              <Skeleton className="mt-2 h-8 w-24" />
            ) : (
              <h3 className="font-headline-sm text-headline-sm font-bold text-primary mt-xs">
                {activitiesCount.toLocaleString("id-ID")} Program
              </h3>
            )}
          </div>
        </div>

        {/* Total Attendance Check-ins Card */}
        <div className="bg-surface-container-lowest p-lg rounded-lg border border-outline-variant premium-shadow flex flex-col justify-between">
          <div>
            <div className="flex justify-between items-start mb-sm">
              <span className="p-xs bg-error-container text-on-error-container rounded-lg">
                <Icon name="qr_code_scanner" />
              </span>
              <span className="text-success font-label-sm text-label-sm flex items-center gap-xs font-semibold">
                +{data?.summary?.growth?.attendances ?? 20}% <Icon name="trending_up" className="text-[14px]" />
              </span>
            </div>
            <p className="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Kehadiran Check-In</p>
            {isLoading ? (
              <Skeleton className="mt-2 h-8 w-24" />
            ) : (
              <h3 className="font-headline-sm text-headline-sm font-bold text-primary mt-xs">
                {attendancesCount.toLocaleString("id-ID")} Sesi
              </h3>
            )}
          </div>
        </div>
      </section>

      {/* Charts Section */}
      <section className="grid grid-cols-1 lg:grid-cols-3 gap-lg">
        {/* Community Engagement Area Chart */}
        <div className="lg:col-span-2 bg-surface-container-lowest p-lg rounded-lg border border-outline-variant premium-shadow">
          <div className="flex justify-between items-center mb-lg">
            <div>
              <h4 className="font-headline-sm text-headline-sm text-primary font-bold">Tren Kehadiran Jemaah</h4>
              <p className="font-body-sm text-body-sm text-on-surface-variant">Grafik partisipasi anggota dalam setiap sesi kajian</p>
            </div>
            <select className="bg-surface-muted border border-outline-variant text-label-sm font-label-sm rounded-lg px-md py-xs focus:ring-primary outline-none">
              <option>7 Hari Terakhir</option>
              <option>30 Hari Terakhir</option>
            </select>
          </div>
          {isLoading ? (
            <Skeleton className="h-64 w-full" />
          ) : (data?.attendanceTrend ?? []).length === 0 ? (
            <EmptyState title="Belum ada data tren" description="Data kehadiran akan muncul setelah sesi check-in berlangsung." icon="inbox" />
          ) : (
            <div className="h-64 relative w-full">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={data?.attendanceTrend ?? []}>
                  <defs>
                    <linearGradient id="emeraldGradient" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#059669" stopOpacity={0.3} />
                      <stop offset="95%" stopColor="#059669" stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="4 4" stroke="#E2E8F0" vertical={false} />
                  <XAxis dataKey="period" tickFormatter={(v: string) => formatDate(v)} stroke="#707974" fontSize={12} />
                  <YAxis stroke="#707974" fontSize={12} />
                  <Tooltip
                    contentStyle={{ borderRadius: "8px", border: "1px solid #bfc9c3", background: "#ffffff", boxShadow: "0 4px 6px -1px rgba(0,0,0,0.1)" }}
                    formatter={(value: number) => [value, "Jemaah Hadir"]}
                    labelFormatter={(label: string) => formatDate(label)}
                  />
                  <Area type="monotone" dataKey="count" stroke="#059669" strokeWidth={3} fillOpacity={1} fill="url(#emeraldGradient)" />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          )}
        </div>

        {/* Library Engagement Pie Chart */}
        <div className="bg-surface-container-lowest p-lg rounded-lg border border-outline-variant premium-shadow flex flex-col">
          <div className="mb-lg">
            <h4 className="font-headline-sm text-headline-sm text-primary font-bold">Statistik Perpustakaan</h4>
            <p className="font-body-sm text-body-sm text-on-surface-variant">Kategori dokumen terbanyak diunduh</p>
          </div>
          {isLoading ? (
            <Skeleton className="h-64 w-full" />
          ) : (data?.libraryEngagement ?? []).length === 0 ? (
            <EmptyState title="Belum ada data" description="Unggah dokumen perpustakaan untuk melihat grafik ini." icon="inbox" />
          ) : (
            <div className="flex-grow flex flex-col items-center justify-center">
              <div className="h-48 w-full">
                <ResponsiveContainer width="100%" height="100%">
                  <PieChart>
                    <Pie
                      data={data?.libraryEngagement}
                      dataKey="downloads"
                      nameKey="categoryName"
                      cx="50%"
                      cy="50%"
                      innerRadius="50%"
                      outerRadius="80%"
                    >
                      {data?.libraryEngagement.map((_, i) => (
                        <Cell key={i} fill={CHART_COLORS[i % CHART_COLORS.length]} />
                      ))}
                    </Pie>
                    <Tooltip contentStyle={{ borderRadius: "8px", border: "1px solid #bfc9c3", background: "#ffffff" }} />
                  </PieChart>
                </ResponsiveContainer>
              </div>
              <div className="mt-md w-full space-y-xs">
                {data?.libraryEngagement.slice(0, 3).map((item, idx) => (
                  <div key={item.categoryId} className="flex items-center justify-between font-body-sm text-body-sm">
                    <div className="flex items-center gap-xs">
                      <div className="w-3 h-3 rounded-full" style={{ backgroundColor: CHART_COLORS[idx % CHART_COLORS.length] }} />
                      <span className="truncate max-w-[140px] text-on-surface">{item.categoryName}</span>
                    </div>
                    <span className="font-label-md text-label-md text-primary">{item.downloads} Unduhan</span>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </section>

      {/* Detailed Lists Section */}
      <section className="grid grid-cols-1 xl:grid-cols-2 gap-lg">
        {/* Upcoming Study Sessions */}
        <div className="bg-surface-container-lowest p-lg rounded-lg border border-outline-variant premium-shadow">
          <div className="flex justify-between items-center mb-lg">
            <h4 className="font-headline-sm text-headline-sm text-primary font-bold">Agenda Kajian Mendatang</h4>
            <button
              onClick={() => router.push("/admin/study-schedules")}
              className="text-emerald-600 font-label-md text-label-md hover:underline flex items-center gap-xs"
            >
              Lihat Semua
              <Icon name="arrow_forward" className="text-[16px]" />
            </button>
          </div>
          <div className="space-y-md">
            <div className="flex items-center p-md bg-surface-muted rounded-lg border border-transparent hover:border-emerald-600/30 transition-colors group">
              <div className="bg-emerald-900 text-white px-md py-xs rounded-lg text-center min-w-[64px]">
                <p className="font-label-sm text-label-sm uppercase leading-none">JUM</p>
                <p className="font-headline-sm text-headline-sm font-bold leading-tight">15</p>
              </div>
              <div className="ml-md flex-grow">
                <p className="font-label-md text-label-md text-primary font-bold mb-xs">Tafsir Al-Qur'an: Surat Al-Kahfi</p>
                <p className="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-xs">
                  <Icon name="schedule" className="text-[16px]" /> 18:30 - 20:00 WIB • Masjid LDII Condet
                </p>
              </div>
              <span className="p-xs text-emerald-600 group-hover:translate-x-1 transition-transform">
                <Icon name="chevron_right" />
              </span>
            </div>

            <div className="flex items-center p-md bg-surface-muted rounded-lg border border-transparent hover:border-emerald-600/30 transition-colors group">
              <div className="bg-gold-light text-secondary px-md py-xs rounded-lg text-center min-w-[64px]">
                <p className="font-label-sm text-label-sm uppercase leading-none text-secondary">AHAD</p>
                <p className="font-headline-sm text-headline-sm font-bold leading-tight">17</p>
              </div>
              <div className="ml-md flex-grow">
                <p className="font-label-md text-label-md text-primary font-bold mb-xs">Kajian Kemandirian & Youth Leadership</p>
                <p className="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-xs">
                  <Icon name="schedule" className="text-[16px]" /> 09:00 - 11:30 WIB • Gedung Pemuda LDII
                </p>
              </div>
              <span className="p-xs text-emerald-600 group-hover:translate-x-1 transition-transform">
                <Icon name="chevron_right" />
              </span>
            </div>

            <div className="flex items-center p-md bg-surface-muted rounded-lg border border-transparent hover:border-emerald-600/30 transition-colors group">
              <div className="bg-emerald-900 text-white px-md py-xs rounded-lg text-center min-w-[64px]">
                <p className="font-label-sm text-label-sm uppercase leading-none">RAB</p>
                <p className="font-headline-sm text-headline-sm font-bold leading-tight">20</p>
              </div>
              <div className="ml-md flex-grow">
                <p className="font-label-md text-label-md text-primary font-bold mb-xs">Kajian Hadits & Dzikir Petang</p>
                <p className="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-xs">
                  <Icon name="schedule" className="text-[16px]" /> 19:30 - 20:30 WIB • Ruang Utama
                </p>
              </div>
              <span className="p-xs text-emerald-600 group-hover:translate-x-1 transition-transform">
                <Icon name="chevron_right" />
              </span>
            </div>
          </div>
        </div>

        {/* Recent Community Actions Timeline */}
        <div className="bg-surface-container-lowest p-lg rounded-lg border border-outline-variant premium-shadow">
          <div className="flex justify-between items-center mb-lg">
            <h4 className="font-headline-sm text-headline-sm text-primary font-bold">Aktivitas Terkini</h4>
            <span className="p-xs bg-surface-muted rounded-full text-on-surface-variant cursor-pointer hover:bg-surface-container transition-colors">
              <Icon name="history" />
            </span>
          </div>
          <div className="relative pl-8 space-y-lg before:absolute before:left-3 before:top-2 before:bottom-2 before:w-[2px] before:bg-outline-variant">
            <div className="relative">
              <div className="absolute -left-[29px] top-1 w-3.5 h-3.5 rounded-full bg-emerald-600 border-2 border-white" />
              <div>
                <p className="font-body-md text-body-md text-on-surface">
                  <span className="font-bold">Ahmad Hassan</span> mendaftar ke sesi kajian <span className="text-emerald-600 font-semibold">Tafsir Surat Al-Kahfi</span>.
                </p>
                <p className="font-label-sm text-label-sm text-on-surface-variant mt-xs">2 jam yang lalu</p>
              </div>
            </div>

            <div className="relative">
              <div className="absolute -left-[29px] top-1 w-3.5 h-3.5 rounded-full bg-gold-accent border-2 border-white" />
              <div>
                <p className="font-body-md text-body-md text-on-surface">
                  <span className="font-bold">Fatima Ali</span> mengunggah dokumen baru: <span className="italic text-primary font-medium font-hanken">"Panduan Praktis Fiqih Muamalah Pemuda"</span>
                </p>
                <p className="font-label-sm text-label-sm text-on-surface-variant mt-xs">5 jam yang lalu</p>
              </div>
            </div>

            <div className="relative">
              <div className="absolute -left-[29px] top-1 w-3.5 h-3.5 rounded-full bg-emerald-600 border-2 border-white" />
              <div>
                <p className="font-body-md text-body-md text-on-surface">
                  <span className="font-bold">Zaid Omar</span> menyelesaikan modul keikutsertaan <span className="text-emerald-600 font-semibold">Pelatihan Kepemimpinan Pemuda</span>.
                </p>
                <p className="font-label-sm text-label-sm text-on-surface-variant mt-xs">Kemarin jam 21.15 WIB</p>
              </div>
            </div>

            <div className="relative">
              <div className="absolute -left-[29px] top-1 w-3.5 h-3.5 rounded-full bg-on-surface-variant border-2 border-white" />
              <div>
                <p className="font-body-md text-body-md text-on-surface">
                  <span className="font-bold">Admin Mudes</span> memperbarui jadwal kegiatan bulan ini.
                </p>
                <p className="font-label-sm text-label-sm text-on-surface-variant mt-xs">Kemarin jam 11.30 WIB</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
