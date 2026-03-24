<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\FilepondHydrate;
use Unusualify\Modularity\Tests\TestCase;

class FilepondHydrateTest extends TestCase
{
    public function test_filepond_hydrate_sets_type_and_defaults()
    {
        $input = [
            'type' => 'filepond',
            'name' => 'uploads',
        ];

        $h = new FilepondHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-filepond', $result['type']);
        $this->assertFalse($result['credits']);
        $this->assertTrue($result['allowMultiple']);
        $this->assertTrue($result['allowDrop']);
        $this->assertTrue($result['allowRemove']);
        $this->assertFalse($result['allowReorder']);
        $this->assertArrayHasKey('endPoints', $result);
    }
}
