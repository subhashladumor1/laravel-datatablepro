<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\DataTable;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use SubhashLadumor\DataTablePro\DataTable\Exporters\CsvExporter;
use SubhashLadumor\DataTablePro\DataTable\Exporters\ImageExporter;
use SubhashLadumor\DataTablePro\DataTable\Exporters\PdfExporter;
use SubhashLadumor\DataTablePro\DataTable\Exporters\XlsxExporter;
use SubhashLadumor\DataTablePro\Exceptions\DataTableException;

/**
 * ExportManager
 *
 * Manages export operations for DataTable instances.
 */
class ExportManager
{
    protected Application $app;
    /** @var array<string, string> */
    protected array $exporters = [
        'csv' => CsvExporter::class,
        'xlsx' => XlsxExporter::class,
        'pdf' => PdfExporter::class,
        'image' => ImageExporter::class,
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function export(Builder $builder, string $format, Request $request): mixed
    {
        $format = strtolower($format);

        if (!isset($this->exporters[$format])) {
            throw new DataTableException("Unsupported export format: {$format}");
        }

        $exporterClass = $this->exporters[$format];
        $exporter = $this->app->make($exporterClass);

        return $exporter->export($builder, $request);
    }

    /**
     * Register a custom exporter.
     */
    public function registerExporter(string $format, string $exporterClass): void
    {
        $this->exporters[$format] = $exporterClass;
    }

    /**
     * Get available export formats.
     *
     * @return array<int, string>
     */
    public function getAvailableFormats(): array
    {
        return array_keys($this->exporters);
    }
}
