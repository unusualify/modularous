<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Support\CmsPathLocale;
use Unusualify\Modularous\Tests\TestCase;

class CmsPathLocaleTest extends TestCase
{
    public function test_path_segment_locales_uses_config_override_when_set(): void
    {
        $this->app['config']->set('modularous.cms_routing.path_segment_locales', ['xx', 'yy']);

        $this->assertSame(['xx', 'yy'], CmsPathLocale::pathSegmentLocales());
    }

    public function test_path_segment_locales_falls_back_to_translatable_locales(): void
    {
        $this->app['config']->set('modularous.cms_routing.path_segment_locales', null);
        $this->app['config']->set('translatable.locales', ['tr', 'en']);

        $locales = CmsPathLocale::pathSegmentLocales();

        $this->assertContains('tr', $locales);
        $this->assertContains('en', $locales);
        $this->assertCount(2, $locales);
    }
}
