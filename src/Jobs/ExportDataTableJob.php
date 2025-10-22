<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use SubhashLadumor\DataTablePro\DataTable\Builder;

/**
 * ExportDataTableJob
 *
 * Background job for exporting large DataTable datasets.
 */
class ExportDataTableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param array<string, mixed> $requestData
     */
    public function __construct(
        protected Builder $builder,
        protected string $filename,
        protected array $requestData
    ) {
    }

    public function handle(): void
    {
        if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            throw new \RuntimeException('maatwebsite/excel package is required.');
        }

        $engine = $this->builder->getEngine();
        $columns = $this->builder->getColumns()->exportable();

        if (!$engine) {
            throw new \RuntimeException('No data source configured.');
        }

        // Apply filters and search from request data
        $search = $this->requestData['search']['value'] ?? '';
        $filters = $this->requestData['filters'] ?? [];

        if (!empty($filters)) {
            $parsedFilters = $this->parseFilters($filters);
            $engine->applyFilters($parsedFilters);
        }

        if (!empty($search)) {
            $engine->applyGlobalSearch($search);
        }

        $data = $engine->all();

        // Create Excel export
        \Maatwebsite\Excel\Facades\Excel::store(
            new class($data, $columns) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
                public function __construct(protected array $data, protected array $columns) {}
                
                public function array(): array
                {
                    return array_map(function ($row) {
                        $exportRow = [];
                        foreach ($this->columns as $column) {
                            $key = $column->getKey();
                            $value = is_array($row) ? ($row[$key] ?? '') : ($row->$key ?? '');
                            
                            if ($formatCallback = $column->getFormatCallback()) {
                                $value = $formatCallback($value, $row);
                            }
                            
                            $exportRow[] = $value;
                        }
                        return $exportRow;
                    }, $this->data);
                }
                
                public function headings(): array
                {
                    return array_map(fn($col) => $col->getLabel(), $this->columns);
                }
            },
            $this->filename,
            config('datatable.export_disk', 'local')
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, array{type: string, value: mixed, callback: callable|null}>
     */
    protected function parseFilters(array $filters): array
    {
        $parsed = [];
        
        foreach ($this->builder->getFilters() as $filter) {
            $key = $filter->getKey();
            if (isset($filters[$key]) && $filters[$key] !== '' && $filters[$key] !== null) {
                $parsed[$key] = [
                    'type' => $filter->getType(),
                    'value' => $filters[$key],
                    'callback' => $filter->getCallback(),
                ];
            }
        }
        
        return $parsed;
    }
}
