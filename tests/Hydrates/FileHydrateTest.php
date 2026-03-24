<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\FileHydrate;
use Unusualify\Modularity\Tests\TestCase;

class FileHydrateTest extends TestCase
{
    public function test_file_hydrate_sets_type_and_label()
    {
        $input = [
            'type' => 'file',
            'name' => 'documents',
        ];

        $h = new FileHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-file', $result['type']);
        $this->assertEquals('Files', $result['label']);
        $this->assertEquals([], $result['default']);
    }
}
