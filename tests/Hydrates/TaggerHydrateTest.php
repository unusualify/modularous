<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\TaggerHydrate;
use Unusualify\Modularity\Tests\TestCase;

class TaggerHydrateTest extends TestCase
{
    public function test_tagger_hydrate_sets_requirements()
    {
        $input = [
            'type' => 'tagger',
            'name' => 'tags',
        ];

        $h = new TaggerHydrate($input, null, null, true);

        $this->assertInstanceOf(TaggerHydrate::class, $h);
        $this->assertEquals('Tags', $h->requirements['label']);
        $this->assertEquals('tags', $h->requirements['name']);
        $this->assertTrue($h->requirements['multiple']);
    }

    public function test_tagger_hydrate_throws_without_module_context()
    {
        $input = [
            'type' => 'tagger',
            'name' => 'tags',
        ];

        $h = new TaggerHydrate($input, null, null, true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid input');

        $h->render();
    }
}
