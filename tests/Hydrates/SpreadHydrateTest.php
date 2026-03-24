<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Illuminate\Support\Facades\App;
use Mockery as m;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Hydrates\Inputs\SpreadHydrate;
use Unusualify\Modularity\Tests\TestCase;

class SpreadHydrateTest extends TestCase
{
    public function test_spread_hydrate_sets_type_and_reserved_keys()
    {
        $input = [
            'type' => 'spread',
            'name' => 'spread_data',
            '_moduleName' => 'TestModule',
            '_routeName' => 'testRoute',
        ];

        // Mock model with all required methods
        $modelMock = m::mock();
        $modelMock->shouldReceive('getReservedKeys')->andReturn(['id', 'created_at', 'updated_at']);
        $modelMock->shouldReceive('getRouteInputs')->andReturn([
            ['name' => 'title', 'spreadable' => true],
            ['name' => 'slug', 'spreadable' => false],
        ]);
        $modelMock->shouldReceive('getSpreadableSavingKey')->andReturn('spread_data');

        $moduleMock = m::mock();
        $moduleMock->shouldReceive('getRouteClass')->with('testRoute', 'model')->andReturn(get_class($modelMock));

        Modularity::shouldReceive('find')
            ->with('TestModule')
            ->andReturn($moduleMock);

        App::shouldReceive('make')
            ->with(get_class($modelMock))
            ->andReturn($modelMock);

        $h = new SpreadHydrate($input, null, null, true);
        $result = $h->render();

        $this->assertEquals('input-spread', $result['type']);
        $this->assertEquals('spread_data', $result['name']);
        $this->assertArrayHasKey('col', $result);
        $this->assertEquals(12, $result['col']['cols']);
        $this->assertArrayHasKey('reservedKeys', $result);
    }
}
