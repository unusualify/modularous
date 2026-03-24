<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Tests\Http\Controllers\Auth;

use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Mockery;
use Unusualify\Modularity\Http\Controllers\Auth\ResetPasswordController;

/**
 * Testable controller that allows overriding getUserFromToken for success-path tests.
 */
class ResetPasswordControllerTestable extends ResetPasswordController
{
    public ?object $stubUser = null;

    protected function getUserFromToken($token)
    {
        return $this->stubUser ?? parent::getUserFromToken($token);
    }
}

class ResetPasswordControllerTest extends AuthTestCase
{
    protected ResetPasswordController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ResetPasswordController;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(ResetPasswordController::class, $this->controller);
    }

    /** @test */
    public function it_uses_password_broker(): void
    {
        $broker = $this->controller->broker();

        $this->assertInstanceOf(PasswordBroker::class, $broker);
    }

    /** @test */
    public function it_returns_success_view(): void
    {
        $response = $this->controller->success();

        $this->assertInstanceOf(View::class, $response);
    }

    /** @test */
    public function it_redirects_when_reset_token_invalid(): void
    {
        $request = Request::create('/password/reset/invalid-token', 'GET');

        $response = $this->controller->showResetForm($request, 'invalid-token');

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /** @test */
    public function it_returns_validation_error_on_reset_with_invalid_data(): void
    {
        $request = Request::create('/password/reset', 'POST', [
            'email' => '',
            'token' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->reset($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    /** @test */
    public function it_redirects_when_welcome_token_invalid(): void
    {
        $request = Request::create('/password/welcome/invalid-token', 'GET');

        $response = $this->controller->showWelcomeForm($request, 'invalid-token');

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /** @test */
    public function it_calls_send_reset_response_when_password_reset_succeeds(): void
    {
        $mockBroker = Mockery::mock(PasswordBroker::class);
        $mockBroker->shouldReceive('reset')
            ->andReturn(Password::PASSWORD_RESET);

        Password::shouldReceive('broker')
            ->andReturn($mockBroker);

        $request = Request::create('/password/reset', 'POST', [
            'email' => 'user@example.com',
            'token' => 'valid-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->reset($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('variant', $data);
        $this->assertArrayHasKey('redirector', $data);
    }

    /** @test */
    public function it_calls_send_reset_failed_response_when_password_reset_fails(): void
    {
        $mockBroker = Mockery::mock(PasswordBroker::class);
        $mockBroker->shouldReceive('reset')
            ->andReturn(Password::INVALID_TOKEN);

        Password::shouldReceive('broker')
            ->andReturn($mockBroker);

        $request = Request::create('/password/reset', 'POST', [
            'email' => 'user@example.com',
            'token' => 'expired-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->reset($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('variant', $data);
    }

    /** @test */
    public function it_returns_redirect_on_reset_success_when_not_requesting_json(): void
    {
        $mockBroker = Mockery::mock(PasswordBroker::class);
        $mockBroker->shouldReceive('reset')
            ->andReturn(Password::PASSWORD_RESET);

        Password::shouldReceive('broker')
            ->andReturn($mockBroker);

        $request = Request::create('/password/reset', 'POST', [
            'email' => 'user@example.com',
            'token' => 'valid-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response = $this->controller->reset($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /** @test */
    public function it_returns_redirect_on_reset_failure_when_not_requesting_json(): void
    {
        $mockBroker = Mockery::mock(PasswordBroker::class);
        $mockBroker->shouldReceive('reset')
            ->andReturn(Password::INVALID_TOKEN);

        Password::shouldReceive('broker')
            ->andReturn($mockBroker);

        $request = Request::create('/password/reset', 'POST', [
            'email' => 'user@example.com',
            'token' => 'expired-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response = $this->controller->reset($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    private function createCanResetPasswordStub(string $email): CanResetPassword
    {
        return new class($email) implements CanResetPassword
        {
            public function __construct(public string $email) {}

            public function getEmailForPasswordReset(): string
            {
                return $this->email;
            }

            public function sendPasswordResetNotification($token): void {}
        };
    }

    /** @test */
    public function it_returns_reset_form_view_when_token_valid(): void
    {
        $stubUser = $this->createCanResetPasswordStub('resetuser@example.com');
        $controller = new ResetPasswordControllerTestable;
        $controller->stubUser = $stubUser;

        $mockRepository = Mockery::mock(DatabaseTokenRepository::class);
        $mockRepository->shouldReceive('exists')
            ->with($stubUser, 'valid-reset-token')
            ->andReturn(true);

        $mockBroker = Mockery::mock(PasswordBroker::class);
        $mockBroker->shouldReceive('getRepository')
            ->andReturn($mockRepository);

        Password::shouldReceive('broker')
            ->with('users')
            ->andReturn($mockBroker);

        $request = Request::create('/password/reset/valid-reset-token', 'GET');

        $response = $controller->showResetForm($request, 'valid-reset-token');

        $this->assertInstanceOf(View::class, $response);
    }

    /** @test */
    public function it_returns_welcome_form_view_when_user_exists_for_token(): void
    {
        $stubUser = $this->createCanResetPasswordStub('welcomeuser@example.com');
        $controller = new ResetPasswordControllerTestable;
        $controller->stubUser = $stubUser;

        $request = Request::create('/password/welcome/valid-welcome-token', 'GET');

        $response = $controller->showWelcomeForm($request, 'valid-welcome-token');

        $this->assertInstanceOf(View::class, $response);
    }

    /** @test */
    public function it_resolves_user_from_hashed_token_via_get_user_from_token(): void
    {
        $plainToken = 'hashed-token-value';
        $email = 'hasheduser@example.com';

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => Hash::make($plainToken),
            'created_at' => now(),
        ]);

        $stubUser = $this->createCanResetPasswordStub($email);
        $controller = new ResetPasswordControllerTestable;
        $controller->stubUser = $stubUser;

        $mockRepository = Mockery::mock(DatabaseTokenRepository::class);
        $mockRepository->shouldReceive('exists')
            ->andReturn(true);

        $mockBroker = Mockery::mock(PasswordBroker::class);
        $mockBroker->shouldReceive('getRepository')
            ->andReturn($mockRepository);

        Password::shouldReceive('broker')
            ->with('users')
            ->andReturn($mockBroker);

        $request = Request::create("/password/reset/{$plainToken}", 'GET');

        $response = $controller->showResetForm($request, $plainToken);

        $this->assertInstanceOf(View::class, $response);
    }
}
