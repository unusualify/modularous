<?php

namespace Unusualify\Modularity\Tests\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Unusualify\Modularity\Services\RedirectService;
use Unusualify\Modularity\Tests\TestCase;

class RedirectServiceTest extends TestCase
{
    protected RedirectService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RedirectService();
    }

    /** @test */
    public function test_set_stores_url_in_session_by_default()
    {
        Session::shouldReceive('put')
            ->once()
            ->with(RedirectService::SESSION_KEY, 'https://example.com');

        $this->service->set('https://example.com');
        
        $this->assertTrue(true); // Mockery validates the expectations
    }

    /** @test */
    public function test_set_stores_url_in_cache_when_use_cache_is_true()
    {
        Cache::shouldReceive('put')
            ->once()
            ->with(RedirectService::CACHE_KEY, 'https://example.com', 600);

        $this->service->set('https://example.com', null, true);
        
        $this->assertTrue(true); // Mockery validates the expectations
    }

    /** @test */
    public function test_set_uses_custom_ttl_for_cache()
    {
        Cache::shouldReceive('put')
            ->once()
            ->with(RedirectService::CACHE_KEY, 'https://example.com', 300);

        $this->service->set('https://example.com', 300, true);
        
        $this->assertTrue(true); // Mockery validates the expectations
    }

    /** @test */
    public function test_get_returns_url_from_session()
    {
        Session::shouldReceive('get')
            ->once()
            ->with(RedirectService::SESSION_KEY)
            ->andReturn('https://session-url.com');

        $result = $this->service->get();

        $this->assertEquals('https://session-url.com', $result);
    }

    /** @test */
    public function test_get_falls_back_to_cache_when_session_empty()
    {
        Session::shouldReceive('get')
            ->once()
            ->with(RedirectService::SESSION_KEY)
            ->andReturn(null);

        Cache::shouldReceive('get')
            ->once()
            ->with(RedirectService::CACHE_KEY)
            ->andReturn('https://cache-url.com');

        $result = $this->service->get();

        $this->assertEquals('https://cache-url.com', $result);
    }

    /** @test */
    public function test_get_returns_null_when_no_url_stored()
    {
        Session::shouldReceive('get')
            ->once()
            ->with(RedirectService::SESSION_KEY)
            ->andReturn(null);

        Cache::shouldReceive('get')
            ->once()
            ->with(RedirectService::CACHE_KEY)
            ->andReturn(null);

        $result = $this->service->get();

        $this->assertNull($result);
    }

    /** @test */
    public function test_get_returns_null_for_empty_string()
    {
        Session::shouldReceive('get')
            ->once()
            ->with(RedirectService::SESSION_KEY)
            ->andReturn('');

        Cache::shouldReceive('get')
            ->once()
            ->with(RedirectService::CACHE_KEY)
            ->andReturn('');

        $result = $this->service->get();

        $this->assertNull($result);
    }

    /** @test */
    public function test_clear_removes_url_from_both_session_and_cache()
    {
        Session::shouldReceive('forget')
            ->once()
            ->with(RedirectService::SESSION_KEY);

        Cache::shouldReceive('forget')
            ->once()
            ->with(RedirectService::CACHE_KEY);

        $this->service->clear();

        $this->assertTrue(true); // Assertion to pass test
    }

    /** @test */
    public function test_pull_returns_url_and_clears_it()
    {
        Session::shouldReceive('get')
            ->once()
            ->with(RedirectService::SESSION_KEY)
            ->andReturn('https://pull-url.com');

        Session::shouldReceive('forget')
            ->once()
            ->with(RedirectService::SESSION_KEY);

        Cache::shouldReceive('forget')
            ->once()
            ->with(RedirectService::CACHE_KEY);

        $result = $this->service->pull();

        $this->assertEquals('https://pull-url.com', $result);
    }

    /** @test */
    public function test_pull_returns_null_when_no_url_and_does_not_clear()
    {
        Session::shouldReceive('get')
            ->once()
            ->with(RedirectService::SESSION_KEY)
            ->andReturn(null);

        Cache::shouldReceive('get')
            ->once()
            ->with(RedirectService::CACHE_KEY)
            ->andReturn(null);

        // Should not call forget when no URL exists
        Session::shouldReceive('forget')->never();
        Cache::shouldReceive('forget')->never();

        $result = $this->service->pull();

        $this->assertNull($result);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
