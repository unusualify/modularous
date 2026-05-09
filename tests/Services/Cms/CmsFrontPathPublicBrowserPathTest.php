<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Support\CmsFrontPath;
use Unusualify\Modularous\Tests\TestCase;

class CmsFrontPathPublicBrowserPathTest extends TestCase
{
    public function test_public_browser_path_includes_front_prefix_and_locale_when_not_hidden(): void
    {
        $this->app['config']->set('modularous.cms_routing.front_route_prefix', 'cms');
        $this->app['config']->set('modularous.cms_routing.default_locale', 'en');
        $this->app['config']->set('modularous.cms_routing.hide_default_locale_segment', false);

        $canonical = new CanonicalUrlResolver;
        $path = CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath('en', '/blog/post', $canonical);

        $this->assertSame('/cms/en/blog/post', $path);
    }

    public function test_public_browser_path_hides_default_locale_segment_when_configured(): void
    {
        $this->app['config']->set('modularous.cms_routing.front_route_prefix', 'cms');
        $this->app['config']->set('modularous.cms_routing.default_locale', 'en');
        $this->app['config']->set('modularous.cms_routing.hide_default_locale_segment', true);

        $canonical = new CanonicalUrlResolver;
        $path = CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath('en', '/blog/post', $canonical);

        $this->assertSame('/cms/blog/post', $path);
    }

    public function test_public_browser_path_keeps_non_default_locale_when_default_hidden(): void
    {
        $this->app['config']->set('modularous.cms_routing.front_route_prefix', 'cms');
        $this->app['config']->set('modularous.cms_routing.default_locale', 'en');
        $this->app['config']->set('modularous.cms_routing.hide_default_locale_segment', true);

        $canonical = new CanonicalUrlResolver;
        $path = CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath('tr', '/haber/foo', $canonical);

        $this->assertSame('/cms/tr/haber/foo', $path);
    }

    public function test_public_browser_path_omits_slugless_fallback_locale_segment_when_toggle_on(): void
    {
        $this->app['config']->set('modularous.cms_routing.front_route_prefix', 'cms');
        $this->app['config']->set('modularous.cms_routing.default_locale', 'tr');
        $this->app['config']->set('modularous.cms_routing.hide_default_locale_segment', false);
        $this->app['config']->set('modularous.cms_routing.fallback_locale_optional_path_segment', true);
        $this->app['config']->set('modularous.cms_routing.fallback_locale_optional_path_segment_locale', 'en');

        $canonical = new CanonicalUrlResolver;
        $path = CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath('en', '/pages/test', $canonical);

        $this->assertSame('/cms/pages/test', $path);

        $pathTr = CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath('tr', '/sayfa/x', $canonical);

        $this->assertSame('/cms/tr/sayfa/x', $pathTr);
    }
}
