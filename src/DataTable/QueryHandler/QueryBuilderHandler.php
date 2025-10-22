<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\DataTable\QueryHandler;

use Illuminate\Database\Query\Builder;
use SubhashLadumor1\DataTablePro\Contracts\DataTableEngineInterface;
use SubhashLadumor1\DataTablePro\DataTable\ColumnCollection;

/**
 * QueryBuilderHandler
 *
 * Handles DataTable queries for Query Builder instances.
 */
class QueryBuilderHandler implements DataTableEngineInterface
{
    protected Builder $query;
    protected Builder $originalQuery;
    protected ColumnCollection $columns;
    protected ?int $totalCount = null;
    protected ?int $filteredCount = null;

    public function __construct(Builder $query, ColumnCollection $columns)
    {
        $this->query = clone $query;
        $this->originalQuery = clone $query;
        $this->columns = $columns;
    }

    public function applyFilters(array $filters): self
    {
        foreach ($filters as $key => $filter) {
            $type = $filter['type'];
            $value = $filter['value'];
            $callback = $filter['callback'] ?? null;

            if ($callback && is_callable($callback)) {
                $callback($this->query, $value);
                continue;
            }

            match ($type) {
                'text' => $this->query->where($key, 'LIKE', "%{$value}%"),
                'select' => $this->query->where($key, $value),
                'date-range' => $this->applyDateRangeFilter($key, $value),
                'numeric-range' => $this->applyNumericRangeFilter($key, $value),
                default => null,
            };
        }

        return $this;
    }

    protected function applyDateRangeFilter(string $key, mixed $value): void
    {
        if (!is_array($value) || !isset($value['from'], $value['to'])) {
            return;
        }

        $this->query->whereBetween($key, [$value['from'], $value['to']]);
    }

    protected function applyNumericRangeFilter(string $key, mixed $value): void
    {
        if (!is_array($value)) {
            return;
        }

        if (isset($value['min'])) {
            $this->query->where($key, '>=', $value['min']);
        }
        if (isset($value['max'])) {
            $this->query->where($key, '<=', $value['max']);
        }
    }

    public function applyGlobalSearch(string $search): self
    {
        $searchableColumns = $this->columns->searchable();

        if (empty($searchableColumns)) {
            return $this;
        }

        $this->query->where(function ($query) use ($search, $searchableColumns) {
            foreach ($searchableColumns as $column) {
                $key = $column->getKey();
                $query->orWhere($key, 'LIKE', "%{$search}%");
            }
        });

        return $this;
    }

    public function applyOrdering(array $orders): self
    {
        foreach ($orders as $order) {
            $columnKey = $order['column'];
            $direction = $order['dir'];
            $column = $this->columns->get($columnKey);

            if (!$column || !$column->isOrderable()) {
                continue;
            }

            $this->query->orderBy($columnKey, $direction);
        }

        return $this;
    }

    public function paginate(int $start, int $length): array
    {
        // Cache total count before filters
        $this->totalCount = $this->getTotalCount();

        // Clone query to count filtered results
        $countQuery = clone $this->query;
        $this->filteredCount = $countQuery->count();

        // Get paginated results
        $data = $this->query
            ->skip($start)
            ->take($length)
            ->get()
            ->toArray();

        return [
            'data' => $data,
            'recordsTotal' => $this->totalCount,
            'recordsFiltered' => $this->filteredCount,
        ];
    }

    public function all(): array
    {
        return $this->query->get()->toArray();
    }

    public function getTotalCount(): int
    {
        if ($this->totalCount !== null) {
            return $this->totalCount;
        }

        return $this->originalQuery->count();
    }

    public function getFilteredCount(): int
    {
        if ($this->filteredCount !== null) {
            return $this->filteredCount;
        }

        return $this->query->count();
    }
}
