<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Jobs\ScanCmsPublishWindowBoundariesJob;
use Unusualify\Modularous\Tests\TestCase;

class ScanCmsPublishWindowBoundariesJobTest extends TestCase
{
    public function test_handle_does_not_throw_when_cms_schedule_disabled(): void
    {
        $this->app['config']->set('modularous.cms_schedule.enabled', false);

        (new ScanCmsPublishWindowBoundariesJob)->handle();

        $this->assertTrue(true);
    }
}
