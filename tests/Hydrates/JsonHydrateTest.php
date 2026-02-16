<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\JsonHydrate;
use Unusualify\Modularity\Tests\TestCase;

class JsonHydrateTest extends TestCase
{
    public function test_json_hydrate_sets_type_and_merges_col()
    {
        $input = [
            'type' => 'json',
            'name' => 'metadata',
            'col' => ['sm' => 6]
        ];

        $h = new JsonHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('group', $result['type']);
        $this->assertArrayHasKey('col', $result);
        $this->assertEquals(12, $result['col']['cols']);
        $this->assertEquals(6, $result['col']['sm']);
    }
}
