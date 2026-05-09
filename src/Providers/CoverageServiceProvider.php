<?php

namespace Unusualify\Modularous\Providers;

use Illuminate\Support\ServiceProvider;
use Unusualify\Modularous\Services\CoverageService;
use Unusualify\Modularous\Support\CommandDiscovery;
use Unusualify\Modularous\Support\CoverageAnalyzer;

class CoverageServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Register the low-level analyzer
        $this->app->bind('coverage.analyzer', function ($app, $params = []) {
            $cloverDir = $params['cloverDir'] ?? config('modularous-coverage.clover_dir', get_modularous_vendor_path());
            $cloverName = $params['cloverName'] ?? config('modularous-coverage.clover_name', 'coverage-clover.xml');

            return new CoverageAnalyzer($cloverDir, $cloverName);
        });

        // Register the high-level service (this is what the facade uses)
        $this->app->singleton('coverage.service', function ($app) {
            $cloverDir = config('modularous-coverage.clover_dir', get_modularous_vendor_path());
            $cloverName = config('modularous-coverage.clover_name', 'coverage-clover.xml');

            return new CoverageService($cloverDir, $cloverName);
        });

        // Alias for convenience
        $this->app->alias('coverage.service', CoverageService::class);
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../../config/coverage.php' => config_path('modularous-coverage.php'),
        ], 'modularous-coverage-config');

        // Merge default config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/coverage.php',
            'modularous-coverage'
        );

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands(CommandDiscovery::discover([
                __DIR__ . '/../Console/Coverage/*.php',
            ]));
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'coverage.analyzer',
            'coverage.service',
            CoverageService::class,
        ];
    }
}
