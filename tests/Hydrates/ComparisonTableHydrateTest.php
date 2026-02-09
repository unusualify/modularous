<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\ComparisonTableHydrate;
use Unusualify\Modularity\Tests\TestCase;

class ComparisonTableHydrateTest extends TestCase
{
    public function test_comparison_table_hydrate_preserves_structure()
    {
        $input = [
            'type' => 'comparison-table',
            'name' => 'comparison',
            'comparators' => [
                'comp1' => ['label' => 'Option 1'],
                'comp2' => ['label' => 'Option 2']
            ]
        ];

        $h = new ComparisonTableHydrate($input, null, null, true);
        $result = $h->render();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('comparators', $result);
        $this->assertCount(2, $result['comparators']);
    }

    public function test_comparison_table_hydrate_filters_empty_comparators()
    {
        $input = [
            'type' => 'comparison-table',
            'name' => 'comparison',
            'schema' => [
                ['name' => 'item1'],
                ['name' => 'item2']
            ]
        ];

        $h = new ComparisonTableHydrate($input, null, null, true);
        $result = $h->render();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('schema', $result);
    }
}
