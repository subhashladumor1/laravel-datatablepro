<?php

declare(strict_types=1);

if (!function_exists('datatable')) {
    /**
     * Create a new DataTable Builder instance.
     *
     * @return \SubhashLadumor\DataTablePro\DataTable\Builder
     */
    function datatable(): \SubhashLadumor\DataTablePro\DataTable\Builder
    {
        return app('datatable');
    }
}

if (!function_exists('dtable_column')) {
    /**
     * Create a new DataTable Column instance.
     */
    function dtable_column(string $key, string $label): \SubhashLadumor\DataTablePro\DataTable\Column
    {
        return \SubhashLadumor\DataTablePro\DataTable\Column::make($key, $label);
    }
}

if (!function_exists('dtable_filter')) {
    /**
     * Create a new DataTable Filter instance.
     */
    function dtable_filter(string $key, string $type, string $label): \SubhashLadumor\DataTablePro\DataTable\Filter
    {
        return \SubhashLadumor\DataTablePro\DataTable\Filter::make($key, $type, $label);
    }
}

if (!function_exists('dtable_escape')) {
    /**
     * Escape string for safe HTML output.
     */
    function dtable_escape(mixed $value): string
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }
        
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('dtable_format_date')) {
    /**
     * Format date for DataTable display.
     */
    function dtable_format_date(mixed $date, string $format = 'Y-m-d H:i:s'): string
    {
        if (empty($date)) {
            return '';
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format($format);
        }

        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return (string) $date;
        }
    }
}

if (!function_exists('dtable_format_currency')) {
    /**
     * Format currency for DataTable display.
     */
    function dtable_format_currency(mixed $amount, string $currency = 'USD', int $decimals = 2): string
    {
        if (!is_numeric($amount)) {
            return '';
        }

        return $currency . ' ' . number_format((float) $amount, $decimals);
    }
}

if (!function_exists('dtable_format_number')) {
    /**
     * Format number for DataTable display.
     */
    function dtable_format_number(mixed $number, int $decimals = 0): string
    {
        if (!is_numeric($number)) {
            return '';
        }

        return number_format((float) $number, $decimals);
    }
}

if (!function_exists('dtable_render_link')) {
    /**
     * Render a link for DataTable cell.
     */
    function dtable_render_link(string $url, string $text, array $attributes = []): string
    {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . $key . '="' . dtable_escape($value) . '"';
        }

        return '<a href="' . dtable_escape($url) . '"' . $attrs . '>' . dtable_escape($text) . '</a>';
    }
}

if (!function_exists('dtable_render_badge')) {
    /**
     * Render a badge for DataTable cell.
     */
    function dtable_render_badge(string $text, string $color = 'primary'): string
    {
        return '<span class="badge badge-' . dtable_escape($color) . '">' . dtable_escape($text) . '</span>';
    }
}
