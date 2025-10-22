<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Page Length
    |--------------------------------------------------------------------------
    |
    | Default number of records per page.
    |
    */
    'page_length' => 10,

    /*
    |--------------------------------------------------------------------------
    | Maximum Page Length
    |--------------------------------------------------------------------------
    |
    | Maximum allowed records per page request.
    |
    */
    'max_page_length' => 100,

    /*
    |--------------------------------------------------------------------------
    | Enable Global Search
    |--------------------------------------------------------------------------
    |
    | Enable or disable global search across all searchable columns.
    |
    */
    'global_search' => true,

    /*
    |--------------------------------------------------------------------------
    | Export Disk
    |--------------------------------------------------------------------------
    |
    | The disk where temporary export files will be stored.
    |
    */
    'export_disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Export Queue
    |--------------------------------------------------------------------------
    |
    | Queue name for export jobs.
    |
    */
    'export_queue' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Export Chunk Size
    |--------------------------------------------------------------------------
    |
    | Number of records to process per chunk when exporting.
    |
    */
    'export_chunk_size' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Export Signed URL Expiration
    |--------------------------------------------------------------------------
    |
    | Number of minutes before export download URLs expire.
    |
    */
    'export_url_expiration' => 60,

    /*
    |--------------------------------------------------------------------------
    | Enable XSS Protection
    |--------------------------------------------------------------------------
    |
    | Automatically escape output to prevent XSS attacks.
    |
    */
    'xss_protection' => true,

    /*
    |--------------------------------------------------------------------------
    | Responsive Breakpoint
    |--------------------------------------------------------------------------
    |
    | Pixel width at which responsive mode activates.
    |
    */
    'responsive_breakpoint' => 768,

    /*
    |--------------------------------------------------------------------------
    | Virtual Scroll Threshold
    |--------------------------------------------------------------------------
    |
    | Number of rows before virtual scrolling is enabled.
    |
    */
    'virtual_scroll_threshold' => 100,

    /*
    |--------------------------------------------------------------------------
    | Debounce Delay (ms)
    |--------------------------------------------------------------------------
    |
    | Milliseconds to wait before triggering search after user input.
    |
    */
    'debounce_delay' => 300,
];
