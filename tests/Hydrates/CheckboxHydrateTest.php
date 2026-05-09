<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\CheckboxHydrate;
use Unusualify\Modularous\Tests\TestCase;

class CheckboxHydrateTest extends TestCase
{
    public function test_checkbox_hydrate_sets_defaults_and_type()
    {
        $input = [
            'type' => 'checkbox',
            'name' => 'is_active',
        ];

        $h = new CheckboxHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('success', $result['color']);
        $this->assertEquals(1, $result['trueValue']);
        $this->assertEquals(0, $result['falseValue']);
        $this->assertTrue($result['hideDetails']);
        $this->assertEquals(0, $result['default']);
    }
}
