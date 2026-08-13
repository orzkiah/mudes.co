<?php

declare(strict_types=1);

namespace App\Shared\Support;

use Illuminate\Http\JsonResponse;

/**
 * Builds the standard response envelope (API_SPECIFICATION.md §2). Every
 * endpoint, success or failure, returns this shape.
 */
final class ApiResponse
{
    /**
     * @param array<string, mixed> $meta
     */
    public static function success(mixed $data = null, string $message = 'Success', array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => (object) $meta,
            'errors' => null,
        ], $status);
    }

    /**
     * RFC 7807-inspired error envelope (API_SPECIFICATION.md §2.3). Built for
     * the centralized Exception Handler to call per exception type; Controllers
     * never call this directly (IMPLEMENTATION_RULES.md §16).
     *
     * @param array<string, mixed>|null $fields
     */
    public static function error(
        string $message,
        string $type,
        string $title,
        int $status,
        ?string $detail = null,
        ?array $fields = null,
    ): JsonResponse {
        $errors = [
            'type' => $type,
            'title' => $title,
            'status' => $status,
            'detail' => $detail ?? $message,
            'instance' => request()->path(),
        ];

        if ($fields !== null) {
            $errors['fields'] = $fields;
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'meta' => (object) [],
            'errors' => $errors,
        ], $status);
    }
}
