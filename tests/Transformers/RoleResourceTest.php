<?php

namespace Unusualify\Modularity\Tests\Transformers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Transformers\RoleResource;

class RoleResourceTest extends TestCase
{
    public function test_to_array_returns_parent_array()
    {
        $role = new class implements Arrayable
        {
            public function toArray(): array
            {
                return [
                    'id' => 1,
                    'name' => 'Admin',
                    'guard_name' => 'web',
                ];
            }
        };

        $resource = new RoleResource($role);
        $request = Request::create('/');

        $result = $resource->toArray($request);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Admin', $result['name']);
        $this->assertEquals('web', $result['guard_name']);
    }

    public function test_to_array_with_array_input()
    {
        $role = [
            'id' => 2,
            'name' => 'Publisher',
            'guard_name' => 'web',
        ];

        $resource = new RoleResource($role);
        $request = Request::create('/');

        $result = $resource->toArray($request);

        $this->assertIsArray($result);
        $this->assertEquals(2, $result['id']);
        $this->assertEquals('Publisher', $result['name']);
    }
}
