<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\CreateArticleDTO;
use App\Application\DTO\UpdateArticleDTO;
use App\Application\Services\ArticleService;
use App\Domain\Enums\ArticleStatus;
use App\Domain\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Shared\Support\CursorPaginationMeta;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends BaseController
{
    public function __construct(private readonly ArticleService $service)
    {
    }

    public function publicIndex(Request $request): JsonResponse
    {
        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['article_category_id'],
            searchableColumns: ['title'],
        );

        $query = Article::query()->with(['category', 'cover'])->where('status', 'published');
        $filter->apply($query);

        $paginator = $query->cursorPaginate(min((int) $request->query('perPage', 20), 100));

        return $this->success(
            data: ArticleResource::collection($paginator)->resolve(),
            message: 'Articles retrieved successfully.',
            meta: CursorPaginationMeta::fromPaginator($paginator),
        );
    }

    public function publicShow(string $slug): JsonResponse
    {
        $model = $this->service->findBySlug($slug);

        abort_if($model === null, 404);

        /** @var ArticleStatus $status */
        $status = $model->status;

        abort_if($status->value !== 'published', 404);

        $this->service->incrementViewCount($model);

        return $this->success(new ArticleResource($model), 'Article retrieved successfully.');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Article::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['article_category_id', 'status'],
            allowedSorts: ['published_at', 'created_at'],
            searchableColumns: ['title'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: ArticleResource::collection($paginator)->resolve(),
            message: 'Articles retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $article): JsonResponse
    {
        $model = $this->service->find($article);

        $this->authorize('view', $model);

        return $this->success(new ArticleResource($model), 'Article retrieved successfully.');
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $dto = CreateArticleDTO::fromRequest($request);

        $model = $this->service->create($dto->toArray());

        return $this->success(new ArticleResource($model), 'Article created successfully.', status: 201);
    }

    public function update(UpdateArticleRequest $request, string $article): JsonResponse
    {
        $model = $this->service->find($article);

        $dto = UpdateArticleDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto->toArray());

        return $this->success(new ArticleResource($updated), 'Article updated successfully.');
    }

    public function destroy(string $article): JsonResponse
    {
        $model = $this->service->find($article);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->getKey()], 'Article deleted successfully.');
    }

    public function restore(string $article): JsonResponse
    {
        $this->authorize('restore', Article::class);

        $restored = $this->service->restore($article);

        return $this->success(new ArticleResource($restored), 'Article restored successfully.');
    }
}
