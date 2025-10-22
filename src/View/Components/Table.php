<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\View\Components;

use Illuminate\View\Component;

/**
 * Table Component
 *
 * Blade component for rendering DataTable.
 */
class Table extends Component
{
    public string $id;
    public string $ajax;
    public array $columns;
    public int $pageLength;
    public bool $responsive;
    public ?string $persistKey;
    public ?string $exportUrl;
    public array $filters;
    public bool $virtualScroll;
    public bool $realtime;

    /**
     * Create a new component instance.
     *
     * @param array<int, array<string, mixed>> $columns
     * @param array<int, array<string, mixed>> $filters
     */
    public function __construct(
        string $id,
        string $ajax,
        array $columns = [],
        int $pageLength = 10,
        bool $responsive = false,
        ?string $persistKey = null,
        ?string $exportUrl = null,
        array $filters = [],
        bool $virtualScroll = false,
        bool $realtime = false
    ) {
        $this->id = $id;
        $this->ajax = $ajax;
        $this->columns = $columns;
        $this->pageLength = $pageLength;
        $this->responsive = $responsive;
        $this->persistKey = $persistKey;
        $this->exportUrl = $exportUrl;
        $this->filters = $filters;
        $this->virtualScroll = $virtualScroll;
        $this->realtime = $realtime;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('datatable::components.table');
    }
}
