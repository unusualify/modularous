<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\TagHydrate;
use Unusualify\Modularity\Tests\TestCase;
use Mockery as m;

class TagHydrateTest extends TestCase
{
    public function test_tag_hydrate_sets_type_and_defaults()
    {
        $input = [
            'type' => 'tag',
            'name' => 'tags',
            '_moduleName' => 'TestModule',
            '_routeName' => 'testRoute'
        ];

        $repositoryMock = m::mock();
        $repositoryMock->shouldReceive('getTags')->andReturn(
            collect([['id' => 1, 'name' => 'tag1']])
        );
        $repositoryMock->shouldReceive('getModel')->andReturn(new class { public function __toString() { return 'TagModel'; }});

        $moduleMock = m::mock();
        $moduleMock->shouldReceive('getRouteClass')->with('testRoute', 'repository')->andReturn(get_class($repositoryMock));
        $moduleMock->shouldReceive('getRouteActionUrl')->andReturn('/tags');

        \Unusualify\Modularity\Facades\Modularity::shouldReceive('find')
            ->with('TestModule')
            ->andReturn($moduleMock);

        \Illuminate\Support\Facades\App::shouldReceive('make')
            ->andReturn($repositoryMock);

        $h = new TagHydrate($input, null, null, false);
        $result = $h->render();

        $this->assertEquals('input-tag', $result['type']);
        $this->assertFalse($result['returnObject']);
        $this->assertFalse($result['chips']);
    }
}
