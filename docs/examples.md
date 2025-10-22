# Examples

Real-world examples of using Laravel DataTablePro.

## Basic User Management

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use SubhashLadumor1\DataTablePro\DataTable\Builder;
use SubhashLadumor1\DataTablePro\DataTable\Column;
use SubhashLadumor1\DataTablePro\DataTable\Filter;

class UserController extends Controller
{
    public function datatable(Request $request)
    {
        return Builder::make()
            ->eloquent(User::query())
            ->columns([
                Column::make('id', 'ID')->orderable(),
                Column::make('name', 'Name')->searchable()->orderable(),
                Column::make('email', 'Email')->searchable(),
                Column::make('role', 'Role')
                    ->render('badge')
                    ->attributes([
                        'colorMap' => [
                            'admin' => 'danger',
                            'user' => 'primary',
                        ]
                    ]),
                Column::make('created_at', 'Joined')
                    ->render('datetime')
                    ->orderable(),
            ])
            ->filters([
                Filter::select('role', 'Role', [
                    'admin' => 'Admin',
                    'user' => 'User',
                ]),
            ])
            ->pageLength(25)
            ->responsive()
            ->toResponse($request);
    }
}
```

## E-commerce Orders

```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use SubhashLadumor1\DataTablePro\DataTable\Builder;
use SubhashLadumor1\DataTablePro\DataTable\Column;
use SubhashLadumor1\DataTablePro\DataTable\Filter;

class OrderController extends Controller
{
    public function datatable(Request $request)
    {
        return Builder::make()
            ->eloquent(Order::query())
            ->columns([
                Column::make('order_number', 'Order #')->searchable()->orderable(),
                
                Column::make('name', 'Customer')
                    ->relationship('customer')
                    ->searchable()
                    ->render('link')
                    ->attributes([
                        'url' => '/customers/{customer_id}',
                        'target' => '_blank'
                    ]),
                
                Column::make('items_count', 'Items')
                    ->render(function ($value, $row) {
                        return $row->items->count();
                    }),
                
                Column::make('total', 'Total')
                    ->render('currency')
                    ->attributes(['currency' => 'USD', 'symbol' => '$'])
                    ->orderable(),
                
                Column::make('status', 'Status')
                    ->render('status')
                    ->attributes([
                        'statusMap' => [
                            'pending' => ['icon' => '⏳', 'color' => 'warning', 'label' => 'Pending'],
                            'processing' => ['icon' => '⚙️', 'color' => 'info', 'label' => 'Processing'],
                            'shipped' => ['icon' => '📦', 'color' => 'primary', 'label' => 'Shipped'],
                            'delivered' => ['icon' => '✓', 'color' => 'success', 'label' => 'Delivered'],
                            'cancelled' => ['icon' => '✗', 'color' => 'danger', 'label' => 'Cancelled'],
                        ]
                    ]),
                
                Column::make('created_at', 'Ordered')
                    ->render('date')
                    ->attributes(['format' => 'Y-m-d'])
                    ->orderable(),
                
                Column::make('actions', 'Actions')
                    ->render('actions')
                    ->attributes([
                        'actions' => [
                            ['url' => '/orders/{id}/edit', 'label' => 'Edit', 'icon' => '✏️'],
                            ['url' => '/orders/{id}/invoice', 'label' => 'Invoice', 'icon' => '📄'],
                        ]
                    ])
                    ->exportable(false),
            ])
            ->filters([
                Filter::select('status', 'Status', [
                    'pending' => 'Pending',
                    'processing' => 'Processing',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled',
                ]),
                Filter::dateRange('created_at', 'Order Date'),
                Filter::numericRange('total', 'Total Amount'),
            ])
            ->with(['customer', 'items'])
            ->exportable()
            ->toResponse($request);
    }
}
```

## Blog Posts with Categories

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use SubhashLadumor1\DataTablePro\DataTable\Builder;
use SubhashLadumor1\DataTablePro\DataTable\Column;
use SubhashLadumor1\DataTablePro\DataTable\Filter;

class PostController extends Controller
{
    public function datatable(Request $request)
    {
        return Builder::make()
            ->eloquent(Post::query())
            ->columns([
                Column::make('title', 'Title')
                    ->searchable()
                    ->orderable()
                    ->render('truncate')
                    ->attributes(['length' => 50]),
                
                Column::make('name', 'Author')
                    ->relationship('author')
                    ->searchable()
                    ->render('avatar')
                    ->attributes([
                        'imageKey' => 'author.avatar',
                        'size' => 32
                    ]),
                
                Column::make('category_name', 'Category')
                    ->relationship('category')
                    ->render('badge')
                    ->attributes(['defaultColor' => 'info']),
                
                Column::make('tags', 'Tags')
                    ->render('tags')
                    ->attributes(['color' => 'secondary']),
                
                Column::make('views', 'Views')
                    ->render('number')
                    ->orderable(),
                
                Column::make('published', 'Published')
                    ->render('boolean')
                    ->attributes(['style' => 'icon']),
                
                Column::make('published_at', 'Date')
                    ->render('datetime')
                    ->orderable(),
            ])
            ->filters([
                Filter::select('category_id', 'Category')
                    ->options(\App\Models\Category::pluck('name', 'id')->toArray()),
                
                Filter::select('published', 'Status', [
                    '1' => 'Published',
                    '0' => 'Draft',
                ]),
                
                Filter::make('tag', 'select', 'Tag')
                    ->options(\App\Models\Tag::pluck('name', 'id')->toArray())
                    ->callback(function ($query, $value) {
                        $query->whereHas('tags', fn($q) => $q->where('tags.id', $value));
                    }),
            ])
            ->with(['author', 'category', 'tags'])
            ->realtime()
            ->toResponse($request);
    }
}
```

