# Laravel DataTablePro

[![Latest Version](https://img.shields.io/packagist/v/subhashladumor1/laravel-datatablepro.svg)](https://packagist.org/packages/subhashladumor1/laravel-datatablepro)
[![Total Downloads](https://img.shields.io/packagist/dt/subhashladumor1/laravel-datatablepro.svg)](https://packagist.org/packages/subhashladumor1/laravel-datatablepro)
[![License](https://img.shields.io/packagist/l/subhashladumor1/laravel-datatablepro.svg)](https://packagist.org/packages/subhashladumor1/laravel-datatablepro)

A high-performance, feature-rich Laravel package for creating responsive DataTables with real-time search, sorting, filtering, pagination, and advanced export capabilities (CSV, PDF, XLSX, Image) without page reloads.

## Features

✨ **Core Features:**
- Fluent, Yajra-compatible API
- Eloquent, Query Builder, and Collection support
- Real-time search across multiple columns and relationships
- Multi-column sorting (including relationship columns)
- Advanced filtering (text, select, date-range, numeric-range, custom callbacks)
- Responsive design with mobile card layout
- Server-side and client-side rendering
- XSS protection with safe HTML escaping

🚀 **Advanced Features:**
- **Export System**: CSV (streaming), XLSX (chunked & queued), PDF, Image (html2canvas)
- **Virtual Scrolling**: Handle large datasets efficiently
- **Realtime Updates**: Polling or WebSocket support
- **Column Persistence**: Save column visibility and order per user
- **Custom Renderers**: Built-in renderers (link, avatar, badge, date, currency, etc.)
- **Whitelisting**: Secure column and relationship access control
- **Deep Linking**: History API integration for shareable states

## Requirements

- PHP ^8.2
- Laravel ^10.0 | ^11.0

## Installation

```bash
composer require subhashladumor1/laravel-datatablepro
```

### Optional Dependencies

```bash
# For XLSX export
composer require maatwebsite/excel

# For PDF export
composer require barryvdh/laravel-dompdf

# For advanced image processing
composer require intervention/image
```

### Publish Assets

```bash
# Publish configuration
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-config"

# Publish views
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-views"

# Publish assets
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets"

# Run migrations for table presets
php artisan migrate
```

### Build Frontend Assets

```bash
npm install
npm run build
```

## Quick Start

### 1. Create a Controller

```php
use SubhashLadumor1\DataTablePro\DataTable\Builder;
use SubhashLadumor1\DataTablePro\DataTable\Column;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index');
    }

    public function datatable(Request $request)
    {
        return Builder::make()
            ->eloquent(User::query())
            ->columns([
                Column::make('id', 'ID')->orderable(),
                Column::make('name', 'Name')->searchable()->orderable(),
                Column::make('email', 'Email')->searchable(),
                Column::make('created_at', 'Created')->orderable()
                    ->format(fn($value) => $value->format('Y-m-d')),
            ])
            ->with(['posts', 'profile'])
            ->pageLength(25)
            ->responsive()
            ->exportable()
            ->toResponse($request);
    }

    public function export(Request $request)
    {
        return Builder::make()
            ->eloquent(User::query())
            ->columns([
                Column::make('name', 'Name')->exportable(),
                Column::make('email', 'Email')->exportable(),
            ])
            ->exportable()
            ->toExport($request->get('format', 'csv'), $request);
    }
}
```

### 2. Add Routes

```php
Route::get('/users', [UserController::class, 'index']);
Route::post('/users/datatable', [UserController::class, 'datatable']);
Route::post('/users/export', [UserController::class, 'export']);
```

### 3. Create Blade View

```blade
@extends('layouts.app')

@section('content')
    <x-dtable-table
        id="users-table"
        :ajax="route('users.datatable')"
        :columns="[
            ['key' => 'id', 'label' => 'ID', 'orderable' => true],
            ['key' => 'name', 'label' => 'Name', 'searchable' => true, 'orderable' => true],
            ['key' => 'email', 'label' => 'Email', 'searchable' => true],
            ['key' => 'created_at', 'label' => 'Created', 'orderable' => true],
        ]"
        :page-length="25"
        :responsive="true"
        :export-url="route('users.export')"
    />
@endsection
```

## Advanced Usage

### Custom Renderers

**Server-side rendering:**

```php
Column::make('status', 'Status')
    ->render(function ($value, $row) {
        return "<span class='badge badge-{$value}'>" . ucfirst($value) . "</span>";
    })
    ->raw(); // Allow HTML
```

**Client-side rendering:**

```php
// In your controller
Column::make('avatar', 'Avatar')
    ->render('avatar') // Use built-in avatar renderer
    ->attributes(['imageKey' => 'avatar_url', 'size' => 40]);

Column::make('price', 'Price')
    ->render('currency')
    ->attributes(['currency' => 'USD', 'symbol' => '$']);
```

### Filters

```php
use SubhashLadumor1\DataTablePro\DataTable\Filter;

Builder::make()
    ->eloquent(User::query())
    ->filters([
        Filter::text('name', 'Search Name'),
        Filter::select('status', 'Status', [
            'active' => 'Active',
            'inactive' => 'Inactive'
        ]),
        Filter::dateRange('created_at', 'Registration Date'),
        Filter::numericRange('age', 'Age'),
        
        // Custom filter with callback
        Filter::make('custom', 'select', 'Department')
            ->options(['IT' => 'IT', 'HR' => 'HR'])
            ->callback(function ($query, $value) {
                $query->whereHas('department', fn($q) => $q->where('name', $value));
            }),
    ]);
```

### Relationship Columns

```php
Builder::make()
    ->eloquent(Post::query())
    ->columns([
        Column::make('title', 'Title'),
        Column::make('name', 'Author')
            ->relationship('user') // belongsTo relationship
            ->searchable()
            ->orderable(),
    ])
    ->with(['user']); // Eager load to avoid N+1
```

### Export Configuration

```php
// Simple export
Builder::make()
    ->eloquent(User::query())
    ->columns([
        Column::make('name', 'Name')->exportable(),
        Column::make('email', 'Email')->exportable(),
        Column::make('password', 'Password')->exportable(false), // Exclude from export
    ])
    ->exportable()
    ->toExport('xlsx', $request);
```

For large datasets, queued exports are automatically triggered and a signed download URL is returned.

### Virtual Scrolling

```php
Builder::make()
    ->eloquent(User::query())
    ->columns([...])
    ->virtualScroll()
    ->pageLength(100);
```

### Realtime Updates

```php
Builder::make()
    ->eloquent(User::query())
    ->columns([...])
    ->realtime(); // Poll every 5 seconds (default)
```

## Verification Checklist

To verify the package locally:

```bash
# 1. Install dependencies
composer install
npm install

# 2. Build assets
npm run build

# 3. Publish package resources
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-views"
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets"

# 4. Run migrations
php artisan migrate

# 5. Run tests
composer test

# 6. Run static analysis
composer static

# 7. Check code style
composer cs-check
```

## Documentation

- [Usage Guide](docs/usage.md) - Comprehensive usage examples
- [API Reference](docs/api.md) - Complete API documentation
- [Examples](docs/examples.md) - Real-world examples
- [Implementation Details](IMPLEMENTATION.md) - Design rationale and trade-offs
- [Upgrade Guide](UPGRADE.md) - Migration guide between versions

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email security@example.com instead of using the issue tracker.

## Credits

- [Subhash Ladumor](https://github.com/subhashladumor1)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.
