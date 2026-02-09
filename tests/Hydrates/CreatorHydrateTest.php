<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\CreatorHydrate;
use Unusualify\Modularity\Tests\TestCase;

class CreatorHydrateTest extends TestCase
{
    public function test_creator_hydrate_can_be_instantiated()
    {
        $input = [
            'type' => 'creator',
            'name' => 'created_by',
        ];

        $h = new CreatorHydrate($input, null, null, true);

        // Just verify the object was created
        $this->assertInstanceOf(CreatorHydrate::class, $h);
    }
}
