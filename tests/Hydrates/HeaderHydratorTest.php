<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\HeaderHydrator;
use Unusualify\Modularity\Tests\TestCase;

class HeaderHydratorTest extends TestCase
{
    public function test_switch_formatter_sets_width()
    {
        $header = ['formatter' => ['switch'], 'key' => 'col'];

        $h = new HeaderHydrator($header, null, null);

        $result = $h->hydrate();

        $this->assertArrayHasKey('width', $result);
        $this->assertEquals('20px', $result['width']);
    }

    public function test_actions_defaults_are_set()
    {
        $header = ['key' => 'actions'];

        $h = new HeaderHydrator($header, null, null);

        $result = $h->hydrate();

        $this->assertEquals(100, $result['width']);
        $this->assertEquals('center', $result['align']);
        $this->assertFalse($result['sortable']);
        $this->assertTrue($result['visible']);
    }

    public function test_no_mobile_sets_responsive()
    {
        $header = ['noMobile' => true, 'key' => 'col'];

        $h = new HeaderHydrator($header, null, null);

        $result = $h->hydrate();

        $this->assertIsArray($result['responsive']);
        $this->assertArrayHasKey('hideBelow', $result['responsive']);
        $this->assertEquals('md', $result['responsive']['hideBelow']);
    }
}
