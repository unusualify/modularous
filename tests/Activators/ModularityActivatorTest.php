<?php

namespace Unusualify\Modularity\Tests\Activators;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use Nwidart\Modules\Module;
use PHPUnit\Framework\TestCase;
use Unusualify\Modularity\Activators\ModularityActivator;

class ModularityActivatorTest extends TestCase
{
    /**
     * @var ModularityActivator
     */
    private $activator;

    /**
     * @var Mockery\MockInterface|Container
     */
    private $mockContainer;

    /**
     * @var Mockery\MockInterface|CacheManager
     */
    private $mockCache;

    /**
     * @var Filesystem (real instance)
     */
    private $files;

    /**
     * @var Mockery\MockInterface|Config
     */
    private $mockConfig;

    /**
     * @var string
     */
    private $statusesFile;

    protected function setUp(): void
    {
        parent::setUp();

        // Use a temporary unique file path for status storage
        $this->statusesFile = tempnam(sys_get_temp_dir(), 'modularity_statuses_');

        // Create mocks for dependencies
        $this->mockCache = Mockery::mock(CacheManager::class);
        $this->files = new Filesystem();
        $this->mockConfig = Mockery::mock(Config::class);

        // Create container mock and bind the dependencies
        $this->mockContainer = Mockery::mock(Container::class);
        $this->mockContainer->shouldReceive('offsetGet')->with('cache')->andReturn($this->mockCache);
        $this->mockContainer->shouldReceive('offsetGet')->with('files')->andReturn($this->files);
        $this->mockContainer->shouldReceive('offsetGet')->with('config')->andReturn($this->mockConfig);

        // Configure flexible config responses using a callable
        $this->mockConfig->shouldReceive('get')->andReturnUsing(function ($key, $default = null) {
            $configMap = [
                'modules.activators.modularity.statuses-file' => $this->statusesFile,
                'modules.activators.modularity.cache-key' => 'modularity.activator.installed',
                'modules.activators.modularity.cache-lifetime' => 604800,
                'modules.cache.enabled' => false,
                'modules.cache.driver' => 'redis',
            ];

            return $configMap[$key] ?? $default;
        });

        // Pre-initialize the file with empty array
        $this->files->put($this->statusesFile, json_encode([]));

        // Initialize the activator
        $this->activator = new ModularityActivator($this->mockContainer);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        // Clean up temporary file
        if (file_exists($this->statusesFile)) {
            unlink($this->statusesFile);
        }

        parent::tearDown();
    }

    /**
     * @test
     * Test the constructor initializes properly
     */
    public function test_constructor_initializes_properties(): void
    {
        $this->assertInstanceOf(ModularityActivator::class, $this->activator);
    }

    /**
     * @test
     * Test getStatusesFilePath returns the correct file path
     */
    public function test_get_statuses_file_path_returns_correct_path(): void
    {
        $path = $this->activator->getStatusesFilePath();

        $this->assertEquals($this->statusesFile, $path);
        $this->assertIsString($path);
    }

    /**
     * @test
     * Test getStatusesFilePath returns same path on multiple calls
     */
    public function test_get_statuses_file_path_consistent(): void
    {
        $path1 = $this->activator->getStatusesFilePath();
        $path2 = $this->activator->getStatusesFilePath();

        $this->assertEquals($path1, $path2);
    }

    /**
     * @test
     * Test reset removes the statuses file and clears cache
     */
    public function test_reset_removes_statuses_file(): void
    {
        // Write a file first
        $this->files->put($this->statusesFile, json_encode(['TestModule' => true]));
        $this->assertTrue($this->files->exists($this->statusesFile));

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->once();

        $this->activator->reset();

        // File should be deleted
        $this->assertFalse($this->files->exists($this->statusesFile));
    }

    /**
     * @test
     * Test reset when file doesn't exist
     */
    public function test_reset_when_file_not_exists(): void
    {
        // Ensure file doesn't exist
        if ($this->files->exists($this->statusesFile)) {
            $this->files->delete($this->statusesFile);
        }

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->once();

        // Should not throw an error
        $this->activator->reset();
        $this->assertTrue(true);
    }

