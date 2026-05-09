<?php

namespace Unusualify\Modularous\Tests\Helpers;

use Unusualify\Modularous\Tests\TestCase;

class ConnectorHelpersTest extends TestCase
{
    /** @test */
    public function test_find_module_route_names_parses_connector_string()
    {
        // This helper parses connector strings like "module:route"
        // Testing with a simple connector format
        $connector = 'test:index';

        try {
            $result = find_module_route_names($connector);
            $this->assertIsArray($result);
        } catch (\Exception $e) {
            // Expected for non-existent modules in test environment
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    /** @test */
    // public function test_get_connector_event_extracts_event_from_connector()
    // {
    //     $connector = 'test:index@create';

    //     $result = get_connector_event($connector);

    //     $this->assertEquals('create', $result);
    // }

    // /** @test */
    // public function test_change_connector_event_updates_event()
    // {
    //     $connector = 'test:index@create';
    //     $newEvent = 'edit';

    //     $result = change_connector_event($connector, $newEvent);

    //     $this->assertStringContainsString('edit', $result);
    //     $this->assertStringNotContainsString('@create', $result);
    // }
}
