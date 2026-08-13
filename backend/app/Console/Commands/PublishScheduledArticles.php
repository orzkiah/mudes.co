<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Services\ArticleService;
use Illuminate\Console\Command;

/**
 * PROJECT_SPECIFICATION.md §3.9 - "Scheduled articles auto-publish via
 * queued job at the scheduled time." Registered on the schedule in
 * routes/console.php.
 */
class PublishScheduledArticles extends Command
{
    protected $signature = 'articles:publish-scheduled';

    protected $description = 'Publishes scheduled articles whose published_at has arrived.';

    public function handle(ArticleService $service): int
    {
        $count = $service->publishDueScheduled();

        $this->info("Published {$count} scheduled article(s).");

        return self::SUCCESS;
    }
}
