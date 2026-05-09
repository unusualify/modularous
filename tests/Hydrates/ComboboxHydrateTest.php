<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\ComboboxHydrate;
use Unusualify\Modularous\Tests\TestCase;

class ComboboxHydrateTest extends TestCase
{
    public function test_combobox_hydrate_sets_defaults_and_handles_select_scroll()
    {
        $input = [
            'type' => 'combobox',
            'name' => 'tags',
        ];

        $h = new ComboboxHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('id', $result['itemValue']);
        $this->assertEquals('name', $result['itemTitle']);
        $this->assertNull($result['default']);
    }

    public function test_default_array_is_converted_to_null_when_not_multiple()
    {
        $input = [
            'type' => 'combobox',
            'default' => [],
        ];

        $h = new ComboboxHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertNull($result['default']);
    }
}
