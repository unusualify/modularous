<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Staging;

use Closure;
use Illuminate\Console\Scheduling\Event as ScheduledEvent;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Unusualify\Modularous\Exceptions\StagingLockedException;

/**
 * Decides which side effects a staging app may perform. Everything is locked
 * while staging is enabled unless the modularous.staging config unlocks it.
 */
class StagingGuard
{
    private const WRITE_STATEMENT = '/^\s*(?:insert(?:\s+or\s+\w+)?(?:\s+ignore)?\s+into|replace\s+into|update(?:\s+ignore)?|delete\s+from|truncate(?:\s+table)?|(?:create|alter|drop)\s+(?:temporary\s+)?table(?:\s+if\s+(?:not\s+)?exists)?)\s+((?:[`"\[]?\w+[`"\]]?\.)?[`"\[]?\w+[`"\]]?)/i';

    private int $unguardedDepth = 0;

    public function __construct(private readonly Application $app) {}

    public function enabled(): bool
    {
        return (bool) $this->config('enabled', false);
    }

    public function label(): ?string
    {
        return $this->config('label');
    }

    public function canWriteTable(string $table): bool
    {
        if (! $this->enabled() || $this->isUnguarded()) {
            return true;
        }

        return Str::is([...$this->config('defaults.tables', []), ...$this->config('writable_tables', [])], $table);
    }

    /**
     * @throws StagingLockedException
     */
    public function assertWritable(string $table, string $operation): void
    {
        if (! $this->canWriteTable($table)) {
            throw StagingLockedException::table($table, $operation);
        }
    }

    public function canWriteRoute(?string $route): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        return $route !== null && Str::is($this->config('writable_routes', []), $route);
    }

    public function isUnguardedRoute(?string $route): bool
    {
        return $route !== null && Str::is($this->config('defaults.unguarded_routes', []), $route);
    }

    /**
     * Run the callback with table and query guards lifted (sign-in, sign-out).
     *
     * @template T
     *
     * @param Closure(): T $callback
     * @return T
     */
    public function unguarded(Closure $callback): mixed
    {
        $this->unguardedDepth++;

        try {
            return $callback();
        } finally {
            $this->unguardedDepth--;
        }
    }

    public function isUnguarded(): bool
    {
        return $this->unguardedDepth > 0;
    }

    public function shouldGuardQueries(): bool
    {
        if (! $this->enabled() || $this->isUnguarded()) {
            return false;
        }

        return ! $this->app->runningInConsole() || (bool) $this->config('guard_console_queries', false);
    }

    /**
     * Table written by a SQL statement, or null for reads and transaction control.
     */
    public function writtenTable(string $sql, string $tablePrefix = ''): ?string
    {
        if (preg_match(self::WRITE_STATEMENT, $sql, $matches) !== 1) {
            return null;
        }

        $table = Str::afterLast(str_replace(['`', '"', '[', ']'], '', $matches[1]), '.');

        return $tablePrefix !== '' ? Str::after($table, $tablePrefix) : $table;
    }

    public function allowsNotificationChannel(string $channel): bool
    {
        return ! $this->enabled() || Str::is($this->config('notification_channels', []), $channel);
    }

    public function allowsMail(): bool
    {
        return ! $this->enabled() || (bool) $this->config('mail', false);
    }

    public function allowsJob(string $class): bool
    {
        return ! $this->enabled() || Str::is($this->config('queue_jobs', []), ltrim($class, '\\'));
    }

    public function allowsScheduledEvent(ScheduledEvent $event): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        $patterns = $this->config('schedule_commands', []);

        $command = preg_match("/artisan'?\s+(\S+)/", (string) $event->command, $matches) === 1
            ? trim($matches[1], "'\"")
            : null;

        return ($command !== null && Str::is($patterns, $command))
            || ($event->description !== null && Str::is($patterns, $event->description));
    }

    public function allowsHost(string $host): bool
    {
        return ! $this->enabled() || Str::is($this->config('http_hosts', []), $host);
    }

    public function allowsCommand(?string $command): bool
    {
        if (! $this->enabled() || $command === null) {
            return true;
        }

        return Str::is([...$this->config('defaults.commands', []), ...$this->config('commands', [])], $command);
    }

    /**
     * Effective lock state, for diagnostics.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'enabled' => $this->enabled(),
            'label' => $this->label(),
            'writable_tables' => [...$this->config('defaults.tables', []), ...$this->config('writable_tables', [])],
            'writable_routes' => $this->config('writable_routes', []),
            'notification_channels' => $this->config('notification_channels', []),
            'mail' => (bool) $this->config('mail', false),
            'queue_jobs' => $this->config('queue_jobs', []),
            'schedule_commands' => $this->config('schedule_commands', []),
            'http_hosts' => $this->config('http_hosts', []),
            'commands' => [...$this->config('defaults.commands', []), ...$this->config('commands', [])],
        ];
    }

    private function config(string $key, mixed $default = null): mixed
    {
        return $this->app['config']->get(modularousBaseKey() . '.staging.' . $key, $default);
    }
}
