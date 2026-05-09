<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Routing\CmsFrontRouteLocalizationBinding;
use Unusualify\Modularous\Tests\TestCase;

class CmsFrontRouteLocalizationBindingTest extends TestCase
{
    public function test_implode_locales_as_regex_alternation_orders_longest_first(): void
    {
        $pattern = CmsFrontRouteLocalizationBinding::implodeLocalesAsRegexAlternation(['pt', 'pt-br', 'en']);

        $this->assertStringContainsString('pt\-br', $pattern);
        // Longest key first so `pt-br` is not shadowed by `pt` in alternation matching.
        $this->assertStringStartsWith('pt\-br|', $pattern);
    }

    public function test_should_use_locale_prefix_route_group_is_false_when_mode_is_catch_all(): void
    {
        $this->app['config']->set('modularous.cms_routing.public_front_route_group_mode', 'catch_all');

        $this->assertFalse(CmsFrontRouteLocalizationBinding::shouldUseLocalePrefixRouteGroup());
    }

    public function test_should_use_locale_prefix_route_group_is_false_when_driver_is_translatable(): void
    {
        $this->app['config']->set('modularous.cms_routing.public_front_route_group_mode', 'locale_param');
        $this->app['config']->set('modularous.cms_routing.localization_driver', 'translatable');

        $this->assertFalse(CmsFrontRouteLocalizationBinding::shouldUseLocalePrefixRouteGroup());
    }
}
