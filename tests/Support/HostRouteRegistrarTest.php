<?php

namespace Unusualify\Modularity\Tests\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Support\HostRouteRegistrar;
use Unusualify\Modularity\Tests\Support\Stubs\HostableStub;
use Unusualify\Modularity\Tests\TestCase;

class HostRouteRegistrarTest extends TestCase
{
    public function test_group_registers_routes_with_options(): void
    {
        $registrar = new HostRouteRegistrar(app(), 'example.com');

        // host() must be called first to set options; use reflection to set options for group test
        $reflection = new \ReflectionClass($registrar);
        $optionsProp = $reflection->getProperty('options');
        $optionsProp->setAccessible(true);
        $optionsProp->setValue($registrar, [
            'domain' => 'example.com',
            'prefix' => '',
            'middleware' => ['hostable'],
        ]);

        Route::shouldReceive('group')->once()->with(
            \Mockery::on(fn ($opts) => isset($opts['domain']) && $opts['domain'] === 'example.com'),
            \Mockery::type('Closure')
        );

        $registrar->group(function () {
            // callback
        });
    }

    public function test_host_sets_model_and_options(): void
    {
        Schema::shouldReceive('hasTable')->andReturn(true);
        $stub = new HostableStub;
        App::bind(HostableStub::class, fn () => $stub);

        $registrar = new HostRouteRegistrar(app(), 'example.com');
        $result = $registrar->host(HostableStub::class);

        $this->assertSame($registrar, $result);
    }

    public function test_get_route_arguments_returns_parameters_when_no_host_model(): void
    {
        $route = \Mockery::mock(\Illuminate\Routing\Route::class);
        $route->shouldReceive('parameters')->andReturn(['id' => 1]);

        $request = \Illuminate\Http\Request::create('http://example.com/test');
        $request->setRouteResolver(fn () => $route);

        $this->app->instance('request', $request);

        $registrar = new HostRouteRegistrar($this->app, 'example.com');
        $args = $registrar->getRouteArguments();

        $this->assertIsArray($args);
        $this->assertArrayHasKey('id', $args);
        $this->assertSame(1, $args['id']);
    }

    public function test_get_route_parameters_returns_route_parameters(): void
    {
        $route = \Mockery::mock(\Illuminate\Routing\Route::class);
        $route->shouldReceive('parameters')->andReturn(['item' => 5]);

        $request = \Illuminate\Http\Request::create('http://example.com/test');
        $request->setRouteResolver(fn () => $route);

        $this->app->instance('request', $request);

        $registrar = new HostRouteRegistrar($this->app, 'example.com');
        $params = $registrar->getRouteParameters();

        $this->assertIsArray($params);
        $this->assertSame(['item' => 5], $params);
    }

    public function test_middleware_attribute_sets_options(): void
    {
        $registrar = new HostRouteRegistrar(app(), 'example.com');

        Schema::shouldReceive('hasTable')->andReturn(false);
        $registrar->host(HostableStub::class);

        $result = $registrar->middleware(['web']);

        $this->assertSame($registrar, $result);
    }

    public function test_name_attribute_sets_options(): void
    {
        $registrar = new HostRouteRegistrar(app(), 'example.com');

        Schema::shouldReceive('hasTable')->andReturn(false);
        $registrar->host(HostableStub::class);

        $result = $registrar->name('test');

        $this->assertSame($registrar, $result);
    }

    public function test_call_throws_for_undefined_method(): void
    {
        $registrar = new HostRouteRegistrar(app(), 'example.com');

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('does not exists');

        $registrar->nonexistentMethod();
    }
}
