# Usage Guide

Complete guide to using Laravel DataTablePro in your applications.

## Table of Contents

- [Basic Usage](#basic-usage)
- [Data Sources](#data-sources)
- [Columns](#columns)
- [Filtering](#filtering)
- [Searching and Ordering](#searching-and-ordering)
- [Relationships](#relationships)
- [Rendering](#rendering)
- [Exports](#exports)
- [Advanced Features](#advanced-features)

## Basic Usage

### Controller Setup

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use SubhashLadumor\DataTablePro\DataTable\Builder;
use SubhashLadumor\DataTablePro\DataTable\Column;

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
                Column::make('created_at', 'Created')->orderable(),
            ])
            ->toResponse($request);
    }
}
```

### Routes

```php
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users/datatable', [UserController::class, 'datatable'])->name('users.datatable');
```

### Blade View

```blade
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Users</h1>
        
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
        />
    </div>
@endsection
```

## Data Sources

### Eloquent Builder

```php
Builder::make()
    ->eloquent(User::query())
    ->columns([...]);
```

### Query Builder

```php
use Illuminate\Support\Facades\DB;

Builder::make()
    ->query(DB::table('users'))
    ->columns([...]);
```

### Collection

```php
$users = collect([
    ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
    ['id' => 2, 'name' => 'Jane', 'email' => 'jane@example.com'],
]);

Builder::make()
    ->collection($users)
    ->columns([...]);
```

## Columns

### Basic Column

```php
Column::make('name', 'Full Name')
```

### Searchable Column

```php
Column::make('name', 'Name')->searchable()
```

### Orderable Column

```php
Column::make('created_at', 'Created')->orderable()
```

### Hidden Column

```php
Column::make('secret', 'Secret')->visible(false)
```

### Default Value

```php
Column::make('middle_name', 'Middle Name')->default('N/A')
```

### Custom Attributes

```php
Column::make('status', 'Status')
    ->attributes(['class' => 'text-center', 'data-type' => 'badge'])
```

## Filtering

### Text Filter

```php
use SubhashLadumor\DataTablePro\DataTable\Filter;

Builder::make()
    ->filters([
        Filter::text('name', 'Search by Name'),
    ]);
```

### Select Filter

```php
Filter::select('status', 'Status', [
    'active' => 'Active',
    'inactive' => 'Inactive',
    'pending' => 'Pending',
])
```

### Date Range Filter

```php
Filter::dateRange('created_at', 'Registration Date')
```

### Numeric Range Filter

```php
Filter::numericRange('age', 'Age Range')
```

### Custom Filter with Callback

```php
Filter::make('department', 'select', 'Department')
    ->options(['IT' => 'IT', 'HR' => 'HR', 'Sales' => 'Sales'])
    ->callback(function ($query, $value) {
        $query->whereHas('department', function ($q) use ($value) {
            $q->where('name', $value);
        });
    })
```

## Searching and Ordering

### Global Search

Global search is enabled by default across all searchable columns:

```php
Column::make('name', 'Name')->searchable()
Column::make('email', 'Email')->searchable()
```

### Disable Global Search

```php
// In config/datatable.php
'global_search' => false,
```

### Multi-Column Ordering

Users can hold Shift and click multiple column headers to sort by multiple columns.

### Programmatic Ordering

```php
$request->merge([
    'order' => [
        ['column' => 0, 'dir' => 'asc'],
        ['column' => 1, 'dir' => 'desc'],
    ]
]);
```

## Relationships

### BelongsTo Relationship

```php
// Post model has belongsTo User relationship

Builder::make()
    ->eloquent(Post::query())
    ->columns([
        Column::make('title', 'Title'),
        Column::make('name', 'Author')
            ->relationship('user')
            ->searchable()
            ->orderable(),
    ])
    ->with(['user']); // Eager load to prevent N+1
```

### HasMany Relationship

```php
// User model has hasMany Posts relationship

Builder::make()
    ->eloquent(User::query())
    ->columns([
        Column::make('name', 'Name'),
        Column::make('posts_count', 'Posts')
            ->render(function ($value, $row) {
                return $row->posts->count();
            }),
    ])
    ->with(['posts']);
```

### Complex Relationship Access

```php
Column::make('name', 'Company Name')
    ->relationship('profile.company')
    ->searchable()
```

## Rendering

### Server-Side Rendering

```php
Column::make('status', 'Status')
    ->render(function ($value, $row) {
        $colors = [
            'active' => 'success',
            'inactive' => 'danger',
            'pending' => 'warning',
        ];
        $color = $colors[$value] ?? 'secondary';
        return "<span class='badge badge-{$color}'>{$value}</span>";
    })
    ->raw(); // Allow HTML
```

### Format Callback

```php
Column::make('created_at', 'Created')
    ->format(function ($value, $row) {
        return $value->format('Y-m-d H:i:s');
    })
```

### Client-Side Rendering

```php
// Use built-in renderers
Column::make('avatar', 'Avatar')
    ->render('avatar')
    ->attributes(['imageKey' => 'avatar_url', 'size' => 40]);

Column::make('price', 'Price')
    ->render('currency')
    ->attributes(['currency' => 'USD', 'symbol' => '$']);

Column::make('published_at', 'Published')
    ->render('datetime');
```

### Available Built-in Renderers

- `link` - Clickable links
- `avatar` - User avatars with initials fallback
- `badge` - Colored badges
- `status` - Status with icons
- `date` - Formatted dates
- `datetime` - Relative datetime
- `currency` - Formatted currency
- `number` - Formatted numbers
- `percentage` - Percentage values
- `boolean` - Checkboxes or icons
- `image` - Image thumbnails
- `progress` - Progress bars
- `actions` - Action buttons
- `truncate` - Truncated text with tooltip
- `tags` - Multiple tags

## Exports

### Enable Export

```php
Builder::make()
    ->eloquent(User::query())
    ->columns([
        Column::make('name', 'Name')->exportable(),
        Column::make('email', 'Email')->exportable(),
        Column::make('password', 'Password')->exportable(false), // Exclude
    ])
    ->exportable()
    ->toExport($request->get('format'), $request);
```

### CSV Export

```php
public function export(Request $request)
{
    return Builder::make()
        ->eloquent(User::query())
        ->columns([...])
        ->exportable()
        ->toExport('csv', $request);
}
```

### XLSX Export (Queued for Large Datasets)

```php
// Automatically queued if > 1000 records
$response = $builder->toExport('xlsx', $request);

// Returns JSON with download URL
// {
//     "message": "Export queued successfully",
//     "download_url": "https://...",
//     "filename": "exports/export-..."
// }
```

### PDF Export

```php
$builder->toExport('pdf', $request);
```

### Configure Export Settings

```php
// In config/datatable.php

'export_chunk_size' => 1000, // Records per chunk
'export_disk' => 'local', // Storage disk
'export_queue' => 'default', // Queue name
'export_url_expiration' => 60, // Minutes
```

## Advanced Features

### Responsive Mode

```php
Builder::make()
    ->eloquent(User::query())
    ->columns([...])
    ->responsive(); // Enable mobile card layout
```

### Virtual Scrolling

```php
Builder::make()
    ->eloquent(User::query())
    ->columns([...])
    ->virtualScroll() // For large datasets
    ->pageLength(100);
```

### Realtime Updates

```php
Builder::make()
    ->eloquent(User::query())
    ->columns([...])
    ->realtime(); // Poll every 5 seconds
```

### Column Persistence

```php
Builder::make()
    ->eloquent(User::query())
    ->columns([...])
    ->persistColumns('users-table'); // Save column state per user
```

### Using Helper Functions

```php
// In your controller
use function SubhashLadumor\DataTablePro\datatable;
use function SubhashLadumor\DataTablePro\dtable_column;

$table = datatable()
    ->eloquent(User::query())
    ->columns([
        dtable_column('name', 'Name')->searchable(),
    ]);
```

### Custom Rendering in Blade

```php
Column::make('actions', 'Actions')
    ->render(function ($value, $row) {
        return view('users.partials.actions', ['user' => $row])->render();
    })
    ->raw();
```

### Conditional Columns

```php
$columns = [
    Column::make('id', 'ID'),
    Column::make('name', 'Name'),
];

if (auth()->user()->isAdmin()) {
    $columns[] = Column::make('email', 'Email');
}

Builder::make()
    ->eloquent(User::query())
    ->columns($columns);
```

### Query Modification

```php
Builder::make()
    ->eloquent(User::where('active', true)->query())
    ->columns([...]);
```

### Performance Tips

1. **Always eager load relationships**:
```php
->with(['user', 'posts', 'comments'])
```

2. **Use indexes on searchable/orderable columns**:
```php
Schema::table('users', function (Blueprint $table) {
    $table->index('name');
    $table->index('email');
});
```

3. **Limit exportable columns**:
```php
Column::make('sensitive_data', 'Data')->exportable(false)
```

4. **Use virtual scrolling for large datasets**:
```php
->virtualScroll()->pageLength(100)
```

## Complete Example

```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use SubhashLadumor\DataTablePro\DataTable\Builder;
use SubhashLadumor\DataTablePro\DataTable\Column;
use SubhashLadumor\DataTablePro\DataTable\Filter;

class OrderController extends Controller
{
    public function datatable(Request $request)
    {
        return Builder::make()
            ->eloquent(Order::query())
            ->columns([
                Column::make('id', 'Order #')->orderable(),
                Column::make('name', 'Customer')
                    ->relationship('user')
                    ->searchable()
                    ->orderable(),
                Column::make('total', 'Total')
                    ->render('currency')
                    ->attributes(['currency' => 'USD', 'symbol' => '$'])
                    ->orderable(),
                Column::make('status', 'Status')
                    ->render('badge')
                    ->attributes([
                        'colorMap' => [
                            'pending' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                        ]
                    ]),
                Column::make('created_at', 'Date')
                    ->render('datetime')
                    ->orderable(),
                Column::make('actions', 'Actions')
                    ->render(function ($value, $row) {
                        return view('orders.actions', ['order' => $row])->render();
                    })
                    ->raw()
                    ->exportable(false),
            ])
            ->filters([
                Filter::select('status', 'Status', [
                    'pending' => 'Pending',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ]),
                Filter::dateRange('created_at', 'Order Date'),
                Filter::numericRange('total', 'Total Amount'),
            ])
            ->with(['user'])
            ->pageLength(25)
            ->responsive()
            ->exportable()
            ->persistColumns('orders-table')
            ->toResponse($request);
    }

    public function export(Request $request)
    {
        return Builder::make()
            ->eloquent(Order::query())
            ->columns([
                Column::make('id', 'Order #')->exportable(),
                Column::make('name', 'Customer')->relationship('user')->exportable(),
                Column::make('total', 'Total')->exportable(),
                Column::make('status', 'Status')->exportable(),
                Column::make('created_at', 'Date')->exportable()
                    ->format(fn($v) => $v->format('Y-m-d H:i:s')),
            ])
            ->with(['user'])
            ->exportable()
            ->toExport($request->get('format', 'csv'), $request);
    }
}
```
