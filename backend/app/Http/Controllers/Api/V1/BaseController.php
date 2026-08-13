<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * Every versioned API controller extends this. Controllers may only receive
 * a Request, authorize, call one Service method, and return an API Resource
 * (IMPLEMENTATION_RULES.md §4) - never build an error response themselves,
 * which is why only the success() shortcut lives here; errors come from
 * thrown exceptions caught by the centralized Exception Handler.
 */
abstract class BaseController extends Controller
{
    /**
     * @param array<string, mixed> $meta
     */
    protected function success(mixed $data = null, string $message = 'Success', array $meta = [], int $status = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $meta, $status);
    }
}
