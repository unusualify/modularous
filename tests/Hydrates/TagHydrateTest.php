<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\TagHydrate;
use Unusualify\Modularity\Tests\TestCase;
use Illuminate\Support\Collection;

class TagHydrateTest extends TestCase
{
    public function test_tag_hydrate_can_be_instantiated()
    {
        $input = [
            'type' => 'tag',
            'name' => 'tags'
        ];

        $h = new TagHydrate($input, null, null, true);

        // Just verify the object was created
        $this->assertInstanceOf(TagHydrate::class, $h);
    }
}
