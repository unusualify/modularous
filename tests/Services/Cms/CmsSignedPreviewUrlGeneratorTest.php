<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Services\CmsSignedPreviewUrlGenerator;
use Unusualify\Modularous\Tests\TestCase;

class CmsSignedPreviewUrlGeneratorTest extends TestCase
{
    public function test_ttl_minutes_respects_config_minimum(): void
    {
        $this->app['config']->set('modularous.cms_routing.signed_preview.ttl_minutes', 3);

        $generator = new CmsSignedPreviewUrlGenerator;

        $this->assertSame(5, $generator->ttlMinutes());
    }

    public function test_ttl_minutes_uses_config_when_above_minimum(): void
    {
        $this->app['config']->set('modularous.cms_routing.signed_preview.ttl_minutes', 90);

        $generator = new CmsSignedPreviewUrlGenerator;

        $this->assertSame(90, $generator->ttlMinutes());
    }
}
