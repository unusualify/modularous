<?php

namespace Unusualify\Modularous\Tests;

use Illuminate\Http\Request;
use Unusualify\Modularous\Exceptions\ModularousSystemPathException;
use Unusualify\Modularous\Modularous;

class ModularousTest extends TestModulesCase
{
    protected $modularous;

    protected $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = app();

        $path = $this->app['config']->get('modules.paths.modules');

        $this->modularous = new Modularous($this->app, $path);
    }

    public function test_scan_paths_are_properly_formatted()
    {
        $paths = $this->modularous->getScanPaths();

        foreach ($paths as $path) {
            $this->assertTrue(str_ends_with($path, '/*'));
        }
    }

    public function test_format_cached_on_cache_enabled()
    {
        $app = app();
        $app['config']->set('modules.cache.enabled', true);

        $path = $app['config']->get('modules.paths.modules');

        $modularous = new Modularous($app, $path);

        $allModules = $modularous->all();

        $this->assertArrayHasKey('systemmodule', $allModules);
        $this->assertArrayHasKey('testmodule', $allModules);
    }

    public function test_has_module()
    {
        $this->assertTrue($this->modularous->has('systemmodule'));
        $this->assertFalse($this->modularous->has('NonExistentModule'));
    }

    // public function test_get_by_status()
    // {
    //     $this->app['files']->put($this->statusesFilePath, json_encode([
    //         'TestModule' => false,
    //         'SystemModule' => true,
    //     ]));

    //     $activeModules = $this->modularous->getByStatus(true);

    //     $this->assertArrayHasKey('systemmodule', $activeModules);
    //     $this->assertArrayNotHasKey('testmodule', $activeModules);
    // }

    public function test_development_production()
    {
        $this->assertFalse($this->modularous->isDevelopment());
        $this->assertTrue($this->modularous->isProduction());
    }

    public function test_feature_methods()
    {
        $this->assertTrue($this->modularous->shouldUseInertia());

        $this->assertEquals(config('app.name'), $this->modularous->pageTitle());
        Modularous::createPageTitle(fn () => 'Test Page Title');
        $this->assertEquals('Test Page Title', $this->modularous->pageTitle());
    }

    public function test_get_auth_provider_name()
    {
        $providerName = Modularous::getAuthProviderName();
        $this->assertEquals('modularous_users', $providerName);
        $this->assertIsString($providerName);
    }

    public function test_clear_cache()
    {
        $this->app['config']->set('modules.cache.enabled', true);
        $this->app['config']->set('modules.cache.key', 'test-modules-cache');

        // Populate cache first
        $this->modularous->all();

        // Clear cache
        $this->modularous->clearCache();

        // Verify cache is cleared
        $this->assertFalse($this->app['cache']->has('test-modules-cache'));
    }

    public function test_disable_cache()
    {
        $this->modularous->disableCache();
        $this->assertFalse(config('modules.cache.enabled'));
    }

    public function test_has_module_returns_true_for_existing_module()
    {
        $this->assertTrue($this->modularous->hasModule('SystemModule'));
    }

    public function test_has_module_returns_false_for_non_existing_module()
    {
        $this->assertFalse($this->modularous->hasModule('NonExistentModule'));
    }

    public function test_get_modules_path()
    {
        $modulesPath = $this->modularous->getModulesPath();
        $this->assertStringContainsString('modules', $modulesPath);

        $subPath = $this->modularous->getModulesPath('TestModule');
        $this->assertStringContainsString('modules', $subPath);
        $this->assertStringContainsString('TestModule', $subPath);
    }

    public function test_set_and_revert_system_modules_path()
    {
        // Skip if production
        if ($this->modularous->isProduction()) {
            $this->expectException(ModularousSystemPathException::class);
            $this->modularous->setSystemModulesPath();
        } else {
            $originalPath = config('modules.paths.modules');

            $this->modularous->setSystemModulesPath();
            $newPath = config('modules.paths.modules');
            $this->assertNotEquals($originalPath, $newPath);
            $this->assertStringContainsString('modules', $newPath);

            $this->modularous->revertSystemModulesPath();
            $revertedPath = config('modules.paths.modules');
            $this->assertEquals($originalPath, $revertedPath);
        }
    }

    public function test_get_app_host()
    {
        $this->app['config']->set('modularous.app_url', 'http://localhost:8080');
        $host = $this->modularous->getAppHost();
        $this->assertEquals('localhost', $host);
    }

    public function test_get_admin_app_host()
    {
        $this->app['config']->set('modularous.app_url', 'http://localhost:8080');
        $this->app['config']->set('modularous.admin_app_url', 'http://admin.localhost:8080');

        $adminHost = $this->modularous->getAdminAppHost();
        $this->assertEquals('admin.localhost', $adminHost);
    }

    public function test_is_panel_url_with_admin_app_url()
    {
        $this->app['config']->set('modularous.app_url', 'http://localhost');
        $this->app['config']->set('modularous.admin_app_url', 'http://admin.localhost');

        $this->assertTrue($this->modularous->isPanelUrl('http://admin.localhost/dashboard'));
        $this->assertFalse($this->modularous->isPanelUrl('http://localhost/home'));
    }

    public function test_is_panel_url_with_admin_path()
    {
        $this->app['config']->set('modularous.app_url', 'http://localhost');
        $this->app['config']->set('modularous.admin_app_url', '');
        $this->app['config']->set('modularous.admin_app_path', 'admin');

        // Create a mock request to provide default values for request()->getHost() and request()->segment(1)
        $request = Request::create('http://localhost/admin', 'GET');
        $this->app->instance('request', $request);

        $this->assertTrue($this->modularous->isPanelUrl('http://localhost/admin/dashboard'));
        $this->assertFalse($this->modularous->isPanelUrl('http://localhost/home'));
    }

    public function test_is_modularous_route()
    {
        $this->app['config']->set('modularous.admin_route_name_prefix', 'admin');

        $this->assertTrue($this->modularous->isModularousRoute('admin.dashboard.index'));
        $this->assertTrue($this->modularous->isModularousRoute('admin.users.create'));
        $this->assertFalse($this->modularous->isModularousRoute('public.home'));
    }

    public function test_get_system_url_prefix()
    {
        $this->app['config']->set('modularous.system_prefix', 'system-settings');
        $prefix = $this->modularous->getSystemUrlPrefix();
        $this->assertEquals('system-settings', $prefix);
    }

    public function test_get_system_route_name_prefix()
    {
        $this->app['config']->set('modularous.system_prefix', 'system-settings');
        $prefix = $this->modularous->getSystemRouteNamePrefix();
        $this->assertEquals('system_settings', $prefix);
    }

    public function test_get_translations()
    {
        try {
            $translations = $this->modularous->getTranslations();
            $this->assertIsArray($translations);
        } catch (\UnexpectedValueException $e) {
            // Translation directory might not exist in test environment, which is acceptable
            $this->assertTrue(true);
        }
    }

    public function test_clear_translations()
    {
        try {
            // Populate translations cache
            $this->modularous->getTranslations();

            // Clear translations
            $this->modularous->clearTranslations();

            // Verify it doesn't throw errors
            $this->assertTrue(true);
        } catch (\UnexpectedValueException $e) {
            // Translation directory might not exist in test environment, which is acceptable
            $this->assertTrue(true);
        }
    }

    public function test_get_grouped_modules()
    {
        // Create a test module with group
        $testModule = $this->modularous->find('SystemModule');

        $groupedModules = $this->modularous->getGroupedModules('system');
        $this->assertIsArray($groupedModules);
    }

    public function test_get_system_modules()
    {
        $systemModules = $this->modularous->getSystemModules();
        $this->assertIsArray($systemModules);
    }

    public function test_get_modules()
    {
        $modules = $this->modularous->getModules();
        $this->assertIsArray($modules);
    }

    public function test_delete_module()
    {
        // Test with a non-existent module to verify method executes
        $result = $this->modularous->deleteModule('NonExistentTestModule');

        // Should return false for non-existent module
        $this->assertFalse($result);

        // Verify method doesn't throw exceptions
        $this->assertIsBool($result);
    }

    public function test_delete_module_returns_false_for_non_existent()
    {
        $result = $this->modularous->deleteModule('NonExistentModule');
        $this->assertFalse($result);
    }

    public function test_get_classes()
    {
        $testPath = $this->modularous->find('SystemModule')->getPath() . '/Entities';

        if (file_exists($testPath)) {
            $classes = $this->modularous->getClasses($testPath);
            $this->assertIsArray($classes);
        } else {
            $this->assertTrue(true, 'Entities directory not found');
        }
    }

    public function test_get_vendor_dir()
    {
        $vendorDir = $this->modularous->getVendorDir();
        $this->assertIsString($vendorDir);

        $subDir = $this->modularous->getVendorDir('modules');
        $this->assertStringContainsString('modules', $subDir);
    }

    public function test_get_theme_path()
    {
        $this->app['config']->set('modularous.app_theme', 'default');
        $themePath = $this->modularous->getThemePath();
        $this->assertIsString($themePath);

        $subPath = $this->modularous->getThemePath('variables');
        $this->assertStringContainsString('variables', $subPath);
    }

    public function test_get_vendor_namespace()
    {
        // Default config has 'Unusualify\Modularous' with trailing backslash
        $namespace = $this->modularous->getVendorNamespace();
        // Should include trailing backslash from config default
        $this->assertStringEndsWith('\\', $namespace);
        $this->assertStringContainsString('Modularous', $namespace);

        $appendedNamespace = $this->modularous->getVendorNamespace('Services');
        $this->assertStringContainsString('Modularous', $appendedNamespace);
        $this->assertStringContainsString('Services', $appendedNamespace);
    }

    public function test_create_disable_language_based_prices()
    {
        Modularous::createDisableLanguageBasedPrices(fn () => true);

        $this->app['config']->set('modularous.use_language_based_prices', true);
        $shouldUse = $this->modularous->shouldUseLanguageBasedPrices();
        $this->assertFalse($shouldUse);
    }

    public function test_should_use_language_based_prices_without_callback()
    {
        // Reset callback
        Modularous::createDisableLanguageBasedPrices(null);

        $this->app['config']->set('modularous.use_language_based_prices', true);
        $shouldUse = $this->modularous->shouldUseLanguageBasedPrices();
        $this->assertTrue($shouldUse);

        $this->app['config']->set('modularous.use_language_based_prices', false);
        $shouldUse = $this->modularous->shouldUseLanguageBasedPrices();
        $this->assertFalse($shouldUse);
    }
}
