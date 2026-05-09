<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsParentSegmentResolver;
use Modules\Cms\Services\CmsUrlRouteRegistry;
use Modules\Cms\Services\RedirectValidationService;
use Unusualify\Modularous\Tests\TestCase;

class RedirectValidationServiceTest extends TestCase
{
    private function makeService(): RedirectValidationService
    {
        $resolver = new CanonicalUrlResolver;

        $parent = new CmsParentSegmentResolver($resolver);

        return new RedirectValidationService($resolver, new CmsUrlRouteRegistry($resolver, $parent));
    }

    public function test_it_rejects_self_and_looping_redirects(): void
    {
        $service = $this->makeService();

        $self = $service->validate('/about', '/about');
        $loop = $service->validate('/a', '/b', [
            'existing_redirects' => [
                '/b' => '/a',
            ],
        ]);

        $this->assertFalse($self['valid']);
        $this->assertFalse($loop['valid']);
    }

    public function test_it_blocks_redirect_conflicting_with_active_page(): void
    {
        $service = $this->makeService();

        $validation = $service->validate('/home', '/landing', [
            'active_paths' => ['/home'],
        ]);

        $this->assertFalse($validation['valid']);
    }

    public function test_it_returns_warnings_for_cross_locale_redirect_without_failing(): void
    {
        config(['translatable.locales' => ['en', 'tr']]);

        $service = $this->makeService();

        $validation = $service->validate('/en/source', '/tr/target', []);

        $this->assertTrue($validation['valid']);
        $this->assertNotEmpty($validation['warnings']);
    }
}
