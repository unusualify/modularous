<?php

namespace Unusualify\Modularity\Providers;

class ModularityProvider extends ServiceProvider
{
    protected $providers = [
        // Third Party Providers
        \Torann\GeoIP\GeoIPServiceProvider::class,
        \Camroncade\Timezone\TimezoneServiceProvider::class,

        // Unusual Providers
        BaseServiceProvider::class,
        ModuleServiceProvider::class,
        RouteServiceProvider::class,
        AuthServiceProvider::class,

        // AuthServiceProvider::class,
        // ValidationServiceProvider::class,
        // TranslatableServiceProvider::class,
        // TagsServiceProvider::class,
        // ActivitylogServiceProvider::class,
        // CapsulesServiceProvider::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerProviders();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Has to be merged after routeServiceProvider registered
        if (exceptionalRunningInConsole()) {
            // $this->mergeConfigFrom(__DIR__ . '/../../config/navigation.php', modularityBaseKey() . '-navigation');
            $this->booted(function () {

                foreach (glob(__DIR__ . '/../../config/defers/*.php') as $path) {
                    extract(pathinfo($path)); // $filename
                    $this->mergeConfigFrom($path, $this->baseKey . ".{$filename}");
                }

                $newNavigationExists = false;

                foreach (glob(base_path('modularity/*.php')) as $path) {
                    extract(pathinfo($path)); // $filename
                    // $this->mergeConfigFrom($path, $this->baseKey . ".{$filename}",);

                    if ($filename === 'navigation') {
                        $newNavigationExists = true;
                    }

                    $modularityConfigPart = $this->app->config->get($this->baseKey . ".{$filename}");
                    $newConfigPart = require $path;

                    if (! $this->app->config->get($this->baseKey . ".{$filename}", false)) {
                        continue;
                    }

                    $this->app->config->set($this->baseKey . ".{$filename}", array_merge_recursive_preserve($modularityConfigPart, $newConfigPart));
                }

                /**
                 * @deprecated 10.0.0 Remove this after 10.0.0 release
                 *
                 * @uses modularity/navigation.php instead
                 */
                if (! $newNavigationExists) {
                    $this->app->config->set("{$this->baseKey}.navigation", array_merge_recursive_preserve($this->app->config->get("{$this->baseKey}.navigation"), $this->app->config->get("{$this->baseKey}-navigation", [])));
                }
            });
        }
    }

    /**
     * Register providers.
     */
    protected function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }
}
