<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Http\Controllers\Front\RobotsTxtController;
use Unusualify\Modularous\Tests\TestCase;

class RobotsTxtControllerTest extends TestCase
{
    public function test_resolved_body_uses_config_and_trailing_newline(): void
    {
        config([
            'modularous.cms_seo.robots.global_robots_txt' => "User-agent: *\nDisallow: /private",
        ]);

        $body = RobotsTxtController::resolvedBody();

        $this->assertStringEndsWith("\n", $body);
        $this->assertStringContainsString('Disallow: /private', $body);
    }

    public function test_resolved_body_falls_back_when_empty_config(): void
    {
        config(['modularous.cms_seo.robots.global_robots_txt' => '   ']);

        $body = RobotsTxtController::resolvedBody();

        $this->assertStringContainsString('User-agent', $body);
    }
}
