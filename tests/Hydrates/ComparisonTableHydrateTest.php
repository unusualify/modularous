<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\ComparisonTableHydrate;
use Unusualify\Modularity\Tests\TestCase;

class ComparisonTableHydrateTest extends TestCase
{
    public function test_comparison_table_hydrate_sets_defaults()
    {
        $input = [
            'type' => 'comparison-table',
            'name' => 'comparison',
            'comparators' => []
        ];

        $h = new ComparisonTableHydrate($input, null, null, true);

        $result = $h->render();

        // ComparisonTableHydrate just passes through with afterHydrateRecords hook
        // Verify input structure is preserved
        $this->assertIsArray($result);
        $this->assertArrayHasKey('comparators', $result);
    }
}
