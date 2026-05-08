<?php

namespace Unusualify\Modularity\Tests\Services\Cms;

use Carbon\Carbon;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Entities\Page;
use Modules\Cms\Services\CmsAdminWarnings;
use Modules\Cms\Services\CmsParentSegmentResolver;
use Modules\Cms\Services\CmsUrlRouteRegistry;
use ReflectionClass;
use Unusualify\Modularity\Tests\TestCase;

class CmsAdminWarningsTest extends TestCase
{
    private function makeWarningsService(): CmsAdminWarnings
    {
        $canonical = $this->createMock(CanonicalUrlResolverInterface::class);

        $parent = new CmsParentSegmentResolver($canonical);

        return new CmsAdminWarnings(new CmsUrlRouteRegistry($canonical, $parent));
    }

    /**
     * Avoid {@see Page} constructor / activity boot in testbench (no auth causer).
     */
    private function makePageWithAttributes(array $attributes): Page
    {
        $ref = new ReflectionClass(Page::class);
        /** @var Page $page */
        $page = $ref->newInstanceWithoutConstructor();
        $page->setRawAttributes($attributes);
        $page->syncOriginal();

        return $page;
    }

    public function test_gather_includes_schedule_warning_before_start(): void
    {
        config([
            'modularity.cms_seo.admin.publish_schedule_warnings' => true,
            'modularity.cms_seo.admin.publish_soft_warnings' => false,
        ]);

        $service = $this->makeWarningsService();

        $page = $this->makePageWithAttributes([
            'published' => true,
            'publish_start_date' => Carbon::now()->addDay()->toDateTimeString(),
            'publish_end_date' => null,
        ]);

        $warnings = $service->gather($page);

        $this->assertNotEmpty($warnings);
        $this->assertTrue(
            str_contains($warnings[0], 'Not publicly visible yet') ||
            str_contains(implode(' ', $warnings), 'Not publicly visible yet')
        );
    }

    public function test_gather_includes_schedule_warning_after_end(): void
    {
        config([
            'modularity.cms_seo.admin.publish_schedule_warnings' => true,
            'modularity.cms_seo.admin.publish_soft_warnings' => false,
        ]);

        $service = $this->makeWarningsService();

        $page = $this->makePageWithAttributes([
            'published' => true,
            'publish_start_date' => null,
            'publish_end_date' => Carbon::now()->subDay()->toDateTimeString(),
        ]);

        $warnings = $service->gather($page);

        $flat = implode(' ', $warnings);
        $this->assertStringContainsString('Not publicly visible anymore', $flat);
    }

    public function test_schedule_warnings_disabled_emits_none_for_schedule_only(): void
    {
        config([
            'modularity.cms_seo.admin.publish_schedule_warnings' => false,
            'modularity.cms_seo.admin.publish_soft_warnings' => false,
        ]);

        $service = $this->makeWarningsService();

        $page = $this->makePageWithAttributes([
            'published' => true,
            'publish_start_date' => Carbon::now()->addDay()->toDateTimeString(),
        ]);

        $warnings = $service->gather($page);

        $this->assertSame([], $warnings);
    }
}
