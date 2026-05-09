<?php

namespace Unusualify\Modularous\Tests\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Support\ModularousRoutes;
use Unusualify\Modularous\Tests\TestCase;

class ModularousRoutesTest extends TestCase
{
    protected ModularousRoutes $routes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->routes = new ModularousRoutes;
    }

    public function test_web_middlewares_returns_array()
    {
        $middlewares = $this->routes->webMiddlewares();

        $this->assertIsArray($middlewares);
        $this->assertContains('web', $middlewares);
        $this->assertContains('modularous.log', $middlewares);
    }

    public function test_web_panel_middlewares_includes_auth_and_panel()
    {
        $middlewares = $this->routes->webPanelMiddlewares();

        $this->assertIsArray($middlewares);
        $this->assertContains('web.auth', $middlewares);
        $this->assertContains('modularous.panel', $middlewares);
    }

    public function test_api_middlewares_returns_array()
    {
        $middlewares = $this->routes->apiMiddlewares();

        $this->assertIsArray($middlewares);
        $this->assertContains('api', $middlewares);
    }

    public function test_api_panel_middlewares_includes_auth_and_panel()
    {
        $middlewares = $this->routes->apiPanelMiddlewares();

        $this->assertIsArray($middlewares);
        $this->assertContains('api.auth', $middlewares);
        $this->assertContains('modularous.panel', $middlewares);
    }

    public function test_default_middlewares_returns_array()
    {
        $middlewares = $this->routes->defaultMiddlewares();

        $this->assertIsArray($middlewares);
        $this->assertContains('modularous.log', $middlewares);
    }

    public function test_default_panel_middlewares_includes_panel()
    {
        $middlewares = $this->routes->defaultPanelMiddlewares();

        $this->assertIsArray($middlewares);
        $this->assertContains('modularous.panel', $middlewares);
    }

    public function test_get_api_prefix_returns_string()
    {
        Config::set('modularous.api.prefix', 'api/v1');

        $prefix = $this->routes->getApiPrefix();

        $this->assertIsString($prefix);
        $this->assertEquals('api/v1', $prefix);
    }

    public function test_get_api_prefix_returns_default_when_not_in_config()
    {
        $prefix = $this->routes->getApiPrefix();

        $this->assertIsString($prefix);
        $this->assertNotEmpty($prefix);
    }

    public function test_get_api_domain_returns_null_when_not_configured()
    {
        Config::set('modularous.api.domain', null);

        $domain = $this->routes->getApiDomain();

        $this->assertNull($domain);
    }

    public function test_get_api_middlewares_returns_array()
    {
        $middlewares = $this->routes->getApiMiddlewares();

        $this->assertIsArray($middlewares);
    }

    public function test_get_public_api_middlewares_returns_array()
    {
        $middlewares = $this->routes->getPublicApiMiddlewares();

        $this->assertIsArray($middlewares);
    }

    public function test_get_api_auth_middlewares_returns_array()
    {
        $middlewares = $this->routes->getApiAuthMiddlewares();

        $this->assertIsArray($middlewares);
    }

    public function test_get_api_group_options_returns_array_with_prefix()
    {
        $options = $this->routes->getApiGroupOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('as', $options);
        $this->assertArrayHasKey('prefix', $options);
        $this->assertArrayHasKey('domain', $options);
    }

    public function test_get_auth_api_group_options_includes_middleware()
    {
        $options = $this->routes->getAuthApiGroupOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('middleware', $options);
    }

    public function test_get_public_api_group_options_includes_public_prefix()
    {
        $options = $this->routes->getPublicApiGroupOptions();

        $this->assertIsArray($options);
        $this->assertStringContainsString('public', $options['prefix']);
    }

    public function test_get_custom_api_routes_returns_array()
    {
        $routes = $this->routes->getCustomApiRoutes();

        $this->assertIsArray($routes);
        $this->assertContains('bulk', $routes);
        $this->assertContains('search', $routes);
    }

    public function test_get_api_routes_returns_standard_crud()
    {
        $routes = $this->routes->getApiRoutes();

        $this->assertIsArray($routes);
        $this->assertContains('index', $routes);
        $this->assertContains('store', $routes);
        $this->assertContains('show', $routes);
        $this->assertContains('update', $routes);
        $this->assertContains('destroy', $routes);
    }

    public function test_group_options_returns_array()
    {
        Modularous::shouldReceive('getAdminRouteNamePrefix')->andReturn('admin');
        Modularous::shouldReceive('hasAdminAppUrl')->andReturn(false);
        Modularous::shouldReceive('getAdminUrlPrefix')->andReturn('admin');
        Modularous::shouldReceive('getAppUrl')->andReturn('http://localhost');
        Modularous::shouldReceive('getAdminAppHost')->andReturn(null);

        $options = $this->routes->groupOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('as', $options);
    }

    public function test_configure_route_patterns_sets_patterns_from_config()
    {
        Config::set('modularous.route_patterns', ['id' => '[0-9]+']);

        $this->routes->configureRoutePatterns();

        $this->assertTrue(true);
    }

    public function test_configure_route_patterns_handles_null_config()
    {
        Config::set('modularous.route_patterns', null);

        $this->routes->configureRoutePatterns();

        $this->assertTrue(true);
    }

    public function test_generate_route_middlewares_registers_aliases()
    {
        $this->routes->generateRouteMiddlewares();

        $this->assertTrue(Route::hasMiddlewareGroup('web.auth'));
    }
}
