<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\DataTable\Exporters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use SubhashLadumor1\DataTablePro\DataTable\Builder;

/**
 * CsvExporter
 *
 * Exports DataTable data to CSV format with streaming support.
 */
class CsvExporter
{
    public function export(Builder $builder, Request $request): mixed
    {
        $engine = $builder->getEngine();
        $columns = $builder->getColumns()->exportable();

        if (!$engine) {
            throw new \RuntimeException('No data source configured.');
        }

        // Apply filters and search from request
        $search = $request->input('search.value', '');
        $filters = $request->input('filters', []);

        if (!empty($filters)) {
            $parsedFilters = $this->parseFilters($filters, $builder);
            $engine->applyFilters($parsedFilters);
        }

        if (!empty($search)) {
            $engine->applyGlobalSearch($search);
        }

        // Stream the CSV response
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export-' . date('Y-m-d-His') . '.csv"',
        ];

        $callback = function () use ($engine, $columns) {
            $file = fopen('php://output', 'w');

            // Write headers
            $headers = [];
            foreach ($columns as $column) {
                $headers[] = $column->getLabel();
            }
            fputcsv($file, $headers);

            // Stream data in chunks
            $chunkSize = config('datatable.export_chunk_size', 1000);
            $data = $engine->all();
            
            foreach (array_chunk($data, $chunkSize) as $chunk) {
                foreach ($chunk as $row) {
                    $csvRow = [];
                    foreach ($columns as $column) {
                        $key = $column->getKey();
                        $value = is_array($row) ? ($row[$key] ?? '') : ($row->$key ?? '');
                        
                        // Apply format callback
                        if ($formatCallback = $column->getFormatCallback()) {
                            $value = $formatCallback($value, $row);
                        }
                        
                        $csvRow[] = $value;
                    }
                    fputcsv($file, $csvRow);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
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
