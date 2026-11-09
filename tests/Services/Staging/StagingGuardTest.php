<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Staging;

use Illuminate\Console\Scheduling\Schedule;
use PHPUnit\Framework\Attributes\DataProvider;
use Unusualify\Modularous\Exceptions\StagingLockedException;
use Unusualify\Modularous\Services\Staging\StagingGuard;
use Unusualify\Modularous\Tests\TestCase;

class StagingGuardTest extends TestCase
{
    private function guard(array $config = []): StagingGuard
    {
        foreach (['enabled' => true, ...$config] as $key => $value) {
            config()->set("modularous.staging.{$key}", $value);
        }

        return $this->app->make(StagingGuard::class);
    }

    public function test_disabled_guard_allows_everything(): void
    {
        $guard = $this->guard(['enabled' => false]);

        $this->assertTrue($guard->canWriteTable('users'));
        $this->assertTrue($guard->canWriteRoute(null));
        $this->assertTrue($guard->allowsNotificationChannel('mail'));
        $this->assertTrue($guard->allowsMail());
        $this->assertTrue($guard->allowsJob('App\\Jobs\\SyncJob'));
        $this->assertTrue($guard->allowsHost('api.example.com'));
        $this->assertTrue($guard->allowsCommand('db:wipe'));
        $this->assertFalse($guard->shouldGuardQueries());
    }

    public function test_enabled_guard_locks_everything_by_default(): void
    {
        $guard = $this->guard();

        $this->assertFalse($guard->canWriteTable('users'));
        $this->assertFalse($guard->canWriteRoute('admin.user.update'));
        $this->assertFalse($guard->allowsNotificationChannel('mail'));
        $this->assertFalse($guard->allowsMail());
        $this->assertFalse($guard->allowsJob('App\\Jobs\\SyncJob'));
        $this->assertFalse($guard->allowsHost('api.example.com'));
        $this->assertFalse($guard->allowsCommand('db:wipe'));
    }

    public function test_default_tables_and_commands_stay_unlocked(): void
    {
        $guard = $this->guard();

        $this->assertTrue($guard->canWriteTable('sessions'));
        $this->assertTrue($guard->canWriteTable('activity_log'));
        $this->assertTrue($guard->allowsCommand('migrate'));
        $this->assertTrue($guard->allowsCommand('config:cache'));
        $this->assertTrue($guard->allowsCommand(null));
    }

    public function test_config_unlocks_with_globs(): void
    {
        $guard = $this->guard([
            'writable_tables' => ['press_release*'],
            'writable_routes' => ['admin.press-release.*'],
            'notification_channels' => ['database'],
            'mail' => true,
            'queue_jobs' => ['App\\Jobs\\*'],
            'http_hosts' => ['*.zoho.com'],
            'commands' => ['modularous:cache:*'],
        ]);

        $this->assertTrue($guard->canWriteTable('press_release_translations'));
        $this->assertFalse($guard->canWriteTable('users'));
        $this->assertTrue($guard->canWriteRoute('admin.press-release.update'));
        $this->assertTrue($guard->allowsNotificationChannel('database'));
        $this->assertFalse($guard->allowsNotificationChannel('mail'));
        $this->assertTrue($guard->allowsMail());
        $this->assertTrue($guard->allowsJob('\\App\\Jobs\\SyncJob'));
        $this->assertTrue($guard->allowsHost('www.zoho.com'));
        $this->assertTrue($guard->allowsCommand('modularous:cache:clear'));
    }

    public function test_assert_writable_throws_a_423_naming_the_env_key(): void
    {
        $guard = $this->guard();

        try {
            $guard->assertWritable('users', 'update');
            $this->fail('Expected StagingLockedException');
        } catch (StagingLockedException $e) {
            $this->assertSame(423, $e->getStatusCode());
            $this->assertSame('MODULAROUS_STAGING_WRITABLE_TABLES', $e->envKey);
            $this->assertStringContainsString('"users"', $e->getMessage());
        }
    }

    public function test_unguarded_callback_lifts_table_locks(): void
    {
        $guard = $this->guard();

        $this->assertTrue($guard->unguarded(fn () => $guard->canWriteTable('users')));
        $this->assertFalse($guard->canWriteTable('users'));
    }

    public function test_unguarded_routes_cover_sign_in_and_out(): void
    {
        $guard = $this->guard();

        $this->assertTrue($guard->isUnguardedRoute('admin.login'));
        $this->assertTrue($guard->isUnguardedRoute('admin.logout'));
        $this->assertTrue($guard->isUnguardedRoute('admin.login-2fa'));
        $this->assertFalse($guard->isUnguardedRoute('admin.password.reset.update'));
        $this->assertFalse($guard->isUnguardedRoute(null));
    }

    public function test_query_guard_skips_console_unless_configured(): void
    {
        $this->assertFalse($this->guard()->shouldGuardQueries());
        $this->assertTrue($this->guard(['guard_console_queries' => true])->shouldGuardQueries());
    }

    #[DataProvider('statements')]
    public function test_written_table_is_parsed_from_sql(string $sql, ?string $table): void
    {
        $this->assertSame($table, $this->guard()->writtenTable($sql));
    }

    public static function statements(): array
    {
        return [
            'select' => ['select * from "users" where "id" = ?', null],
            'select for update' => ['select * from `users` for update', null],
            'savepoint' => ['SAVEPOINT trans2', null],
            'insert' => ['insert into "users" ("name") values (?)', 'users'],
            'insert ignore' => ['insert ignore into `users` (`name`) values (?)', 'users'],
            'sqlite insert or ignore' => ['insert or ignore into "users" ("name") values (?)', 'users'],
            'replace' => ['replace into `cache` values (?)', 'cache'],
            'update' => ['update `press_releases` set `title` = ?', 'press_releases'],
            'delete' => ['delete from "users" where "id" = ?', 'users'],
            'schema qualified' => ['delete from `b2press`.`users`', 'users'],
            'truncate' => ['truncate table `users`', 'users'],
            'drop' => ['drop table if exists "users"', 'users'],
            'alter' => ['ALTER TABLE users ADD COLUMN x int', 'users'],
        ];
    }

    public function test_written_table_strips_the_connection_prefix(): void
    {
        $this->assertSame('users', $this->guard()->writtenTable('update "umod_users" set "x" = 1', 'umod_'));
    }

    public function test_scheduled_events_match_by_command_or_description(): void
    {
        $guard = $this->guard(['schedule_commands' => ['modularous:fileponds:*', 'Prune reports']]);
        $schedule = $this->app->make(Schedule::class);

        $this->assertTrue($guard->allowsScheduledEvent($schedule->command('modularous:fileponds:scheduler --days=7')));
        $this->assertFalse($guard->allowsScheduledEvent($schedule->command('telescope:prune')));
        $this->assertTrue($guard->allowsScheduledEvent($schedule->call(fn () => null)->description('Prune reports')));
    }

    public function test_status_command_lists_locks(): void
    {
        $this->guard(['label' => 'PR #73', 'writable_tables' => ['press_releases']]);

        $this->artisan('modularous:staging:status')
            ->expectsOutputToContain('PR #73')
            ->assertSuccessful();
    }

    public function test_status_command_reports_disabled_guard(): void
    {
        $this->guard(['enabled' => false]);

        $this->artisan('modularous:staging:status')
            ->expectsOutputToContain('disabled')
            ->assertSuccessful();
    }
}
