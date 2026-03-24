<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Unusualify\Modularity\Facades\Utm;

class UtmMiddleware
{
    public function handle($request, Closure $next)
    {
        Utm::getParameters();

        view()->composer(['modularity::layouts.app-inertia', 'modularity::layouts.master'], function ($view) {
            $view->with(array_merge($view->getData(), [
                'utmParameters' => Utm::getParameters(),
            ]));
        });

        return $next($request);
    }
}
