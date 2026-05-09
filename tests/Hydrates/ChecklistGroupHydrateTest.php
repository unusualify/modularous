<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\ChecklistGroupHydrate;
use Unusualify\Modularous\Tests\TestCase;

class ChecklistGroupHydrateTest extends TestCase
{
    public function test_checklist_group_hydrate_sets_type_and_filters_schema()
    {
        $input = [
            'type' => 'checklist-group',
            'name' => 'groups',
            'schema' => [
                [
                    'name' => 'group1',
                    'items' => [['id' => 1, 'name' => 'Item 1']],
                ],
                [
                    'name' => 'group2',
                    'items' => [], // Should be filtered out
                ],
                [
                    'name' => 'group3', // No items key, should be filtered out
                ],
            ],
        ];

        $h = new ChecklistGroupHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-checklist-group', $result['type']);
        $this->assertCount(1, $result['schema']);
        $this->assertEquals('group1', array_values($result['schema'])[0]['name']);
    }
}
