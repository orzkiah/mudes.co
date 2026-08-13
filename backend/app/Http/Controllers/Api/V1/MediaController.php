<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Models\Media;
use App\Domain\Models\MediaOwner;
use App\Http\Requests\UploadMediaRequest;
use App\Http\Resources\MediaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Handles standalone file uploads for the Media Manager
 * (API_SPECIFICATION.md §9.27).
 *
 * Flow:
 *   POST /admin/media  multipart/form-data  { file, collection }
 *     → validate MIME/size/collection (UploadMediaRequest)
 *     → store via Spatie FileAdder pipeline on a MediaOwner placeholder
 *     → return the persisted Media row UUID so the frontend can attach it
 *       to an entity (Member.photoMediaId, Article.coverMediaId, …)
 */
class MediaController extends BaseController
{
    public function store(UploadMediaRequest $request): JsonResponse
    {
        $collection = $request->validated('collection');

        // Build a transient MediaOwner. It is never saved to the database;
        // its UUID is used only as model_id on the media row. Spatie reads
        // $owner->id and $owner->getMorphClass() to populate those columns.
        $owner = new MediaOwner();
        /** @phpstan-ignore-next-line assign.propertyType — id is a UUID string, parent declares mixed */
        $owner->id = (string) Str::uuid7();

        try {
            /** @var Media $media */
            $media = $owner
                ->addMediaFromRequest('file')
                ->usingName($request->file('file')?->getClientOriginalName() ?? 'upload')
                ->withCustomProperties(['uploadedBy' => $request->user()?->id])
                ->toMediaCollection($collection);

            return $this->success(
                data: (new MediaResource($media))->resolve(),
                message: 'Media uploaded successfully.',
                status: 201,
            );
        } catch (\Throwable $e) {
            return \App\Shared\Support\ApiResponse::error(
                message: 'Gagal mengunggah file. ' . $e->getMessage(),
                type: 'urn:mudes:error:validation-failed',
                title: 'Validation Failed',
                status: 422,
                fields: ['file' => [$e->getMessage()]],
            );
        }
    }
}
