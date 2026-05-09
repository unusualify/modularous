<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsParentSegmentResolver;
use Modules\Cms\Services\CmsSlugInputValidationService;
use ReflectionMethod;
use Unusualify\Modularous\Tests\TestCase;

class CmsSlugPathSegmentPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->app['config']->set('modularous.cms_routing.admin.slug_max_path_segments', null);

        parent::tearDown();
    }

    public function test_slug_path_segment_policy_respects_max_one(): void
    {
        $this->app['config']->set('modularous.cms_routing.admin.slug_max_path_segments', 1);

        $resolver = new CanonicalUrlResolver;
        $service = new CmsSlugInputValidationService(new CmsParentSegmentResolver($resolver));
        $invoke = $this->invokePolicyFailure($service);

        $this->assertNull($invoke('foo'));
        $this->assertNull($invoke(''));
        $this->assertNotNull($invoke('foo/bar'));
        $this->assertNotNull($invoke('foo\\bar'));
    }

    public function test_slug_path_segment_policy_allows_two_when_max_two(): void
    {
        $this->app['config']->set('modularous.cms_routing.admin.slug_max_path_segments', 2);

        $resolver = new CanonicalUrlResolver;
        $service = new CmsSlugInputValidationService(new CmsParentSegmentResolver($resolver));
        $invoke = $this->invokePolicyFailure($service);

        $this->assertNull($invoke('a/b'));
        $this->assertNotNull($invoke('a/b/c'));
    }

    public function test_slug_path_segment_policy_unlimited_when_null(): void
    {
        $this->app['config']->set('modularous.cms_routing.admin.slug_max_path_segments', null);

        $resolver = new CanonicalUrlResolver;
        $service = new CmsSlugInputValidationService(new CmsParentSegmentResolver($resolver));
        $invoke = $this->invokePolicyFailure($service);

        $this->assertNull($invoke('a/b/c/d'));
    }

    /**
     * @return callable(string): ?string
     */
    private function invokePolicyFailure(CmsSlugInputValidationService $service): callable
    {
        $method = new ReflectionMethod(CmsSlugInputValidationService::class, 'slugPathSegmentPolicyFailure');
        $method->setAccessible(true);

        return static fn (string $raw): ?string => $method->invoke($service, $raw);
    }
}
