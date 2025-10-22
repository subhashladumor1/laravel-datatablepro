<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\DataTable\Exporters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use SubhashLadumor1\DataTablePro\DataTable\Builder;
use SubhashLadumor1\DataTablePro\Jobs\ExportDataTableJob;

/**
 * XlsxExporter
 *
 * Exports DataTable data to XLSX format using maatwebsite/excel.
 * Supports chunked and queued exports for large datasets.
 */
class XlsxExporter
{
    public function export(Builder $builder, Request $request): mixed
    {
        // Check if maatwebsite/excel is installed
        if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            throw new \RuntimeException('maatwebsite/excel package is required for XLSX export. Run: composer require maatwebsite/excel');
        }

        $engine = $builder->getEngine();
        $columns = $builder->getColumns()->exportable();

        if (!$engine) {
            throw new \RuntimeException('No data source configured.');
        }

        // Apply filters and search
        $search = $request->input('search.value', '');
        $filters = $request->input('filters', []);

        if (!empty($filters)) {
            $parsedFilters = $this->parseFilters($filters, $builder);
            $engine->applyFilters($parsedFilters);
        }

        if (!empty($search)) {
            $engine->applyGlobalSearch($search);
        }

        // Determine if we should queue the export
        $totalRecords = $engine->getFilteredCount();
        $shouldQueue = $totalRecords > config('datatable.export_chunk_size', 1000);

        if ($shouldQueue) {
            return $this->queuedExport($builder, $request, $engine, $columns);
        }

        return $this->immediateExport($engine, $columns);
    }

    protected function immediateExport($engine, array $columns): mixed
    {
        $data = $engine->all();
        
        return \Maatwebsite\Excel\Facades\Excel::download(
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
            'export-' . date('Y-m-d-His') . '.xlsx'
        );
    }

    protected function queuedExport(Builder $builder, Request $request, $engine, array $columns): mixed
    {
        $filename = 'exports/export-' . uniqid() . '-' . date('Y-m-d-His') . '.xlsx';
        
        // Dispatch export job
        ExportDataTableJob::dispatch($builder, $filename, $request->all())
            ->onQueue(config('datatable.export_queue', 'default'));

        // Generate signed temporary URL
        $url = URL::temporarySignedRoute(
            'datatable.export.download',
            now()->addMinutes(config('datatable.export_url_expiration', 60)),
            ['filename' => $filename]
        );

        return response()->json([
            'message' => 'Export queued successfully',
            'download_url' => $url,
            'filename' => $filename,
        ]);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, array{type: string, value: mixed, callback: callable|null}>
     */
    protected function parseFilters(array $filters, Builder $builder): array
    {
        $parsed = [];
        
        foreach ($builder->getFilters() as $filter) {
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
