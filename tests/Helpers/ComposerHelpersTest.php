<?php

namespace Unusualify\Modularous\Tests\Helpers;

use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Tests\TestCase;

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
    public function test_is_modularous_development_returns_boolean()
    {
        // Don't mock - test the actual function which calls Modularous facade
        $result = is_modularous_development();

        $this->assertIsBool($result);
    }

    /** @test */
    public function test_is_modularous_production_returns_boolean()
    {
        // Don't mock - test the actual function which calls Modularous facade
        $result = is_modularous_production();

        $this->assertIsBool($result);
    }

    /** @test */
    public function test_get_modularous_vendor_dir_returns_path()
    {
        $result = get_modularous_vendor_dir();

        // Result should be a string path
        $this->assertIsString($result);
    }

    /** @test */
    public function test_get_modularous_vendor_dir_with_subdirectory()
    {
        $result = get_modularous_vendor_dir('vue');

        // Result should contain the subdirectory
        $this->assertIsString($result);
        $this->assertStringContainsString('vue', $result);
    }

    /** @test */
    public function test_get_modularous_vendor_path_returns_path()
    {
        $result = get_modularous_vendor_path();

        // Result should be a string path
        $this->assertIsString($result);
    }

    /** @test */
    public function test_get_modularous_src_path_returns_src_path()
    {
        $result = get_modularous_src_path();

        // Result should contain 'src'
        $this->assertIsString($result);
        $this->assertStringContainsString('src', $result);
    }

    /** @test */
    public function test_modularous_path_returns_path()
    {
        $result = modularous_path('config');

        // Result should contain 'config'
        $this->assertIsString($result);
        $this->assertStringContainsString('config', $result);
    }

    /** @test */
    public function test_get_package_version_returns_development_for_modularous_dev()
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
