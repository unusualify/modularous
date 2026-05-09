<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\ImageHydrate;
use Unusualify\Modularous\Tests\TestCase;

class ImageHydrateTest extends TestCase
{
    public function test_image_hydrate_sets_type_and_label()
    {
        $input = [
            'type' => 'image',
            'name' => 'gallery',
        ];

        $h = new ImageHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-image', $result['type']);
        $this->assertEquals('Images', $result['label']);
        $this->assertEquals([], $result['default']);
    }
}
