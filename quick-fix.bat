@echo off
REM Quick Fix Script for Laravel DataTablePro (Windows)

echo === Laravel DataTablePro Quick Fix ===
echo.

REM Check if we're in a Laravel project
if not exist "artisan" (
    echo Error: Not in a Laravel project root directory
    exit /b 1
)

echo [OK] Laravel project detected
echo.

REM Check if package is installed
if not exist "vendor\subhashladumor\laravel-datatablepro" (
    echo Error: Package not installed
    echo Run: composer require subhashladumor/laravel-datatablepro
    exit /b 1
)

echo [OK] Package installed
echo.

set PACKAGE_DIR=vendor\subhashladumor\laravel-datatablepro

REM Option 1: Build assets
echo Do you want to build the assets? (Recommended for production)
echo This requires Node.js and npm to be installed.
set /p BUILD="Build assets? (y/n): "

if /i "%BUILD%"=="y" (
    echo Building assets...
    cd "%PACKAGE_DIR%"
    
    if not exist "package.json" (
        echo Error: package.json not found
        exit /b 1
    )
    
    echo Installing npm dependencies...
    call npm install
    
    echo Building with Vite...
    call npm run build
    
    cd ..\..\..
    
    if exist "%PACKAGE_DIR%\src\Resources\dist" (
        echo [OK] Assets built successfully
    ) else (
        echo [WARNING] Build may have failed, check for errors
    )
)

echo.
echo Publishing assets...
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets" --force

echo.
echo Publishing views...
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-views"

echo.
echo Publishing config...
php artisan vendor:publish --provider="SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-config"

echo.
echo Running migrations...
php artisan migrate

echo.
echo === Verification ===

REM Check published assets
if exist "public\vendor\dtable\js" (
    echo [OK] Assets published
    echo   - Using built assets (public/vendor/dtable/js)
) else if exist "public\vendor\dtable\raw" (
    echo [OK] Assets published
    echo   - Using raw assets (public/vendor/dtable/raw)
    echo   [WARNING] For production, build the assets for better performance
) else (
    echo [ERROR] Assets not published properly
)

echo.
echo === Setup Complete ===
echo.
echo Next steps:
echo 1. Make sure your layout has @stack('styles') in ^<head^>
echo 2. Make sure your layout has @stack('scripts') before ^</body^>
echo 3. Use the component in your blade files:
echo    ^<x-dtable-table id="my-table" :ajax="route('datatable')" :columns="[...]" /^>
echo.
echo If you still see 'DTable is not defined', check:
echo - Browser console for 404 errors on JS files
echo - That scripts are loaded before DTable.init() is called
echo.

pause
