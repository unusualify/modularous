<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\SlugHydrate;
use Unusualify\Modularous\Tests\TestCase;

class SlugHydrateTest extends TestCase
{
    public function test_sets_type_input_slug(): void
    {
        $input = [];

        $h = new SlugHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-slug', $result['type']);
    }
}
