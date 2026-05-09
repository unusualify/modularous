<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\AuthorizeHydrate;
use Unusualify\Modularous\Tests\TestCase;

// simple stub model to satisfy ::query()
class AuthorizeStubModel
{
    public static function query()
    {
        return new class
        {
            public function get($cols = ['id', 'name'])
            {
                return [['id' => 1, 'name' => 'Authy']];
            }
        };
    }
}

class AuthorizeHydrateTest extends TestCase
{
    public function test_authorized_type_items_are_set()
    {
        $input = [
            'authorized_type' => AuthorizeStubModel::class,
        ];

        $h = new AuthorizeHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('select', $result['type']);
        $this->assertEquals('authorized_id', $result['name']);
        $this->assertFalse($result['multiple']);
    }

    public function test_skip_queries_returns_empty_items()
    {
        $input = [
            'authorized_type' => AuthorizeStubModel::class,
        ];

        $h = new AuthorizeHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals([], $result['items']);
    }
}