## Analytics Dashboard

```php
<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use SubhashLadumor1\DataTablePro\DataTable\Builder;
use SubhashLadumor1\DataTablePro\DataTable\Column;
use SubhashLadumor1\DataTablePro\DataTable\Filter;

class AnalyticsController extends Controller
{
    public function datatable(Request $request)
    {
        return Builder::make()
            ->eloquent(PageView::query())
            ->columns([
                Column::make('url', 'Page URL')
                    ->searchable()
                    ->render('link')
                    ->attributes([
                        'url' => '{url}',
                        'target' => '_blank'
                    ]),
                
                Column::make('views', 'Views')
                    ->render('number')
                    ->orderable(),
                
                Column::make('unique_visitors', 'Unique Visitors')
                    ->render('number')
                    ->orderable(),
                
                Column::make('avg_time', 'Avg. Time')
                    ->render(function ($value) {
                        return gmdate('i:s', $value);
                    }),
                
                Column::make('bounce_rate', 'Bounce Rate')
                    ->render('percentage')
                    ->attributes(['decimals' => 1])
                    ->orderable(),
                
                Column::make('conversion_rate', 'Conversion')
                    ->render('progress')
                    ->attributes([
                        'color' => 'success',
                        'showLabel' => true
                    ]),
            ])
            ->filters([
                Filter::dateRange('date', 'Date Range'),
                Filter::numericRange('views', 'Views'),
            ])
            ->virtualScroll()
            ->pageLength(100)
            ->toResponse($request);
    }
}
```

## File Manager

