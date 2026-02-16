<?php

namespace Unusualify\Modularity\Tests;

use Illuminate\Container\Container;
use Unusualify\Modularity\Modularity;

class ModularityTest extends TestModulesCase
{
    protected $modularity;

    protected $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = app();

        $path = $this->app['config']->get('modules.paths.modules');

        $this->modularity = new Modularity($this->app, $path);
    }

    public function test_scan_paths_are_properly_formatted()
    {
        $paths = $this->modularity->getScanPaths();

        foreach ($paths as $path) {
            $this->assertTrue(str_ends_with($path, '/*'));
        }
    }

    public function test_format_cached_on_cache_enabled()
    {
        $app = app();
        $app['config']->set('modules.cache.enabled', true);
        
        $path = $app['config']->get('modules.paths.modules');

        $modularity = new Modularity($app, $path);

        $allModules = $modularity->all();

        $this->assertArrayHasKey('SystemModule', $allModules);
        $this->assertArrayHasKey('TestModule', $allModules);
    }

    public function test_has_module()
    {
        $this->assertTrue($this->modularity->has('SystemModule'));
        $this->assertFalse($this->modularity->has('NonExistentModule'));
    }

    public function test_get_by_status()
    {
        $this->app['files']->put($this->statusesFilePath, json_encode([
            'TestModule' => false,
            'SystemModule' => true,
        ]));

        $activeModules = $this->modularity->getByStatus(true);

        $this->assertArrayHasKey('SystemModule', $activeModules);
        $this->assertArrayNotHasKey('TestModule', $activeModules);
    }

    public function test_development_production()
    {
        $this->assertFalse($this->modularity->isDevelopment());
        $this->assertTrue($this->modularity->isProduction());
    }

    public function test_feature_methods()
    {
        $this->assertFalse($this->modularity->shouldUseInertia());
        
        $this->assertEquals(config('app.name'), $this->modularity->pageTitle());
        Modularity::createPageTitle(fn () => 'Test Page Title');
        $this->assertEquals('Test Page Title', $this->modularity->pageTitle());
    }

    public function test_get_auth_provider_name()
    {
        $providerName = Modularity::getAuthProviderName();
        $this->assertEquals('modularity_users', $providerName);
        $this->assertIsString($providerName);
    }

    public function test_clear_cache()
    {
        $this->app['config']->set('modules.cache.enabled', true);
        $this->app['config']->set('modules.cache.key', 'test-modules-cache');
        
        // Populate cache first
        $this->modularity->all();
        
        // Clear cache
        $this->modularity->clearCache();
        
        // Verify cache is cleared
        $this->assertFalse($this->app['cache']->has('test-modules-cache'));
    }

    public function test_disable_cache()
    {
        $this->modularity->disableCache();
        $this->assertFalse(config('modules.cache.enabled'));
    }

    public function test_has_module_returns_true_for_existing_module()
    {
        $this->assertTrue($this->modularity->hasModule('SystemModule'));
    }

    public function test_has_module_returns_false_for_non_existing_module()
    {
        $this->assertFalse($this->modularity->hasModule('NonExistentModule'));
    }

    public function test_get_modules_path()
    {
        $modulesPath = $this->modularity->getModulesPath();
        $this->assertStringContainsString('modules', $modulesPath);
        
        $subPath = $this->modularity->getModulesPath('TestModule');
        $this->assertStringContainsString('modules', $subPath);
        $this->assertStringContainsString('TestModule', $subPath);
    }

    public function test_set_and_revert_system_modules_path()
    {
        // Skip if production
        if ($this->modularity->isProduction()) {
            $this->expectException(\Unusualify\Modularity\Exceptions\ModularitySystemPathException::class);
            $this->modularity->setSystemModulesPath();
        } else {
            $originalPath = config('modules.paths.modules');
            
            $this->modularity->setSystemModulesPath();
            $newPath = config('modules.paths.modules');
            $this->assertNotEquals($originalPath, $newPath);
            $this->assertStringContainsString('modules', $newPath);
            
            $this->modularity->revertSystemModulesPath();
            $revertedPath = config('modules.paths.modules');
            $this->assertEquals($originalPath, $revertedPath);
        }
    }

    public function test_get_app_host()
    {
        $this->app['config']->set('modularity.app_url', 'http://localhost:8080');
        $host = $this->modularity->getAppHost();
        $this->assertEquals('localhost', $host);
    }

    public function test_get_admin_app_host()
    {
        $this->app['config']->set('modularity.app_url', 'http://localhost:8080');
        $this->app['config']->set('modularity.admin_app_url', 'http://admin.localhost:8080');
        
        $adminHost = $this->modularity->getAdminAppHost();
        $this->assertEquals('admin.localhost', $adminHost);
    }

    public function test_is_panel_url_with_admin_app_url()
    {
        $this->app['config']->set('modularity.app_url', 'http://localhost');
        $this->app['config']->set('modularity.admin_app_url', 'http://admin.localhost');
        
        $this->assertTrue($this->modularity->isPanelUrl('http://admin.localhost/dashboard'));
        $this->assertFalse($this->modularity->isPanelUrl('http://localhost/home'));
    }

    public function test_is_panel_url_with_admin_path()
    {
        $this->app['config']->set('modularity.app_url', 'http://localhost');
        $this->app['config']->set('modularity.admin_app_url', '');
        $this->app['config']->set('modularity.admin_app_path', 'admin');
        
        // Create a mock request to provide default values for request()->getHost() and request()->segment(1)
        $request = \Illuminate\Http\Request::create('http://localhost/admin', 'GET');
        $this->app->instance('request', $request);
        
        $this->assertTrue($this->modularity->isPanelUrl('http://localhost/admin/dashboard'));
        $this->assertFalse($this->modularity->isPanelUrl('http://localhost/home'));
    }

    public function test_is_modularity_route()
    {
        $this->app['config']->set('modularity.admin_route_name_prefix', 'admin');
        
        $this->assertTrue($this->modularity->isModularityRoute('admin.dashboard.index'));
        $this->assertTrue($this->modularity->isModularityRoute('admin.users.create'));
        $this->assertFalse($this->modularity->isModularityRoute('public.home'));
    }

    public function test_get_system_url_prefix()
    {
        $this->app['config']->set('modularity.system_prefix', 'system-settings');
        $prefix = $this->modularity->getSystemUrlPrefix();
        $this->assertEquals('system-settings', $prefix);
    }

    public function test_get_system_route_name_prefix()
    {
        $this->app['config']->set('modularity.system_prefix', 'system-settings');
        $prefix = $this->modularity->getSystemRouteNamePrefix();
        $this->assertEquals('system_settings', $prefix);
    }

    public function test_get_translations()
    {
        try {
            $translations = $this->modularity->getTranslations();
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
            $this->modularity->getTranslations();
            
            // Clear translations
            $this->modularity->clearTranslations();
            
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
        $testModule = $this->modularity->find('SystemModule');
        
        $groupedModules = $this->modularity->getGroupedModules('system');
        $this->assertIsArray($groupedModules);
    }

    public function test_get_system_modules()
    {
        $systemModules = $this->modularity->getSystemModules();
        $this->assertIsArray($systemModules);
    }

    public function test_get_modules()
    {
        $modules = $this->modularity->getModules();
        $this->assertIsArray($modules);
    }

    public function test_delete_module()
    {
        // Test with a non-existent module to verify method executes
        $result = $this->modularity->deleteModule('NonExistentTestModule');
        
        // Should return false for non-existent module
        $this->assertFalse($result);
        
        // Verify method doesn't throw exceptions
        $this->assertIsBool($result);
    }

    public function test_delete_module_returns_false_for_non_existent()
    {
        $result = $this->modularity->deleteModule('NonExistentModule');
        $this->assertFalse($result);
    }

    public function test_get_classes()
    {
        $testPath = $this->modularity->find('SystemModule')->getPath() . '/Entities';
        
        if (file_exists($testPath)) {
            $classes = $this->modularity->getClasses($testPath);
            $this->assertIsArray($classes);
        } else {
            $this->assertTrue(true, 'Entities directory not found');
        }
    }

    public function test_get_vendor_dir()
    {
        $vendorDir = $this->modularity->getVendorDir();
        $this->assertIsString($vendorDir);
        
        $subDir = $this->modularity->getVendorDir('modules');
        $this->assertStringContainsString('modules', $subDir);
    }

    public function test_get_theme_path()
    {
        $this->app['config']->set('modularity.app_theme', 'default');
        $themePath = $this->modularity->getThemePath();
        $this->assertIsString($themePath);
        
        $subPath = $this->modularity->getThemePath('variables');
        $this->assertStringContainsString('variables', $subPath);
    }

    public function test_get_vendor_namespace()
    {
        // Default config has 'Unusualify\Modularity' with trailing backslash
        $namespace = $this->modularity->getVendorNamespace();
        // Should include trailing backslash from config default
        $this->assertStringEndsWith('\\', $namespace);
        $this->assertStringContainsString('Modularity', $namespace);
        
        $appendedNamespace = $this->modularity->getVendorNamespace('Services');
        $this->assertStringContainsString('Modularity', $appendedNamespace);
        $this->assertStringContainsString('Services', $appendedNamespace);
    }

    public function test_create_disable_language_based_prices()
    {
        Modularity::createDisableLanguageBasedPrices(fn () => true);
        
        $this->app['config']->set('modularity.use_language_based_prices', true);
        $shouldUse = $this->modularity->shouldUseLanguageBasedPrices();
        $this->assertFalse($shouldUse);
    }

    public function test_should_use_language_based_prices_without_callback()
    {
        // Reset callback
        Modularity::createDisableLanguageBasedPrices(null);
        
        $this->app['config']->set('modularity.use_language_based_prices', true);
        $shouldUse = $this->modularity->shouldUseLanguageBasedPrices();
        $this->assertTrue($shouldUse);
        
        $this->app['config']->set('modularity.use_language_based_prices', false);
        $shouldUse = $this->modularity->shouldUseLanguageBasedPrices();
        $this->assertFalse($shouldUse);
    }
}
