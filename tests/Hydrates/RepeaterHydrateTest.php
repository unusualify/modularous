<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\RepeaterHydrate;
use Unusualify\Modularity\Tests\TestCase;

class RepeaterHydrateTest extends TestCase
{
    public function test_repeater_hydrate_sets_type_and_root()
    {
        $input = [
            'type' => 'repeater',
            'name' => 'items',
            'schema' => [
                ['type' => 'input', 'name' => 'name'],
            ],
        ];

        $h = new RepeaterHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-repeater', $result['type']);
        $this->assertEquals('default', $result['root']);
        $this->assertTrue($result['autoIdGenerator']);
    }

    public function test_repeater_hydrate_sets_singular_label()
    {
        $input = [
            'type' => 'repeater',
            'name' => 'items',
            'label' => 'Product Items',
            'schema' => [],
        ];

        $h = new RepeaterHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('Product Item', $result['singularLabel']);
    }
}
