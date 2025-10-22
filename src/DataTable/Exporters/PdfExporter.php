<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\DataTable\Exporters;

use Illuminate\Http\Request;
use SubhashLadumor\DataTablePro\DataTable\Builder;

/**
 * PdfExporter
 *
 * Exports DataTable data to PDF format using dompdf.
 */
class PdfExporter
{
    public function export(Builder $builder, Request $request): mixed
    {
        // Check if dompdf is installed
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            throw new \RuntimeException('barryvdh/laravel-dompdf package is required for PDF export. Run: composer require barryvdh/laravel-dompdf');
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

        $data = $engine->all();

        // Prepare data for PDF
        $rows = [];
        foreach ($data as $row) {
            $pdfRow = [];
            foreach ($columns as $column) {
                $key = $column->getKey();
                $value = is_array($row) ? ($row[$key] ?? '') : ($row->$key ?? '');
                
                if ($formatCallback = $column->getFormatCallback()) {
                    $value = $formatCallback($value, $row);
                }
                
                $pdfRow[$column->getLabel()] = $value;
            }
            $rows[] = $pdfRow;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('datatable::exports.pdf', [
            'columns' => $columns,
            'rows' => $rows,
            'title' => 'Data Export',
        ]);

        return $pdf->download('export-' . date('Y-m-d-His') . '.pdf');
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
