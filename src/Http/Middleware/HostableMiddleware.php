<?php

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HostableMiddleware
{
    /**
     * Handles an incoming request.
     *
     * @param Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
