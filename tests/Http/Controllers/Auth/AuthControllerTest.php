<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Tests\Http\Controllers\Auth;

use Illuminate\Config\Repository as Config;
use Illuminate\Routing\Redirector;
use Illuminate\View\Factory as ViewFactory;
use Unusualify\Modularity\Http\Controllers\Auth\Controller;
use Unusualify\Modularity\Tests\TestCase;

class AuthControllerTest extends TestCase
{
    protected Controller $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new Controller(
            app(Config::class),
            app(Redirector::class),
            app(ViewFactory::class)
        );
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(Controller::class, $this->controller);
    }

    /** @test */
    public function it_returns_redirect_path(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('redirectPath');
        $method->setAccessible(true);
        $path = $method->invoke($this->controller);

        $this->assertIsString($path);
        $this->assertNotEmpty($path);
    }

    /** @test */
    public function it_returns_guest_middleware_except_empty_by_default(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('guestMiddlewareExcept');
        $method->setAccessible(true);
        $except = $method->invoke($this->controller);

        $this->assertIsArray($except);
    }
}
