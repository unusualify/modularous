<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\PaymentServiceHydrate;
use Unusualify\Modularity\Tests\TestCase;

class PaymentServiceHydrateTest extends TestCase
{
    public function test_payment_service_hydrate_can_be_instantiated()
    {
        $input = [
            'type' => 'payment-service',
            'name' => 'payment_method'
        ];

        $h = new PaymentServiceHydrate($input, null, null, true);

        // Just verify the object was created
        $this->assertInstanceOf(PaymentServiceHydrate::class, $h);
    }
}
