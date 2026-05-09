<?php

namespace Unusualify\Modularous\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularous\Entities\Tagged;
use Unusualify\Modularous\Tests\ModelTestCase;

class TaggedTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_tagged()
    {
        $tagged = new Tagged;
        $this->assertEquals(modularousConfig('tables.tagged', 'tagged'), $tagged->getTable());
    }
}
