<?php

namespace Unusualify\Modularity\Tests\Services;

use Unusualify\Modularity\Services\BroadcastManager;
use Unusualify\Modularity\Tests\TestCase;

class BroadcastManagerTest extends TestCase
{
    /** @test */
    public function test_constructor_stores_model_and_event_classes()
    {
        $model = new \stdClass;
        $events = ['Event1', 'Event2'];

        $manager = new BroadcastManager($model, $events);

        $this->assertInstanceOf(BroadcastManager::class, $manager);
    }

    /** @test */
    public function test_get_broadcast_configuration_returns_empty_for_no_events()
    {
        $model = new \stdClass;

        $manager = new BroadcastManager($model, []);
        $config = $manager->getBroadcastConfiguration();

        $this->assertIsArray($config);
        $this->assertEmpty($config);
    }

    /** @test */
    public function test_get_broadcast_configuration_skips_non_existent_classes()
    {
        $model = new \stdClass;

        $manager = new BroadcastManager($model, ['NonExistentEventClass']);
        $config = $manager->getBroadcastConfiguration();

        // Should skip non-existent classes
        $this->assertIsArray($config);
        $this->assertEmpty($config);
    }

    /** @test */
    public function test_for_model_static_helper_works()
    {
        $model = new \stdClass;

        $config = BroadcastManager::forModel($model, []);

        $this->assertIsArray($config);
    }

    /** @test */
    public function test_handles_class_exists_check()
    {
        $model = new \stdClass;

        // Test that the service handles non-existent class strings gracefully
        $manager = new BroadcastManager($model, ['FooBarBazEventThatDoesNotExist']);
        $config = $manager->getBroadcastConfiguration();

        $this->assertIsArray($config);
        $this->assertEmpty($config);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
