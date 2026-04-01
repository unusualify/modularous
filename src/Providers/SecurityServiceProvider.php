<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Facades\ModularityRoutes;
use Unusualify\Modularity\Http\Middleware\RequireMfaMiddleware;
use Unusualify\Modularity\Http\Middleware\SessionSecurityMiddleware;
use Unusualify\Modularity\Http\Middleware\StepUpMiddleware;
use Unusualify\Modularity\Services\Security\SecurityService;


class SecurityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // $this->app->singleton(SecurityService::class, fn () => new SecurityService);

        if (! modularityConfig('security.enabled', false)) {
            return;
        }

        ModularityRoutes::addDefaultMiddlewares([
            'modularity.security.session',
            'modularity.security.require_mfa',
            'modularity.security.step_up',
        ]);
    }

    public function boot(): void
    {
        Route::aliasMiddleware('modularity.security.session', SessionSecurityMiddleware::class);
        Route::aliasMiddleware('modularity.security.require_mfa', RequireMfaMiddleware::class);
        Route::aliasMiddleware('modularity.security.step_up', StepUpMiddleware::class);
    }
}
