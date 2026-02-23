<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Tests\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Unusualify\Modularity\Facades\Register;
use Unusualify\Modularity\Brokers\RegisterBroker;
use Unusualify\Modularity\Http\Controllers\Auth\CompleteRegisterController;

class CompleteRegisterControllerTest extends AuthTestCase
{
    protected CompleteRegisterController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new CompleteRegisterController();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(CompleteRegisterController::class, $this->controller);
    }

    /** @test */
    public function it_returns_broker(): void
    {
        $broker = $this->controller->broker();

        $this->assertInstanceOf(RegisterBroker::class, $broker);
    }

    /** @test */
    public function it_returns_guard(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('guard');
        $method->setAccessible(true);
        $guard = $method->invoke($this->controller);

        $this->assertInstanceOf(\Illuminate\Contracts\Auth\Guard::class, $guard);
    }

    /** @test */
    public function it_redirects_when_token_invalid(): void
    {
        $request = Request::create('/complete/register/invalid-token', 'GET', [
            'email' => 'test@example.com',
        ]);

        $route = new Route('GET', 'complete/register/{token}', []);
        $route->bind($request);
        $route->setParameter('token', 'invalid-token');
        $request->setRouteResolver(fn () => $route);

        $response = $this->controller->showCompleteRegisterForm($request, 'invalid-token');

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    /** @test */
    public function it_returns_register_form_when_token_valid(): void
    {
        $plainToken = 'valid-token-123';
        $hashedToken = Hash::make($plainToken);

        DB::table('register_verified_users')->insert([
            'email' => 'valid@example.com',
            'token' => $hashedToken,
            'created_at' => now(),
        ]);

        $request = Request::create('/complete/register/' . $plainToken, 'GET', [
            'email' => 'valid@example.com',
        ]);

        $route = new Route('GET', 'complete/register/{token}', []);
        $route->bind($request);
        $route->setParameter('token', $plainToken);
        $request->setRouteResolver(fn () => $route);

        $response = $this->controller->showCompleteRegisterForm($request, $plainToken);

        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $response);
    }

    /** @test */
    public function it_returns_validation_error_on_complete_register_with_invalid_data(): void
    {
        $request = Request::create('/complete/register', 'POST', [
            'email' => '',
            'token' => '',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->completeRegister($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    /** @test */
    public function it_returns_success_response_on_complete_register(): void
    {
        $mockBroker = Mockery::mock(RegisterBroker::class)->makePartial();
        $mockBroker->shouldReceive('register')
            ->andReturn(Register::VERIFIED_EMAIL_REGISTER);

        Register::shouldReceive('broker')
            ->andReturn($mockBroker);

        $request = Request::create('/complete/register', 'POST', [
            'email' => 'newuser@example.com',
            'token' => 'valid-token',
            'name' => 'John',
            'surname' => 'Doe',
            'company' => 'Test Co',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->completeRegister($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('redirector', $data);
    }

    /** @test */
    public function it_returns_failed_response_on_complete_register(): void
    {
        $mockBroker = Mockery::mock(RegisterBroker::class)->makePartial();
        $mockBroker->shouldReceive('register')
            ->andReturn(Register::INVALID_VERIFICATION_TOKEN);

        Register::shouldReceive('broker')
            ->andReturn($mockBroker);

        $request = Request::create('/complete/register', 'POST', [
            'email' => 'existing@example.com',
            'token' => 'invalid-token',
            'name' => 'John',
            'surname' => 'Doe',
            'company' => 'Test Co',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->completeRegister($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('email', $data);
    }
}
