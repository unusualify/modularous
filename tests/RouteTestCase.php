<?php

namespace Unusualify\Modularous\Tests;

use App\Models\User;
use Unusualify\Modularous\Providers\RouteServiceProvider;

abstract class RouteTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // dd(
        //     parse_url('http://localhost'),
        //     $this->app['config']->get('modularous.app_url'),
        //     $this->app['config']->get('modularous.admin_app_url'),
        //     $this->app['config']->get('modularous.admin_app_path'),
        //     $this->app['config']->get('modularous.admin_route_name_prefix'),
        // );

        // Set the application URL to match the domain in routes
        // $this->app['config']->set('app.url', 'http://admin.app.b2press.test');

        // Force the URL generator to use the correct domain
        // $this->app['url']->forceRootUrl('http://admin.app.b2press.test');
    }

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            RouteServiceProvider::class,
        ]);
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // $app['config']->set('app.url', 'http://localhost');
        $app['config']->set('modularous.app_url', 'localhost');
        $app['config']->set('modularous.admin_app_url', null);
        $app['config']->set('modularous.admin_app_path', 'admin');

        // Configure the modularous auth guard
        $app['config']->set('auth.guards.modularous', [
            'driver' => 'session',
            'provider' => 'modularous_users',
        ]);

        // Configure the auth provider for modularous
        $app['config']->set('auth.providers.modularous_users', [
            'driver' => 'eloquent',
            'model' => User::class, // Adjust this to your actual User model
        ]);

        // Add password reset configuration
        $app['config']->set('auth.passwords.modularous_users', [
            'provider' => 'modularous_users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ]);

        // Configure session driver for testing
        $app['config']->set('session.driver', 'array');
    }
}
