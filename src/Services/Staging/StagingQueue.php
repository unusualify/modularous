<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Staging;

use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\SyncQueue;
use Illuminate\Support\Facades\Log;

/**
 * Runs allowed jobs synchronously and drops the rest, whatever connection they target.
 */
class StagingQueue extends SyncQueue
{
    public function __construct(private readonly StagingGuard $guard)
    {
        parent::__construct();
    }

    public function push($job, $data = '', $queue = null)
    {
        $class = $this->jobClass($job);

        if (! $this->guard->allowsJob($class)) {
            Log::info("Staging: dropped queued {$class}; unlock it with MODULAROUS_STAGING_QUEUE_JOBS.");

            return null;
        }

        return parent::push($job, $data, $queue);
    }

    public function jobClass(mixed $job): string
    {
        return match (true) {
            $job instanceof CallQueuedListener => $job->class,
            $job instanceof SendQueuedNotifications => $job->notification::class,
            $job instanceof SendQueuedMailable => $job->mailable::class,
            $job instanceof BroadcastEvent => $job->event::class,
            is_object($job) => $job::class,
            default => (string) $job,
        };
    }
}
