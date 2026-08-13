<?php

declare(strict_types=1);

namespace App\Shared\Support;

use Illuminate\Contracts\Pagination\CursorPaginator;

/**
 * Cursor-pagination counterpart to PaginationMeta (API_SPECIFICATION.md
 * §4.4) - used by every public content listing (Standard CRUD Contract
 * §8.1) and Notifications.
 */
final class CursorPaginationMeta
{
    /**
     * @return array<string, mixed>
     */
    public static function fromPaginator(CursorPaginator $paginator): array
    {
        return [
            'pagination' => [
                'strategy' => 'cursor',
                'perPage' => $paginator->perPage(),
                'nextCursor' => $paginator->nextCursor()?->encode(),
                'prevCursor' => $paginator->previousCursor()?->encode(),
                'hasMore' => $paginator->hasMorePages(),
            ],
        ];
    }
}
