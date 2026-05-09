<?php

namespace Unusualify\Modularous\Tests\View\Widgets;

use Unusualify\Modularous\Module;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\View\Widgets\TableWidget;

class TableWidgetTest extends TestCase
{
    public function test_widget_can_be_instantiated()
    {
        $widget = new TableWidget;

        $this->assertInstanceOf(TableWidget::class, $widget);
        $this->assertEquals('ue-table', $widget->tag);
        $this->assertEquals('v-col', $widget->widgetTag);
    }

    public function test_hydrate_attributes_returns_merged_attributes_when_no_route_or_columns()
    {
        $widget = new TableWidget;
        $attributes = ['title' => 'Test Table'];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('title', $result);
        $this->assertEquals('Test Table', $result['title']);
    }

    public function test_default_attributes_include_table_options()
    {
        $widget = new TableWidget;

        $this->assertArrayHasKey('tableOptions', $widget->attributes);
        $this->assertArrayHasKey('itemsPerPage', $widget->attributes['tableOptions']);
        $this->assertEquals(5, $widget->attributes['tableOptions']['itemsPerPage']);
    }

    public function test_hydrate_attributes_merges_with_default_table_attributes()
    {
        $widget = new TableWidget;
        $attributes = ['title' => 'Test Table'];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('striped', $result);
        $this->assertArrayHasKey('roundedRows', $result);
    }

    public function test_hydrate_attributes_sets_endpoints_when_module_and_route_provided()
    {
        $mockModule = \Mockery::mock(Module::class);
        $mockModule->shouldReceive('getRoutePanelUrls')
            ->with('Payment', true, ':id')
            ->once()
            ->andReturn(['index' => '/admin/payments']);

        $widget = new TableWidget;
        $attributes = [
            '_routeName' => 'Payment',
            '_module' => $mockModule,
        ];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('endpoints', $result);
        $this->assertIsArray($result['endpoints']);
    }

    public function test_hydrate_attributes_processes_columns_with_allowable_user()
    {
        $user = \Mockery::mock();
        $user->shouldReceive('isSuperAdmin')->andReturn(true);

        $widget = new TableWidget;
        $widget->setAllowableUser($user);

        $attributes = [
            'columns' => [
                ['name' => 'id', 'key' => 'id', 'title' => 'name'],
                ['name' => 'title', 'key' => 'title', 'title' => 'title'],
            ],
        ];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('columns', $result);
        $this->assertCount(2, $result['columns']);
    }

    public function test_render_returns_widget_structure()
    {
        $widget = new TableWidget;

        $result = $widget->render();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tag', $result);
        $this->assertArrayHasKey('attributes', $result);
        $this->assertArrayHasKey('slots', $result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertEquals('v-col', $result['tag']);
    }
}
