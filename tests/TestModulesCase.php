<?php

namespace Unusualify\Modularity\Tests;

use Modules\SystemPayment\Entities\Payment;
use Nwidart\Modules\LaravelModulesServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Unusualify\Modularity\Activators\ModularityActivator;
use Unusualify\Modularity\Entities\Enums\PaymentStatus;
use Unusualify\Modularity\Entities\Observers\PriceableObserver;
use Unusualify\Modularity\LaravelServiceProvider;
use Unusualify\Modularity\Providers\ModularityProvider;

abstract class TestModulesCase extends TestCase
{
    protected $statusesFilePath;

    protected function setUp(): void
    {
        parent::setUp();

    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('modules.scan.enabled', true);
        $app['config']->set('modules.cache.enabled', false);
        $app['config']->set('modules.namespace', 'TestModules');
        $app['config']->set('modules.scan.paths', [
            base_path('vendor/*/*'),
            realpath(__DIR__ . '/../test-modules'),
        ]);

        $app['config']->set('modules.paths.modules', realpath(__DIR__ . '/../test-modules') ?: __DIR__ . '/../test-modules');

        $generatorPaths = [
            'config' => ['path' => 'Config', 'generate' => true],
            'command' => ['path' => 'Console', 'generate' => false],
            'migration' => ['path' => 'Database/Migrations', 'generate' =>true],
            'seeder' => ['path' => 'Database/Seeders', 'generate' => true],
            'model' => ['path' => 'Entities', 'generate' => true],
            'repository' => ['path' => 'Repositories', 'generate' => true],
            'routes' => ['path' => 'Routes', 'generate' => true],
            'controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'request' => ['path' => 'Http/Requests', 'generate' => true],
            'resource' => ['path' => 'Transformers', 'generate' => true],
            'lang' => ['path' => 'Resources/lang', 'generate' => true],
            'filter' => ['path' => 'Http/Middleware', 'generate' => true],
            'provider' => ['path' => 'Providers', 'generate' => true],
        ];

        $modularityGeneratorPaths = array_merge(config('modules.paths.generator'), $generatorPaths, [
            'route-controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'route-request' => ['path' => 'Http/Requests', 'generate' => true],
            'route-resource' => ['path' => 'Transformers', 'generate' => true],
        ]);

        $app['config']->set('modules.paths.generator', $modularityGeneratorPaths);
        $app['config']->set('modularity.paths.generator', $modularityGeneratorPaths);
        $app['config']->set('modularity.base_key', 'modularity');
        $app['config']->set('modularity.stubs.path', realpath(__DIR__ . '/../src/Console/stubs'));

        $statusesFile = 'modules_statuses.json';
        if (getenv('TEST_TOKEN')) {
            $statusesFile = 'modules_statuses_' . getenv('TEST_TOKEN') . '.json';
        } elseif (function_exists('getmypid')) {
            $statusesFile = 'modules_statuses_' . getmypid() . '.json';
        }
        
        $this->statusesFilePath = base_path($statusesFile);

        $app['files']->put($this->statusesFilePath, json_encode([
            'TestModule' => true,
            'SystemModule' => true,
        ]));
        $app['config']->set('modules.activators.modularity', [
            'class' => ModularityActivator::class,
            'statuses-file' => $this->statusesFilePath,
            'cache-key' => 'modularity.activator.installed',
            'cache-lifetime' => 604800,
        ]);
    }
}
