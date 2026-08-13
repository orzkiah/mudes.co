<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Enums\ActivityStatus;
use App\Domain\Models\Activity;
use Illuminate\Console\Command;

class UpdateActivityStatuses extends Command
{
    protected $signature = 'activities:update-statuses';

    protected $description = 'Updates activity statuses in database based on start_at and end_at timestamps.';

    public function handle(): int
    {
        $now = now();

        // 1. Update upcoming to ongoing
        $ongoingCount = Activity::query()
            ->where('status', ActivityStatus::Upcoming)
            ->where('start_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->update(['status' => ActivityStatus::Ongoing]);

        // 2. Update ongoing/upcoming to completed if end_at has passed
        $completedCount = Activity::query()
            ->whereIn('status', [ActivityStatus::Upcoming, ActivityStatus::Ongoing])
            ->where(function ($q) use ($now) {
                $q->whereNotNull('end_at')->where('end_at', '<', $now)
                  ->orWhere(function ($sub) use ($now) {
                      $sub->whereNull('end_at')->where('start_at', '<', $now->copy()->subDay());
                  });
            })
            ->update(['status' => ActivityStatus::Completed]);

        $this->info("Updated {$ongoingCount} activity to ongoing, {$completedCount} activity to completed.");

        return self::SUCCESS;
    }
}
