<?php

declare(strict_types=1);

namespace App\Shared\Support;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * One shared way to build the offset-pagination meta block
 * (API_SPECIFICATION.md §4.4), instead of a bespoke Resource Collection
 * class per module repeating the same shape.
 */
final class PaginationMeta
{
    /**
     * @return array<string, mixed>
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): array
    {
        return [
            'pagination' => [
                'strategy' => 'offset',
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
                'lastPage' => $paginator->lastPage(),
            ],
        ];
    }
}
