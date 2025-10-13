<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Unusualify\Modularity\Facades\Modularity;

class LoadLocalizedConfig
{
    public function handle($request, Closure $next)
    {
        $baseKey = modularityBaseKey();

        foreach (glob(Modularity::getVendorPath('config/defers/*.php')) as $path) {
            extract(pathinfo($path)); // $filename
            mergeConfigFrom($path, $baseKey . ".{$filename}");
        }

        $newNavigationExists = false;

        foreach (glob(base_path('modularity/*.php')) as $path) {
            extract(pathinfo($path)); // $filename
            // $this->mergeConfigFrom($path, $this->baseKey . ".{$filename}",);

            if ($filename === 'navigation') {
                $newNavigationExists = true;
            }

            $modularityConfigPart = config($baseKey . ".{$filename}");
            $newConfigPart = require $path;

            if (! config($baseKey . ".{$filename}", false)) {
                continue;
            }

            config()->set($baseKey . ".{$filename}", array_merge_recursive_preserve($modularityConfigPart, $newConfigPart));
        }

        /**
         * @deprecated 10.0.0 Remove this after 10.0.0 release
         *
         * @uses modularity/navigation.php instead
         */
        if (! $newNavigationExists) {
            config("{$baseKey}.navigation", array_merge_recursive_preserve(config("{$baseKey}.navigation"), config("{$baseKey}-navigation", [])));
        }

        return $next($request);
    }
}
