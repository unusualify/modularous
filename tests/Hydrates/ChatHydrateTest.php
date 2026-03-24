<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\ChatHydrate;
use Unusualify\Modularity\Tests\TestCase;

class ChatHydrateTest extends TestCase
{
    public function test_chat_hydrate_sets_type_and_endpoints()
    {
        $input = [
            'type' => 'chat',
            'name' => 'messages',
        ];

        $h = new ChatHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-chat', $result['type']);
        $this->assertArrayHasKey('endpoints', $result);
        $this->assertArrayHasKey('index', $result['endpoints']);
        $this->assertArrayHasKey('store', $result['endpoints']);
        $this->assertEquals(-1, $result['default']);
        $this->assertEquals('40vh', $result['height']);
        $this->assertEquals('26vh', $result['bodyHeight']);
    }
}
