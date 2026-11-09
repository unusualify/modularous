<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Staging;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Unusualify\Modularous\Exceptions\StagingLockedException;
use Unusualify\Modularous\Http\Middleware\StagingMiddleware;
use Unusualify\Modularous\Providers\StagingServiceProvider;
use Unusualify\Modularous\Support\ModularousRoutes;
use Unusualify\Modularous\Tests\Repositories\RepositorySources;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class StagingWriteLocksTest extends RepositoryTestCase
{
    use RepositorySources;

    private const SCHEMA = [['name' => 'name', 'type' => 'text']];

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();
        $this->resetDynamicDefaultMiddlewares();

        Schema::create('staging_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
    }

    protected function tearDown(): void
    {
        $this->resetDynamicDefaultMiddlewares();

        parent::tearDown();
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

    public function test_repository_writes_work_while_staging_is_disabled(): void
    {
        $object = $this->repository->create(['name' => 'Draft', 'is_active' => true], self::SCHEMA);

        $this->assertSame('Draft', $object->name);
    }

    public function test_repository_create_is_locked(): void
    {
        $this->enableStaging();

        $this->expectException(StagingLockedException::class);
        $this->expectExceptionMessage('create on table "test_models"');

        $this->repository->create(['name' => 'Draft', 'is_active' => true], self::SCHEMA);
    }

    public function test_repository_update_and_delete_are_locked(): void
    {
        $object = $this->repository->create(['name' => 'Draft', 'is_active' => true], self::SCHEMA);

        $this->enableStaging();

        foreach ([
            'update' => fn () => $this->repository->update($object->id, ['name' => 'Changed'], self::SCHEMA),
            'delete' => fn () => $this->repository->delete($object->id),
            'bulkDelete' => fn () => $this->repository->bulkDelete([$object->id]),
            'restore' => fn () => $this->repository->restore($object->id),
        ] as $operation => $write) {
            try {
                $write();
                $this->fail("{$operation} should be locked");
            } catch (StagingLockedException $e) {
                $this->assertStringContainsString("{$operation} on table", $e->getMessage());
            }
        }

        $this->assertSame('Draft', $object->fresh()->name);
    }

    public function test_writable_tables_unlock_repository_writes(): void
    {
        $this->enableStaging(['writable_tables' => ['test_model*']]);

        $object = $this->repository->create(['name' => 'Draft', 'is_active' => true], self::SCHEMA);

        $this->assertSame('Draft', $object->name);
    }

    public function test_query_guard_blocks_writes_to_locked_tables(): void
    {
        $this->enableStaging(['guard_console_queries' => true]);

        $this->assertSame(0, DB::table('staging_items')->count());

        $this->expectException(StagingLockedException::class);
        $this->expectExceptionMessage('insert on table "staging_items"');

        DB::table('staging_items')->insert(['name' => 'blocked']);
    }

    public function test_query_guard_allows_writable_tables(): void
    {
        $this->enableStaging(['guard_console_queries' => true, 'writable_tables' => ['staging_items']]);

        DB::table('staging_items')->insert(['name' => 'allowed']);

        $this->assertSame(1, DB::table('staging_items')->count());
    }

    public function test_query_guard_is_off_in_console_by_default(): void
    {
        $this->enableStaging();

        DB::table('staging_items')->insert(['name' => 'deploy']);

        $this->assertSame(1, DB::table('staging_items')->count());
    }

    public function test_provider_adds_the_middleware_only_when_enabled(): void
    {
        (new StagingServiceProvider($this->app))->register();
        $this->assertNotContains('modularous.staging', $this->app->make(ModularousRoutes::class)->defaultMiddlewares());

        $this->enableStaging();
        $this->assertContains('modularous.staging', $this->app->make(ModularousRoutes::class)->defaultMiddlewares());
    }

    public function test_middleware_locks_non_get_requests(): void
    {
        $this->registerRoutes();
        $this->enableStaging();

        $this->getJson('/staging-test/items')->assertOk();
        $this->postJson('/staging-test/items')
            ->assertStatus(423)
            ->assertJson(['staging' => true, 'variant' => 'error']);
    }

    public function test_writable_routes_unlock_non_get_requests(): void
    {
        $this->registerRoutes();
        $this->enableStaging(['writable_routes' => ['admin.staging-item.*']]);

        $this->postJson('/staging-test/items')->assertOk();
    }

    public function test_sign_in_routes_run_unguarded(): void
    {
        $this->registerRoutes();
        $this->enableStaging(['guard_console_queries' => true]);

        $this->postJson('/staging-test/login')->assertOk();

        $this->assertSame(1, DB::table('staging_items')->count());
    }

    private function registerRoutes(): void
    {
        Route::middleware(StagingMiddleware::class)->prefix('staging-test')->group(function () {
            Route::get('items', fn () => 'list')->name('admin.staging-item.index');
            Route::post('items', fn () => 'stored')->name('admin.staging-item.store');
            Route::post('login', function () {
                DB::table('staging_items')->insert(['name' => 'session']);

                return 'signed in';
            })->name('admin.login');
        });
    }

    private function resetDynamicDefaultMiddlewares(): void
    {
        $property = (new ReflectionClass(ModularousRoutes::class))->getProperty('dynamicDefaultMiddlewares');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }
}
