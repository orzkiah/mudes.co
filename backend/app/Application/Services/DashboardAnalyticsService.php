<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Models\Activity;
use App\Domain\Models\Article;
use App\Domain\Models\Attendance;
use App\Domain\Models\AttendanceSession;
use App\Domain\Models\Member;
use Illuminate\Support\Facades\DB;

/**
 * PROJECT_SPECIFICATION.md §17 - read-only aggregation over existing
 * tables; no dedicated table of its own (growth is computed at query time,
 * not precomputed, per the same section's explicit note).
 */
class DashboardAnalyticsService
{
    /**
     * @return array<int, array{period: string, count: int}>
     */
    public function attendanceTrend(string $period = 'week'): array
    {
        $bucket = $period === 'month' ? 'month' : 'week';

        return DB::table('attendances')
            ->selectRaw("to_char(date_trunc('{$bucket}', checked_in_at), 'YYYY-MM-DD') as period, count(*) as count")
            ->whereNull('deleted_at')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => ['period' => $row->period, 'count' => (int) $row->count])
            ->all();
    }

    /**
     * @return array<int, array{month: string, articles: int, announcements: int}>
     */
    public function contentVolume(): array
    {
        $articles = DB::table('articles')
            ->selectRaw("to_char(date_trunc('month', created_at), 'YYYY-MM') as month, count(*) as count")
            ->whereNull('deleted_at')
            ->groupBy('month')
            ->pluck('count', 'month');

        $announcements = DB::table('announcements')
            ->selectRaw("to_char(date_trunc('month', created_at), 'YYYY-MM') as month, count(*) as count")
            ->whereNull('deleted_at')
            ->groupBy('month')
            ->pluck('count', 'month');

        $months = $articles->keys()->merge($announcements->keys())->unique()->sort()->values();

        return $months->map(fn ($month) => [
            'month' => $month,
            'articles' => (int) ($articles[$month] ?? 0),
            'announcements' => (int) ($announcements[$month] ?? 0),
        ])->all();
    }

    /**
     * @return array<int, array{categoryId: string, categoryName: string, downloads: int, documentCount: int}>
     */
    public function libraryEngagement(): array
    {
        return DB::table('library_documents')
            ->join('library_categories', 'library_categories.id', '=', 'library_documents.library_category_id')
            ->whereNull('library_documents.deleted_at')
            ->selectRaw('library_categories.id as category_id, library_categories.name as category_name, sum(library_documents.download_count) as downloads, count(*) as document_count')
            ->groupBy('library_categories.id', 'library_categories.name')
            ->get()
            ->map(fn ($row) => [
                'categoryId' => $row->category_id,
                'categoryName' => $row->category_name,
                'downloads' => (int) $row->downloads,
                'documentCount' => (int) $row->document_count,
            ])
            ->all();
    }

    /**
     * @return array<int, array{activityId: string, title: string, attendanceCount: int}>
     */
    public function activityParticipation(): array
    {
        return Activity::query()
            ->get()
            ->map(function (Activity $activity) {
                $attendanceCount = Attendance::query()
                    ->whereIn('attendance_session_id', AttendanceSession::query()
                        ->where('source_type', 'activity')
                        ->where('source_id', $activity->id)
                        ->pluck('id'))
                    ->count();

                return [
                    'activityId' => (string) $activity->id,
                    'title' => $activity->title,
                    'attendanceCount' => $attendanceCount,
                ];
            })
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonthNoOverflow()->startOfMonth();

        return [
            'totalMembers' => Member::query()->count(),
            'totalArticles' => Article::query()->where('status', 'published')->count(),
            'totalActivities' => Activity::query()->count(),
            'totalAttendances' => Attendance::query()->count(),
            'growth' => [
                'members' => $this->growthPercent(
                    Member::query()->where('created_at', '>=', $lastMonth)->where('created_at', '<', $thisMonth)->count(),
                    Member::query()->where('created_at', '>=', $thisMonth)->count(),
                ),
                'attendances' => $this->growthPercent(
                    Attendance::query()->where('checked_in_at', '>=', $lastMonth)->where('checked_in_at', '<', $thisMonth)->count(),
                    Attendance::query()->where('checked_in_at', '>=', $thisMonth)->count(),
                ),
            ],
        ];
    }

    private function growthPercent(int $previous, int $current): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
