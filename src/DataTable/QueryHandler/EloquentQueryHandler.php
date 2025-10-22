<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\DataTable\QueryHandler;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use SubhashLadumor1\DataTablePro\Contracts\DataTableEngineInterface;
use SubhashLadumor1\DataTablePro\DataTable\ColumnCollection;

/**
 * EloquentQueryHandler
 *
 * Handles DataTable queries for Eloquent Builder instances.
 */
class EloquentQueryHandler implements DataTableEngineInterface
{
    protected Builder $query;
    protected Builder $originalQuery;
    protected ColumnCollection $columns;
    /** @var array<string> */
    protected array $eagerLoad = [];
    protected ?int $totalCount = null;
    protected ?int $filteredCount = null;

    public function __construct(Builder $query, ColumnCollection $columns)
    {
        $this->query = clone $query;
        $this->originalQuery = clone $query;
        $this->columns = $columns;
    }

    /**
     * @param array<string> $relationships
     */
    public function setEagerLoad(array $relationships): void
    {
        $this->eagerLoad = $relationships;
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
        $column = $this->columns->get($key);
        if (!$column) {
            return;
        }

        if ($column->getRelationship()) {
            $this->query->whereHas($column->getRelationship(), function ($q) use ($key, $value) {
                $q->where($key, 'LIKE', "%{$value}%");
            });
        } else {
            $this->query->where($key, 'LIKE', "%{$value}%");
        }
    }

    protected function applySelectFilter(string $key, mixed $value): void
    {
        $column = $this->columns->get($key);
        if (!$column) {
            return;
        }

        if ($column->getRelationship()) {
            $this->query->whereHas($column->getRelationship(), function ($q) use ($key, $value) {
                $q->where($key, $value);
            });
        } else {
            $this->query->where($key, $value);
        }
    }

    protected function applyDateRangeFilter(string $key, mixed $value): void
    {
        if (!is_array($value) || !isset($value['from'], $value['to'])) {
            return;
        }

        $column = $this->columns->get($key);
        if (!$column) {
            return;
        }

        if ($column->getRelationship()) {
            $this->query->whereHas($column->getRelationship(), function ($q) use ($key, $value) {
                $q->whereBetween($key, [$value['from'], $value['to']]);
            });
        } else {
            $this->query->whereBetween($key, [$value['from'], $value['to']]);
        }
    }

    protected function applyNumericRangeFilter(string $key, mixed $value): void
    {
        if (!is_array($value)) {
            return;
        }

        $column = $this->columns->get($key);
        if (!$column) {
            return;
        }

        $applyRange = function ($q) use ($key, $value) {
            if (isset($value['min'])) {
                $q->where($key, '>=', $value['min']);
            }
            if (isset($value['max'])) {
                $q->where($key, '<=', $value['max']);
            }
        };

        if ($column->getRelationship()) {
            $this->query->whereHas($column->getRelationship(), $applyRange);
        } else {
            $applyRange($this->query);
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
                $relationship = $column->getRelationship();

                if ($relationship) {
                    $query->orWhereHas($relationship, function ($q) use ($key, $search) {
                        $q->where($key, 'LIKE', "%{$search}%");
                    });
                } else {
                    $query->orWhere($key, 'LIKE', "%{$search}%");
                }
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

            $relationship = $column->getRelationship();

            if ($relationship) {
                // Order by relationship column using JOIN
                $this->applyRelationshipOrdering($column, $direction);
            } else {
                $this->query->orderBy($columnKey, $direction);
            }
        }

        return $this;
    }

    protected function applyRelationshipOrdering(mixed $column, string $direction): void
    {
        $relationship = $column->getRelationship();
        $columnKey = $column->getKey();
        $model = $this->query->getModel();
        
        // Get the relationship instance
        if (!method_exists($model, $relationship)) {
            return;
        }

        $relationInstance = $model->$relationship();
        
        // For belongsTo relationships, we can use a JOIN
        if ($relationInstance instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
            $relatedTable = $relationInstance->getRelated()->getTable();
            $foreignKey = $relationInstance->getForeignKeyName();
            $ownerKey = $relationInstance->getOwnerKeyName();
            $table = $model->getTable();
            
            // Avoid duplicate joins
            $joinAlias = $relatedTable . '_for_' . $relationship;
            
            $this->query->leftJoin(
                "{$relatedTable} as {$joinAlias}",
                "{$table}.{$foreignKey}",
                '=',
                "{$joinAlias}.{$ownerKey}"
            )->orderBy("{$joinAlias}.{$columnKey}", $direction)
             ->select("{$table}.*");
        } else {
            // For other relationships, use a subquery (less efficient but works)
            // This is a trade-off noted in IMPLEMENTATION.md
            $this->query->orderBy(
                $relationInstance->getRelated()->select($columnKey)
                    ->whereColumn(
                        $relationInstance->getQualifiedForeignKeyName(),
                        $relationInstance->getQualifiedParentKeyName()
                    )
                    ->limit(1),
                $direction
            );
        }
    }

    public function paginate(int $start, int $length): array
    {
        // Cache total count before filters
        $this->totalCount = $this->getTotalCount();

        // Clone query to count filtered results
        $countQuery = clone $this->query;
        $this->filteredCount = $countQuery->count();

        // Apply eager loading
        if (!empty($this->eagerLoad)) {
            $this->query->with($this->eagerLoad);
        }

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
        if (!empty($this->eagerLoad)) {
            $this->query->with($this->eagerLoad);
        }

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
