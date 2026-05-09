<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\BrowserHydrate;
use Unusualify\Modularous\Tests\TestCase;

class BrowserHydrateTest extends TestCase
{
    public function test_sets_type_input_browser()
    {
        $input = [];

        $h = new BrowserHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-browser', $result['type']);
    }
}
