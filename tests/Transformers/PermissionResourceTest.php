<?php

namespace Unusualify\Modularity\Tests\Transformers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Transformers\PermissionResource;

class PermissionResourceTest extends TestCase
{
    public function test_to_array_returns_parent_array()
    {
        $permission = new class implements Arrayable
        {
            public function toArray(): array
            {
                return [
                    'id' => 1,
                    'name' => 'test-permission',
                    'guard_name' => 'web',
                ];
            }
        };

        $resource = new PermissionResource($permission);
        $request = Request::create('/');

        $result = $resource->toArray($request);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('test-permission', $result['name']);
        $this->assertEquals('web', $result['guard_name']);
    }

    public function test_to_array_with_array_input()
    {
        $permission = [
            'id' => 2,
            'name' => 'view',
            'guard_name' => 'web',
        ];

        $resource = new PermissionResource($permission);
        $request = Request::create('/');

        $result = $resource->toArray($request);

        $this->assertIsArray($result);
        $this->assertEquals(2, $result['id']);
        $this->assertEquals('view', $result['name']);
    }
}
