<?php

namespace Unusualify\Modularity\Tests\Services;

use Unusualify\Modularity\Services\ModularityCacheService;
use Unusualify\Modularity\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class ModularityCacheServiceTest extends TestCase
{
    protected $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Force array driver for testing to avoid Redis/Memcached dependencies
        Config::set('modularity.cache.driver', 'array');
        Config::set('modularity.cache.enabled', true);
        Config::set('modularity.cache.use_tags', false); // Array driver doesn't support tags in some Laravel versions
        
        $this->cacheService = new ModularityCacheService();
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(ModularityCacheService::class, $this->cacheService);
    }

    /** @test */
    public function it_returns_correct_config()
    {
        $config = $this->cacheService->getConfig();
        $this->assertEquals('array', $config['driver']);
    }

    /** @test */
    public function it_checks_if_enabled()
    {
        $this->assertTrue($this->cacheService->isEnabled());
        
        Config::set('modularity.cache.enabled', false);
        $cacheService = new ModularityCacheService();
        $this->assertFalse($cacheService->isEnabled());
    }

    /** @test */
    public function it_checks_module_specific_enabled_state()
    {
        Config::set('modularity.cache.all_modules', false);
        Config::set('modularity.cache.modules.TestModule.enabled', true);
        
        $cacheService = new ModularityCacheService();
        
        $this->assertTrue($cacheService->isEnabled('TestModule'));
        $this->assertFalse($cacheService->isEnabled('OtherModule'));
    }

    /** @test */
    public function it_generates_correct_cache_key()
    {
        $key = $this->cacheService->generateCacheKey('test-module', 'test-route', 'list', ['id' => 1]);
        
        $this->assertStringStartsWith('modularity:', $key);
        $this->assertStringContainsString('TestModule', $key);
        $this->assertStringContainsString('TestRoute', $key);
        $this->assertStringContainsString('list', $key);
    }

    /** @test */
    public function it_gets_correct_ttl()
    {
        Config::set('modularity.cache.ttl.list', 100);
        Config::set('modularity.cache.modules.TestModule.ttl.list', 200);
        
        $cacheService = new ModularityCacheService();
        
        $this->assertEquals(200, $cacheService->getTtl('list', 'TestModule'));
        $this->assertEquals(100, $cacheService->getTtl('list', 'OtherModule'));
        $this->assertEquals(300, $cacheService->getTtl('show')); // Default fallback
    }

    /** @test */
    public function it_respects_use_tags_config_when_disabled()
    {
        Config::set('modularity.cache.use_tags', false);
        
        $cacheService = new ModularityCacheService();
        
        $this->assertFalse($cacheService->usesTags());
    }

    /** @test */
    public function it_can_get_cache_store()
    {
        $store = $this->cacheService->getStore();
        
        $this->assertInstanceOf(\Illuminate\Cache\Repository::class, $store);
    }

    /** @test */
    public function it_normalizes_parameters_for_consistent_hashing()
    {
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->cacheService);
        $method = $reflection->getMethod('normalizeParams');
        $method->setAccessible(true);
        
        $params1 = ['z' => 3, 'a' => 1, 'b' => 2];
        $params2 = ['a' => 1, 'b' => 2, 'z' => 3];
        
        $normalized1 = $method->invoke($this->cacheService, $params1);
        $normalized2 = $method->invoke($this->cacheService, $params2);
        
        $this->assertEquals($normalized1, $normalized2);
        $this->assertEquals(['a' => 1, 'b' => 2, 'z' => 3], $normalized1);
    }

    /** @test */
    public function it_normalizes_nested_arrays_recursively()
    {
        $reflection = new \ReflectionClass($this->cacheService);
        $method = $reflection->getMethod('normalizeParams');
        $method->setAccessible(true);
        
        $params = [
            'z' => ['nested_z' => 1, 'nested_a' => 2],
            'a' => ['nested_z' => 3, 'nested_a' => 4]
        ];
        
        $normalized = $method->invoke($this->cacheService, $params);
        
        // Check outer array is sorted
        $this->assertEquals(['a', 'z'], array_keys($normalized));
        // Check nested arrays are sorted
        $this->assertEquals(['nested_a', 'nested_z'], array_keys($normalized['a']));
        $this->assertEquals(['nested_a', 'nested_z'], array_keys($normalized['z']));
    }

    /** @test */
    public function it_can_get_stats_for_all_modules()
    {
        // Mock Redis connection with generic object to avoid strict type checking
        // from phpredis extension's Redis class signature
        $redisMock = \Mockery::mock('stdClass');
        
        // scan method is called with variadic args: scan($cursor, 'MATCH', $pattern, 'COUNT', 100)
        $redisMock->shouldReceive('scan')
            ->withAnyArgs()
            ->andReturn([0, []]);
            
        // zRange is called in getTaggedCacheStats if scan finds keys, 
        // but since we return empty keys, it might not be called.
        // Adding it just in case logic changes or coverage hits it.
        $redisMock->shouldReceive('zRange')
            ->withAnyArgs()
            ->andReturn([]);
        
        Redis::shouldReceive('connection')
            ->with('cache')
            ->andReturn($redisMock);
        
        $stats = $this->cacheService->getStats();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('keys_count', $stats);
        $this->assertEquals(0, $stats['keys_count']);
    }

    /** @test */
    public function it_can_get_stats_for_specific_module()
    {
        Config::set('modularity.cache.modules.TestModule.enabled', true);
        
        // Mock Redis connection with generic object
        $redisMock = \Mockery::mock('stdClass');
        
        // scan method is called with variadic args
        $redisMock->shouldReceive('scan')
            ->withAnyArgs()
            ->andReturn([0, []]);
            
        $redisMock->shouldReceive('zRange')
            ->withAnyArgs()
            ->andReturn([]);
        
        Redis::shouldReceive('connection')
            ->with('cache')
            ->andReturn($redisMock);
        
        $cacheService = new ModularityCacheService();
        $stats = $cacheService->getStats('TestModule');
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('keys_count', $stats);
    }

    /** @test */
    public function it_generates_different_keys_for_different_parameters()
    {
        $key1 = $this->cacheService->generateCacheKey('test-module', 'test-route', 'list', ['page' => 1]);
        $key2 = $this->cacheService->generateCacheKey('test-module', 'test-route', 'list', ['page' => 2]);
        
        $this->assertNotEquals($key1, $key2);
    }
}
