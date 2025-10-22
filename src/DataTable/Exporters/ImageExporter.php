<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\DataTable\Exporters;

use Illuminate\Http\Request;
use SubhashLadumor\DataTablePro\DataTable\Builder;

/**
 * ImageExporter
 *
 * Server-side fallback for image export.
 * Primary image export is handled client-side using html2canvas.
 */
class ImageExporter
{
    public function export(Builder $builder, Request $request): mixed
    {
        // This is a placeholder for server-side image generation
        // In practice, image export is typically handled client-side via html2canvas
        
        return response()->json([
            'message' => 'Image export should be handled client-side using html2canvas.',
            'hint' => 'Use the client-side export functionality in dtable.core.js',
        ], 400);
    }
}
