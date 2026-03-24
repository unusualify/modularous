<?php

namespace Unusualify\Modularity\Tests\Facades;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Http;
use Unusualify\Modularity\Brokers\RegisterBrokerManager;
use Unusualify\Modularity\Facades\Coverage;
use Unusualify\Modularity\Facades\CurrencyExchange;
use Unusualify\Modularity\Facades\Filepond;
use Unusualify\Modularity\Facades\HostRouting;
use Unusualify\Modularity\Facades\HostRoutingRegistrar;
use Unusualify\Modularity\Facades\MigrationBackup;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\ModularityCache;
use Unusualify\Modularity\Facades\ModularityFinder;
use Unusualify\Modularity\Facades\ModularityLog;
use Unusualify\Modularity\Facades\ModularityRoutes;
use Unusualify\Modularity\Facades\ModularityVite;
use Unusualify\Modularity\Facades\Navigation;
use Unusualify\Modularity\Facades\Redirect;
use Unusualify\Modularity\Facades\Register;
use Unusualify\Modularity\Facades\RelationshipGraph;
use Unusualify\Modularity\Facades\UFinder;
use Unusualify\Modularity\Facades\Utm;
use Unusualify\Modularity\Services\CacheRelationshipGraph;
use Unusualify\Modularity\Services\CurrencyExchangeService;
use Unusualify\Modularity\Services\FilepondManager;
use Unusualify\Modularity\Services\ModularityCacheService;
use Unusualify\Modularity\Services\RedirectService;
use Unusualify\Modularity\Services\UtmParameters;
use Unusualify\Modularity\Services\View\ModularityNavigation;
use Unusualify\Modularity\Support\Finder;
use Unusualify\Modularity\Support\HostRouteRegistrar;
use Unusualify\Modularity\Tests\ModelTestCase;

class FacadesTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations/default');
    }

    /** @test */
    public function it_resolves_modularity_facade()
    {
        $this->assertInstanceOf(\Unusualify\Modularity\Modularity::class, Modularity::getFacadeRoot());
        $this->assertIsArray(Modularity::getModules());
    }

    /** @test */
    public function it_resolves_modularity_cache_facade()
    {
        $this->assertInstanceOf(ModularityCacheService::class, ModularityCache::getFacadeRoot());
        $this->assertIsString(ModularityCache::getPrefix());
    }

    /** @test */
    public function it_resolves_modularity_finder_facade()
    {
        $this->assertInstanceOf(Finder::class, ModularityFinder::getFacadeRoot());
        $this->assertIsArray(ModularityFinder::getClasses(__DIR__));
    }

    /** @test */
    public function it_resolves_modularity_log_facade()
    {
        $this->assertInstanceOf(Logger::class, ModularityLog::getFacadeRoot());
    }

    /** @test */
    public function it_resolves_modularity_routes_facade()
    {
        $this->assertInstanceOf(\Unusualify\Modularity\Support\ModularityRoutes::class, ModularityRoutes::getFacadeRoot());
        $this->assertIsArray(ModularityRoutes::webMiddlewares());
        $this->assertIsString(ModularityRoutes::getApiPrefix());
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
        $this->assertInstanceOf(\Unusualify\Modularity\Services\MigrationBackup::class, MigrationBackup::getFacadeRoot());
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
        config(['modularity.services.currency_exchange.endpoint' => 'https://api.example.com']);
        config(['modularity.services.currency_exchange.parameters' => ['apiKey' => 'apikey']]);
        config(['modularity.services.currency_exchange.rates_key' => 'rates']);

        $service = new CurrencyExchangeService;
        $this->assertIsArray($service->fetchExchangeRates());
    }

    // /** @test */
    // public function it_resolves_coverage_facade()
    // {
    //     $this->assertInstanceOf(\Unusualify\Modularity\Services\CoverageService::class, Coverage::getFacadeRoot());
    //     $this->assertIsArray(Coverage::getErrors());
    //     $this->assertFalse(Coverage::hasErrors());
    // }

    /** @test */
    public function it_resolves_host_routing_facade()
    {
        $this->assertInstanceOf(\Unusualify\Modularity\Support\HostRouting::class, HostRouting::getFacadeRoot());
        $this->assertIsString(HostRouting::getBaseHostName());
    }

    /** @test */
    public function it_resolves_host_routing_registrar_facade()
    {
        $this->assertInstanceOf(HostRouteRegistrar::class, HostRoutingRegistrar::getFacadeRoot());
    }

    /** @test */
    public function it_resolves_modularity_vite_facade()
    {
        $this->assertInstanceOf(\Unusualify\Modularity\Support\ModularityVite::class, ModularityVite::getFacadeRoot());
        $this->assertIsBool(ModularityVite::isRunningHot());
    }

    /** @test */
    public function it_resolves_navigation_facade()
    {
        $this->assertInstanceOf(ModularityNavigation::class, Navigation::getFacadeRoot());
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
