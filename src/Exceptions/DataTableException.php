<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\Exceptions;

use Exception;

/**
 * DataTableException
 *
 * Custom exception for DataTable-related errors.
 */
class DataTableException extends Exception
{
    /**
     * Create a new exception for invalid column.
     */
    public static function invalidColumn(string $column): self
    {
        return new self("Column '{$column}' is not whitelisted or does not exist.");
    }

    /**
     * Create a new exception for invalid relationship.
     */
    public static function invalidRelationship(string $relationship): self
    {
        return new self("Relationship '{$relationship}' is not whitelisted or does not exist.");
    }

    /**
     * Create a new exception for missing data source.
     */
    public static function missingDataSource(): self
    {
        return new self('No data source configured. Use eloquent(), query(), or collection().');
    }

    /**
     * Create a new exception for export not enabled.
     */
    public static function exportNotEnabled(): self
    {
        return new self('Export is not enabled for this DataTable. Call exportable(true) on the builder.');
    }

    /**
     * Create a new exception for unsupported export format.
     */
    public static function unsupportedExportFormat(string $format): self
    {
        return new self("Unsupported export format: {$format}");
    }
}
