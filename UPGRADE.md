# Upgrade Guide

This guide will help you upgrade between major versions of Laravel DataTablePro.

## Upgrading from 0.x to 1.0

### Requirements Changed

- **PHP**: Now requires PHP ^8.2 (previously ^8.0)
- **Laravel**: Now requires Laravel ^10.0|^11.0 (previously ^9.0)

### API Changes

#### 1. Builder Instantiation

**Before:**
```php
use DataTable;

$table = DataTable::of(User::query());
```

**After:**
```php
use SubhashLadumor1\DataTablePro\DataTable\Builder;

$table = Builder::make()->eloquent(User::query());
```

#### 2. Column Definition

**Before:**
```php
->addColumn('name', 'Name')
```

**After:**
```php
use SubhashLadumor1\DataTablePro\DataTable\Column;

->columns([
    Column::make('name', 'Name'),
])
```

#### 3. Export Methods

**Before:**
```php
->export('csv')
```

**After:**
```php
->exportable()
->toExport('csv', $request)
```

### New Features in 1.0

- Virtual scrolling support
- Realtime updates (polling/WebSocket)
- Advanced filtering system
- Column persistence
- Built-in cell renderers
- Responsive mobile layout
- Comprehensive export system with queuing

### Breaking Changes

1. **Namespace Changes**: All classes moved to `SubhashLadumor1\DataTablePro` namespace
2. **Configuration File**: Renamed from `datatables.php` to `datatable.php`
3. **Asset Publishing**: New tag names for publishing
4. **Facade**: Now `DataTable` instead of `DataTables`

### Migration Steps

1. Update composer requirements:
```bash
composer require subhashladumor1/laravel-datatablepro:^1.0
```

2. Update configuration:
```bash
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-config" --force
```

3. Update imports in your code:
```php
// Old
use Yajra\DataTables\DataTables;

// New
use SubhashLadumor1\DataTablePro\Facades\DataTable;
use SubhashLadumor1\DataTablePro\DataTable\Builder;
use SubhashLadumor1\DataTablePro\DataTable\Column;
```

4. Update controller methods (see examples in README.md)

5. Republish assets:
```bash
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets" --force
npm run build
```

6. Run migrations:
```bash
php artisan migrate
```

### Deprecations

None in 1.0.0 (initial release)

## Future Versions

Check the [CHANGELOG.md](CHANGELOG.md) for version-specific upgrade notes.
