<?php

namespace Unusualify\Modularity\Tests\Helpers;

use Illuminate\Support\Facades\Lang;
use Unusualify\Modularity\Hydrates\HeaderHydrator;
use Unusualify\Modularity\Module;
use Unusualify\Modularity\Tests\TestCase;

class ColumnHelpersTest extends TestCase
{
    /** @test */
    public function test_configure_table_columns_transforms_columns_with_hydrator()
    {
        $columns = [
            ['name' => 'id', 'key' => 'id'],
            ['name' => 'name', 'key' => 'name'],
        ];

        $result = configure_table_columns($columns);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        // HeaderHydrator preserves the original column structure
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('name', $result[1]);
    }

    /** @test */
    public function test_configure_table_columns_with_module_and_route()
    {
        // Create a mock module
        $module = \Mockery::mock(Module::class);

        $columns = [
            ['name' => 'id', 'key' => 'id'],
        ];

        $result = configure_table_columns($columns, $module, 'test-route');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    /** @test */
    public function test_configure_table_columns_handles_empty_array()
    {
        $result = configure_table_columns([]);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /** @test */
    public function test_hydrate_table_column_translation_returns_null_when_no_title()
    {
        $column = ['name' => 'id', 'key' => 'id'];

        $result = hydrate_table_column_translation($column);

        $this->assertNull($result);
    }

    /** @test */
    public function test_hydrate_table_column_translation_translates_title()
    {
        Lang::shouldReceive('get')
            ->with('table-headers.name', [], null)
            ->once()
            ->andReturn('Name');

        $column = ['title' => 'name'];

        $result = hydrate_table_column_translation($column);

        $this->assertIsArray($result);
        $this->assertEquals('Name', $result['title']);
    }

    /** @test */
    public function test_hydrate_table_column_translation_keeps_original_when_no_translation()
    {
        Lang::shouldReceive('get')
            ->with('table-headers.custom_field', [], null)
            ->once()
            ->andReturn('table-headers.custom_field'); // Translation key returned as-is

        $column = ['title' => 'custom_field'];

        $result = hydrate_table_column_translation($column);

        $this->assertIsArray($result);
        $this->assertEquals('custom_field', $result['title']);
    }

    /** @test */
    public function test_hydrate_table_column_translation_keeps_original_when_array_translation()
    {
        Lang::shouldReceive('get')
            ->with('table-headers.status', [], null)
            ->once()
            ->andReturn('status'); // Return translation key as-is (not found)

        $column = ['title' => 'status'];

        $result = hydrate_table_column_translation($column);

        $this->assertIsArray($result);
        $this->assertEquals('status', $result['title']); // Original kept
    }

    /** @test */
    public function test_hydrate_table_columns_translations_processes_array_of_columns()
    {
        Lang::shouldReceive('get')
            ->with('table-headers.id', [], null)
            ->once()
            ->andReturn('ID');

        Lang::shouldReceive('get')
            ->with('table-headers.name', [], null)
            ->once()
            ->andReturn('Name');

        $columns = [
            ['title' => 'id'],
            ['title' => 'name'],
        ];

        $result = hydrate_table_columns_translations($columns);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('ID', $result[0]['title']);
        $this->assertEquals('Name', $result[1]['title']);
    }

    /** @test */
    public function test_hydrate_table_columns_translations_handles_empty_array()
    {
        $result = hydrate_table_columns_translations([]);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /** @test */
    public function test_hydrate_table_columns_translations_handles_columns_without_title()
    {
        $columns = [
            ['name' => 'id'],
            ['title' => 'name'],
        ];

        Lang::shouldReceive('get')
            ->with('table-headers.name', [], null)
            ->once()
            ->andReturn('Name');

        $result = hydrate_table_columns_translations($columns);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertNull($result[0]); // No title, returns null
        $this->assertEquals('Name', $result[1]['title']);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
