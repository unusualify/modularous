<?php

namespace Unusualify\Modularous\Console;

use Unusualify\Modularous\Services\Staging\StagingGuard;

class StagingStatusCommand extends BaseCommand
{
    protected $signature = 'modularous:staging:status';

    protected $description = 'Show which side effects the staging guard locks or unlocks';

    public function handle(): int
    {
        $summary = $this->laravel->make(StagingGuard::class)->summary();

        if (! $summary['enabled']) {
            $this->info('Staging guard is disabled (MODULAROUS_STAGING=false); nothing is locked.');

            return self::SUCCESS;
        }

        $this->info('Staging guard is enabled' . ($summary['label'] ? " ({$summary['label']})" : '') . '.');

        $format = fn (mixed $value): string => match (true) {
            is_bool($value) => $value ? 'unlocked' : 'locked',
            $value === [] => 'locked',
            default => implode(', ', $value),
        };

        $this->table(['Lock', 'Unlocked'], [
            ['Writable tables', $format($summary['writable_tables'])],
            ['Writable routes', $format($summary['writable_routes'])],
            ['Notification channels', $format($summary['notification_channels'])],
            ['Mail', $format($summary['mail'])],
            ['Queue jobs', $format($summary['queue_jobs'])],
            ['Scheduled tasks', $format($summary['schedule_commands'])],
            ['HTTP hosts', $format($summary['http_hosts'])],
            ['Artisan commands', $format($summary['commands'])],
        ]);

        return self::SUCCESS;
    }
}
