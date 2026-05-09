<?php

namespace Unusualify\Modularous\Tests\View;

use Illuminate\Contracts\View\View;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\View\Table;

class TableTest extends TestCase
{
    public function test_table_can_be_instantiated()
    {
        $table = new Table(['id', 'name'], [['id' => 1, 'name' => 'Test']], 'test-table');

        $this->assertInstanceOf(Table::class, $table);
        $this->assertEquals(['id', 'name'], $table->headers);
        $this->assertEquals([['id' => 1, 'name' => 'Test']], $table->inputs);
        $this->assertEquals('test-table', $table->name);
    }

    public function test_render_returns_view()
    {
        $table = new Table(['id'], [['id' => 1]], 'table-name');

        $result = $table->render();

        $this->assertInstanceOf(View::class, $result);
    }

    public function test_render_uses_modularous_base_key_when_base_key_not_set()
    {
        $table = new Table(['id'], [['id' => 1]], 'table-name');

        $result = $table->render();

        $this->assertStringContainsString('modularous', $result->name());
    }
}
