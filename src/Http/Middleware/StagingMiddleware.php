<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Unusualify\Modularous\Exceptions\StagingLockedException;
use Unusualify\Modularous\Services\Staging\StagingGuard;

class StagingMiddleware
{
    private const READ_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(private readonly StagingGuard $guard) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->guard->enabled()) {
            return $next($request);
        }

        $route = $request->route()?->getName();

        if ($this->guard->isUnguardedRoute($route)) {
            return $this->guard->unguarded(fn () => $next($request));
        }

        if (! in_array($request->getMethod(), self::READ_METHODS, true) && ! $this->guard->canWriteRoute($route)) {
            throw StagingLockedException::route($request->getMethod(), $route);
        }

        return $next($request);
    }
}
