<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\RelationshipsHydrate;
use Unusualify\Modularity\Tests\TestCase;

class RelationshipsHydrateTest extends TestCase
{
    public function test_relationships_hydrate_test_incomplete()
    {
        // RelationshipsHydrate has dd() call in hydrate() - incomplete implementation
        $this->markTestIncomplete('RelationshipsHydrate is incomplete - uses dd() in hydrate()');
    }
}
