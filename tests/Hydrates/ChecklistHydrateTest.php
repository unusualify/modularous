<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\ChecklistHydrate;
use Unusualify\Modularity\Tests\TestCase;

class ChecklistHydrateTest extends TestCase
{
    public function test_checklist_hydrate_sets_type_and_defaults()
    {
        $input = [
            'type' => 'checklist',
            'name' => 'options',
        ];

        $h = new ChecklistHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-checklist', $result['type']);
        $this->assertEquals('id', $result['itemValue']);
        $this->assertEquals('name', $result['itemTitle']);
        $this->assertEquals([], $result['default']);
    }
}
