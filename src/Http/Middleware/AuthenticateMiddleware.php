<?php

namespace Unusualify\Modularous\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Middleware\Concerns\HandlesUnauthenticatedInertiaAndAjax;

class AuthenticateMiddleware extends Middleware
{
    use HandlesUnauthenticatedInertiaAndAjax;

    protected function fallbackLoginUrlForUnauthenticated(): string
    {
        if (Route::has('admin.login')) {
            return route('admin.login');
        }

        return route(Route::hasAdmin('login.form'));
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if ($request->expectsJson()) {
            $referer = $request->headers->get('referer');
            session()->put('url.intended', $referer);

            return null;
        }

        $modularousAdminRouteNamePrefix = Modularous::getAdminRouteNamePrefix();
        // Define auth routes that should not be stored as intended URL
        $excludedRoutes = Arr::map([
            'login.form', 'login', 'logout',
            'register.form', 'register', 'register.success',
            'password.reset.link', 'password.reset.email',
            'password.reset.success', 'password.reset',
            'password.reset.update',
            'impersonate.stop', 'impersonate',
        ], function ($route) use ($modularousAdminRouteNamePrefix) {
            return $modularousAdminRouteNamePrefix ? $modularousAdminRouteNamePrefix . '.' . $route : $route;
        });

        $routeName = $request->route()?->getName();
        // Only store the previous URL if it's not an auth route
        if ($routeName === null || ! in_array($routeName, $excludedRoutes)) {
            session()->put('url.intended', url()->previous());
        }

        return route(Route::hasAdmin('login.form'));
    }
}
