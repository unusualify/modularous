<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\SelectScrollHydrate;
use Unusualify\Modularity\Tests\TestCase;

class SelectScrollHydrateTest extends TestCase
{
    public function test_select_scroll_hydrate_sets_type_and_defaults()
    {
        $input = [
            'type' => 'select-scroll',
            'name' => 'infinite_items',
            'endpoint' => '/api/scroll'
        ];

        $h = new SelectScrollHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-select-scroll', $result['type']);
        $this->assertEquals('v-autocomplete', $result['componentType']);
        $this->assertNull($result['default']);
        $this->assertTrue($result['noRecords']);
    }
}
