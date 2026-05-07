<?php

namespace Unusualify\Modularity\Tests\Services\Cms;

use Modules\Cms\Entities\SiteSetting;
use Modules\Cms\Http\Controllers\Front\RobotsTxtController;
use Modules\Cms\Repositories\SiteSettingRepository;
use Modules\Cms\Services\CmsSiteSeoSettingsService;
use Unusualify\Modularity\Tests\TestCase;

class CmsSiteSeoSettingsServiceTest extends TestCase
{
    public function test_resolved_prefers_persisted_value_when_use_site_settings(): void
    {
        config([
            'modularity.cms_seo.robots.use_site_settings' => true,
            'modularity.cms_seo.robots.global_robots_txt' => 'User-agent: *\nAllow: /',
            'modularity.cms_seo.robots.site_setting' => [
                'group_key' => 'seo',
                'key' => 'global_robots_txt',
                'locale' => '*',
            ],
        ]);

        $row = $this->getMockBuilder(SiteSetting::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $row->setRawAttributes([
            'value' => "User-agent: *\nDisallow: /staging",
        ]);

        $repo = $this->createMock(SiteSettingRepository::class);
        $repo->method('findScoped')->with('seo', 'global_robots_txt', '*')->willReturn($row);

        $service = new CmsSiteSeoSettingsService($repo);
        $body = $service->resolvedRobotsTxtBody();

        $this->assertStringContainsString('Disallow: /staging', $body);
        $this->assertStringEndsWith("\n", $body);
    }

    public function test_resolved_body_static_accepts_injected_service(): void
    {
        config([
            'modularity.cms_seo.robots.use_site_settings' => true,
            'modularity.cms_seo.robots.site_setting' => [
                'group_key' => 'seo',
                'key' => 'global_robots_txt',
                'locale' => '*',
            ],
        ]);

        $repo = $this->createMock(SiteSettingRepository::class);
        $repo->method('findScoped')->willReturn(null);

        $service = new CmsSiteSeoSettingsService($repo);
        config(['modularity.cms_seo.robots.global_robots_txt' => 'User-agent: *\nDisallow: /cfg']);

        $body = RobotsTxtController::resolvedBody($service);

        $this->assertStringContainsString('/cfg', $body);
    }
}
