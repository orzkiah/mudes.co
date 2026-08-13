"use client";

import { useQuery } from "@tanstack/react-query";
import { Card } from "@/components/ui/Card";
import { Icon } from "@/components/ui/Icon";
import { Alert } from "@/components/ui/Alert";
import { Skeleton } from "@/components/ui/Skeleton";
import { api } from "@/lib/api";
import { getApiErrorMessage } from "@/lib/query-client";
import type { ApiResponse } from "@/lib/api-types";

interface DashboardSummary {
  totalMembers: number;
  totalArticles: number;
  totalActivities: number;
  totalAttendances: number;
}

interface ContentVolumeItem {
  month: string;
  articles: number;
  announcements: number;
}

interface LibraryEngagementItem {
  categoryName: string;
  downloads: number;
  documentCount: number;
}

interface ActivityParticipationItem {
  title: string;
  attendanceCount: number;
}

interface AnalyticsData {
  summary: DashboardSummary;
  contentVolume: ContentVolumeItem[];
  libraryEngagement: LibraryEngagementItem[];
  activityParticipation: ActivityParticipationItem[];
}

function StatCard({ label, value, icon, loading }: { label: string; value: number; icon: string; loading?: boolean }) {
  return (
    <Card className="p-md">
      <div className="flex items-center justify-between gap-4">
        <div>
          <p className="text-label-md text-on-surface-variant">{label}</p>
          {loading ? <Skeleton className="mt-3 h-8 w-20" /> : <p className="mt-2 text-display-sm font-display-sm text-on-surface">{value.toLocaleString("id-ID")}</p>}
        </div>
        <div className="h-12 w-12 rounded-xl bg-primary-container/10 text-primary flex items-center justify-center">
          <Icon name={icon} className="text-3xl" filled />
        </div>
      </div>
    </Card>
  );
}

export default function AnalyticsPage() {
  const { data, error, isLoading } = useQuery<AnalyticsData>({
    queryKey: ["analytics", "dashboard"],
    queryFn: async () => {
      const [summaryRes, volumeRes, libraryRes, activityRes] = await Promise.all([
        api.get<ApiResponse<DashboardSummary>>("/admin/dashboard/summary"),
        api.get<ApiResponse<ContentVolumeItem[]>>("/admin/dashboard/content-volume"),
        api.get<ApiResponse<LibraryEngagementItem[]>>("/admin/dashboard/library-engagement"),
        api.get<ApiResponse<ActivityParticipationItem[]>>("/admin/dashboard/activity-participation"),
      ]);

      return {
        summary: summaryRes.data.data,
        contentVolume: volumeRes.data.data,
        libraryEngagement: libraryRes.data.data,
        activityParticipation: activityRes.data.data,
      };
    },
  });

  const latestVolume = data?.contentVolume?.at(-1);
  const topLibrary = data?.libraryEngagement?.[0];
  const topActivity = data?.activityParticipation?.[0];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-headline-md font-headline-md text-on-surface flex items-center gap-2"><Icon name="analytics" /> Analytics</h1>
        <p className="text-body-md text-on-surface-variant mt-1">Ringkasan performa konten, library, dan partisipasi kegiatan.</p>
      </div>

      {error && <Alert variant="error">{getApiErrorMessage(error)}</Alert>}

      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <StatCard label="Total Anggota" value={data?.summary.totalMembers ?? 0} icon="group" loading={isLoading} />
        <StatCard label="Total Artikel" value={data?.summary.totalArticles ?? 0} icon="article" loading={isLoading} />
        <StatCard label="Total Kegiatan" value={data?.summary.totalActivities ?? 0} icon="event" loading={isLoading} />
        <StatCard label="Total Kehadiran" value={data?.summary.totalAttendances ?? 0} icon="fact_check" loading={isLoading} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <Card className="p-md space-y-2">
          <p className="text-label-md text-on-surface-variant">Konten Bulan Terakhir</p>
          <p className="text-headline-sm text-on-surface">{latestVolume ? `${latestVolume.articles} artikel, ${latestVolume.announcements} pengumuman` : "Belum ada data"}</p>
          <p className="text-body-sm text-on-surface-variant">Periode: {latestVolume?.month ?? "-"}</p>
        </Card>
        <Card className="p-md space-y-2">
          <p className="text-label-md text-on-surface-variant">Library Teratas</p>
          <p className="text-headline-sm text-on-surface">{topLibrary?.categoryName ?? "Belum ada data"}</p>
          <p className="text-body-sm text-on-surface-variant">{topLibrary ? `${topLibrary.downloads} unduhan dari ${topLibrary.documentCount} dokumen` : "-"}</p>
        </Card>
        <Card className="p-md space-y-2">
          <p className="text-label-md text-on-surface-variant">Kegiatan Teratas</p>
          <p className="text-headline-sm text-on-surface">{topActivity?.title ?? "Belum ada data"}</p>
          <p className="text-body-sm text-on-surface-variant">{topActivity ? `${topActivity.attendanceCount} peserta hadir` : "-"}</p>
        </Card>
      </div>
    </div>
  );
}
