<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Staging;

use Illuminate\Bus\Queueable;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Unusualify\Modularous\Exceptions\StagingLockedException;
use Unusualify\Modularous\Providers\StagingServiceProvider;
use Unusualify\Modularous\Services\Staging\StagingQueue;
use Unusualify\Modularous\Tests\TestCase;

class StagingSideEffectLocksTest extends TestCase
{
    public static array $ran = [];

    protected function setUp(): void
    {
        parent::setUp();

        self::$ran = [];
        config()->set('mail.default', 'array');

        // Laravel only fires CommandStarting outside unit tests unless rerouted.
        $this->app->make(ConsoleKernel::class)->rerouteSymfonyCommandEvents();
    }

    private function enableStaging(array $config = []): void
    {
        foreach (['enabled' => true, ...$config] as $key => $value) {
            config()->set("modularous.staging.{$key}", $value);
        }

        $provider = new StagingServiceProvider($this->app);
        $provider->register();
        $provider->boot();
    }

    private function sentMails(): int
    {
        return count($this->app['mailer']->getSymfonyTransport()->messages());
    }

    public function test_disabled_guard_registers_no_locks(): void
    {
        $events = [NotificationSending::class, MessageSending::class, CommandStarting::class];
        $before = array_map(fn ($event) => count(Event::getListeners($event)), $events);

        $provider = new StagingServiceProvider($this->app);
        $provider->register();
        $provider->boot();

        $this->assertSame($before, array_map(fn ($event) => count(Event::getListeners($event)), $events));
        $this->assertNotSame('staging', config('queue.connections.redis.driver'));
    }

    public function test_notifications_are_skipped_unless_their_channel_is_unlocked(): void
    {
        $this->enableStaging();
        NotificationFacade::route(StagingRecordingChannel::class, 'ops')->notifyNow(new StagingTestNotification);
        $this->assertSame([], self::$ran);

        $this->enableStaging(['notification_channels' => [StagingRecordingChannel::class]]);
        NotificationFacade::route(StagingRecordingChannel::class, 'ops')->notifyNow(new StagingTestNotification);
        $this->assertSame(['notification'], self::$ran);
    }

    public function test_mail_is_skipped_unless_unlocked(): void
    {
        $this->enableStaging();
        Mail::raw('hello', fn ($message) => $message->to('user@example.com'));
        $this->assertSame(0, $this->sentMails());

        config()->set('modularous.staging.mail', true);
        Mail::raw('hello', fn ($message) => $message->to('user@example.com'));
        $this->assertSame(1, $this->sentMails());
    }

    public function test_queued_jobs_are_dropped_unless_unlocked(): void
    {
        $this->enableStaging();
        StagingTestJob::dispatch();
        $this->assertSame([], self::$ran);

        config()->set('modularous.staging.queue_jobs', [StagingTestJob::class]);
        StagingTestJob::dispatch();
        $this->assertSame(['job'], self::$ran);
    }

    public function test_every_queue_connection_uses_the_staging_driver(): void
    {
        $this->enableStaging();

        $this->assertSame('staging', config('queue.connections.redis.driver'));
        $this->assertInstanceOf(StagingQueue::class, $this->app['queue']->connection('redis'));
    }

    public function test_queue_resolves_the_class_behind_queued_wrappers(): void
    {
        $queue = $this->app->make(StagingQueue::class);

        $this->assertSame(StagingTestJob::class, $queue->jobClass(new StagingTestJob));
        $this->assertSame('App\\Jobs\\Raw', $queue->jobClass('App\\Jobs\\Raw'));
    }

    public function test_scheduled_tasks_are_skipped_unless_unlocked(): void
    {
        $this->enableStaging(['schedule_commands' => ['Allowed task']]);

        $schedule = $this->app->make(Schedule::class);
        $schedule->call(function () {
            self::$ran[] = 'allowed';
        })->description('Allowed task')->everyMinute();
        $schedule->call(function () {
            self::$ran[] = 'locked';
        })->description('Locked task')->everyMinute();

        Artisan::call('schedule:run');

        $this->assertSame(['allowed'], self::$ran);
    }

    public function test_artisan_commands_outside_the_allowlist_are_locked(): void
    {
        $this->enableStaging();

        $this->expectException(StagingLockedException::class);
        $this->expectExceptionMessage('"cache:clear" command is locked');

        Artisan::call('cache:clear');
    }

    public function test_unlocked_artisan_commands_run(): void
    {
        $this->enableStaging(['commands' => ['cache:*']]);

        $this->assertSame(0, Artisan::call('cache:clear'));
    }

    public function test_http_requests_to_locked_hosts_are_rejected(): void
    {
        Http::fake();
        $this->enableStaging(['http_hosts' => ['*.allowed.test']]);

        $this->assertTrue(Http::get('https://api.allowed.test/ping')->successful());

        $this->expectException(StagingLockedException::class);
        $this->expectExceptionMessage('"api.blocked.test"');

        Http::get('https://api.blocked.test/ping');
    }
}

class StagingTestJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle(): void
    {
        StagingSideEffectLocksTest::$ran[] = 'job';
    }
}

class StagingRecordingChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        StagingSideEffectLocksTest::$ran[] = 'notification';
    }
}

class StagingTestNotification extends Notification
{
    public function via(mixed $notifiable): array
    {
        return [StagingRecordingChannel::class];
    }
}
