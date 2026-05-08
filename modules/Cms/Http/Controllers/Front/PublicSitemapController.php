<?php

namespace Modules\Cms\Http\Controllers\Front;

use Illuminate\Http\Response;
use Modules\Cms\Services\CmsSitemapCacheService;

/**
 * Serves the last **committed** sitemap from cache; rebuild via {@see \Modules\Cms\Jobs\RebuildCmsSitemapJob} or
 * `cms:sitemap:rebuild` artisan.
 */
final class PublicSitemapController
{
    public function __invoke(CmsSitemapCacheService $cache): Response
    {
        $body = $cache->getCommittedXml();

        return response($body, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'all',
        ]);
    }
}
