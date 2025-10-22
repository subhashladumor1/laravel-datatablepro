<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\DataTable;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SubhashLadumor\DataTablePro\Contracts\DataTableEngineInterface;
use SubhashLadumor\DataTablePro\DataTable\QueryHandler\CollectionHandler;
use SubhashLadumor\DataTablePro\DataTable\QueryHandler\EloquentQueryHandler;
use SubhashLadumor\DataTablePro\DataTable\QueryHandler\QueryBuilderHandler;
use SubhashLadumor\DataTablePro\Exceptions\DataTableException;

/**
 * Builder
 *
 * Fluent builder for creating DataTable instances with advanced features.
 */
class Builder
{
    protected ?DataTableEngineInterface $engine = null;
    protected ColumnCollection $columns;
    /** @var array<int, Filter> */
    protected array $filters = [];
    /** @var array<string> */
    protected array $with = [];
    protected bool $exportable = false;
    protected int $pageLength = 10;
    protected bool $responsive = false;
    protected ?string $persistKey = null;
    protected bool $virtualScroll = false;
    protected bool $realtime = false;
    /** @var array<string> */
    protected array $whitelistedColumns = [];
    /** @var array<string> */
    protected array $whitelistedRelationships = [];

    public function __construct()
    {
        $this->columns = new ColumnCollection();
        $this->pageLength = config('datatable.page_length', 10);
    }

    public static function make(): self
    {
        return new static();
    }

    public function eloquent(EloquentBuilder $query): self
    {
        $this->engine = new EloquentQueryHandler($query, $this->columns);
        return $this;
    }

    public function query(QueryBuilder $query): self
    {
        $this->engine = new QueryBuilderHandler($query, $this->columns);
        return $this;
    }

    public function collection(Collection $collection): self
    {
        $this->engine = new CollectionHandler($collection, $this->columns);
        return $this;
    }

    /**
     * @param array<int, Column> $columns
     */
    public function columns(array $columns): self
    {
        foreach ($columns as $column) {
            $this->columns->add($column);
            $this->whitelistedColumns[] = $column->getKey();
            if ($column->getRelationship()) {
                $this->whitelistedRelationships[] = $column->getRelationship();
            }
        }
        return $this;
    }

    /**
     * @param array<int, Filter> $filters
     */
    public function filters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function searchable(bool $searchable = true): self
    {
        foreach ($this->columns->all() as $column) {
            $column->searchable($searchable);
        }
        return $this;
    }

    public function orderable(bool $orderable = true): self
    {
        foreach ($this->columns->all() as $column) {
            $column->orderable($orderable);
        }
        return $this;
    }

    /**
     * @param array<string> $relationships
     */
    public function with(array $relationships): self
    {
        $this->with = $relationships;
        $this->whitelistedRelationships = array_merge($this->whitelistedRelationships, $relationships);
        
        if ($this->engine instanceof EloquentQueryHandler) {
            $this->engine->setEagerLoad($relationships);
        }
        
        return $this;
    }

    public function exportable(bool $exportable = true): self
    {
        $this->exportable = $exportable;
        return $this;
    }

    public function pageLength(int $length): self
    {
        $this->pageLength = min($length, config('datatable.max_page_length', 100));
        return $this;
    }

    public function responsive(bool $responsive = true): self
    {
        $this->responsive = $responsive;
        return $this;
    }

    public function persistColumns(string $key): self
    {
        $this->persistKey = $key;
        return $this;
    }

    public function virtualScroll(bool $enabled = true): self
    {
        $this->virtualScroll = $enabled;
        return $this;
    }

