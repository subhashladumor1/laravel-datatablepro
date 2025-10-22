<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use SubhashLadumor\DataTablePro\Http\Requests\DataTableRequest;

/**
 * DataTableController
 *
 * Base controller for DataTable operations.
 */
class DataTableController extends Controller
{
    /**
     * Handle DataTable AJAX request.
     * This method should be extended by your application controllers.
     */
    public function index(DataTableRequest $request): JsonResponse
    {
        // This is a base implementation
        // Extend this in your own controllers
        return response()->json([
            'draw' => $request->input('draw', 1),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
        ]);
    }

    /**
     * Download queued export file.
     */
    public function downloadExport(string $filename): mixed
    {
        $disk = config('datatable.export_disk', 'local');
        
        if (!Storage::disk($disk)->exists($filename)) {
            abort(404, 'Export file not found or has expired.');
        }

        return Storage::disk($disk)->download($filename);
    }
}
