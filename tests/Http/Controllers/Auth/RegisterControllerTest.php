<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Tests\Http\Controllers\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Oobook\Database\Eloquent\ManageEloquentServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Controllers\Auth\RegisterController;

class RegisterControllerTest extends AuthTestCase
{
    use RefreshDatabase;

    protected RegisterController $controller;

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            ManageEloquentServiceProvider::class,
            ActivitylogServiceProvider::class,
        ]);
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('modularity.email_verified_register', false);
        $app['config']->set('activitylog', [
            'enabled' => false,
            'delete_records_older_than_days' => 365,
            'default_log_name' => 'default',
            'default_auth_driver' => null,
            'subject_returns_soft_deleted_models' => false,
            'activity_model' => Activity::class,
            'table_name' => 'sp_activity_logs',
            'database_connection' => 'testdb',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new RegisterController;
        Role::firstOrCreate(
            ['name' => 'client-manager', 'guard_name' => Modularity::getAuthGuardName()],
            ['name' => 'client-manager', 'guard_name' => Modularity::getAuthGuardName()]
        );
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(RegisterController::class, $this->controller);
    }

    /** @test */
    public function it_returns_register_form_view_when_email_verified_register_disabled(): void
    {
        $response = $this->controller->showForm();

        $this->assertInstanceOf(View::class, $response);
    }

    /** @test */
    public function it_redirects_to_email_form_when_email_verified_register_enabled(): void
    {
        config(['modularity.email_verified_register' => true]);
        $controller = new RegisterController;

        $response = $controller->showForm();

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /** @test */
    public function it_returns_success_view(): void
    {
        $response = $this->controller->success();

        $this->assertInstanceOf(View::class, $response);
    }

    /** @test */
    public function it_returns_validation_rules(): void
    {
        $rules = $this->controller->rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('surname', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
    }

    /** @test */
    public function it_returns_validator_instance(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('validator');
        $method->setAccessible(true);

        $validator = $method->invoke($this->controller, [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertInstanceOf(Validator::class, $validator);
    }

    /** @test */
    public function it_returns_json_with_restricted_message_when_email_verified_register_enabled_and_requesting_json(): void
    {
        config(['modularity.email_verified_register' => true]);
        $controller = new RegisterController;

        $request = Request::create('/register', 'POST', []);
        $request->headers->set('Accept', 'application/json');

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('register');
        $method->setAccessible(true);

        $response = $method->invoke($controller, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Restricted Registration', $data['message']);
    }

    /** @test */
    public function it_redirects_when_email_verified_register_enabled_and_not_requesting_json(): void
    {
        config(['modularity.email_verified_register' => true]);
        $controller = new RegisterController;

        $request = Request::create('/register', 'POST', []);

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('register');
        $method->setAccessible(true);

        $response = $method->invoke($controller, $request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /** @test */
    public function it_returns_json_with_validation_errors_when_validation_fails_and_requesting_json(): void
    {
        $request = Request::create('/register', 'POST', [
            'name' => '',
            'surname' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
        $request->headers->set('Accept', 'application/json');

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('register');
        $method->setAccessible(true);

        $response = $method->invoke($this->controller, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    /** @test */
    public function it_returns_json_with_success_when_registration_succeeds_and_requesting_json(): void
    {
        $request = Request::create('/register', 'POST', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);
        $request->headers->set('Accept', 'application/json');

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('register');
        $method->setAccessible(true);

        $response = $method->invoke($this->controller, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('User registered successfully', $data['message']);
    }
}
