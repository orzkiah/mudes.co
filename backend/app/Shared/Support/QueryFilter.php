<?php

declare(strict_types=1);

namespace App\Shared\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Generic filter/search/sort value object (BACKEND_ARCHITECTURE.md §6.6). One
 * shared mechanism for every Repository's list endpoint, instead of a bespoke
 * filter class per module.
 */
final class QueryFilter
{
    /**
     * @param array<string, mixed> $filters
     * @param array<int, string> $searchableColumns
     */
    public function __construct(
        private readonly array $filters = [],
        private readonly ?string $search = null,
        private readonly array $searchableColumns = [],
        private readonly ?string $sortColumn = null,
        private readonly bool $sortDescending = false,
    ) {
    }

    /**
     * @param array<int, string> $allowedFilters
     * @param array<int, string> $allowedSorts
     * @param array<int, string> $searchableColumns
     */
    public static function fromRequest(
        Request $request,
        array $allowedFilters = [],
        array $allowedSorts = [],
        array $searchableColumns = [],
    ): self {
        /** @var array<string, mixed> $rawFilters */
        $rawFilters = (array) $request->query('filter', []);
        $filters = array_intersect_key($rawFilters, array_flip($allowedFilters));

        $sort = (string) $request->query('sort', '');
        $sortDescending = str_starts_with($sort, '-');
        $sortColumn = ltrim($sort, '-');

        if ($sortColumn === '' || ! in_array($sortColumn, $allowedSorts, true)) {
            $sortColumn = null;
        }

        $search = $request->query('search');
        $search = is_string($search) && $search !== '' ? $search : null;

        return new self($filters, $search, $searchableColumns, $sortColumn, $sortDescending);
    }

    public function apply(Builder $query): Builder
    {
        foreach ($this->filters as $column => $value) {
            $query->where($column, $value);
        }

        if ($this->search !== null && $this->searchableColumns !== []) {
            $query->where(function (Builder $query): void {
                foreach ($this->searchableColumns as $column) {
                    $query->orWhere($column, 'ILIKE', "%{$this->search}%");
                }
            });
        }

        if ($this->sortColumn !== null) {
            $query->orderBy($this->sortColumn, $this->sortDescending ? 'desc' : 'asc');
        }

        return $query;
    }
}