    /**
     * @test
     * Test enable activates a module
     */
    public function test_enable_activates_module(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('TestModule');

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->once();

        $this->activator->enable($module);

        // Verify file was written
        $this->assertTrue($this->files->exists($this->statusesFile));
        $content = json_decode($this->files->get($this->statusesFile), true);
        $this->assertEquals(['TestModule' => true], $content);
    }

    /**
     * @test
     * Test disable deactivates a module
     */
    public function test_disable_deactivates_module(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('TestModule');

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->once();

        $this->activator->disable($module);

        // Verify file was written
        $this->assertTrue($this->files->exists($this->statusesFile));
        $content = json_decode($this->files->get($this->statusesFile), true);
        $this->assertEquals(['TestModule' => false], $content);
    }

    /**
     * @test
     * Test setActive with true status
     */
    public function test_set_active_with_true_status(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('TestModule');

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->once();

        $this->activator->setActive($module, true);

        // Verify file was written
        $this->assertTrue($this->files->exists($this->statusesFile));
        $content = json_decode($this->files->get($this->statusesFile), true);
        $this->assertEquals(['TestModule' => true], $content);
    }

    /**
     * @test
     * Test setActive with false status
     */
    public function test_set_active_with_false_status(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('TestModule');

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->once();

        $this->activator->setActive($module, false);

        // Verify file was written
        $this->assertTrue($this->files->exists($this->statusesFile));
        $content = json_decode($this->files->get($this->statusesFile), true);
        $this->assertEquals(['TestModule' => false], $content);
    }

    /**
     * @test
     * Test setActiveByName updates module status
     */
    public function test_set_active_by_name_updates_status(): void
    {
        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->once();

        $this->activator->setActiveByName('TestModule', true);

        // Verify file was written
        $this->assertTrue($this->files->exists($this->statusesFile));
        $content = json_decode($this->files->get($this->statusesFile), true);
        $this->assertEquals(['TestModule' => true], $content);
    }

    /**
     * @test
     * Test hasStatus returns true for active module
     */
    public function test_has_status_returns_true_for_active_module(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('TestModule');

        // Setup initial state with active module
        $this->files->put($this->statusesFile, json_encode(['TestModule' => true]));

        // Recreate activator with the new file state
        $activator = new ModularityActivator($this->mockContainer);

        $result = $activator->hasStatus($module, true);

        $this->assertTrue($result);
    }

    /**
     * @test
     * Test hasStatus returns false for inactive module
     */
    public function test_has_status_returns_false_for_inactive_module(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('TestModule');

        // Setup initial state with inactive module
        $this->files->put($this->statusesFile, json_encode(['TestModule' => false]));

        // Recreate activator with the new file state
        $activator = new ModularityActivator($this->mockContainer);

        $result = $activator->hasStatus($module, true);

        $this->assertFalse($result);
    }

    /**
     * @test
     * Test hasStatus returns false for non-existent module when checking for active
     */
    public function test_has_status_returns_false_for_non_existent_module_when_checking_active(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('NonExistentModule');

        // Ensure file is empty
        if ($this->files->exists($this->statusesFile)) {
            $this->files->delete($this->statusesFile);
        }

        // Recreate activator with empty state
        $activator = new ModularityActivator($this->mockContainer);

        $result = $activator->hasStatus($module, true);

        $this->assertFalse($result);
    }

    /**
     * @test
     * Test hasStatus returns true for non-existent module when checking for inactive
     */
    public function test_has_status_returns_true_for_non_existent_module_when_checking_inactive(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('NonExistentModule');

        // Ensure file is empty
        if ($this->files->exists($this->statusesFile)) {
            $this->files->delete($this->statusesFile);
        }

        // Recreate activator with empty state
        $activator = new ModularityActivator($this->mockContainer);

        $result = $activator->hasStatus($module, false);

        $this->assertTrue($result);
    }

