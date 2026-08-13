<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Models\Article;
use App\Infrastructure\Repositories\Contracts\ArticleRepositoryInterface;

class ArticleService extends AbstractContentService
{
    public function __construct(private readonly ArticleRepositoryInterface $articleRepository)
    {
        parent::__construct($articleRepository);
    }

    public function findBySlug(string $slug): ?Article
    {
        return $this->articleRepository->findBySlug($slug);
    }

    public function incrementViewCount(Article $article): void
    {
        $article->increment('view_count');
    }

    /**
     * Auto-publishes scheduled articles whose time has arrived
     * (PROJECT_SPECIFICATION.md §3.9 - "auto-publish via queued job at the
     * scheduled time"). Called from the articles:publish-scheduled Artisan
     * command, itself registered on the schedule.
     */
    public function publishDueScheduled(): int
    {
        return $this->transaction(function () {
            $due = $this->articleRepository->dueForAutoPublish();

            foreach ($due as $article) {
                $article->update(['status' => 'published']);
            }

            return $due->count();
        });
    }
}
