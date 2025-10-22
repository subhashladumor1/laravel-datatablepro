# Fix: Unable to locate class [SubhashLadumor\DataTablePro\View\Components\Table]

## Error Message
```
Unable to locate class or view [SubhashLadumor\DataTablePro\View\Components\Table] for component [dtable-table].
```

## Root Cause
This error occurs because Laravel has **cached the old namespace** (`SubhashLadumor\DataTablePro`) before it was changed to `SubhashLadumor1\DataTablePro`.

## Solution: Clear All Caches

### Quick Fix (Run these commands in your Laravel app):

```bash
# Clear all Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Rebuild autoload files
composer dump-autoload

# Clear compiled class cache
php artisan clear-compiled

# If using package discovery cache
php artisan package:discover --ansi
```

### Complete Fix Steps:

#### Step 1: Navigate to Your Laravel Project
```bash
cd C:\Users\Subhash\Documents\GitHub\taxido-laravel
```

#### Step 2: Remove Package and Clear Caches
```bash
# Remove the package
composer remove subhashladumor/laravel-datatablepro

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan clear-compiled

# Clear composer cache
composer clear-cache
```

#### Step 3: Reinstall Package
```bash
# Reinstall with fresh autoload
composer require subhashladumor/laravel-datatablepro

# Rebuild autoload
composer dump-autoload -o
```

#### Step 4: Republish Assets
```bash
# Publish configuration
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-config" --force

# Publish views
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-views" --force

# Publish assets
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets" --force
```

#### Step 5: Verify Package Discovery
```bash
php artisan package:discover --ansi
```

You should see:
```
Discovered Package: subhashladumor1/laravel-datatablepro
```

#### Step 6: Clear Browser Cache
- Press `Ctrl + Shift + Delete` (or `Cmd + Shift + Delete` on Mac)
- Clear browser cache and cookies
- Or use Incognito/Private mode

### Alternative Quick Fix Script

Create a file `fix-cache.bat` in your Laravel project root:

```batch
@echo off
echo Fixing Laravel DataTablePro cache issues...
echo.

echo [1/6] Clearing config cache...
php artisan config:clear

echo [2/6] Clearing application cache...
php artisan cache:clear

echo [3/6] Clearing view cache...
php artisan view:clear

echo [4/6] Clearing route cache...
php artisan route:clear

echo [5/6] Clearing compiled files...
php artisan clear-compiled

echo [6/6] Rebuilding autoload...
composer dump-autoload -o

echo.
echo [7/7] Discovering packages...
php artisan package:discover --ansi

echo.
echo ✓ Cache cleared successfully!
echo.
echo Now try your application again.
pause
```

Then run:
```bash
.\fix-cache.bat
```

### For Linux/Mac Users

Create `fix-cache.sh`:

```bash
#!/bin/bash

echo "Fixing Laravel DataTablePro cache issues..."
echo ""

echo "[1/6] Clearing config cache..."
php artisan config:clear

echo "[2/6] Clearing application cache..."
php artisan cache:clear

echo "[3/6] Clearing view cache..."
php artisan view:clear

echo "[4/6] Clearing route cache..."
php artisan route:clear

echo "[5/6] Clearing compiled files..."
php artisan clear-compiled

echo "[6/6] Rebuilding autoload..."
composer dump-autoload -o

echo ""
echo "[7/7] Discovering packages..."
php artisan package:discover --ansi

echo ""
echo "✓ Cache cleared successfully!"
echo ""
echo "Now try your application again."
```

Then run:
```bash
chmod +x fix-cache.sh
./fix-cache.sh
```

## Verification

After clearing caches, verify the component is registered:

```bash
php artisan about
```

Look for the package in the "Packages" section.

## If Issue Persists

### Check 1: Verify Namespace in Your Code

If you have any imports in your controllers or views:

```php
// OLD (incorrect)
use SubhashLadumor\DataTablePro\DataTable\Builder;

// NEW (correct)
use SubhashLadumor1\DataTablePro\DataTable\Builder;
```

### Check 2: Verify Component Usage

In your Blade files, use:

```blade
<x-dtable-table
    id="my-table"
    :ajax="route('datatable')"
    :columns="$columns"
/>
```

NOT:

```blade
<!-- Don't use this -->
<x-SubhashLadumor\DataTablePro\View\Components\Table ... />
```

### Check 3: Verify composer.json in Vendor

Check if the package is correctly installed:

```bash
# View package info
composer show subhashladumor/laravel-datatablepro

# Check vendor directory
cat vendor/subhashladumor/laravel-datatablepro/composer.json
```

The `autoload` section should show:
```json
{
    "autoload": {
        "psr-4": {
            "SubhashLadumor1\\DataTablePro\\": "src/"
        }
    }
}
```

### Check 4: Delete Bootstrap Cache Files

Sometimes Laravel caches package discovery:

```bash
# Delete cache files
rm bootstrap/cache/packages.php
rm bootstrap/cache/services.php
rm bootstrap/cache/config.php

# Regenerate
php artisan package:discover
php artisan config:cache
```

## Common Mistakes to Avoid

1. ❌ **Don't** forget to clear caches after updating the package
2. ❌ **Don't** use the old namespace `SubhashLadumor\DataTablePro` anywhere
3. ❌ **Don't** manually register the service provider if using auto-discovery
4. ❌ **Don't** forget to rebuild composer autoload after changes

## Success Indicators

✅ `php artisan package:discover` shows the package
✅ `php artisan about` lists the package
✅ No errors when loading pages with the component
✅ Component renders correctly in the browser

## Still Not Working?

If the issue persists after all these steps:

1. **Check Laravel logs**: `storage/logs/laravel.log`
2. **Enable debug mode**: Set `APP_DEBUG=true` in `.env`
3. **Check PHP version**: Must be PHP 8.0 or higher
4. **Verify Laravel version**: Must be Laravel 9, 10, 11, or 12
5. **Check file permissions**: Ensure storage and bootstrap/cache are writable

```bash
# Fix permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
```

## Need More Help?

Create an issue with:
- Laravel version: `php artisan --version`
- PHP version: `php --version`
- Package version: `composer show subhashladumor/laravel-datatablepro`
- Output of: `php artisan package:discover`
- Laravel log errors from `storage/logs/laravel.log`
