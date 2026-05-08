<?php

namespace Modules\Cms\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Cms\Services\CmsPromotionService;

class PromoteCmsReleaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param array<string, mixed> $payload `scope`, optional `user_id` for audit / activity attribution
     */
    public function __construct(
        public array $payload = [],
    ) {}

    public function handle(CmsPromotionService $promotionService): void
    {
        $payload = array_merge($this->payload, ['dry_run' => false]);

        $promotionService->promote($payload, null);
    }
}
