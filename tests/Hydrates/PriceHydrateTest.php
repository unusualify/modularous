<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\PriceHydrate;
use Unusualify\Modularity\Tests\TestCase;

class PriceHydrateTest extends TestCase
{
    public function test_price_hydrate_instantiation()
    {
        $input = [
            'type' => 'price',
            'name' => 'prices',
        ];

        $h = new PriceHydrate($input, null, null, true);

        $this->assertInstanceOf(PriceHydrate::class, $h);
    }

    public function test_price_hydrate_has_requirements()
    {
        $input = [
            'type' => 'price',
            'name' => 'prices',
        ];

        $h = new PriceHydrate($input, null, null, true);

        $this->assertIsArray($h->requirements);
        $this->assertArrayHasKey('name', $h->requirements);
        $this->assertArrayHasKey('col', $h->requirements);
        $this->assertEquals('prices', $h->requirements['name']);
    }
}
