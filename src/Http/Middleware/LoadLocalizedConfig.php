<?php

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Unusualify\Modularous\Facades\Modularous;

class LoadLocalizedConfig
{
    public function handle($request, Closure $next)
    {
        $baseKey = modularousBaseKey();

        foreach (glob(Modularous::getVendorPath('config/defers/*.php')) as $path) {
            extract(pathinfo($path)); // $filename
            mergeConfigFrom($path, $baseKey . ".{$filename}");
        }

        $newNavigationExists = false;

        foreach (glob(base_path('modularous/*.php')) as $path) {
            extract(pathinfo($path)); // $filename
            // $this->mergeConfigFrom($path, $this->baseKey . ".{$filename}",);

            if ($filename === 'navigation') {
                $newNavigationExists = true;
            }

            $modularousConfigPart = config($baseKey . ".{$filename}");
            $newConfigPart = require $path;

            if (! config($baseKey . ".{$filename}", false)) {
                continue;
            }

            config()->set($baseKey . ".{$filename}", array_merge_recursive_preserve($modularousConfigPart, $newConfigPart));
        }

        /**
         * @deprecated 10.0.0 Remove this after 10.0.0 release
         *
         * @uses modularous/navigation.php instead
         */
        if (! $newNavigationExists) {
            config("{$baseKey}.navigation", array_merge_recursive_preserve(config("{$baseKey}.navigation"), config("{$baseKey}-navigation", [])));
        }

        return $next($request);
    }
}