    /**
     * @test
     * Test delete removes module from statuses
     */
    public function test_delete_removes_module_from_statuses(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('TestModule');

        // Setup initial state with a module
        $this->files->put($this->statusesFile, json_encode(['TestModule' => true, 'OtherModule' => false]));

        // Recreate activator
        $activator = new ModularityActivator($this->mockContainer);

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->once();

        $activator->delete($module);

        // Verify file was updated
        $content = json_decode($this->files->get($this->statusesFile), true);
        $this->assertArrayNotHasKey('TestModule', $content);
        $this->assertArrayHasKey('OtherModule', $content);
    }

    /**
     * @test
     * Test delete when module doesn't exist
     */
    public function test_delete_when_module_not_exists(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('NonExistentModule');

        // Setup state without the module
        $this->files->put($this->statusesFile, json_encode(['TestModule' => true]));

        // Recreate activator
        $activator = new ModularityActivator($this->mockContainer);

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldNotReceive('forget');

        $activator->delete($module);

        // File should remain unchanged
        $content = json_decode($this->files->get($this->statusesFile), true);
        $this->assertEquals(['TestModule' => true], $content);
    }

    /**
     * @test
     * Test getModulesStatuses uses cache when cache is enabled
     */
    public function test_get_modules_statuses_uses_cache_when_enabled(): void
    {
        $statuses = ['TestModule' => true, 'OtherModule' => false];

        // Create a new mock config for cache-enabled scenario
        $cacheEnabledConfig = Mockery::mock(Config::class);
        $cacheEnabledConfig->shouldReceive('get')->andReturnUsing(function ($key, $default = null) {
            $configMap = [
                'modules.activators.modularity.statuses-file' => $this->statusesFile,
                'modules.activators.modularity.cache-key' => 'modularity.activator.installed',
                'modules.activators.modularity.cache-lifetime' => 604800,
                'modules.cache.enabled' => true,
                'modules.cache.driver' => 'redis',
            ];

            return $configMap[$key] ?? $default;
        });

        // Create new container for cache scenario
        $cacheContainer = Mockery::mock(Container::class);
        $cacheContainer->shouldReceive('offsetGet')->with('cache')->andReturn($this->mockCache);
        $cacheContainer->shouldReceive('offsetGet')->with('files')->andReturn($this->files);
        $cacheContainer->shouldReceive('offsetGet')->with('config')->andReturn($cacheEnabledConfig);

        // Set up cache mock
        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->with('redis')->andReturn($storeMock);
        $storeMock->shouldReceive('remember')->with('modularity.activator.installed', 604800, Mockery::any())->andReturnUsing(function ($key, $lifetime, $callback) use ($statuses) {
            return $callback();
        });

        // Set up file mock to return statuses
        $this->files->put($this->statusesFile, json_encode($statuses));

        // Create activator with cache enabled
        $activator = new ModularityActivator($cacheContainer);

        $result = $activator->getModulesStatuses();

        $this->assertEquals($statuses, $result);
    }

    /**
     * @test
     * Test getModulesStatuses returns file data when cache is disabled
     */
    public function test_get_modules_statuses_reads_file_when_cache_disabled(): void
    {
        $statuses = ['TestModule' => true, 'OtherModule' => false];

        $this->files->put($this->statusesFile, json_encode($statuses));

        // Recreate activator with cache disabled (which is already configured)
        $activator = new ModularityActivator($this->mockContainer);

        $result = $activator->getModulesStatuses();

        $this->assertEquals($statuses, $result);
    }

    /**
     * @test
     * Test getModulesStatuses returns empty array when file doesn't exist
     */
    public function test_get_modules_statuses_returns_empty_array_when_file_not_exists(): void
    {
        // Ensure file doesn't exist
        if ($this->files->exists($this->statusesFile)) {
            $this->files->delete($this->statusesFile);
        }

        // Recreate activator
        $activator = new ModularityActivator($this->mockContainer);

        $result = $activator->getModulesStatuses();

        $this->assertEquals([], $result);
    }

