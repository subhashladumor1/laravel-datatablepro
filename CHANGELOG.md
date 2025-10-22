# Changelog

All notable changes to `laravel-datatablepro` will be documented in this file.

## [1.0.0] - 2024-01-15

### Added
- Initial release
- Fluent Builder API for DataTables
- Support for Eloquent, Query Builder, and Collection data sources
- Multi-column search and ordering
- Relationship column support with JOIN optimization for belongsTo
- Advanced filtering system (text, select, date-range, numeric-range, custom callbacks)
- Export system (CSV streaming, XLSX with chunking/queuing, PDF, Image)
- Responsive design with mobile card layout
- Virtual scrolling for large datasets
- Realtime updates via polling or WebSocket
- Column persistence per user via TablePreset model
- XSS protection with automatic escaping
- Whitelisting for columns and relationships
- Built-in cell renderers (link, avatar, badge, status, date, currency, etc.)
- Blade component for easy integration
- Comprehensive test coverage
- Full documentation and examples
- PSR-12 code style
- PHPStan level 7 static analysis
- CI/CD GitHub Actions workflow

### Security
- Automatic XSS escaping (configurable per column)
- Request validation and sanitization
- Column and relationship whitelisting
- Signed temporary URLs for export downloads

### Performance
- Streaming exports for large files
- Query optimization with eager loading
- Efficient pagination
- Virtual scrolling for client-side performance
- Debounced search input

## [Unreleased]

### Planned
- Server-sent events (SSE) for realtime updates
- Advanced relationship ordering strategies
- Column grouping and aggregation
- Row selection with bulk actions
- Inline editing
- Advanced search builder UI
- Export scheduling
- Dashboard widgets
