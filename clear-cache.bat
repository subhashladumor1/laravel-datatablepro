@echo off
REM Laravel DataTablePro Cache Clear Script
REM Run this in your Laravel application root directory

echo.
echo ========================================
echo   Laravel DataTablePro Cache Clearer
echo ========================================
echo.

REM Check if artisan exists
if not exist "artisan" (
    echo [ERROR] artisan file not found!
    echo This script must be run from your Laravel project root directory.
    echo.
    pause
    exit /b 1
)

echo [OK] Laravel project detected
echo.

echo [1/7] Clearing config cache...
php artisan config:clear
if errorlevel 1 (
    echo [ERROR] Failed to clear config cache
    pause
    exit /b 1
)

echo [2/7] Clearing application cache...
php artisan cache:clear
if errorlevel 1 (
    echo [ERROR] Failed to clear application cache
    pause
    exit /b 1
)

echo [3/7] Clearing view cache...
php artisan view:clear
if errorlevel 1 (
    echo [ERROR] Failed to clear view cache
    pause
    exit /b 1
)

echo [4/7] Clearing route cache...
php artisan route:clear
if errorlevel 1 (
    echo [ERROR] Failed to clear route cache
    pause
    exit /b 1
)

echo [5/7] Clearing compiled files...
php artisan clear-compiled
if errorlevel 1 (
    echo [ERROR] Failed to clear compiled files
    pause
    exit /b 1
)

echo [6/7] Rebuilding composer autoload...
composer dump-autoload -o
if errorlevel 1 (
    echo [ERROR] Failed to rebuild autoload
    pause
    exit /b 1
)

echo [7/7] Discovering packages...
php artisan package:discover --ansi
if errorlevel 1 (
    echo [ERROR] Failed to discover packages
    pause
    exit /b 1
)

echo.
echo ========================================
echo   ✓ All caches cleared successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Refresh your browser (Ctrl+F5)
echo 2. Clear browser cache if needed
echo 3. Try your application again
echo.
echo If issues persist, see: CACHE-CLEAR-FIX.md
echo.

pause
