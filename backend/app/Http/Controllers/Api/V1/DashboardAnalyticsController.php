<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\DashboardAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * PROJECT_SPECIFICATION.md §17 - read-only aggregation endpoints, no
 * dedicated Model/Migration/DTO (nothing is created or updated here).
 */
class DashboardAnalyticsController extends BaseController
{
    public function __construct(private readonly DashboardAnalyticsService $service)
    {
    }

    public function summary(): JsonResponse
    {
        Gate::authorize('dashboard.view');

        return $this->success($this->service->summary(), 'Dashboard summary retrieved successfully.');
    }

    public function attendanceTrend(Request $request): JsonResponse
    {
        Gate::authorize('dashboard.view');

        $period = $request->query('period', 'week');

        return $this->success($this->service->attendanceTrend(is_string($period) ? $period : 'week'), 'Attendance trend retrieved successfully.');
    }

    public function contentVolume(): JsonResponse
    {
        Gate::authorize('dashboard.view');

        return $this->success($this->service->contentVolume(), 'Content volume retrieved successfully.');
    }

    public function libraryEngagement(): JsonResponse
    {
        Gate::authorize('dashboard.view');

        return $this->success($this->service->libraryEngagement(), 'Library engagement retrieved successfully.');
    }

    public function activityParticipation(): JsonResponse
    {
        Gate::authorize('dashboard.view');

        return $this->success($this->service->activityParticipation(), 'Activity participation retrieved successfully.');
    }
}
