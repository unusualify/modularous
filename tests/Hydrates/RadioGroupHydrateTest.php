<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\RadioGroupHydrate;
use Unusualify\Modularous\Tests\TestCase;

class RadioGroupHydrateTest extends TestCase
{
    public function test_radio_group_hydrate_sets_type_and_default()
    {
        $input = [
            'type' => 'radio-group',
            'name' => 'choice',
            'items' => [
                ['id' => 1, 'name' => 'Option 1'],
                ['id' => 2, 'name' => 'Option 2'],
            ],
        ];

        $h = new RadioGroupHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-radio-group', $result['type']);
        $this->assertEquals(1, $result['default']);
    }
}
