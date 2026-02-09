<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\FormTabsHydrate;
use Unusualify\Modularity\Tests\TestCase;

class FormTabsHydrateTest extends TestCase
{
    public function test_form_tabs_hydrate_sets_type_and_default()
    {
        $input = [
            'type' => 'form-tabs',
            'name' => 'tabs',
            'schema' => []
        ];

        $h = new FormTabsHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-form-tabs', $result['type']);
        $this->assertEquals(new \stdClass(), $result['default']);
        $this->assertEquals([], $result['eagers']);
        $this->assertEquals([], $result['lazy']);
    }

    public function test_form_tabs_hydrate_collects_eagers_and_lazy()
    {
        $input = [
            'type' => 'form-tabs',
            'name' => 'tabs',
            'schema' => [
                [
                    'type' => 'checklist',
                    'name' => 'checklist1',
                    'itemValue' => 'id',
                    'itemTitle' => 'name'
                ],
                [
                    'type' => 'select',
                    'name' => 'select1',
                    'lazy' => ['relation1', 'relation2'],
                    'itemValue' => 'id',
                    'itemTitle' => 'name'
                ],
                [
                    'type' => 'input-comparison-table',
                    'name' => 'table1',
                    'comparators' => [
                        'comp1' => ['eager' => ['eager1']],
                        'comp2' => ['lazy' => ['lazy1']]
                    ]
                ]
            ]
        ];

        $h = new FormTabsHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertIsArray($result['eagers']);
        $this->assertIsArray($result['lazy']);
        $this->assertContains('tabs.checklist1', $result['eagers']);
    }
}
