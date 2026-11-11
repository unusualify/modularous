<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Psr\Http\Message\RequestInterface;
use Unusualify\Modularous\Exceptions\StagingLockedException;
use Unusualify\Modularous\Facades\ModularousRoutes;
use Unusualify\Modularous\Http\Middleware\StagingMiddleware;
use Unusualify\Modularous\Services\Staging\StagingGuard;
use Unusualify\Modularous\Services\Staging\StagingQueueConnector;
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
        $this->registerNotificationAndMailLocks($guard);
        $this->registerQueueLock($guard);
        $this->registerConsoleLocks($guard);
        $this->registerHttpLock($guard);
    }

    private function registerNotificationAndMailLocks(StagingGuard $guard): void
    {
        Event::listen(NotificationSending::class, function (NotificationSending $event) use ($guard): ?bool {
            if ($guard->allowsNotificationChannel($event->channel)) {
                return null;
            }

            Log::info('Staging: skipped ' . $event->notification::class . " on the {$event->channel} channel; unlock it with MODULAROUS_STAGING_NOTIFICATIONS.");

            return false;
        });

        Event::listen(MessageSending::class, function () use ($guard): ?bool {
            if ($guard->allowsMail()) {
                return null;
            }

            Log::info('Staging: skipped an outgoing mail; unlock it with MODULAROUS_STAGING_MAIL.');

            return false;
        });
    }

    /**
     * Every queue connection is switched to the staging driver so jobs targeting
     * a specific connection cannot slip past the allowlist.
     */
    private function registerQueueLock(StagingGuard $guard): void
    {
        $this->app['queue']->addConnector('staging', fn () => new StagingQueueConnector($guard));

        foreach (array_keys($this->app['config']->get('queue.connections', [])) as $connection) {
            $this->app['config']->set("queue.connections.{$connection}.driver", 'staging');
        }
    }

    private function registerConsoleLocks(StagingGuard $guard): void
    {
        Event::listen(CommandStarting::class, function (CommandStarting $event) use ($guard): void {
            if (! $guard->allowsCommand($event->command)) {
                throw StagingLockedException::command($event->command);
            }

            if (in_array($event->command, ['schedule:run', 'schedule:work', 'schedule:test'], true)) {
                foreach ($this->app->make(Schedule::class)->events() as $scheduled) {
                    if (! $guard->allowsScheduledEvent($scheduled)) {
                        $scheduled->skip(fn () => true);
                    }
                }
            }
        });
    }

    private function registerHttpLock(StagingGuard $guard): void
    {
        Http::globalRequestMiddleware(function (RequestInterface $request) use ($guard): RequestInterface {
            $host = $request->getUri()->getHost();

            if (! $guard->allowsHost($host)) {
                throw StagingLockedException::host($host);
            }

            return $request;
        });
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
