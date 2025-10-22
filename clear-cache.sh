#!/bin/bash

# Laravel DataTablePro Cache Clear Script
# Run this in your Laravel application root directory

echo ""
echo "========================================"
echo "  Laravel DataTablePro Cache Clearer"
echo "========================================"
echo ""

# Check if artisan exists
if [ ! -f "artisan" ]; then
    echo "[ERROR] artisan file not found!"
    echo "This script must be run from your Laravel project root directory."
    echo ""
    exit 1
fi

echo "[OK] Laravel project detected"
echo ""

echo "[1/7] Clearing config cache..."
php artisan config:clear || {
    echo "[ERROR] Failed to clear config cache"
    exit 1
}

echo "[2/7] Clearing application cache..."
php artisan cache:clear || {
    echo "[ERROR] Failed to clear application cache"
    exit 1
}

echo "[3/7] Clearing view cache..."
php artisan view:clear || {
    echo "[ERROR] Failed to clear view cache"
    exit 1
}

echo "[4/7] Clearing route cache..."
php artisan route:clear || {
    echo "[ERROR] Failed to clear route cache"
    exit 1
}

echo "[5/7] Clearing compiled files..."
php artisan clear-compiled || {
    echo "[ERROR] Failed to clear compiled files"
    exit 1
}

echo "[6/7] Rebuilding composer autoload..."
composer dump-autoload -o || {
    echo "[ERROR] Failed to rebuild autoload"
    exit 1
}

echo "[7/7] Discovering packages..."
php artisan package:discover --ansi || {
    echo "[ERROR] Failed to discover packages"
    exit 1
}

echo ""
echo "========================================"
echo "  ✓ All caches cleared successfully!"
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Refresh your browser (Ctrl+F5)"
echo "2. Clear browser cache if needed"
echo "3. Try your application again"
echo ""
echo "If issues persist, see: CACHE-CLEAR-FIX.md"
echo ""
