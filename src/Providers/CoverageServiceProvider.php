<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Support\ServiceProvider;
use Unusualify\Modularity\Services\CoverageService;
use Unusualify\Modularity\Support\CommandDiscovery;
use Unusualify\Modularity\Support\CoverageAnalyzer;

class CoverageServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Register the low-level analyzer
        $this->app->bind('coverage.analyzer', function ($app, $params = []) {
            $cloverDir = $params['cloverDir'] ?? config('modularity-coverage.clover_dir', get_modularity_vendor_path());
            $cloverName = $params['cloverName'] ?? config('modularity-coverage.clover_name', 'coverage-clover.xml');

            return new CoverageAnalyzer($cloverDir, $cloverName);
        });

        // Register the high-level service (this is what the facade uses)
        $this->app->singleton('coverage.service', function ($app) {
            $cloverDir = config('modularity-coverage.clover_dir', get_modularity_vendor_path());
            $cloverName = config('modularity-coverage.clover_name', 'coverage-clover.xml');

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
            __DIR__ . '/../../config/coverage.php' => config_path('modularity-coverage.php'),
        ], 'modularity-coverage-config');

        // Merge default config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/coverage.php',
            'modularity-coverage'
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
