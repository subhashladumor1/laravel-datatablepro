<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * DataTable Facade
 *
 * @method static \SubhashLadumor1\DataTablePro\DataTable\Builder eloquent(\Illuminate\Database\Eloquent\Builder $query)
 * @method static \SubhashLadumor1\DataTablePro\DataTable\Builder query(\Illuminate\Database\Query\Builder $query)
 * @method static \SubhashLadumor1\DataTablePro\DataTable\Builder collection(\Illuminate\Support\Collection $collection)
 *
 * @see \SubhashLadumor1\DataTablePro\DataTable\Builder
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
