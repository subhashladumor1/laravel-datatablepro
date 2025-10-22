<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\Traits;

/**
 * WhitelistsRequests
 *
 * Trait for validating whitelisted columns and relationships in DataTable requests.
 */
trait WhitelistsRequests
{
    /** @var array<string> */
    protected array $whitelistedColumns = [];

    /** @var array<string> */
    protected array $whitelistedRelationships = [];

    /**
     * Set whitelisted columns.
     *
     * @param array<string> $columns
     */
    public function whitelistColumns(array $columns): self
    {
        $this->whitelistedColumns = $columns;
        return $this;
    }

    /**
     * Set whitelisted relationships.
     *
     * @param array<string> $relationships
     */
    public function whitelistRelationships(array $relationships): self
    {
        $this->whitelistedRelationships = $relationships;
        return $this;
    }

    /**
     * Check if a column is whitelisted.
     */
    public function isColumnWhitelisted(string $column): bool
    {
        return in_array($column, $this->whitelistedColumns, true);
    }

    /**
     * Check if a relationship is whitelisted.
     */
    public function isRelationshipWhitelisted(string $relationship): bool
    {
        return in_array($relationship, $this->whitelistedRelationships, true);
    }

    /**
     * Get whitelisted columns.
     *
     * @return array<string>
     */
    public function getWhitelistedColumns(): array
    {
        return $this->whitelistedColumns;
    }

    /**
     * Get whitelisted relationships.
     *
     * @return array<string>
     */
    public function getWhitelistedRelationships(): array
    {
        return $this->whitelistedRelationships;
    }

    /**
     * Validate that all requested columns are whitelisted.
     *
     * @param array<string> $columns
     * @throws \SubhashLadumor\DataTablePro\Exceptions\DataTableException
     */
    protected function validateColumns(array $columns): void
    {
        foreach ($columns as $column) {
            if (!$this->isColumnWhitelisted($column)) {
                throw \SubhashLadumor\DataTablePro\Exceptions\DataTableException::invalidColumn($column);
            }
        }
    }

    /**
     * Validate that all requested relationships are whitelisted.
     *
     * @param array<string> $relationships
     * @throws \SubhashLadumor\DataTablePro\Exceptions\DataTableException
     */
    protected function validateRelationships(array $relationships): void
    {
        foreach ($relationships as $relationship) {
            if (!$this->isRelationshipWhitelisted($relationship)) {
                throw \SubhashLadumor\DataTablePro\Exceptions\DataTableException::invalidRelationship($relationship);
            }
        }
    }
}
