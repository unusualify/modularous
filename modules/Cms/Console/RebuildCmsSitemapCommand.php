<?php

namespace Modules\Cms\Console;

use Illuminate\Console\Command;
use Modules\Cms\Services\CmsSitemapBuildService;
use Modules\Cms\Services\CmsSitemapCacheService;

class RebuildCmsSitemapCommand extends Command
{
    protected $signature = 'cms:sitemap:rebuild {--dry-run : Print XML to stdout only; do not update cache}';

    protected $description = 'Build CMS sitemap from UrlRoute (page_public) and write to the committed cache (unless --dry-run).';

    public function handle(CmsSitemapBuildService $build, CmsSitemapCacheService $cache): int
    {
        $xml = $build->buildXml();

        if ((bool) $this->option('dry-run')) {
            $this->line($xml);

            return self::SUCCESS;
        }

        $cache->commit($xml);
        $this->info('Sitemap built and cache committed.');

        return self::SUCCESS;
    }
}
