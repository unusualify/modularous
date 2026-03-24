<?php

namespace Unusualify\Modularity\Tests\Traits;

use Illuminate\Support\Facades\File;
use TestModules\TestModule\Repositories\ItemRepository;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Tests\MockModuleManager;
use Unusualify\Modularity\Tests\TestModulesCase;
use Unusualify\Modularity\Traits\ResolveConnector;

class ResolveConnectorTest extends TestModulesCase
{
    protected function setUp(): void
    {
        parent::setUp();

        MockModuleManager::initialize();

        // Only enable TestModule for fixture-based tests
        $statusFilePath = config('modules.activators.modularity.statuses-file');
        File::put($statusFilePath, json_encode(['TestModule' => true], JSON_PRETTY_PRINT));

        $module = MockModuleManager::getTestModule();
        $statusesFile = $module->getDirectoryPath('routes_statuses.json');
        if (! is_file($statusesFile)) {
            file_put_contents($statusesFile, '{}');
        }
        file_put_contents($statusesFile, json_encode(['Item' => true], JSON_PRETTY_PRINT));
    }

    protected function createTester(): object
    {
        return new class
        {
            use ResolveConnector;

            public function runFindConnectorRepository($connector)
            {
                return $this->findConnectorRepository($connector);
            }

            public function runFindNewConnectorRepository($connector)
            {
                return $this->findNewConnectorRepository($connector);
            }
        };
    }

    public function test_find_connector_repository_returns_repository_from_module(): void
    {
        $mockModule = \Mockery::mock();
        $mockRepo = \Mockery::mock(Repository::class);
        $mockModule->shouldReceive('getRepository')->with('Payment')->once()->andReturn($mockRepo);

        Modularity::shouldReceive('findOrFail')->with('SystemPayment')->once()->andReturn($mockModule);

        $tester = $this->createTester();
        $result = $tester->runFindConnectorRepository('SystemPayment:Payment');

        $this->assertSame($mockRepo, $result);
    }

    public function test_find_new_connector_repository_returns_repository_via_connector(): void
    {
        $mockRepo = \Mockery::mock(Repository::class);
        Modularity::shouldReceive('hasModule')->with('SystemPayment')->andReturn(true);
        Modularity::shouldReceive('find')->with('SystemPayment')->andReturnUsing(function () use ($mockRepo) {
            $module = \Mockery::mock();
            $module->shouldReceive('hasRoute')->with('Payment')->andReturn(true);
            $module->shouldReceive('getRepository')->with('Payment', true)->andReturn($mockRepo);

            return $module;
        });

        $tester = $this->createTester();
        $result = $tester->runFindNewConnectorRepository('SystemPayment|Payment');

        $this->assertSame($mockRepo, $result);
    }

    public function test_find_connector_repository_returns_item_repository_from_test_module(): void
    {
        $tester = $this->createTester();
        $result = $tester->runFindConnectorRepository('TestModule:Item');

        $this->assertInstanceOf(ItemRepository::class, $result);
    }

    public function test_find_new_connector_repository_returns_item_repository_from_test_module(): void
    {
        $tester = $this->createTester();
        $result = $tester->runFindNewConnectorRepository('TestModule|Item');

        $this->assertInstanceOf(ItemRepository::class, $result);
    }
}
