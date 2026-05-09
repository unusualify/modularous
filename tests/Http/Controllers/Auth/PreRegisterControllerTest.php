<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Auth;

use Illuminate\Contracts\View\View;
use Unusualify\Modularous\Brokers\RegisterBroker;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Http\Controllers\Auth\PreRegisterController;
use Unusualify\Modularous\Tests\TestCase;

class PreRegisterControllerTest extends TestCase
{
    protected PreRegisterController $controller;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('modularous.enabled.users-management', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        config(['auth.providers.modularous_users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ]]);
        config(['auth.passwords.register_verified_users' => [
            'provider' => 'modularous_users',
            'table' => 'um_email_verification_tokens',
            'expire' => 60,
            'throttle' => 60,
        ]]);

        $this->controller = new PreRegisterController;
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(PreRegisterController::class, $this->controller);
    }

    /** @test */
    public function it_uses_register_broker(): void
    {
        $broker = $this->controller->broker();

        $this->assertInstanceOf(RegisterBroker::class, $broker);
    }

    /** @test */
    public function it_returns_pre_register_email_form_view(): void
    {
        $response = $this->controller->showEmailForm();

        $this->assertInstanceOf(View::class, $response);
    }
}
