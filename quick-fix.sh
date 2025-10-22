#!/bin/bash

# Quick Fix Script for Laravel DataTablePro

echo "=== Laravel DataTablePro Quick Fix ==="
echo ""

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Error: Not in a Laravel project root directory"
    exit 1
fi

echo "✓ Laravel project detected"
echo ""

# Check if package is installed
if [ ! -d "vendor/subhashladumor/laravel-datatablepro" ]; then
    echo "❌ Error: Package not installed"
    echo "Run: composer require subhashladumor/laravel-datatablepro"
    exit 1
fi

echo "✓ Package installed"
echo ""

PACKAGE_DIR="vendor/subhashladumor/laravel-datatablepro"

# Option 1: Build assets
echo "Do you want to build the assets? (Recommended for production)"
echo "This requires Node.js and npm to be installed."
read -p "Build assets? (y/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Building assets..."
    cd "$PACKAGE_DIR"
    
    if [ ! -f "package.json" ]; then
        echo "❌ Error: package.json not found"
        exit 1
    fi
    
    echo "Installing npm dependencies..."
    npm install
    
    echo "Building with Vite..."
    npm run build
    
    cd ../../..
    
    if [ -d "$PACKAGE_DIR/src/Resources/dist" ]; then
        echo "✓ Assets built successfully"
    else
        echo "⚠️  Build may have failed, check for errors"
    fi
fi

echo ""
echo "Publishing assets..."
php artisan vendor:publish --provider="SubhashLadumor1\\DataTablePro\\Providers\\DataTableServiceProvider" --tag="datatable-assets" --force

echo ""
echo "Publishing views..."
php artisan vendor:publish --provider="SubhashLadumor1\\DataTablePro\\Providers\\DataTableServiceProvider" --tag="datatable-views"

echo ""
echo "Publishing config..."
php artisan vendor:publish --provider="SubhashLadumor1\\DataTablePro\\Providers\\DataTableServiceProvider" --tag="datatable-config"

echo ""
echo "Running migrations..."
php artisan migrate

echo ""
echo "=== Verification ==="

# Check published assets
if [ -d "public/vendor/dtable/js" ] || [ -d "public/vendor/dtable/raw" ]; then
    echo "✓ Assets published"
    
    if [ -d "public/vendor/dtable/js" ]; then
        echo "  - Using built assets (public/vendor/dtable/js)"
    else
        echo "  - Using raw assets (public/vendor/dtable/raw)"
        echo "  ⚠️  For production, build the assets for better performance"
    fi
else
    echo "❌ Assets not published properly"
fi

echo ""
echo "=== Setup Complete ==="
echo ""
echo "Next steps:"
echo "1. Make sure your layout has @stack('styles') in <head>"
echo "2. Make sure your layout has @stack('scripts') before </body>"
echo "3. Use the component in your blade files:"
echo "   <x-dtable-table id=\"my-table\" :ajax=\"route('datatable')\" :columns=\"[...]\" />"
echo ""
echo "If you still see 'DTable is not defined', check:"
echo "- Browser console for 404 errors on JS files"
echo "- That scripts are loaded before DTable.init() is called"
echo ""
