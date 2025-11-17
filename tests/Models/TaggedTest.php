<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Unusualify\Modularity\Entities\State;
use Unusualify\Modularity\Entities\Tagged;
use Unusualify\Modularity\Tests\ModelTestCase;

class TaggedTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_tagged()
    {
        $tagged = new Tagged();
        $this->assertEquals(modularityConfig('tables.tagged', 'tagged'), $tagged->getTable());
    }
}
