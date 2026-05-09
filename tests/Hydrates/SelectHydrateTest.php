<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\SelectHydrate;
use Unusualify\Modularous\Tests\TestCase;

class SelectHydrateTest extends TestCase
{
    public function test_select_hydrate_sets_defaults()
    {
        $input = [
            'type' => 'select',
            'name' => 'category',
        ];

        $h = new SelectHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('id', $result['itemValue']);
        $this->assertEquals('name', $result['itemTitle']);
        $this->assertNull($result['default']);
        $this->assertFalse($result['returnObject']);
    }
}
