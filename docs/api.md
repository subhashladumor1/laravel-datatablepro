# API Reference

Complete API documentation for Laravel DataTablePro.

## Builder

### Static Methods

#### `make(): self`
Create a new Builder instance.

```php
$builder = Builder::make();
```

### Data Source Methods

#### `eloquent(EloquentBuilder $query): self`
Set Eloquent Builder as data source.

#### `query(QueryBuilder $query): self`
Set Query Builder as data source.

#### `collection(Collection $collection): self`
Set Collection as data source.

### Configuration Methods

#### `columns(array $columns): self`
Set columns configuration.

**Parameters:**
- `$columns` - Array of Column instances

#### `filters(array $filters): self`
Set filters configuration.

**Parameters:**
- `$filters` - Array of Filter instances

#### `searchable(bool $searchable = true): self`
Make all columns searchable.

#### `orderable(bool $orderable = true): self`
Make all columns orderable.

#### `with(array $relationships): self`
Set eager loading relationships.

#### `exportable(bool $exportable = true): self`
Enable export functionality.

#### `pageLength(int $length): self`
Set default page length.

**Parameters:**
- `$length` - Number of records per page (max: config value)

#### `responsive(bool $responsive = true): self`
Enable responsive mode.

#### `persistColumns(string $key): self`
Enable column state persistence.

**Parameters:**
- `$key` - Unique identifier for persisting state

#### `virtualScroll(bool $enabled = true): self`
Enable virtual scrolling.

#### `realtime(bool $enabled = true): self`
Enable realtime updates.

### Output Methods

#### `toArray(?Request $request = null): array`
Get array response.

**Returns:**
```php
[
    'draw' => int,
    'recordsTotal' => int,
    'recordsFiltered' => int,
    'data' => array,
]
```

#### `toResponse(?Request $request = null): JsonResponse`
Get JSON response.

#### `toExport(string $format, ?Request $request = null): mixed`
Export data.

**Parameters:**
- `$format` - Export format ('csv', 'xlsx', 'pdf', 'image')
- `$request` - HTTP request

### Getter Methods

#### `getColumns(): ColumnCollection`
Get columns collection.

#### `getFilters(): array`
Get filters array.

#### `getEngine(): ?DataTableEngineInterface`
Get query handler engine.

#### `isExportable(): bool`
Check if export is enabled.

#### `getPageLength(): int`
Get page length.

#### `isResponsive(): bool`
Check if responsive mode is enabled.

#### `getPersistKey(): ?string`
Get persistence key.

#### `isVirtualScroll(): bool`
Check if virtual scroll is enabled.

#### `isRealtime(): bool`
Check if realtime is enabled.

#### `getWhitelistedColumns(): array`
Get whitelisted columns.

#### `getWhitelistedRelationships(): array`
Get whitelisted relationships.

---

## Column

### Static Methods

#### `make(string $key, string $label): self`
Create a new Column instance.

**Parameters:**
- `$key` - Column database key
- `$label` - Display label

### Configuration Methods

#### `searchable(bool $searchable = true): self`
Make column searchable.

#### `orderable(bool $orderable = true): self`
Make column orderable.

#### `relationship(string $relationship): self`
Set relationship name for nested access.

#### `render(callable|string $render): self`
Set render callback or client renderer name.

#### `raw(bool $raw = true): self`
Allow raw HTML output (disable XSS escaping).

#### `default(mixed $default): self`
Set default value for null/empty values.

#### `format(callable $callback): self`
Set format callback.

#### `visible(bool $visible = true): self`
Set column visibility.

#### `exportable(bool $exportable = true): self`
Make column exportable.

#### `attributes(array $attributes): self`
Set custom attributes for client renderer.

### Getter Methods

#### `getKey(): string`
Get column key.

#### `getLabel(): string`
Get column label.

#### `isSearchable(): bool`
Check if searchable.

#### `isOrderable(): bool`
Check if orderable.

#### `getRelationship(): ?string`
Get relationship name.

#### `getRender(): mixed`
Get render callback.

#### `isRaw(): bool`
Check if raw HTML allowed.

#### `getDefault(): mixed`
Get default value.

#### `getFormatCallback(): ?callable`
Get format callback.

#### `isVisible(): bool`
Check if visible.

#### `isExportable(): bool`
Check if exportable.

#### `getClientRenderer(): ?string`
Get client renderer name.

#### `getAttributes(): array`
Get custom attributes.

#### `toArray(): array`
Convert to array.

---

## Filter

### Static Methods

#### `make(string $key, string $type, string $label): self`
Create a new Filter instance.

#### `text(string $key, string $label): self`
Create text filter.

#### `select(string $key, string $label, array $options = []): self`
Create select filter.

