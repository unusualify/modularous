<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\SpreadHydrate;
use Unusualify\Modularity\Tests\TestCase;

class SpreadHydrateTest extends TestCase
{
    public function test_spread_hydrate_sets_type_and_col()
    {
        $input = [
            'type' => 'spread',
            'name' => 'spread_data',
            '_moduleName' => 'TestModule',
            '_routeName' => 'testRoute'
        ];

        // Create a mock model with required methods
        $modelStub = new class {
            public function getReservedKeys() { return ['id', 'created_at']; }
            public function getRouteInputs() { return []; }
            public function getSpreadableSavingKey() { return 'spread'; }
        };

        $moduleStub = new class extends \Unusualify\Modularity\Module {
            public function __construct() {}
            public function getRouteClass(string $routeName, string $target, bool $asClass = false): string {
                return '';
            }
        };

        \Unusualify\Modularity\Facades\Modularity::shouldReceive('find')
            ->andReturn($moduleStub);

        \Illuminate\Support\Facades\App::shouldReceive('make')
            ->andReturn($modelStub);

        $h = new SpreadHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-spread', $result['type']);
        $this->assertArrayHasKey('col', $result);
        $this->assertEquals(12, $result['col']['cols']);
    }
}
