<?php

namespace Modules\Cms\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Cms\Services\CmsSitemapBuildService;
use Modules\Cms\Services\CmsSitemapCacheService;

final class RebuildCmsSitemapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(CmsSitemapBuildService $build, CmsSitemapCacheService $cache): void
    {
        $xml = $build->buildXml();
        $cache->commit($xml);
    }
}
