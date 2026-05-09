<?php

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Unusualify\Modularous\Facades\Utm;

class UtmMiddleware
{
    public function handle($request, Closure $next)
    {
        Utm::getParameters();

        view()->composer(['modularous::layouts.app-inertia', 'modularous::layouts.master'], function ($view) {
            $view->with(array_merge($view->getData(), [
                'utmParameters' => Utm::getParameters(),
            ]));
        });

        return $next($request);
    }
}
