<?php

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NavigationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        view()->composer([
            modularousBaseKey() . '::layouts.*',
            'translation::layout',
        ], function ($view) {
            $view->with('navigation', get_modularous_navigation_config());
        });

        return $next($request);
    }
}
