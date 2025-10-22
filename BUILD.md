# Build Instructions

## The assets need to be built before publishing!

### Quick Fix for Your Error

The error occurs because the frontend assets haven't been built yet. Follow these steps:

### Option 1: Build the Assets (Recommended)

```bash
cd vendor/subhashladumor/laravel-datatablepro
npm install
npm run build
cd ../../..
php artisan vendor:publish --provider="SubhashLadumor\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets"
```

### Option 2: Use CDN (Quick Fix)

If you can't build the assets, temporarily use CDN links in your blade file:

```blade
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-dt@1.13.8/css/jquery.dataTables.min.css">
<style>
    /* Add the dtable styles here or use the raw SCSS compiled */
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendor/dtable/raw/js/dtable.core.js') }}"></script>
<script src="{{ asset('vendor/dtable/raw/js/dtable.renderers.js') }}"></script>
@endpush
```

### Option 3: Publish Raw Assets

The package now automatically publishes raw assets if built assets don't exist:

```bash
php artisan vendor:publish --provider="SubhashLadumor\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets"
```

This will publish to `public/vendor/dtable/raw/`. Then update your blade component to use:

```blade
<script src="{{ asset('vendor/dtable/raw/js/dtable.core.js') }}"></script>
<script src="{{ asset('vendor/dtable/raw/js/dtable.renderers.js') }}"></script>
```

## Complete Build Process

For production use, you should build the assets:

```bash
# Navigate to the package directory
cd vendor/subhashladumor/laravel-datatablepro

# Install dependencies
npm install

# Build assets
npm run build

# Go back to your project root
cd ../../..

# Publish assets
php artisan vendor:publish --provider="SubhashLadumor\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets"
```

## Verify Installation

After publishing, you should see:
- `public/vendor/dtable/js/dtable.js` (if built)
- `public/vendor/dtable/css/dtable-styles.css` (if built)

OR

- `public/vendor/dtable/raw/js/dtable.core.js` (if raw)
- `public/vendor/dtable/raw/scss/dtable.scss` (if raw)

## Fix "DTable is not defined" Error

Make sure your blade layout includes the scripts:

```blade
@stack('scripts')
```

And your component view pushes scripts:

```blade
@push('scripts')
<script src="{{ asset('vendor/dtable/js/dtable.js') }}"></script>
<script src="{{ asset('vendor/dtable/js/dtable-renderers.js') }}"></script>
@endpush
```
