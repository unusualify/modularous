<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Providers;

use Unusualify\Modularous\Services\Staging\StagingGuard;

class StagingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StagingGuard::class);
    }
}
