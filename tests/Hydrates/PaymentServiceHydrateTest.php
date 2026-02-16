<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\PaymentServiceHydrate;
use Unusualify\Modularity\Tests\TestCase;
use Mockery as m;

class PaymentServiceHydrateTest extends TestCase
{
    public function test_payment_service_hydrate_instantiation()
    {
        $input = [
            'type' => 'payment-service',
            'name' => 'payment_method'
        ];

        $h = new PaymentServiceHydrate($input, null, null, true);

        $this->assertInstanceOf(PaymentServiceHydrate::class, $h);
    }

    public function test_payment_service_hydrate_has_requirements()
    {
        $input = [
            'type' => 'payment-service',
            'name' => 'payment'
        ];

        $h = new PaymentServiceHydrate($input, null, null, true);

        $this->assertIsArray($h->requirements);
        $this->assertArrayHasKey('itemValue', $h->requirements);
        $this->assertArrayHasKey('itemTitle', $h->requirements);
        $this->assertArrayHasKey('default', $h->requirements);
    }
}
