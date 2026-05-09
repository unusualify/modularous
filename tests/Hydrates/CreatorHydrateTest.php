<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\CreatorHydrate;
use Unusualify\Modularous\Tests\TestCase;

class CreatorHydrateTest extends TestCase
{
    public function test_creator_hydrate_instantiation()
    {
        $input = [
            'type' => 'creator',
            'name' => 'created_by',
        ];

        $h = new CreatorHydrate($input, null, null, true);

        $this->assertInstanceOf(CreatorHydrate::class, $h);
    }

    public function test_creator_hydrate_has_requirements()
    {
        $input = [
            'type' => 'creator',
            'name' => 'created_by',
        ];

        $h = new CreatorHydrate($input, null, null, true);

        // CreatorHydrate has specific requirements set
        $this->assertIsArray($h->requirements);
        $this->assertArrayHasKey('itemTitle', $h->requirements);
        $this->assertArrayHasKey('label', $h->requirements);
    }
}
