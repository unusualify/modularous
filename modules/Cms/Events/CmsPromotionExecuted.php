<?php

namespace Modules\Cms\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired after a successful {@see \Modules\Cms\Services\CmsPromotionService::promote} when not dry-run.
 * Listeners may trigger pipelines, notifications, or secondary cache invalidation.
 */
class CmsPromotionExecuted
{
    use Dispatchable, SerializesModels;

    /**
     * @param array<string, mixed> $scope
     * @param array<string, mixed> $report
     */
    public function __construct(
        public array $scope,
        public array $report,
        public ?Authenticatable $user = null,
    ) {}
}
