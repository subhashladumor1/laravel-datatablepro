<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\DataTable;

use Illuminate\Support\Arr;

/**
 * ResponseTransformer
 *
 * Transforms raw data rows into formatted response data with column rendering and XSS protection.
 */
class ResponseTransformer
{
    protected ColumnCollection $columns;
    protected bool $xssProtection;

    public function __construct(ColumnCollection $columns)
    {
        $this->columns = $columns;
        $this->xssProtection = config('datatable.xss_protection', true);
    }

    /**
     * @param array<int, mixed> $rows
     * @return array<int, array<string, mixed>>
     */
    public function transform(array $rows): array
    {
        return array_map(fn($row) => $this->transformRow($row), $rows);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformRow(mixed $row): array
    {
        $transformed = [];

        foreach ($this->columns->all() as $column) {
            $key = $column->getKey();
            $value = $this->extractValue($row, $column);

            // Apply server-side render callback
            if ($render = $column->getRender()) {
                if (is_callable($render)) {
                    $value = $render($value, $row);
                }
            }

            // Apply format callback
            if ($formatCallback = $column->getFormatCallback()) {
                $value = $formatCallback($value, $row);
            }

            // Apply XSS protection unless raw is enabled
            if ($this->xssProtection && !$column->isRaw() && is_string($value)) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }

            $transformed[$key] = $value;
        }

        return $transformed;
    }

    protected function extractValue(mixed $row, Column $column): mixed
    {
        $key = $column->getKey();
        $relationship = $column->getRelationship();

        // Handle array/object access
        if (is_array($row)) {
            if ($relationship) {
                return Arr::get($row, str_replace('.', '.', $relationship . '.' . $key), $column->getDefault());
            }
            return Arr::get($row, $key, $column->getDefault());
        }

        // Handle Eloquent models
        if (is_object($row)) {
            if ($relationship) {
                // Handle dot notation relationships
                $relationshipParts = explode('.', $relationship);
                $related = $row;
                foreach ($relationshipParts as $part) {
                    if (isset($related->$part)) {
                        $related = $related->$part;
                    } else {
                        return $column->getDefault();
                    }
                }
                return $related->$key ?? $column->getDefault();
            }

            return $row->$key ?? $column->getDefault();
        }

        return $column->getDefault();
    }
}
