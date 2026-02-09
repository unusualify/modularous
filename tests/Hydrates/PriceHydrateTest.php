<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\PriceHydrate;
use Unusualify\Modularity\Tests\TestCase;

class PriceHydrateTest extends TestCase
{
    public function test_price_hydrate_test_incomplete()
    {
        // PriceHydrate requires mocking static Request::getUserCurrency(),
        // Currency model queries, and external SystemPricing module.
        // This test is marked incomplete until proper integration test setup is available.
        $this->markTestIncomplete('PriceHydrateTest requires Request facade and external modules mocking');
    }
}
