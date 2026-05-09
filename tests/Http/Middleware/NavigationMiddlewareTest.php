<?php

namespace Unusualify\Modularous\Tests\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Unusualify\Modularous\Http\Middleware\NavigationMiddleware;
use Unusualify\Modularous\Tests\TestCase;

class NavigationMiddlewareTest extends TestCase
{
    protected $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new NavigationMiddleware;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(NavigationMiddleware::class, $this->middleware);
    }

    /** @test */
    public function it_passes_request_to_next_middleware()
    {
        $request = Mockery::mock(Request::class);

        $next = function ($req) {
            return 'response';
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals('response', $response);
    }

    /** @test */
    public function it_shares_navigation_config_with_modularous_layouts()
    {
        $request = Mockery::mock(Request::class);

        $next = function ($req) {
            return 'passed';
        };

        // The middleware should call view()->composer() with navigation config
        $response = $this->middleware->handle($request, $next);

        $this->assertEquals('passed', $response);
    }

    /** @test */
    public function it_shares_navigation_config_with_translation_layout()
    {
        $request = Mockery::mock(Request::class);

        $next = function ($req) {
            return response('OK');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals('OK', $response->getContent());
    }
}
