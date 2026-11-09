<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Staging;

use Illuminate\Contracts\Queue\Queue;
use Illuminate\Queue\Connectors\ConnectorInterface;

class StagingQueueConnector implements ConnectorInterface
{
    public function __construct(private readonly StagingGuard $guard) {}

    public function connect(array $config): Queue
    {
        return new StagingQueue($this->guard);
    }
}
