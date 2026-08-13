<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\DTO\CreateMemberDTO;
use App\Application\DTO\UpdateMemberDTO;
use App\Application\Services\MemberService;
use App\Domain\Models\Member;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Shared\Support\PaginationMeta;
use App\Shared\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends BaseController
{
    public function __construct(private readonly MemberService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Member::class);

        $filter = QueryFilter::fromRequest(
            $request,
            allowedFilters: ['status', 'gender'],
            allowedSorts: ['full_name', 'join_date', 'created_at'],
            searchableColumns: ['full_name'],
        );

        $perPage = min((int) $request->query('perPage', 20), 100);
        $paginator = $this->service->paginate($perPage, $filter);

        return $this->success(
            data: MemberResource::collection($paginator)->resolve(),
            message: 'Members retrieved successfully.',
            meta: PaginationMeta::fromPaginator($paginator),
        );
    }

    public function show(string $member): JsonResponse
    {
        $model = $this->service->find($member);

        $this->authorize('view', $model);

        return $this->success(new MemberResource($model), 'Member retrieved successfully.');
    }

    public function store(StoreMemberRequest $request): JsonResponse
    {
        $dto = CreateMemberDTO::fromRequest($request);

        $member = $this->service->create($dto);

        return $this->success(new MemberResource($member), 'Member created successfully.', status: 201);
    }

    public function update(UpdateMemberRequest $request, string $member): JsonResponse
    {
        $model = $this->service->find($member);

        $dto = UpdateMemberDTO::fromRequest($request);

        $updated = $this->service->update($model, $dto);

        return $this->success(new MemberResource($updated), 'Member updated successfully.');
    }

    public function destroy(string $member): JsonResponse
    {
        $model = $this->service->find($member);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return $this->success(['id' => $model->id], 'Member deleted successfully.');
    }

    public function restore(string $member): JsonResponse
    {
        $this->authorize('restore', Member::class);

        $restored = $this->service->restore($member);

        return $this->success(new MemberResource($restored), 'Member restored successfully.');
    }
}
