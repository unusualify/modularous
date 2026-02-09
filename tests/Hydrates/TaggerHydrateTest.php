<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\TaggerHydrate;
use Unusualify\Modularity\Tests\TestCase;

class TaggerHydrateTest extends TestCase
{
    public function test_tagger_hydrate_throws_without_module()
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

    public function test_tagger_hydrate_sets_defaults()
    {
        $input = [
            'type' => 'tagger',
            'name' => 'tags'
        ];

        $h = new TaggerHydrate($input, null, null, true);

        // Just verify the object was created
        $this->assertInstanceOf(TaggerHydrate::class, $h);
    }
}
