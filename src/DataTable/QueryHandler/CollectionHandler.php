<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\DataTable\QueryHandler;

use Illuminate\Support\Collection;
use SubhashLadumor\DataTablePro\Contracts\DataTableEngineInterface;
use SubhashLadumor\DataTablePro\DataTable\ColumnCollection;

/**
 * CollectionHandler
 *
 * Handles DataTable queries for Collection instances.
 */
class CollectionHandler implements DataTableEngineInterface
{
    protected Collection $collection;
    protected Collection $originalCollection;
    protected ColumnCollection $columns;
    protected ?int $totalCount = null;
    protected ?int $filteredCount = null;

    public function __construct(Collection $collection, ColumnCollection $columns)
    {
        $this->collection = $collection;
        $this->originalCollection = clone $collection;
        $this->columns = $columns;
    }

    public function applyFilters(array $filters): self
    {
        foreach ($filters as $key => $filter) {
            $type = $filter['type'];
            $value = $filter['value'];
            $callback = $filter['callback'] ?? null;

            if ($callback && is_callable($callback)) {
                $this->collection = $this->collection->filter(fn($item) => $callback($item, $value));
                continue;
            }

            match ($type) {
                'text' => $this->applyTextFilter($key, $value),
                'select' => $this->applySelectFilter($key, $value),
                'date-range' => $this->applyDateRangeFilter($key, $value),
                'numeric-range' => $this->applyNumericRangeFilter($key, $value),
                default => null,
            };
        }

        return $this;
    }

    protected function applyTextFilter(string $key, mixed $value): void
    {
        $this->collection = $this->collection->filter(function ($item) use ($key, $value) {
            $itemValue = is_array($item) ? ($item[$key] ?? null) : ($item->$key ?? null);
            return $itemValue && str_contains(strtolower((string)$itemValue), strtolower($value));
        });
    }

    protected function applySelectFilter(string $key, mixed $value): void
    {
        $this->collection = $this->collection->filter(function ($item) use ($key, $value) {
            $itemValue = is_array($item) ? ($item[$key] ?? null) : ($item->$key ?? null);
            return $itemValue == $value;
        });
    }

    protected function applyDateRangeFilter(string $key, mixed $value): void
    {
        if (!is_array($value) || !isset($value['from'], $value['to'])) {
            return;
        }

        $this->collection = $this->collection->filter(function ($item) use ($key, $value) {
            $itemValue = is_array($item) ? ($item[$key] ?? null) : ($item->$key ?? null);
            return $itemValue >= $value['from'] && $itemValue <= $value['to'];
        });
    }

    protected function applyNumericRangeFilter(string $key, mixed $value): void
    {
        if (!is_array($value)) {
            return;
        }

        $this->collection = $this->collection->filter(function ($item) use ($key, $value) {
            $itemValue = is_array($item) ? ($item[$key] ?? null) : ($item->$key ?? null);
            
            if (isset($value['min']) && $itemValue < $value['min']) {
                return false;
            }
            if (isset($value['max']) && $itemValue > $value['max']) {
                return false;
            }
            return true;
        });
    }

    public function applyGlobalSearch(string $search): self
    {
        $searchableColumns = $this->columns->searchable();

        if (empty($searchableColumns)) {
            return $this;
        }

        $this->collection = $this->collection->filter(function ($item) use ($search, $searchableColumns) {
            foreach ($searchableColumns as $column) {
                $key = $column->getKey();
                $value = is_array($item) ? ($item[$key] ?? null) : ($item->$key ?? null);
                
                if ($value && str_contains(strtolower((string)$value), strtolower($search))) {
                    return true;
                }
            }
            return false;
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

            $this->collection = $this->collection->sortBy(
                fn($item) => is_array($item) ? ($item[$columnKey] ?? null) : ($item->$columnKey ?? null),
                SORT_REGULAR,
                $direction === 'desc'
            )->values();
        }

        return $this;
    }

    public function paginate(int $start, int $length): array
    {
        // Cache total count before filters
        $this->totalCount = $this->getTotalCount();

        // Get filtered count
        $this->filteredCount = $this->collection->count();

        // Get paginated results
        $data = $this->collection
            ->slice($start, $length)
            ->values()
            ->toArray();

        return [
            'data' => $data,
            'recordsTotal' => $this->totalCount,
            'recordsFiltered' => $this->filteredCount,
        ];
    }

    public function all(): array
    {
        return $this->collection->toArray();
    }

    public function getTotalCount(): int
    {
        if ($this->totalCount !== null) {
            return $this->totalCount;
        }

        return $this->originalCollection->count();
    }

    public function getFilteredCount(): int
    {
        if ($this->filteredCount !== null) {
            return $this->filteredCount;
        }

        return $this->collection->count();
    }
}