```php
<?php

namespace App\Http\Controllers;

use App\Models\File;
use SubhashLadumor1\DataTablePro\DataTable\Builder;
use SubhashLadumor1\DataTablePro\DataTable\Column;
use SubhashLadumor1\DataTablePro\DataTable\Filter;

class FileController extends Controller
{
    public function datatable(Request $request)
    {
        return Builder::make()
            ->eloquent(File::query())
            ->columns([
                Column::make('thumbnail', 'Preview')
                    ->render('image')
                    ->attributes([
                        'width' => 60,
                        'height' => 60,
                        'lightbox' => true
                    ])
                    ->exportable(false),
                
                Column::make('name', 'File Name')
                    ->searchable()
                    ->orderable(),
                
                Column::make('type', 'Type')
                    ->render('badge')
                    ->attributes([
                        'colorMap' => [
                            'image' => 'primary',
                            'document' => 'info',
                            'video' => 'warning',
                            'audio' => 'success',
                        ]
                    ]),
                
                Column::make('size', 'Size')
                    ->render(function ($value) {
                        $units = ['B', 'KB', 'MB', 'GB'];
                        $power = $value > 0 ? floor(log($value, 1024)) : 0;
                        return number_format($value / pow(1024, $power), 2) . ' ' . $units[$power];
                    })
                    ->orderable(),
                
                Column::make('name', 'Uploaded By')
                    ->relationship('user')
                    ->render('avatar')
                    ->attributes([
                        'imageKey' => 'user.avatar',
                        'size' => 32
                    ]),
                
                Column::make('created_at', 'Uploaded')
                    ->render('datetime')
                    ->orderable(),
                
                Column::make('actions', 'Actions')
                    ->render(function ($value, $row) {
                        return '
                            <a href="/files/' . $row->id . '/download" class="btn btn-sm btn-primary">Download</a>
                            <a href="/files/' . $row->id . '/delete" class="btn btn-sm btn-danger">Delete</a>
                        ';
                    })
                    ->raw()
                    ->exportable(false),
            ])
            ->filters([
                Filter::select('type', 'Type', [
                    'image' => 'Images',
                    'document' => 'Documents',
                    'video' => 'Videos',
                    'audio' => 'Audio',
                ]),
                Filter::dateRange('created_at', 'Upload Date'),
            ])
            ->with(['user'])
            ->exportable()
            ->toResponse($request);
    }
}
```

## Custom Export with Filtering

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use SubhashLadumor1\DataTablePro\DataTable\Builder;
use SubhashLadumor1\DataTablePro\DataTable\Column;

class UserExportController extends Controller
{
    public function export(Request $request)
    {
        $builder = Builder::make()
            ->eloquent(User::query())
            ->columns([
                Column::make('id', 'ID')->exportable(),
                Column::make('name', 'Name')->exportable(),
                Column::make('email', 'Email')->exportable(),
                Column::make('created_at', 'Joined')->exportable()
                    ->format(fn($v) => $v->format('Y-m-d H:i:s')),
                Column::make('orders_count', 'Total Orders')->exportable()
                    ->format(function ($value, $row) {
                        return $row->orders->count();
                    }),
                Column::make('total_spent', 'Total Spent')->exportable()
                    ->format(function ($value, $row) {
                        return '$' . number_format($row->orders->sum('total'), 2);
                    }),
            ])
            ->with(['orders'])
            ->exportable();

        // Apply same filters as DataTable
        if ($request->filled('filters')) {
            // Filters will be automatically applied by the exporter
        }

        return $builder->toExport($request->get('format', 'csv'), $request);
    }
}
```

## JavaScript Custom Initialization

```html
<div id="custom-table"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = DTable.init('#custom-table', {
        ajax: '/api/products/datatable',
        columns: [
            { key: 'sku', label: 'SKU', searchable: true, orderable: true },
            { key: 'name', label: 'Product', searchable: true, orderable: true },
            { key: 'price', label: 'Price', orderable: true, clientRenderer: 'currency', attributes: { symbol: '$' } },
            { key: 'stock', label: 'Stock', orderable: true, clientRenderer: 'number' },
            { key: 'status', label: 'Status', clientRenderer: 'badge' }
        ],
        pageLength: 50,
        responsive: true,
        exportUrl: '/api/products/export',
        virtualScroll: true
    });

    // Register custom renderer
    table.registerRenderer('stock-status', function(value, row, column) {
        if (value <= 0) {
            return '<span class="badge badge-danger">Out of Stock</span>';
        } else if (value < 10) {
            return '<span class="badge badge-warning">Low Stock (' + value + ')</span>';
        } else {
            return '<span class="badge badge-success">In Stock (' + value + ')</span>';
        }
    });

    // Custom event handler
    table.elements.tbody.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-product')) {
            const productId = e.target.dataset.id;
            // Handle product view
            window.location.href = '/products/' + productId;
        }
    });
});
</script>
```
