<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Models\Article;
use App\Infrastructure\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ArticleRepository extends BaseRepository implements ArticleRepositoryInterface
{
    public function __construct(Article $model)
    {
        parent::__construct($model);
    }

    public function find(string $id): ?Article
    {
        return Article::query()->with(['category', 'cover'])->find($id);
    }

    public function findOrFail(string $id): Article
    {
        return Article::query()->with(['category', 'cover'])->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Article
    {
        return Article::query()->with(['category', 'cover'])->where('slug', $slug)->first();
    }

    public function findTrashedOrFail(string $id): Article
    {
        return Article::withTrashed()->with(['category', 'cover'])->findOrFail($id);
    }

    public function dueForAutoPublish(): Collection
    {
        return Article::query()
            ->where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();
    }
}
