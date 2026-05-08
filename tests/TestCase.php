<?php

namespace Unusualify\Modularity\Tests;

use Astrotomic\Translatable\TranslatableServiceProvider;
use Illuminate\Foundation\Application;
use JoeDixon\Translation\TranslationServiceProvider;
use Modules\SystemPayment\Entities\Payment;
use Nwidart\Modules\LaravelModulesServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Unusualify\Modularity\Activators\ModularityActivator;
use Unusualify\Modularity\Entities\Enums\PaymentStatus;
use Unusualify\Modularity\Entities\Observers\PriceableObserver;
use Unusualify\Modularity\LaravelServiceProvider;
use Unusualify\Modularity\Providers\ModularityProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public $path;

    public $modulesPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Note: this also flushes the cache from within the migration
        $this->setUpDatabase($this->app);

        $this->path = realpath(__DIR__ . '/..');

        $this->modulesPath = realpath($this->path . '/modules');

        // $app['cache'] = $this->createMock(CacheManager::class);
        // $app['files'] = $this->createMock(Filesystem::class);
        // $app['config'] = $this->createMock(Config::class);

    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelModulesServiceProvider::class,
            LaravelServiceProvider::class,
            ModularityProvider::class,
            PermissionServiceProvider::class,
            \Oobook\Priceable\LaravelServiceProvider::class,
            TranslationServiceProvider::class,
            TranslatableServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('database.default', 'testdb');
        $app['config']->set('database.connections.testdb', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('cache.prefix', 'spatie_tests---');
        $app['config']->set('cache.default', getenv('CACHE_DRIVER') ?: 'array');
        $app['config']->set('modules.scan.enabled', true);
        $app['config']->set('modules.cache.enabled', false);
        $app['config']->set('modules.scan.paths', [
            base_path('vendor/*/*'),
            realpath(__DIR__ . '/../modules'),
        ]);

        $app['config']->set('modules.paths.modules', realpath(__DIR__ . '/../modules') ?: __DIR__ . '/../modules');

        $generatorPaths = [
            'config' => ['path' => 'Config', 'generate' => true],
            'command' => ['path' => 'Console', 'generate' => false],
            'migration' => ['path' => 'Database/Migrations', 'generate' => true],
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
        $statusFilePath = base_path($statusesFile);

        $app['files']->put($statusFilePath, json_encode([
            'SystemNotification' => false,
            'SystemPayment' => false,
            'SystemPricing' => false,
            'SystemSetting' => false,
            'SystemUser' => false,
            'SystemUtility' => false,
        ]));
        $app['config']->set('modules.activators.modularity', [
            'class' => ModularityActivator::class,
            'statuses-file' => $statusFilePath,
            'cache-key' => 'modularity.activator.installed',
            'cache-lifetime' => 604800,
        ]);

        $app['config']->set('modules.activator', 'modularity');

        $app['config']->set('modularity.app_url', 'http://localhost');
        $app['config']->set('modularity.admin_app_url', '');
        $app['config']->set('modularity.admin_app_path', 'admin');
        $app['config']->set('modularity.admin_route_name_prefix', 'admin');

        $app['config']->set('modularity.media_library.image_service', "Unusualify\Modularity\Services\MediaLibrary\Local");
        $app['config']->set('modularity.file_library.file_service', "Unusualify\Modularity\Services\FileLibrary\Disk");

        $app['config']->set([
            'priceable.observers.price' => PriceableObserver::class,
            'priceable.prices_are_including_vat' => false,
        ]);
        $app['config']->set([
            'translatable.locales' => [
                'en',
            ],
        ]);

        $app['config']->set([
            'payable.table' => 'up_payments',
            'payable.model' => Payment::class,
            'payable.status_enum' => PaymentStatus::class,
            'payable.additional_fillable' => ['payment_service_id', 'price_id', 'currency_id'],
            // 'payable.middleware' => ['web.auth', 'modularity.panel'],
        ]);
    }

    /**
     * Set up the database.
     *
     * @param Application $app
     */
    protected function setUpDatabase($app)
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        // $schema->create('users', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
        //     $table->string('email');
        //     $table->softDeletes();
        // });

        // $schema->create('companies', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->string('name');
        //     $table->timestamps();
        // });

        // $schema->create('files', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->uuidMorphs('fileable');
        //     $table->string('name');
        // });

    }

    public function moduleDirectory(string $moduleName): string
    {
        return realpath("{$this->modulesPath}/{$moduleName}");
    }
}