    public function realtime(bool $enabled = true): self
    {
        $this->realtime = $enabled;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(?Request $request = null): array
    {
        if (!$this->engine) {
            throw new DataTableException('No data source configured. Use eloquent(), query(), or collection().');
        }

        $request = $request ?? request();
        
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', $this->pageLength);
        $search = $request->input('search.value', '');
        $orders = $this->parseOrders($request);
        $filters = $this->parseFilters($request);

        // Validate and sanitize inputs
        $this->validateRequest($request, $orders, $filters);

        // Apply filters
        if (!empty($filters)) {
            $this->engine->applyFilters($filters);
        }

        // Apply global search
        if (!empty($search) && config('datatable.global_search', true)) {
            $this->engine->applyGlobalSearch($search);
        }

        // Apply ordering
        if (!empty($orders)) {
            $this->engine->applyOrdering($orders);
        }

        // Get paginated results
        $result = $this->engine->paginate($start, $length);

        // Transform response
        $transformer = new ResponseTransformer($this->columns);
        $data = $transformer->transform($result['data']);

        return [
            'draw' => $draw,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $data,
        ];
    }

    public function toResponse(?Request $request = null): JsonResponse
    {
        return response()->json($this->toArray($request));
    }

    public function toExport(string $format, ?Request $request = null): mixed
    {
        if (!$this->exportable) {
            throw new DataTableException('Export is not enabled for this DataTable.');
        }

        $exportManager = app(ExportManager::class);
        return $exportManager->export($this, $format, $request ?? request());
    }

    /**
     * @return array<int, array{column: string, dir: string}>
     */
    protected function parseOrders(Request $request): array
    {
        $orders = [];
        $orderData = $request->input('order', []);

        foreach ($orderData as $order) {
            $columnIndex = (int) $order['column'];
            $dir = strtolower($order['dir']) === 'asc' ? 'asc' : 'desc';
            
            $columnsArray = $this->columns->all();
            if (isset($columnsArray[$columnIndex])) {
                $column = $columnsArray[$columnIndex];
                if ($column->isOrderable()) {
                    $orders[] = [
                        'column' => $column->getKey(),
                        'dir' => $dir,
                    ];
                }
            }
        }

        return $orders;
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseFilters(Request $request): array
    {
        $filters = [];
        $filterData = $request->input('filters', []);

        foreach ($this->filters as $filter) {
            $key = $filter->getKey();
            if (isset($filterData[$key]) && $filterData[$key] !== '' && $filterData[$key] !== null) {
                $filters[$key] = [
                    'type' => $filter->getType(),
                    'value' => $filterData[$key],
                    'callback' => $filter->getCallback(),
                ];
            }
        }

        return $filters;
    }

    /**
     * @param array<int, array{column: string, dir: string}> $orders
     * @param array<string, mixed> $filters
     */
    protected function validateRequest(Request $request, array $orders, array $filters): void
    {
        // Validate order columns
        foreach ($orders as $order) {
            if (!in_array($order['column'], $this->whitelistedColumns, true)) {
                throw new DataTableException("Column '{$order['column']}' is not whitelisted for ordering.");
            }
        }

        // Validate filter keys
        foreach (array_keys($filters) as $filterKey) {
            $found = false;
            foreach ($this->filters as $filter) {
                if ($filter->getKey() === $filterKey) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                throw new DataTableException("Filter '{$filterKey}' is not allowed.");
            }
        }
    }

    public function getColumns(): ColumnCollection
    {
        return $this->columns;
    }

    /**
     * @return array<int, Filter>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getEngine(): ?DataTableEngineInterface
    {
        return $this->engine;
    }

    public function isExportable(): bool
    {
        return $this->exportable;
    }

    public function getPageLength(): int
    {
        return $this->pageLength;
    }

    public function isResponsive(): bool
    {
        return $this->responsive;
    }

    public function getPersistKey(): ?string
    {
        return $this->persistKey;
    }

    public function isVirtualScroll(): bool
    {
        return $this->virtualScroll;
    }

    public function isRealtime(): bool
    {
        return $this->realtime;
    }

    /**
     * @return array<string>
     */
    public function getWhitelistedColumns(): array
    {
        return $this->whitelistedColumns;
    }

    /**
     * @return array<string>
     */
    public function getWhitelistedRelationships(): array
    {
        return $this->whitelistedRelationships;
    }
}
