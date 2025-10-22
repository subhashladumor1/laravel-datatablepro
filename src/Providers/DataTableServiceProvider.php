<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\Providers;

use Illuminate\Support\ServiceProvider;
use SubhashLadumor1\DataTablePro\DataTable\Builder;
use SubhashLadumor1\DataTablePro\DataTable\ExportManager;

/**
 * DataTableServiceProvider
 *
 * Registers the DataTablePro package services, views, assets, routes, and publishes resources.
 */
class DataTableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/datatable.php',
            'datatable'
        );

        $this->app->singleton(ExportManager::class, function ($app) {
            return new ExportManager($app);
        });

        $this->app->bind('datatable', function ($app) {
            return new Builder();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'datatable');
        
        $this->loadRoutesFrom(__DIR__ . '/../Routes/dtable.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/datatable.php' => config_path('datatable.php'),
            ], 'datatable-config');

            $this->publishes([
                __DIR__ . '/../Resources/views' => resource_path('views/vendor/datatable'),
            ], 'datatable-views');

            // Publish built assets if available, otherwise publish raw assets
            $distPath = __DIR__ . '/../Resources/dist';
            $assetsPath = __DIR__ . '/../Resources/assets';
            
            if (is_dir($distPath)) {
                $this->publishes([
                    $distPath => public_path('vendor/dtable'),
                ], 'datatable-assets');
            } else {
                // Publish raw assets if dist doesn't exist
                $this->publishes([
                    $assetsPath => public_path('vendor/dtable/raw'),
                ], 'datatable-assets');
            }

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        $this->loadBladeComponents();
    }

    /**
     * Load Blade components.
     */
    protected function loadBladeComponents(): void
    {
        $this->loadViewComponentsAs('dtable', [
            \SubhashLadumor1\DataTablePro\View\Components\Table::class,
        ]);
    }
}
