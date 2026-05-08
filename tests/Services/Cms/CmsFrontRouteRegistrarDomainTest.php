<?php

namespace Unusualify\Modularity\Tests\Services\Cms;

use Modules\Cms\Routing\CmsFrontRouteRegistrar;
use Unusualify\Modularity\Tests\TestCase;

class CmsFrontRouteRegistrarDomainTest extends TestCase
{
    public function test_resolve_public_front_route_domain_defaults_to_app_url_host(): void
    {
        $this->app['config']->set('app.url', 'http://frontend.b2press.test');
        $this->app['config']->set('modularity.cms_routing.public_front_route_domain', null);
        $this->app['config']->set('modularity.cms_routing.public_front_routes_allow_any_host', false);
        $this->app['config']->set('modularity.cms_routing.bind_public_routes_to_app_url_host', null);

        $this->assertSame('frontend.b2press.test', CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain());
    }

    public function test_resolve_public_front_route_domain_legacy_bind_false_maps_to_allow_any_host(): void
    {
        $this->app['config']->set('app.url', 'http://frontend.b2press.test');
        $this->app['config']->set('modularity.cms_routing.public_front_route_domain', null);
        $this->app['config']->set('modularity.cms_routing.public_front_routes_allow_any_host', false);
        $this->app['config']->set('modularity.cms_routing.bind_public_routes_to_app_url_host', false);

        $this->assertNull(CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain());
    }

    public function test_resolve_public_front_route_domain_legacy_bind_true_uses_app_url_host(): void
    {
        $this->app['config']->set('app.url', 'http://frontend.b2press.test');
        $this->app['config']->set('modularity.cms_routing.public_front_route_domain', null);
        $this->app['config']->set('modularity.cms_routing.public_front_routes_allow_any_host', false);
        $this->app['config']->set('modularity.cms_routing.bind_public_routes_to_app_url_host', true);

        $this->assertSame('frontend.b2press.test', CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain());
    }

    public function test_resolve_public_front_route_domain_allow_any_host_true_overrides_explicit_bind(): void
    {
        $this->app['config']->set('app.url', 'http://frontend.b2press.test');
        $this->app['config']->set('modularity.cms_routing.public_front_route_domain', null);
        $this->app['config']->set('modularity.cms_routing.public_front_routes_allow_any_host', true);
        $this->app['config']->set('modularity.cms_routing.bind_public_routes_to_app_url_host', true);

        $this->assertNull(CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain());
    }

    public function test_resolve_public_front_route_domain_respects_explicit_config(): void
    {
        $this->app['config']->set('app.url', 'http://ignored.example.test');
        $this->app['config']->set('modularity.cms_routing.public_front_route_domain', 'cms.example.test');

        $this->assertSame('cms.example.test', CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain());
    }

    public function test_resolve_public_front_route_domain_returns_null_when_app_url_has_no_host(): void
    {
        $this->app['config']->set('app.url', '');
        $this->app['config']->set('modularity.cms_routing.public_front_route_domain', null);
        $this->app['config']->set('modularity.cms_routing.public_front_routes_allow_any_host', false);
        $this->app['config']->set('modularity.cms_routing.bind_public_routes_to_app_url_host', null);

        $this->assertNull(CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain());
    }
}
