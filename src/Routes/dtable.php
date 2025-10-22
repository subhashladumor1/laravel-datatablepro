<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use SubhashLadumor\DataTablePro\Http\Controllers\DataTableController;

/*
|--------------------------------------------------------------------------
| DataTable Pro Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the DataTableServiceProvider and are
| prefixed with 'datatable' by default.
|
*/

Route::group(['prefix' => 'datatable', 'as' => 'datatable.'], function () {
    // Export download route (signed URL)
    Route::get('export/download/{filename}', [DataTableController::class, 'downloadExport'])
        ->name('export.download')
        ->middleware('signed');
});
