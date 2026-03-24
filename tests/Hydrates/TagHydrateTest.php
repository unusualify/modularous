<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Illuminate\Support\Facades\App;
use Mockery as m;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Hydrates\Inputs\TagHydrate;
use Unusualify\Modularity\Tests\TestCase;

class TagHydrateTest extends TestCase
{
    public function test_tag_hydrate_sets_type_and_defaults()
    {
        $input = [
            'type' => 'tag',
            'name' => 'tags',
            '_moduleName' => 'TestModule',
            '_routeName' => 'testRoute',
        ];

        $repositoryMock = m::mock();
        $repositoryMock->shouldReceive('getTags')->andReturn(
            collect([['id' => 1, 'name' => 'tag1']])
        );
        $repositoryMock->shouldReceive('getModel')->andReturn(new class
        {
            public function __toString()
            {
                return 'TagModel';
            }
        });

        $moduleMock = m::mock();
        $moduleMock->shouldReceive('getRouteClass')->with('testRoute', 'repository')->andReturn(get_class($repositoryMock));
        $moduleMock->shouldReceive('getRouteActionUrl')->andReturn('/tags');

        Modularity::shouldReceive('find')
            ->with('TestModule')
            ->andReturn($moduleMock);

        App::shouldReceive('make')
            ->andReturn($repositoryMock);

        $h = new TagHydrate($input, null, null, false);
        $result = $h->render();

        $this->assertEquals('input-tag', $result['type']);
        $this->assertFalse($result['returnObject']);
        $this->assertFalse($result['chips']);
    }
}
