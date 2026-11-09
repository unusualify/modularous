<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Providers;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Facades\ModularousRoutes;
use Unusualify\Modularous\Http\Middleware\StagingMiddleware;
use Unusualify\Modularous\Services\Staging\StagingGuard;
use WeakMap;

class StagingServiceProvider extends ServiceProvider
{
    /** @var WeakMap<Connection, true> */
    private WeakMap $guardedConnections;

    public function register(): void
    {
        $this->app->singleton(StagingGuard::class);

        if (! $this->app->make(StagingGuard::class)->enabled()) {
            return;
        }

        ModularousRoutes::addDefaultMiddlewares(['modularous.staging']);
    }

    public function boot(): void
    {
        Route::aliasMiddleware('modularous.staging', StagingMiddleware::class);

        $guard = $this->app->make(StagingGuard::class);

        if (! $guard->enabled()) {
            return;
        }

        $this->registerQueryGuard($guard);
    }

    private function registerQueryGuard(StagingGuard $guard): void
    {
        $this->guardedConnections = new WeakMap;

        foreach ($this->app['db']->getConnections() as $connection) {
            $this->guardConnection($connection, $guard);
        }

        Event::listen(ConnectionEstablished::class, fn (ConnectionEstablished $event) => $this->guardConnection($event->connection, $guard));
    }

    private function guardConnection(Connection $connection, StagingGuard $guard): void
    {
        if (isset($this->guardedConnections[$connection])) {
            return;
        }

        $this->guardedConnections[$connection] = true;

        $connection->beforeExecuting(function (string $query, array $bindings, Connection $connection) use ($guard): void {
            if (! $guard->shouldGuardQueries()) {
                return;
            }

            $table = $guard->writtenTable($query, $connection->getTablePrefix());

            if ($table !== null) {
                $guard->assertWritable($table, mb_strtolower(strtok(ltrim($query), " \n\t")));
            }
        });
    }
}
