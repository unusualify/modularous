<?php

namespace Unusualify\Modularity\Tests\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Notifications\EmailVerification;
use Unusualify\Modularity\Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    /** @test */
    public function test_constructor_sets_token_and_parameters()
    {
        $token = 'test-token-123';
        $parameters = ['foo' => 'bar'];

        $notification = new EmailVerification($token, $parameters);

        $this->assertEquals($token, $notification->token);
        $this->assertEquals($parameters, $notification->parameters);
    }

    /** @test */
    public function test_constructor_sets_empty_parameters_by_default()
    {
        $token = 'test-token-123';

        $notification = new EmailVerification($token);

        $this->assertEquals($token, $notification->token);
        $this->assertEquals([], $notification->parameters);
    }

    /** @test */
    public function test_via_returns_mail_channel()
    {
        $notification = new EmailVerification('token');
        $notifiable = $this->createMockNotifiable();

        $channels = $notification->via($notifiable);

        $this->assertEquals(['mail'], $channels);
    }

    /** @test */
    public function test_to_mail_returns_mail_message()
    {
        Route::shouldReceive('hasAdmin')
            ->with('complete.register.form')
            ->andReturn('admin.complete.register.form');

        Lang::shouldReceive('get')
            ->andReturnUsing(function ($key, $params = []) {
                return $key;
            });

        $notification = new EmailVerification('test-token', ['param1' => 'value1']);
        $notifiable = $this->createMockNotifiable('test@example.com');

        $mailMessage = $notification->toMail($notifiable);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    /** @test */
    public function test_verification_url_includes_token_and_email()
    {
        Route::shouldReceive('hasAdmin')
            ->with('complete.register.form')
            ->andReturn('admin.complete.register.form');

        $notification = new EmailVerification('test-token-456', ['extra' => 'param']);
        $notifiable = $this->createMockNotifiable('user@example.com');

        // Use reflection to call protected method
        $reflection = new \ReflectionClass($notification);
        $method = $reflection->getMethod('verificationUrl');
        $method->setAccessible(true);

        $url = $method->invoke($notification, $notifiable);

        $this->assertStringContainsString('test-token-456', $url);
        // Email gets URL encoded in query string
        $this->assertTrue(
            str_contains($url, 'user@example.com') || str_contains($url, urlencode('user@example.com'))
        );
        $this->assertStringContainsString('extra', $url);
    }

    /** @test */
    public function test_verification_url_spreads_parameters()
    {
        Route::shouldReceive('hasAdmin')
            ->with('complete.register.form')
            ->andReturn('admin.complete.register.form');

        $parameters = ['key1' => 'val1', 'key2' => 'val2'];
        $notification = new EmailVerification('token', $parameters);
        $notifiable = $this->createMockNotifiable('test@example.com');

        $reflection = new \ReflectionClass($notification);
        $method = $reflection->getMethod('verificationUrl');
        $method->setAccessible(true);

        $url = $method->invoke($notification, $notifiable);

        $this->assertStringContainsString('key1', $url);
        $this->assertStringContainsString('val1', $url);
    }

    protected function createMockNotifiable($email = 'test@example.com')
    {
        $notifiable = new class($email)
        {
            public $email;

            public function __construct($email)
            {
                $this->email = $email;
            }
        };

        return $notifiable;
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
