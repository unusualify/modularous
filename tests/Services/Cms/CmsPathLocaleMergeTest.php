<?php

namespace Unusualify\Modularity\Tests\Services\Cms;

use Modules\Cms\Support\CmsPathLocale;
use Unusualify\Modularity\Tests\TestCase;

class CmsPathLocaleMergeTest extends TestCase
{
    public function test_merge_includes_site_locales_not_only_mcamara_keys(): void
    {
        $this->app['config']->set('translatable.locales', ['tr', 'en']);

        $merged = CmsPathLocale::mergeMcamaraKeysWithSiteLocales(['en']);

        $this->assertEqualsCanonicalizing(['tr', 'en'], $merged);
    }

    public function test_merge_handles_null_mcamara_keys(): void
    {
        $this->app['config']->set('translatable.locales', ['de']);

        $merged = CmsPathLocale::mergeMcamaraKeysWithSiteLocales(null);

        $this->assertSame(['de'], $merged);
    }
}
