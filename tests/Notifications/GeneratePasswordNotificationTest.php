<?php

namespace Unusualify\Modularity\Tests\Notifications;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Unusualify\Modularity\Notifications\GeneratePasswordNotification;
use Unusualify\Modularity\Tests\TestCase;

class GeneratePasswordNotificationTest extends TestCase
{
    /** @test */
    public function test_constructor_sets_token()
    {
        $token = 'generate-password-token';

        $notification = new GeneratePasswordNotification($token);

        $this->assertEquals($token, $notification->token);
    }

    /** @test */
    public function test_via_returns_mail_channel()
    {
        $notification = new GeneratePasswordNotification('token');
        $notifiable = $this->createMockNotifiable();

        $channels = $notification->via($notifiable);

        $this->assertEquals(['mail'], $channels);
    }

    /** @test */
    public function test_to_mail_returns_mail_message()
    {
        Config::shouldReceive('get')
            ->andReturnUsing(function ($key, $default = null) {
                if ($key === 'app.name') {
                    return 'Test App';
                }
                return $default;
            });

        Lang::shouldReceive('get')
            ->andReturnUsing(function ($key, $params = []) {
                return $key;
            });

        $notification = new GeneratePasswordNotification('test-token');
        $notifiable = $this->createMockNotifiable('test@example.com');

        $mailMessage = $notification->toMail($notifiable);

        $this->assertInstanceOf(\Illuminate\Notifications\Messages\MailMessage::class, $mailMessage);
    }

    /** @test */
    public function test_build_mail_message_has_correct_subject()
    {
        Config::shouldReceive('get')
            ->andReturnUsing(function ($key, $default = null) {
                if ($key === 'app.name') {
                    return 'MyApp';
                }
                return $default;
            });

        Lang::shouldReceive('get')
            ->with('Generate Your Password For New Account')
            ->once()
            ->andReturn('Generate Your Password For New Account');

        Lang::shouldReceive('get')
            ->andReturnUsing(function ($key) {
                return $key;
            });

        $notification = new GeneratePasswordNotification('token');

        $reflection = new \ReflectionClass($notification);
        $method = $reflection->getMethod('buildMailMessage');
        $method->setAccessible(true);

        $mailMessage = $method->invoke($notification, 'http://example.com/verify');

        $this->assertInstanceOf(\Illuminate\Notifications\Messages\MailMessage::class, $mailMessage);
    }

    /** @test */
    public function test_generate_password_url_contains_token_and_email()
    {
        $notification = new GeneratePasswordNotification('my-token-123');
        $notifiable = $this->createMockNotifiable('user@test.com');

        $reflection = new \ReflectionClass($notification);
        $method = $reflection->getMethod('generatePasswordUrl');
        $method->setAccessible(true);

        $url = $method->invoke($notification, $notifiable);

        $this->assertStringContainsString('my-token-123', $url);
        // Email gets URL encoded in query string
        $this->assertTrue(
            str_contains($url, 'user@test.com') || str_contains($url, urlencode('user@test.com'))
        );
    }

    /** @test */
    public function test_create_url_using_sets_static_callback()
    {
        $callback = function () {
            return 'custom-url';
        };

        GeneratePasswordNotification::createUrlUsing($callback);

        $reflection = new \ReflectionClass(GeneratePasswordNotification::class);
        $property = $reflection->getProperty('createUrlCallback');
        $property->setAccessible(true);

        $this->assertSame($callback, $property->getValue());

        // Clean up
        $property->setValue(null);
    }

    /** @test */
    public function test_to_mail_using_sets_static_callback()
    {
        $callback = function () {
            return 'custom-mail';
        };

        GeneratePasswordNotification::toMailUsing($callback);

        $reflection = new \ReflectionClass(GeneratePasswordNotification::class);
        $property = $reflection->getProperty('toMailCallback');
        $property->setAccessible(true);

        $this->assertSame($callback, $property->getValue());

        // Clean up
        $property->setValue(null);
    }

    protected function createMockNotifiable($email = 'test@example.com')
    {
        $notifiable = new class($email) {
            public $email;

            public function __construct($email)
            {
                $this->email = $email;
            }

            public function getEmailForPasswordGeneration()
            {
                return $this->email;
            }

            public function getKey()
            {
                return 1;
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
