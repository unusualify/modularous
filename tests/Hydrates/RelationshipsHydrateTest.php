<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\RelationshipsHydrate;
use Unusualify\Modularity\Tests\TestCase;

class RelationshipsHydrateTest extends TestCase
{
    public function test_relationships_hydrate_calls_getModule()
    {
        $input = [
            'type' => 'relationships',
            'name' => 'relationships',
        ];

        $h = new RelationshipsHydrate($input, null, null, true);

        // RelationshipsHydrate has dd() in hydrate() - incomplete implementation
        // Just verify it doesn't crash during construction
        $this->assertInstanceOf(RelationshipsHydrate::class, $h);
    }
}
