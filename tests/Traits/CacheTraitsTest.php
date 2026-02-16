<?php

namespace Unusualify\Modularity\Tests\Traits;

use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Traits\Cache\Cacheable;
use Unusualify\Modularity\Traits\Cache\CacheKeyGenerators;
use Unusualify\Modularity\Traits\Cache\HasUserAwareCache;
use Unusualify\Modularity\Facades\ModularityCache;
use Illuminate\Support\Facades\Auth;

class CacheTraitsTest extends TestCase
{
    /** @test */
    public function it_can_check_if_cache_should_be_used()
    {
        $tester = new class { 
            use Cacheable; 
            public function getModuleName() { return 'Blog'; }
            public function getRouteName() { return 'Post'; }
        };

        ModularityCache::shouldReceive('isEnabled')->with('Blog', 'Post', null)->andReturn(true);
        $this->assertTrue($tester->shouldUseCache());

        $tester->withoutCache();
        $this->assertFalse($tester->shouldUseCache());
    }

    /** @test */
    public function it_can_generate_cache_keys_with_user_context()
    {
        $tester = new class { 
            use Cacheable, HasUserAwareCache;
            public function getModuleName() { return 'Blog'; }
            public function getRouteName() { return 'Post'; }
            
            // Override traitProperties for HasUserAwareCache detection since we are an anonymous class
            protected function traitProperties($method) { return []; } 
        };
        $tester->withUserAwareCache(true);

        $user = \Mockery::mock(\Illuminate\Contracts\Auth\Authenticatable::class);
        $user->shouldReceive('getAuthIdentifier')->andReturn(123);
        Auth::shouldReceive('user')->andReturn($user);

        ModularityCache::shouldReceive('generateCacheKey')->with('Blog', 'Post', 'index', ['_user' => 'u123'])->andReturn('blog:post:index:u123');

        $key = $tester->generateTypeCacheKey('index', []);
        $this->assertEquals('blog:post:index:u123', $key);
    }

    /** @test */
    public function it_can_manage_cache_enabled_status()
    {
        $tester = new class { use Cacheable; };
        
        $this->assertTrue($tester->getSelfCacheEnabled());
        $tester->withoutCache();
        $this->assertFalse($tester->getSelfCacheEnabled());
        $tester->withCache(true);
        $this->assertTrue($tester->getSelfCacheEnabled());
    }

    /** @test */
    public function it_can_handle_user_aware_cache_settings()
    {
        $tester = new class { use HasUserAwareCache; };
        
        $this->assertFalse($tester->shouldUseUserAwareCache());
        $tester->withUserAwareCache(true);
        $this->assertTrue($tester->shouldUseUserAwareCache());
        
        $tester->withSharedCache();
        $this->assertFalse($tester->shouldUseUserAwareCache());
    }

    /** @test */
    public function it_generates_guest_identifier_when_unauthenticated()
    {
        $tester = new class { use HasUserAwareCache; };
        
        Auth::shouldReceive('user')->andReturn(null);
        $this->assertEquals('guest', $tester->getUserCacheIdentifier());
    }
}
