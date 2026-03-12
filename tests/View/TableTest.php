<?php

namespace Unusualify\Modularity\Tests\View;

use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\View\Table;

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

        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    }

    public function test_render_uses_modularity_base_key_when_base_key_not_set()
    {
        $table = new Table(['id'], [['id' => 1]], 'table-name');

        $result = $table->render();

        $this->assertStringContainsString('modularity', $result->name());
    }
}