    /**
     * @test
     * Test flushCache calls cache forget
     */
    public function test_flush_cache_forgets_cache(): void
    {
        $this->mockConfig->shouldReceive('get')->with('modules.cache.driver', Mockery::any())
            ->andReturn('redis');

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->with('redis')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->with('modularity.activator.installed')->once();

        $this->activator->flushCache();

        // Assert the mock expectations were met
        $this->assertTrue(true);
    }

    /**
     * @test
     * Test multiple modules can be activated and deactivated
     */
    public function test_multiple_modules_activation(): void
    {
        $module1 = Mockery::mock(Module::class);
        $module1->shouldReceive('getName')->andReturn('Module1');

        $module2 = Mockery::mock(Module::class);
        $module2->shouldReceive('getName')->andReturn('Module2');

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->times(2);

        $this->activator->enable($module1);
        $this->activator->disable($module2);

        // Verify file was updated
        $content = json_decode($this->files->get($this->statusesFile), true);
        $this->assertEquals(['Module1' => true, 'Module2' => false], $content);
    }

    /**
     * @test
     * Test setActiveByName followed by hasStatus integration
     */
    public function test_set_active_by_name_and_has_status_integration(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('TestModule');

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->once();

        $this->activator->setActiveByName('TestModule', true);

        // Create a fresh activator to read the new state
        $activator = new ModularityActivator($this->mockContainer);

        $result = $activator->hasStatus($module, true);

        $this->assertTrue($result);
    }

    /**
     * @test
     * Test enabling then disabling a module
     */
    public function test_enable_then_disable_module(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('TestModule');

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->times(2);

        $this->activator->enable($module);
        $this->activator->disable($module);

        // Verify final state
        $content = json_decode($this->files->get($this->statusesFile), true);
        $this->assertEquals(['TestModule' => false], $content);
    }

    /**
     * @test
     * Test cache config values are used correctly
     */
    public function test_cache_config_values_are_retrieved(): void
    {
        $this->mockConfig->shouldReceive('get')->with('modules.cache.driver', Mockery::any())
            ->andReturn('redis');

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->with('redis')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->once();

        $this->activator->flushCache();

        // Assert the mock expectations were met
        $this->assertTrue(true);
    }

    /**
     * @test
     * Test reset clears the internal module statuses
     */
    public function test_reset_clears_internal_statuses(): void
    {
        // First, set up some module statuses
        $this->files->put($this->statusesFile, json_encode(['TestModule' => true, 'OtherModule' => false]));

        // Create activator with existing statuses
        $activator = new ModularityActivator($this->mockContainer);

        // Verify the statuses are loaded
        $beforeReset = $activator->getModulesStatuses();
        $this->assertNotEmpty($beforeReset);

        // Now reset
        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->once();

        $activator->reset();

        // After reset, file should be deleted
        $this->assertFalse($this->files->exists($this->statusesFile));

        // After reset, statuses should be empty
        $afterReset = $activator->getModulesStatuses();
        $this->assertEquals([], $afterReset);
    }

    /**
     * @test
     * Test JSON encoding with pretty print
     */
    public function test_json_encoding_with_pretty_print(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('TestModule');

        $storeMock = Mockery::mock();
        $this->mockCache->shouldReceive('store')->andReturn($storeMock);
        $storeMock->shouldReceive('forget')->once();

        $this->activator->setActiveByName('TestModule', true);

        // Verify JSON is properly formatted
        $writtenJson = $this->files->get($this->statusesFile);
        $this->assertNotNull($writtenJson);
        $decoded = json_decode($writtenJson, true);
        $this->assertEquals(['TestModule' => true], $decoded);

        // Check for pretty printing (should have indentation)
        $this->assertStringContainsString("\n", $writtenJson);
    }

    /**
     * @test
     * Test constructor loads modules statuses on initialization
     */
    public function test_constructor_loads_statuses_on_initialization(): void
    {
        $statuses = ['TestModule' => true, 'OtherModule' => false];

        $this->files->put($this->statusesFile, json_encode($statuses));

        $activator = new ModularityActivator($this->mockContainer);

        $result = $activator->getModulesStatuses();

        $this->assertEquals($statuses, $result);
    }
}
