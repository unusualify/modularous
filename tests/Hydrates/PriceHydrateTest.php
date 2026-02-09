<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\PriceHydrate;
use Unusualify\Modularity\Tests\TestCase;

class PriceHydrateTest extends TestCase
{
    public function test_price_hydrate_can_be_instantiated()
    {
        $input = [
            'type' => 'price',
            'name' => 'prices',
            'default' => 10.0
        ];

        $h = new PriceHydrate($input, null, null, true);

        // Just verify the object was created
        $this->assertInstanceOf(PriceHydrate::class, $h);
    }
}
