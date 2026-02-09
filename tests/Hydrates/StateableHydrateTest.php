<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\StateableHydrate;
use Unusualify\Modularity\Tests\TestCase;

class StateableHydrateTest extends TestCase
{
    public function test_stateable_hydrate_throws_without_module()
    {
        $input = [
            'type' => 'stateable',
        ];

        $h = new StateableHydrate($input, null, null, true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No Module");
        
        $h->render();
    }

    public function test_stateable_hydrate_throws_without_route_name()
    {
        $input = [
            'type' => 'stateable',
            '_moduleName' => 'TestModule'
        ];

        // Mock the Modularity facade
        \Unusualify\Modularity\Facades\Modularity::shouldReceive('find')
            ->andReturn(new class extends \Unusualify\Modularity\Module {
                public function __construct() {}
            });

        $h = new StateableHydrate($input, null, null, true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No Route");
        
        $h->render();
    }
}
