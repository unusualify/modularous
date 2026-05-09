<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Auth;

use Illuminate\Auth\AuthManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\View\View;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Factory as ViewFactory;
use Unusualify\Modularous\Http\Controllers\Auth\LoginController;

class LoginControllerTest extends AuthTestCase
{
    protected LoginController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new LoginController(
            app(Config::class),
            app(AuthManager::class),
            app(Encrypter::class),
            app(Redirector::class),
            app(ViewFactory::class)
        );
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(LoginController::class, $this->controller);
    }

    /** @test */
    public function it_returns_login_form_view(): void
    {
        $response = $this->controller->showForm();

        $this->assertInstanceOf(View::class, $response);
    }

    /** @test */
    public function it_excludes_logout_from_guest_middleware(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('guestMiddlewareExcept');
        $method->setAccessible(true);
        $except = $method->invoke($this->controller);

        $this->assertContains('logout', $except);
    }

    /** @test */
    public function it_returns_logout_redirect(): void
    {
        $request = Request::create('/logout', 'POST');
        $request->setLaravelSession(app('session.store'));

        $response = $this->controller->logout($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /** @test */
    public function it_returns_login_2fa_form_view(): void
    {
        $response = $this->controller->showLogin2FaForm();

        $this->assertInstanceOf(View::class, $response);
    }

    /** @test */
    public function it_returns_redirect_to_dashboard(): void
    {
        $url = $this->controller->redirectTo();

        $this->assertIsString($url);
        $this->assertNotEmpty($url);
    }

    /** @test */
    public function it_returns_json_on_failed_login_when_requesting_json(): void
    {
        $request = Request::create('/login', 'POST', [
            'email' => 'invalid@example.com',
            'password' => 'wrong-password',
        ]);
        $request->headers->set('Accept', 'application/json');

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('sendFailedLoginResponse');
        $method->setAccessible(true);

        $response = $method->invoke($this->controller, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('email', $data['errors']);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('variant', $data);
    }

    /** @test */
    public function it_throws_validation_exception_on_failed_login_when_not_requesting_json(): void
    {
        $this->expectException(ValidationException::class);

        $request = Request::create('/login', 'POST', [
            'email' => 'invalid@example.com',
            'password' => 'wrong-password',
        ]);

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('sendFailedLoginResponse');
        $method->setAccessible(true);

        $method->invoke($this->controller, $request);
    }

    /** @test */
    public function it_returns_json_response_when_authenticated_without_2fa(): void
    {
        $user = (object) [
            'id' => 1,
            'google_2fa_secret' => null,
            'google_2fa_enabled' => false,
        ];

        $request = Request::create('/login', 'POST', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);
        $request->headers->set('Accept', 'application/json');

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('authenticated');
        $method->setAccessible(true);

        $response = $method->invoke($this->controller, $request, $user);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('variant', $data);
    }

    /** @test */
    public function it_redirects_to_2fa_form_when_user_has_2fa_enabled(): void
    {
        config()->set('modularous.security.mfa.enabled', true);
        config()->set('modularous.security.mfa.provider', 'google_totp');

        $user = (object) [
            'id' => 1,
            'google_2fa_secret' => 'secret',
            'google_2fa_enabled' => true,
        ];

        $request = Request::create('/login', 'POST', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);
        $request->setLaravelSession(app('session.store'));

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('authenticated');
        $method->setAccessible(true);

        $response = $method->invoke($this->controller, $request, $user);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /** @test */
    public function it_returns_json_with_redirector_when_authenticated_with_2fa_and_requesting_json(): void
    {
        config()->set('modularous.security.mfa.enabled', true);
        config()->set('modularous.security.mfa.provider', 'google_totp');

        $user = (object) [
            'id' => 1,
            'google_2fa_secret' => 'secret',
            'google_2fa_enabled' => true,
        ];

        $request = Request::create('/login', 'POST', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);
        $request->headers->set('Accept', 'application/json');
        $request->setLaravelSession(app('session.store'));

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('authenticated');
        $method->setAccessible(true);

        $response = $method->invoke($this->controller, $request, $user);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('redirector', $data);
    }
}
