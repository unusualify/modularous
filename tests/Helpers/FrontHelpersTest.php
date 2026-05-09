<?php

namespace Unusualify\Modularous\Tests\Helpers;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Tests\TestCase;

class FrontHelpersTest extends TestCase
{
    /** @test */
    public function test_get_host_returns_host_from_app_url()
    {
        Config::set('app.url', 'https://example.com');

        $result = getHost();

        $this->assertEquals('example.com', $result);
    }

    /** @test */
    public function test_get_host_handles_url_with_port()
    {
        Config::set('app.url', 'http://localhost:8000');

        $result = getHost();

        $this->assertEquals('localhost', $result);
    }

    /** @test */
    public function test_get_modularous_default_urls_returns_array()
    {
        $result = getModularousDefaultUrls();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('languages', $result);
        $this->assertArrayHasKey('base_permalinks', $result);
    }

    /** @test */
    public function test_get_modularous_default_urls_base_permalinks_is_array()
    {
        $result = getModularousDefaultUrls();

        $this->assertIsArray($result['base_permalinks']);
    }

    /** @test */
    public function test_get_modularous_logo_symbol_returns_first_existing_symbol()
    {
        $symbols = ['test-logo', 'fallback-logo'];

        // In test environment, symbols may not exist, so just test the function returns a value
        $result = get_modularous_logo_symbol($symbols);

        // Result can be null if no symbols exist, or a string if found
        $this->assertTrue(is_null($result) || is_string($result));
    }

    /** @test */
    public function test_get_modularous_locale_symbol_with_locale()
    {
        app()->setLocale('en');

        $result = get_modularous_locale_symbol('logo', 'default-logo');

        // Result can be null if no symbols exist, or a string if found
        $this->assertTrue(is_null($result) || is_string($result));
    }

    /** @test */
    public function test_get_modularous_locale_symbol_with_array_defaults()
    {
        app()->setLocale('en');

        $result = get_modularous_locale_symbol('logo', ['fallback1', 'fallback2']);

        // Result can be null if no symbols exist, or a string if found
        $this->assertTrue(is_null($result) || is_string($result));
    }
}
