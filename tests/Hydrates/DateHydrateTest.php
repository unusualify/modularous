<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\DateHydrate;
use Unusualify\Modularous\Tests\TestCase;

class DateHydrateTest extends TestCase
{
    public function test_date_hydrate_sets_type()
    {
        $input = [
            'type' => 'date',
            'name' => 'published_at',
        ];

        $h = new DateHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-date', $result['type']);
    }
}
