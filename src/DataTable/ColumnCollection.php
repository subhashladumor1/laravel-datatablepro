<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\DataTable;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * ColumnCollection
 *
 * Collection of Column instances with convenient access methods.
 */
class ColumnCollection implements IteratorAggregate, Countable
{
    /** @var array<string, Column> */
    protected array $columns = [];

    /**
     * @param array<int, Column> $columns
     */
    public function __construct(array $columns = [])
    {
        foreach ($columns as $column) {
            $this->add($column);
        }
    }

    public function add(Column $column): self
    {
        $this->columns[$column->getKey()] = $column;
        return $this;
    }

    public function get(string $key): ?Column
    {
        return $this->columns[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->columns[$key]);
    }

    public function remove(string $key): self
    {
        unset($this->columns[$key]);
        return $this;
    }

    /**
     * @return array<int, Column>
     */
    public function all(): array
    {
        return array_values($this->columns);
    }

    /**
     * @return array<int, Column>
     */
    public function searchable(): array
    {
        return array_filter($this->all(), fn(Column $col) => $col->isSearchable());
    }

    /**
     * @return array<int, Column>
     */
    public function orderable(): array
    {
        return array_filter($this->all(), fn(Column $col) => $col->isOrderable());
    }

    /**
     * @return array<int, Column>
     */
    public function visible(): array
    {
        return array_filter($this->all(), fn(Column $col) => $col->isVisible());
    }

    /**
     * @return array<int, Column>
     */
    public function exportable(): array
    {
        return array_filter($this->all(), fn(Column $col) => $col->isExportable());
    }

    /**
     * @return array<string, string>
     */
    public function getKeyLabelMap(): array
    {
        $map = [];
        foreach ($this->columns as $key => $column) {
            $map[$key] = $column->getLabel();
        }
        return $map;
    }

    public function count(): int
    {
        return count($this->columns);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->columns);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(fn(Column $col) => $col->toArray(), $this->all());
    }
}
