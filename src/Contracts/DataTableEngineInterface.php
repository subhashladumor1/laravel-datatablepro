<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\Contracts;

use Illuminate\Http\Request;

/**
 * DataTableEngineInterface
 *
 * Contract for DataTable query handlers (Eloquent, QueryBuilder, Collection).
 */
interface DataTableEngineInterface
{
    /**
     * Apply filters to the query.
     *
     * @param array<string, mixed> $filters
     */
    public function applyFilters(array $filters): self;

    /**
     * Apply global search across searchable columns.
     */
    public function applyGlobalSearch(string $search): self;

    /**
     * Apply ordering.
     *
     * @param array<int, array{column: string, dir: string}> $orders
     */
    public function applyOrdering(array $orders): self;

    /**
     * Get paginated results.
     *
     * @return array{data: array<int, mixed>, recordsTotal: int, recordsFiltered: int}
     */
    public function paginate(int $start, int $length): array;

    /**
     * Get all results (for export).
     *
     * @return array<int, mixed>
     */
    public function all(): array;

    /**
     * Get total count before any filters.
     */
    public function getTotalCount(): int;

    /**
     * Get filtered count after applying filters and search.
     */
    public function getFilteredCount(): int;
}
