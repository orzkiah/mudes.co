<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Models\Article;
use Illuminate\Database\Eloquent\Collection;

interface ArticleRepositoryInterface extends ContentRepositoryInterface
{
    public function find(string $id): ?Article;

    public function findOrFail(string $id): Article;

    public function findBySlug(string $slug): ?Article;

    public function findTrashedOrFail(string $id): Article;

    /**
     * Scheduled articles whose published_at has arrived - for the
     * auto-publish job (PROJECT_SPECIFICATION.md §3.9).
     *
     * @return Collection<int, Article>
     */
    public function dueForAutoPublish(): Collection;
}
