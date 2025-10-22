<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * DataTable Facade
 *
 * @method static \SubhashLadumor\DataTablePro\DataTable\Builder eloquent(\Illuminate\Database\Eloquent\Builder $query)
 * @method static \SubhashLadumor\DataTablePro\DataTable\Builder query(\Illuminate\Database\Query\Builder $query)
 * @method static \SubhashLadumor\DataTablePro\DataTable\Builder collection(\Illuminate\Support\Collection $collection)
 *
 * @see \SubhashLadumor\DataTablePro\DataTable\Builder
 */
class DataTable extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'datatable';
    }
}
