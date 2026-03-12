<?php

namespace Unusualify\Modularity\Tests\View\Widgets;

use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\View\Widgets\MetricsWidget;

class MetricsWidgetTest extends TestCase
{
    public function test_widget_can_be_instantiated()
    {
        $widget = new MetricsWidget;

        $this->assertInstanceOf(MetricsWidget::class, $widget);
        $this->assertEquals('ue-metrics', $widget->tag);
        $this->assertEquals('v-col', $widget->widgetTag);
    }

    public function test_hydrate_attributes_returns_attributes_when_no_items()
    {
        $widget = new MetricsWidget;
        $attributes = ['title' => 'Test'];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('title', $result);
        $this->assertEquals('Test', $result['title']);
        $this->assertArrayHasKey('endpoint', $result);
    }

    public function test_hydrate_attributes_processes_items_without_connector()
    {
        $widget = new MetricsWidget;
        $attributes = [
            'items' => [
                ['title' => 'Metric', 'value' => 10],
            ],
        ];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('Metric', $result['items'][0]['title']);
        $this->assertEquals(10, $result['items'][0]['value']);
    }

    public function test_hydrate_attributes_executes_callable_value()
    {
        $widget = new MetricsWidget;
        $attributes = [
            'items' => [
                [
                    'title' => 'Callable metric',
                    'value' => fn () => 99,
                ],
            ],
        ];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertEquals(99, $result['items'][0]['value']);
    }

    public function test_hydrate_attributes_sets_endpoint()
    {
        $widget = new MetricsWidget;
        $attributes = ['title' => 'Test'];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('endpoint', $result);
        $this->assertStringContainsString('metrics', $result['endpoint']);
    }

    public function test_render_returns_widget_structure()
    {
        $widget = new MetricsWidget;

        $result = $widget->render();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tag', $result);
        $this->assertArrayHasKey('attributes', $result);
        $this->assertArrayHasKey('slots', $result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertEquals('v-col', $result['tag']);
        $this->assertIsArray($result['elements']);
    }

    public function test_default_attributes_include_metric_attributes()
    {
        $widget = new MetricsWidget;

        $this->assertArrayHasKey('metricAttributes', $widget->attributes);
        $this->assertArrayHasKey('color', $widget->attributes['metricAttributes']);
        $this->assertEquals('primary', $widget->attributes['metricAttributes']['color']);
    }
}
