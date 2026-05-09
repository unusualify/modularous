<?php

namespace Unusualify\Modularous\Tests\Facades;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Http;
use Unusualify\Modularous\Brokers\RegisterBrokerManager;
use Unusualify\Modularous\Facades\Coverage;
use Unusualify\Modularous\Facades\CurrencyExchange;
use Unusualify\Modularous\Facades\Filepond;
use Unusualify\Modularous\Facades\HostRouting;
use Unusualify\Modularous\Facades\HostRoutingRegistrar;
use Unusualify\Modularous\Facades\MigrationBackup;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Facades\ModularousFinder;
use Unusualify\Modularous\Facades\ModularousLog;
use Unusualify\Modularous\Facades\ModularousRoutes;
use Unusualify\Modularous\Facades\ModularousVite;
use Unusualify\Modularous\Facades\Navigation;
use Unusualify\Modularous\Facades\Redirect;
use Unusualify\Modularous\Facades\Register;
use Unusualify\Modularous\Facades\RelationshipGraph;
use Unusualify\Modularous\Facades\UFinder;
use Unusualify\Modularous\Facades\Utm;
use Unusualify\Modularous\Services\CacheRelationshipGraph;
use Unusualify\Modularous\Services\CurrencyExchangeService;
use Unusualify\Modularous\Services\FilepondManager;
use Unusualify\Modularous\Services\ModularousCacheService;
use Unusualify\Modularous\Services\RedirectService;
use Unusualify\Modularous\Services\UtmParameters;
use Unusualify\Modularous\Services\View\ModularousNavigation;
use Unusualify\Modularous\Support\Finder;
use Unusualify\Modularous\Support\HostRouteRegistrar;
use Unusualify\Modularous\Tests\ModelTestCase;

class FacadesTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations/default');
    }

    /** @test */
    public function it_resolves_modularous_facade()
    {
        $this->assertInstanceOf(\Unusualify\Modularous\Modularous::class, Modularous::getFacadeRoot());
        $this->assertIsArray(Modularous::getModules());
    }

    /** @test */
    public function it_resolves_modularous_cache_facade()
    {
        $this->assertInstanceOf(ModularousCacheService::class, ModularousCache::getFacadeRoot());
        $this->assertIsString(ModularousCache::getPrefix());
    }

    /** @test */
    public function it_resolves_modularous_finder_facade()
    {
        $this->assertInstanceOf(Finder::class, ModularousFinder::getFacadeRoot());
        $this->assertIsArray(ModularousFinder::getClasses(__DIR__));
    }

    /** @test */
    public function it_resolves_modularous_log_facade()
    {
        $this->assertInstanceOf(Logger::class, ModularousLog::getFacadeRoot());
    }

    /** @test */
    public function it_resolves_modularous_routes_facade()
    {
        $this->assertInstanceOf(\Unusualify\Modularous\Support\ModularousRoutes::class, ModularousRoutes::getFacadeRoot());
        $this->assertIsArray(ModularousRoutes::webMiddlewares());
        $this->assertIsString(ModularousRoutes::getApiPrefix());
    }

    /** @test */
    public function it_resolves_register_facade()
    {
        $this->assertInstanceOf(RegisterBrokerManager::class, Register::getFacadeRoot());
        $this->assertIsString(Register::getDefaultDriver());
    }

    /** @test */
    public function it_resolves_migration_backup_facade()
    {
        $this->assertInstanceOf(\Unusualify\Modularous\Services\MigrationBackup::class, MigrationBackup::getFacadeRoot());
        $this->assertIsArray(MigrationBackup::getBackup());
    }

    /** @test */
    public function it_resolves_filepond_facade()
    {
        $this->assertInstanceOf(FilepondManager::class, Filepond::getFacadeRoot());
        // Workaround for ReflectionException: Class "void" does not exist in ManageEloquent
        config(['manage-eloquent.relations_namespace' => 'Illuminate\Database\Eloquent\Relations']);
        Filepond::clearFolders();
        $this->assertTrue(true); // Just to verify execution
    }

    /** @test */
    public function it_resolves_currency_exchange_facade()
    {
        $this->assertInstanceOf(CurrencyExchangeService::class, CurrencyExchange::getFacadeRoot());
        // Mock Http for CurrenyExchange
        Http::fake([
            '*' => Http::response(['rates' => ['USD' => 1.2]], 200),
        ]);
        // We need to set some config for CurrencyExchange to work
        config(['modularous.services.currency_exchange.endpoint' => 'https://api.example.com']);
        config(['modularous.services.currency_exchange.parameters' => ['apiKey' => 'apikey']]);
        config(['modularous.services.currency_exchange.rates_key' => 'rates']);

        $service = new CurrencyExchangeService;
        $this->assertIsArray($service->fetchExchangeRates());
    }

    // /** @test */
    // public function it_resolves_coverage_facade()
    // {
    //     $this->assertInstanceOf(\Unusualify\Modularous\Services\CoverageService::class, Coverage::getFacadeRoot());
    //     $this->assertIsArray(Coverage::getErrors());
    //     $this->assertFalse(Coverage::hasErrors());
    // }

    /** @test */
    public function it_resolves_host_routing_facade()
    {
        $this->assertInstanceOf(\Unusualify\Modularous\Support\HostRouting::class, HostRouting::getFacadeRoot());
        $this->assertIsString(HostRouting::getBaseHostName());
    }

    /** @test */
    public function it_resolves_host_routing_registrar_facade()
    {
        $this->assertInstanceOf(HostRouteRegistrar::class, HostRoutingRegistrar::getFacadeRoot());
    }

    /** @test */
    public function it_resolves_modularous_vite_facade()
    {
        $this->assertInstanceOf(\Unusualify\Modularous\Support\ModularousVite::class, ModularousVite::getFacadeRoot());
        $this->assertIsBool(ModularousVite::isRunningHot());
    }

    /** @test */
    public function it_resolves_navigation_facade()
    {
        $this->assertInstanceOf(ModularousNavigation::class, Navigation::getFacadeRoot());
        $this->assertIsArray(Navigation::modulesMenu());
    }

    /** @test */
    public function it_resolves_redirect_facade()
    {
        $this->assertInstanceOf(RedirectService::class, Redirect::getFacadeRoot());
        $url = 'https://example.com';
        Redirect::set($url);
        $this->assertEquals($url, Redirect::get());
        Redirect::clear();
        $this->assertNull(Redirect::get());
    }

    /** @test */
    public function it_resolves_relationship_graph_facade()
    {
        $this->assertInstanceOf(CacheRelationshipGraph::class, RelationshipGraph::getFacadeRoot());
        $this->assertIsBool(RelationshipGraph::isEnabled());
    }

    /** @test */
    public function it_resolves_u_finder_facade()
    {
        $this->assertInstanceOf(Finder::class, UFinder::getFacadeRoot());
        $this->assertIsArray(UFinder::getClasses(__DIR__));
    }

    /** @test */
    public function it_resolves_utm_facade()
    {
        $this->assertInstanceOf(UtmParameters::class, Utm::getFacadeRoot());
        $this->assertIsBool(Utm::isEnabled());
        $this->assertIsArray(Utm::getParameters());
    }
}
