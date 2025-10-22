# Troubleshooting Guide

## Common Errors and Solutions

### 1. Error: "Can't locate path: .../Resources/dist"

**Error Message:**
```
ERROR  Can't locate path: <C:\...\vendor\subhashladumor\laravel-datatablepro\src\Providers/../Resources/dist>.
```

**Cause:** The frontend assets haven't been built yet.

**Solution:**

#### Option A: Use the Quick Fix Script (Recommended)

**Windows:**
```powershell
.\quick-fix.bat
```

**Linux/Mac:**
```bash
chmod +x quick-fix.sh
./quick-fix.sh
```

#### Option B: Manual Build

```bash
# 1. Navigate to the package
cd vendor/subhashladumor/laravel-datatablepro

# 2. Install dependencies
npm install

# 3. Build assets
npm run build

# 4. Return to project root
cd ../../..

# 5. Publish assets
php artisan vendor:publish --provider="SubhashLadumor\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets" --force
```

#### Option C: Use Raw Assets (Development Only)

The package now automatically publishes raw assets if dist doesn't exist:

```bash
php artisan vendor:publish --provider="SubhashLadumor\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets"
```

Assets will be published to `public/vendor/dtable/raw/`.

---

### 2. Error: "DTable is not defined"

**Error Message:**
```javascript
Uncaught ReferenceError: DTable is not defined
```

**Cause:** JavaScript files aren't loaded or loaded after the initialization script.

**Solutions:**

#### A. Ensure Layout Stack Points Exist

Your main layout must have these stack points:

```blade
<!DOCTYPE html>
<html>
<head>
    <!-- ... other head content ... -->
    @stack('styles')  <!-- Add this -->
</head>
<body>
    @yield('content')
    
    <!-- ... other scripts ... -->
    @stack('scripts')  <!-- Add this BEFORE closing </body> -->
</body>
</html>
```

#### B. Check Asset Path

Open your browser's developer console (F12) and check:

1. **Network Tab** - Look for 404 errors on dtable.js files
2. **Console Tab** - Look for loading errors

If you see 404 errors:

```bash
# Republish assets
php artisan vendor:publish --provider="SubhashLadumor\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets" --force
```

#### C. Clear Cache

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

#### D. Check File Exists

Verify the JavaScript files exist:

**For built assets:**
- `public/vendor/dtable/js/dtable.js`
- `public/vendor/dtable/js/dtable-renderers.js`

**For raw assets:**
- `public/vendor/dtable/raw/js/dtable.core.js`
- `public/vendor/dtable/raw/js/dtable.renderers.js`

If files don't exist, republish:

```bash
php artisan vendor:publish --provider="SubhashLadumor\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets" --force
```

---

### 3. Error: "Namespace not found"

**Error Message:**
```
Class 'SubhashLadumor\DataTablePro\...' not found
```

**Solution:**

```bash
# Rebuild autoload
composer dump-autoload

# Or reinstall package
composer remove subhashladumor/laravel-datatablepro
composer require subhashladumor/laravel-datatablepro
```

---

### 4. Styles Not Applied

**Symptoms:** Table appears unstyled or broken.

**Solutions:**

#### A. Check CSS is Published

```bash
php artisan vendor:publish --provider="SubhashLadumor\DataTablePro\Providers\DataTableServiceProvider" --tag="datatable-assets" --force
```

#### B. Verify CSS Path

Built assets: `public/vendor/dtable/css/dtable-styles.css`

Raw assets: `public/vendor/dtable/raw/scss/dtable.scss`

#### C. Check Browser Console

Open DevTools → Network tab → Filter CSS → Look for 404 errors

---

### 5. Ajax Errors

**Error Message:**
```
Failed to load resource: the server responded with a status of 500
```

**Solutions:**

#### A. Check Route is Correct

```php
// routes/web.php
Route::post('/datatable', [YourController::class, 'datatable'])->name('datatable');
```

#### B. Check CSRF Token

Ensure your main layout has:

```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
```

#### C. Check Controller Method

```php
public function datatable(Request $request)
{
    return Builder::make()
        ->eloquent(YourModel::query())
        ->columns([...])
        ->toResponse($request);  // Don't forget this!
}
```

#### D. Enable Debug Mode

In `.env`:
```
APP_DEBUG=true
```

Check `storage/logs/laravel.log` for errors.

---

### 6. Export Not Working

**Symptoms:** Export buttons don't work or return errors.

**Solutions:**

#### A. Enable Export

```php
Builder::make()
    ->eloquent(User::query())
    ->columns([...])
    ->exportable()  // Don't forget this!
    ->toResponse($request);
```

#### B. Install Optional Dependencies

```bash
# For XLSX
composer require maatwebsite/excel

# For PDF
composer require barryvdh/laravel-dompdf
```

#### C. Check Export Route

```php
Route::post('/export', [YourController::class, 'export'])->name('export');
```

#### D. Configure Queue for Large Exports

In `.env`:
```
QUEUE_CONNECTION=database
```

Then:
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

---

### 7. Relationship Columns Not Working

**Error Message:**
```
Column 'xyz' doesn't exist
```

**Solutions:**

#### A. Use Eager Loading

```php
Builder::make()
    ->eloquent(Post::query())
    ->columns([
        Column::make('name', 'Author')
            ->relationship('user'),  // Relationship name
    ])
    ->with(['user'])  // Eager load!
    ->toResponse($request);
```

#### B. Check Relationship Exists

```php
// In your model
public function user()
{
    return $this->belongsTo(User::class);
}
```

---

### 8. Build Errors (npm)

**Error Message:**
```
npm ERR! code ENOENT
```

**Solutions:**

#### A. Install Node.js

Download from: https://nodejs.org/

#### B. Clear npm Cache

```bash
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

#### C. Use Correct Node Version

```bash
node --version  # Should be 16+
```

---

## Performance Issues

### Slow Loading

**Solutions:**

1. **Add Database Indexes**
   ```php
   Schema::table('users', function (Blueprint $table) {
       $table->index('email');
       $table->index('created_at');
   });
   ```

2. **Limit Eager Loading**
   ```php
   ->with(['user:id,name'])  // Select only needed columns
   ```

3. **Use Virtual Scrolling**
   ```php
   ->virtualScroll()
   ->pageLength(100)
   ```

4. **Enable Caching** (if using same data frequently)

---

## Browser-Specific Issues

### Internet Explorer

**Not Supported.** This package uses modern JavaScript (ES6+).

Minimum browsers:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

---

## Getting More Help

If none of these solutions work:

1. **Check Laravel Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check Browser Console**
   Press F12 → Console tab

3. **Enable Debug Mode**
   ```
   APP_DEBUG=true
   ```

4. **Create an Issue**
   Visit: https://github.com/subhashladumor/laravel-datatablepro/issues

Include:
- Laravel version
- PHP version
- Error message
- Steps to reproduce
- Browser console errors
