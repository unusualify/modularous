<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\SwitchHydrate;
use Unusualify\Modularity\Tests\TestCase;

class SwitchHydrateTest extends TestCase
{
    public function test_switch_hydrate_sets_defaults()
    {
        $input = [
            'type' => 'switch',
            'name' => 'is_active',
        ];

        $h = new SwitchHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('success', $result['color']);
        $this->assertEquals(1, $result['trueValue']);
        $this->assertEquals(0, $result['falseValue']);
        $this->assertTrue($result['hideDetails']);
        $this->assertEquals(1, $result['default']);
    }

    public function test_switch_hydrate_respects_custom_default()
    {
        $input = [
            'type' => 'switch',
            'name' => 'is_active',
            'default' => 0,
        ];

        $h = new SwitchHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals(0, $result['default']);
    }
}
