<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Tests\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Mockery;
use Unusualify\Modularity\Http\Controllers\Auth\ForgotPasswordController;

class ForgotPasswordControllerTest extends AuthTestCase
{
    protected ForgotPasswordController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ForgotPasswordController();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(ForgotPasswordController::class, $this->controller);
    }

    /** @test */
    public function it_returns_forgot_password_form_view(): void
    {
        $response = $this->controller->showLinkRequestForm();

        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $response);
    }

    /** @test */
    public function it_uses_password_broker(): void
    {
        $broker = $this->controller->broker();

        $this->assertInstanceOf(\Illuminate\Contracts\Auth\PasswordBroker::class, $broker);
    }

    /** @test */
    public function it_returns_success_response_when_reset_link_sent(): void
    {
        $mockBroker = Mockery::mock(\Illuminate\Contracts\Auth\PasswordBroker::class);
        $mockBroker->shouldReceive('sendResetLink')
            ->andReturn(Password::RESET_LINK_SENT);

        Password::shouldReceive('broker')
            ->andReturn($mockBroker);

        $request = Request::create('/password/email', 'POST', [
            'email' => 'user@example.com',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->sendResetLinkEmail($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('variant', $data);
    }

    /** @test */
    public function it_returns_failed_response_when_reset_link_fails(): void
    {
        $mockBroker = Mockery::mock(\Illuminate\Contracts\Auth\PasswordBroker::class);
        $mockBroker->shouldReceive('sendResetLink')
            ->andReturn(Password::INVALID_USER);

        Password::shouldReceive('broker')
            ->andReturn($mockBroker);

        $request = Request::create('/password/email', 'POST', [
            'email' => 'nonexistent@example.com',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->sendResetLinkEmail($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('variant', $data);
    }

    /** @test */
    public function it_returns_redirect_on_success_when_not_requesting_json(): void
    {
        $mockBroker = Mockery::mock(\Illuminate\Contracts\Auth\PasswordBroker::class);
        $mockBroker->shouldReceive('sendResetLink')
            ->andReturn(Password::RESET_LINK_SENT);

        Password::shouldReceive('broker')
            ->andReturn($mockBroker);

        $request = Request::create('/password/email', 'POST', [
            'email' => 'user@example.com',
        ]);

        $response = $this->controller->sendResetLinkEmail($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    /** @test */
    public function it_returns_redirect_on_failure_when_not_requesting_json(): void
    {
        $mockBroker = Mockery::mock(\Illuminate\Contracts\Auth\PasswordBroker::class);
        $mockBroker->shouldReceive('sendResetLink')
            ->andReturn(Password::INVALID_USER);

        Password::shouldReceive('broker')
            ->andReturn($mockBroker);

        $request = Request::create('/password/email', 'POST', [
            'email' => 'nonexistent@example.com',
        ]);

        $response = $this->controller->sendResetLinkEmail($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }
}
