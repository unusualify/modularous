<?php

namespace Unusualify\Modularity\Tests\Services\Cms;

use Modules\Cms\Jobs\ScanCmsPublishWindowBoundariesJob;
use Unusualify\Modularity\Tests\TestCase;

class ScanCmsPublishWindowBoundariesJobTest extends TestCase
{
    public function test_handle_does_not_throw_when_cms_schedule_disabled(): void
    {
        $this->app['config']->set('modularity.cms_schedule.enabled', false);

        (new ScanCmsPublishWindowBoundariesJob)->handle();

        $this->assertTrue(true);
    }
}
