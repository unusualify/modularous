<?php

namespace Unusualify\Modularity\Tests\View\Widgets;

use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\View\Widgets\BoardInformationWidget;

class BoardInformationWidgetTest extends TestCase
{
    public function test_widget_can_be_instantiated()
    {
        $widget = new BoardInformationWidget;

        $this->assertInstanceOf(BoardInformationWidget::class, $widget);
        $this->assertEquals('ue-board-information-plus', $widget->tag);
        $this->assertEquals('v-col', $widget->widgetTag);
    }

    public function test_hydrate_attributes_returns_attributes_when_no_cards()
    {
        $widget = new BoardInformationWidget;
        $attributes = ['title' => 'Test'];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('title', $result);
        $this->assertEquals('Test', $result['title']);
    }

    public function test_hydrate_attributes_skips_cards_without_connector()
    {
        $widget = new BoardInformationWidget;
        $attributes = [
            'cards' => [
                ['title' => 'Card without connector'],
            ],
        ];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('cards', $result);
        $this->assertEmpty($result['cards']);
    }

    public function test_hydrate_attributes_skips_non_associative_cards()
    {
        $widget = new BoardInformationWidget;
        $attributes = [
            'cards' => [
                ['value1', 'value2'],
            ],
        ];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('cards', $result);
        $this->assertEmpty($result['cards']);
    }

    public function test_hydrate_attributes_skips_non_array_cards()
    {
        $widget = new BoardInformationWidget;
        $attributes = [
            'cards' => [
                'not-an-array',
            ],
        ];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('cards', $result);
        $this->assertEmpty($result['cards']);
    }

    public function test_render_returns_widget_structure()
    {
        $widget = new BoardInformationWidget;

        $result = $widget->render();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tag', $result);
        $this->assertArrayHasKey('attributes', $result);
        $this->assertArrayHasKey('slots', $result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertEquals('v-col', $result['tag']);
    }

    public function test_default_attributes_include_card_attribute()
    {
        $widget = new BoardInformationWidget;

        $this->assertArrayHasKey('cardAttribute', $widget->attributes);
        $this->assertArrayHasKey('variant', $widget->attributes['cardAttribute']);
        $this->assertEquals('outlined', $widget->attributes['cardAttribute']['variant']);
    }
}
