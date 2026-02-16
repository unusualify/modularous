<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\JsonRepeaterHydrate;
use Unusualify\Modularity\Tests\TestCase;

class JsonRepeaterHydrateTest extends TestCase
{
    public function test_json_repeater_hydrate_sets_type_and_root()
    {
        $input = [
            'type' => 'json-repeater',
            'name' => 'items'
        ];

        $h = new JsonRepeaterHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-repeater', $result['type']);
        $this->assertEquals('json-repeater', $result['root']);
    }
}