#### `dateRange(string $key, string $label): self`
Create date range filter.

#### `numericRange(string $key, string $label): self`
Create numeric range filter.

### Configuration Methods

#### `default(mixed $default): self`
Set default value.

#### `options(array $options): self`
Set select options.

**Parameters:**
- `$options` - ['value' => 'label'] array

#### `callback(callable $callback): self`
Set custom filter callback.

**Callback signature:**
```php
function($query, $value) {
    // Modify $query
}
```

#### `attributes(array $attributes): self`
Set custom HTML attributes.

### Getter Methods

#### `getKey(): string`
Get filter key.

#### `getType(): string`
Get filter type.

#### `getLabel(): string`
Get filter label.

#### `getDefault(): mixed`
Get default value.

#### `getOptions(): array`
Get select options.

#### `getCallback(): ?callable`
Get custom callback.

#### `getAttributes(): array`
Get attributes.

#### `toArray(): array`
Convert to array.

---

## ColumnCollection

### Methods

#### `add(Column $column): self`
Add column to collection.

#### `get(string $key): ?Column`
Get column by key.

#### `has(string $key): bool`
Check if column exists.

#### `remove(string $key): self`
Remove column by key.

#### `all(): array`
Get all columns.

#### `searchable(): array`
Get searchable columns.

#### `orderable(): array`
Get orderable columns.

#### `visible(): array`
Get visible columns.

#### `exportable(): array`
Get exportable columns.

#### `getKeyLabelMap(): array`
Get key=>label map.

#### `count(): int`
Get column count.

#### `toArray(): array`
Convert to array.

---

## ExportManager

### Methods

#### `export(Builder $builder, string $format, Request $request): mixed`
Export data in specified format.

#### `registerExporter(string $format, string $exporterClass): void`
Register custom exporter.

#### `getAvailableFormats(): array`
Get available export formats.

---

## Helper Functions

### `datatable(): Builder`
Create new Builder instance.

### `dtable_column(string $key, string $label): Column`
Create new Column instance.

### `dtable_filter(string $key, string $type, string $label): Filter`
Create new Filter instance.

### `dtable_escape(mixed $value): string`
Escape value for HTML output.

### `dtable_format_date(mixed $date, string $format = 'Y-m-d H:i:s'): string`
Format date.

### `dtable_format_currency(mixed $amount, string $currency = 'USD', int $decimals = 2): string`
Format currency.

### `dtable_format_number(mixed $number, int $decimals = 0): string`
Format number.

### `dtable_render_link(string $url, string $text, array $attributes = []): string`
Render link.

### `dtable_render_badge(string $text, string $color = 'primary'): string`
Render badge.

---

## JavaScript API

### DTable Class

#### `DTable.init(containerOrSelector, config)`
Initialize DataTable.

**Config Options:**
```javascript
{
    ajax: '',                    // AJAX endpoint URL
    columns: [],                 // Column configuration
    pageLength: 10,              // Records per page
    responsive: false,           // Enable responsive
    persistKey: null,            // LocalStorage key
    exportUrl: null,             // Export endpoint
    filters: [],                 // Filter configuration
    virtualScroll: false,        // Enable virtual scroll
    realtime: false,             // Enable realtime
    realtimeInterval: 5000,      // Realtime poll interval
    debounceDelay: 300,          // Search debounce delay
    responsiveBreakpoint: 768    // Mobile breakpoint
}
```

#### Instance Methods

##### `loadData()`
Reload table data.

##### `registerRenderer(name, callback)`
Register custom renderer.

##### `registerPlugin(name, plugin)`
Register plugin.

##### `export(format)`
Trigger export.

##### `enableRealtime()`
Enable realtime updates.

##### `disableRealtime()`
Disable realtime updates.

##### `destroy()`
Destroy instance.

### Built-in Renderers

See [Usage Guide](usage.md#available-built-in-renderers) for complete list.

### Plugins

#### Virtual Scroll Plugin

```javascript
DTableVirtualScroll.init(dtable);
```

#### Realtime Plugin

```javascript
DTableRealtime.init(dtable);
DTableRealtime.enable({
    mode: 'polling', // or 'websocket'
    interval: 5000,
    websocketUrl: 'ws://...'
});
```

---

## Configuration

### Configuration Options

See `config/datatable.php`:

```php
[
    'page_length' => 10,
    'max_page_length' => 100,
    'global_search' => true,
    'export_disk' => 'local',
    'export_queue' => 'default',
    'export_chunk_size' => 1000,
    'export_url_expiration' => 60,
    'xss_protection' => true,
    'responsive_breakpoint' => 768,
    'virtual_scroll_threshold' => 100,
    'debounce_delay' => 300,
]
```
