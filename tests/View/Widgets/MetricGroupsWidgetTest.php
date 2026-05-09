<?php

namespace Unusualify\Modularous\Tests\View\Widgets;

use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\View\Widgets\MetricGroupsWidget;

class MetricGroupsWidgetTest extends TestCase
{
    public function test_widget_can_be_instantiated()
    {
        $widget = new MetricGroupsWidget;

        $this->assertInstanceOf(MetricGroupsWidget::class, $widget);
        $this->assertEquals('ue-metric-groups', $widget->tag);
        $this->assertEquals('v-col', $widget->widgetTag);
    }

    public function test_hydrate_attributes_returns_attributes_when_no_items()
    {
        $widget = new MetricGroupsWidget;
        $attributes = ['title' => 'Test Metrics'];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('title', $result);
        $this->assertEquals('Test Metrics', $result['title']);
    }

    public function test_hydrate_attributes_processes_items_without_connector()
    {
        $widget = new MetricGroupsWidget;
        $attributes = [
            'items' => [
                [
                    'items' => [
                        ['title' => 'Metric without connector', 'value' => 42],
                    ],
                ],
            ],
        ];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
        $this->assertArrayHasKey('items', $result['items'][0]);
        $this->assertEquals('Metric without connector', $result['items'][0]['items'][0]['title']);
        $this->assertEquals(42, $result['items'][0]['items'][0]['value']);
    }

    public function test_render_returns_widget_structure()
    {
        $widget = new MetricGroupsWidget;

        $result = $widget->render();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tag', $result);
        $this->assertArrayHasKey('attributes', $result);
        $this->assertArrayHasKey('slots', $result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertEquals('v-col', $result['tag']);
    }

    public function test_default_attributes_include_metric_attributes()
    {
        $widget = new MetricGroupsWidget;

        $this->assertArrayHasKey('metricColor', $widget->attributes);
        $this->assertArrayHasKey('metricNoInline', $widget->attributes);
        $this->assertEquals('primary', $widget->attributes['metricColor']);
    }

    public function test_hydrate_attributes_handles_empty_group_items()
    {
        $widget = new MetricGroupsWidget;
        $attributes = [
            'items' => [
                ['title' => 'Empty group', 'items' => []],
            ],
        ];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
        $this->assertEmpty($result['items'][0]['items']);
    }
}
