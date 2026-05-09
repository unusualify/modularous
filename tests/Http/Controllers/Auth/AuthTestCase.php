<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Auth;

use Illuminate\Database\Schema\Blueprint;
use Spatie\Activitylog\Models\Activity;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Tests\TestCase;

/**
 * Base test case for auth controllers. Sets up auth guards, providers, and password brokers.
 */
abstract class AuthTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('modularous.enabled.users-management', true);
        $app['config']->set('auth.guards.modularous', [
            'driver' => 'session',
            'provider' => 'modularous_users',
        ]);
        $app['config']->set('auth.providers.modularous_users', [
            'driver' => 'eloquent',
            'model' => User::class,
        ]);
        $app['config']->set('auth.passwords.modularous_users', [
            'provider' => 'modularous_users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ]);
        $app['config']->set('auth.passwords.users', [
            'provider' => 'modularous_users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ]);
        $app['config']->set('auth.passwords.register_verified_users', [
            'provider' => 'modularous_users',
            'table' => 'register_verified_users',
            'expire' => 60,
            'throttle' => 60,
        ]);

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
        $this->createAuthTables();
    }

    protected function createAuthTables(): void
    {
        $schema = $this->app['db']->connection()->getSchemaBuilder();

        if (! $schema->hasTable('password_resets')) {
            $schema->create('password_resets', function (Blueprint $table) {
                $table->string('email')->index();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! $schema->hasTable('register_verified_users')) {
            $schema->create('register_verified_users', function (Blueprint $table) {
                $table->string('email')->index();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! $schema->hasTable('um_users')) {
            $schema->create('um_users', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('surname')->nullable();
                $table->string('email')->unique();
                $table->string('password')->nullable();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }
}
