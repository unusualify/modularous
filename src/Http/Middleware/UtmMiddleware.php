<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;

class UtmMiddleware
{
    public function handle($request, Closure $next)
    {
        \Unusualify\Modularity\Facades\Utm::getParameters();

        view()->composer(['modularity::layouts.app-inertia', 'modularity::layouts.master'], function ($view) {
            $view->with(array_merge($view->getData(), [
                'utmParameters' => \Unusualify\Modularity\Facades\Utm::getParameters(),
            ]));
        });

        return $next($request);
    }
}
