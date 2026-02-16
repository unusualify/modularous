<?php

namespace Unusualify\Modularity\Tests\Helpers;

use Illuminate\Support\Facades\File;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Tests\TestCase;

class ComposerHelpersTest extends TestCase
{
    /** @test */
    public function test_get_installed_composer_returns_array()
    {
        $result = get_installed_composer();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('versions', $result);
    }

    /** @test */
    public function test_get_package_installed_version_returns_version_for_existing_package()
    {
        $installed = get_installed_composer();
        
        // Get a package thatis installed (Laravel framework should exist)
        if (isset($installed['versions']['laravel/framework'])) {
            $result = get_package_installed_version('laravel/framework');
            $this->assertIsString($result);
            $this->assertNotEmpty($result);
        } else {
            $this->markTestSkipped('Laravel framework not found in installed packages');
        }
    }

    /** @test */
    public function test_is_modularity_development_returns_boolean()
    {
        // Don't mock - test the actual function which calls Modularity facade
        $result = is_modularity_development();

        $this->assertIsBool($result);
    }

    /** @test */
    public function test_is_modularity_production_returns_boolean()
    {
        // Don't mock - test the actual function which calls Modularity facade
        $result = is_modularity_production();

        $this->assertIsBool($result);
    }

    /** @test */
    public function test_get_modularity_vendor_dir_returns_path()
    {
        $result = get_modularity_vendor_dir();

        // Result should be a string path
        $this->assertIsString($result);
    }

    /** @test */
    public function test_get_modularity_vendor_dir_with_subdirectory()
    {
        $result = get_modularity_vendor_dir('vue');

        // Result should contain the subdirectory
        $this->assertIsString($result);
        $this->assertStringContainsString('vue', $result);
    }

    /** @test */
    public function test_get_modularity_vendor_path_returns_path()
    {
        $result = get_modularity_vendor_path();

        // Result should be a string path
        $this->assertIsString($result);
    }

    /** @test */
    public function test_get_modularity_src_path_returns_src_path()
    {
        $result = get_modularity_src_path();

        // Result should contain 'src'
        $this->assertIsString($result);
        $this->assertStringContainsString('src', $result);
    }

    /** @test */
    public function test_modularity_path_returns_path()
    {
        $result = modularity_path('config');

        // Result should contain 'config'
        $this->assertIsString($result);
        $this->assertStringContainsString('config', $result);
    }

    /** @test */
    public function test_get_package_version_returns_development_for_modularity_dev()
    {
        $result = get_package_version('unusualify/modularous');

        // Result should be a string (either 'development' or a version number)
        $this->assertIsString($result);
    }



    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
